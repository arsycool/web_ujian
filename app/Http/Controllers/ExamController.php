<?php

namespace App\Http\Controllers;

use App\Data\QuestionBank;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ExamController extends Controller
{
    const DURATION_SECONDS = 45 * 60;

    public function show(int $sessionId)
    {
        $session = QuestionBank::session($sessionId);
        abort_if(! $session, 404);

        $user = Auth::user();

        // Jika sesi ini sudah pernah diselesaikan, tampilkan hasil terakhirnya
        // (terkunci) — tidak otomatis membuat attempt baru. Untuk mengulang,
        // pengguna harus menekan tombol "Ulangi Sesi" (route exam.reset).
        $finishedAttempt = ExamAttempt::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->where('status', 'finished')
            ->latest('finished_at')
            ->first();

        if ($finishedAttempt) {
            $sessionStatus = collect(QuestionBank::sessions())->mapWithKeys(function ($s) use ($user) {
                $done = ExamAttempt::where('user_id', $user->id)
                    ->where('session_id', $s['id'])
                    ->where('status', 'finished')
                    ->exists();

                return [$s['id'] => $done];
            });

            return view('exam.locked', [
                'user' => $user,
                'session' => $session,
                'attempt' => $finishedAttempt,
                'sessionStatus' => $sessionStatus,
            ]);
        }

        // Attempt yang sedang berjalan (belum finished) untuk sesi ini.
        // PENTING: isi semua kolom numerik secara eksplisit di sini. Kalau
        // hanya mengandalkan default kolom di database, objek $attempt yang
        // baru dibuat di request ini tidak otomatis tahu nilai default
        // tersebut (tetap null di PHP) — dan itu bikin current_index null
        // dikirim ke Blade sebagai string kosong, merusak JS di halaman.
        $attempt = ExamAttempt::firstOrCreate(
            [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'status' => 'in_progress',
            ],
            [
                'session_title' => $session['title'],
                'total_questions' => count($session['questions']),
                'current_index' => 0,
                'score' => 0,
                'correct_count' => 0,
                'wrong_count' => 0,
                'raw_score' => 0,
                'duration_seconds' => 0,
                'answers' => [],
            ]
        );

        // Status semua sesi (selesai / belum) untuk sidebar pemilih sesi.
        $sessionStatus = collect(QuestionBank::sessions())->mapWithKeys(function ($s) use ($user) {
            $done = ExamAttempt::where('user_id', $user->id)
                ->where('session_id', $s['id'])
                ->where('status', 'finished')
                ->exists();

            return [$s['id'] => $done];
        });

        // Data soal yang aman dikirim ke frontend (tanpa kunci jawaban),
        // dihitung di sini (bukan langsung di Blade) supaya @json() tidak
        // salah memecah ekspresi yang mengandung banyak koma.
        $questionsForJs = array_map(function ($q) {
            return [
                'topic' => $q['topic'],
                'text' => $q['text'],
                'opts' => $q['opts'],
            ];
        }, $session['questions']);

        return view('exam.show', [
            'user' => $user,
            'session' => $session,
            'attempt' => $attempt,
            'sessionStatus' => $sessionStatus,
            'durationSeconds' => self::DURATION_SECONDS,
            'questionsForJs' => $questionsForJs,
        ]);
    }

    /**
     * Simpan satu jawaban (dipanggil via fetch/AJAX saat klik "Koreksi Jawaban").
     * Menilai kebenaran jawaban di server (berdasarkan QuestionBank), lalu
     * mengembalikan status benar/salah + pembahasan supaya frontend bisa
     * menampilkannya, tanpa client perlu tahu kunci jawaban sesi lain.
     */
    public function answer(Request $request, int $sessionId)
    {
        $data = $request->validate([
            'question_index' => ['required', 'integer', 'min:0'],
            'selected' => ['required', 'integer', 'min:0'],
        ]);

        $session = QuestionBank::session($sessionId);
        abort_if(! $session, 404);

        $questions = $session['questions'];
        abort_if(! isset($questions[$data['question_index']]), 404);

        $question = $questions[$data['question_index']];
        $isCorrect = (int) $data['selected'] === (int) $question['ans'];

        $attempt = ExamAttempt::where('user_id', Auth::id())
            ->where('session_id', $sessionId)
            ->where('status', 'in_progress')
            ->firstOrFail();

        $answers = collect($attempt->answers ?? []);
        $answers = $answers->reject(fn ($a) => $a['q'] === $data['question_index'])->values();
        $answers->push([
            'q' => $data['question_index'],
            'selected' => (int) $data['selected'],
            'correct' => $isCorrect,
        ]);

        $attempt->update([
            'answers' => $answers->all(),
            'correct_count' => $answers->where('correct', true)->count(),
            'wrong_count' => $answers->where('correct', false)->count(),
            'current_index' => max($attempt->current_index, $data['question_index']),
        ]);

        return response()->json([
            'correct' => $isCorrect,
            'correct_index' => (int) $question['ans'],
            'explanation' => $question['exp'],
        ]);
    }

    /** Simpan posisi soal saat ini (dipanggil saat klik "Lanjut"). */
    public function progress(Request $request, int $sessionId)
    {
        $data = $request->validate([
            'current_index' => ['required', 'integer', 'min:0'],
        ]);

        ExamAttempt::where('user_id', Auth::id())
            ->where('session_id', $sessionId)
            ->where('status', 'in_progress')
            ->update(['current_index' => $data['current_index']]);

        return response()->noContent();
    }

    /** Selesaikan sesi ujian & simpan ke riwayat. */
    public function finish(Request $request, int $sessionId)
    {
        $data = $request->validate([
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
        ]);

        $session = QuestionBank::session($sessionId);
        abort_if(! $session, 404);

        $attempt = ExamAttempt::where('user_id', Auth::id())
            ->where('session_id', $sessionId)
            ->where('status', 'in_progress')
            ->firstOrFail();

        $total = count($session['questions']);
        $correct = $attempt->correct_count;
        $rawScore = $total ? (int) round(($correct / $total) * 100) : 0;

        $attempt->update([
            'status' => 'finished',
            'score' => $correct,
            'total_questions' => $total,
            'raw_score' => $rawScore,
            'duration_seconds' => $data['duration_seconds'] ?? (self::DURATION_SECONDS - 0),
            'finished_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['redirect' => route('exam.show', $sessionId)]);
        }

        return redirect()->route('exam.show', $sessionId)->with('status', 'Sesi selesai dan tersimpan.');
    }

    /** Ulangi satu sesi tertentu dari awal (hapus attempt sesi itu). */
    public function reset(int $sessionId)
    {
        ExamAttempt::where('user_id', Auth::id())
            ->where('session_id', $sessionId)
            ->delete();

        return redirect()->route('exam.show', $sessionId);
    }
}
