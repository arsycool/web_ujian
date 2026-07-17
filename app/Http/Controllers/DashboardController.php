<?php

namespace App\Http\Controllers;

use App\Data\QuestionBank;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sessions = QuestionBank::sessions();

        // Ambil attempt TERBAIK (finished) per sesi milik user.
        $bestAttempts = ExamAttempt::where('user_id', $user->id)
            ->where('status', 'finished')
            ->orderByDesc('raw_score')
            ->get()
            ->groupBy('session_id');

        // Ambil attempt TERAKHIR (finished) per sesi.
        $latestAttempts = ExamAttempt::where('user_id', $user->id)
            ->where('status', 'finished')
            ->orderByDesc('finished_at')
            ->get()
            ->groupBy('session_id');

        $sessionsView = collect($sessions)->map(function ($session) use ($bestAttempts, $latestAttempts) {
            $id = $session['id'];
            $best = optional($bestAttempts->get($id))->first();
            $latest = optional($latestAttempts->get($id))->first();

            return [
                'id' => $id,
                'title' => $session['title'],
                'desc' => $session['desc'],
                'topics' => $session['topicsList'],
                'total_questions' => count($session['questions']),
                'done' => (bool) $latest,
                'best_score' => $best->raw_score ?? null,
                'latest_score' => $latest->raw_score ?? null,
                'correct' => $latest->correct_count ?? null,
                'wrong' => $latest->wrong_count ?? null,
            ];
        });

        $doneCount = $sessionsView->where('done', true)->count();
        $totalSessions = $sessionsView->count();
        $avgScore = $sessionsView->pluck('latest_score')->filter(fn ($v) => ! is_null($v));
        $avgScoreVal = $avgScore->count() ? round($avgScore->avg()) : 0;
        $totalBenar = $sessionsView->pluck('correct')->filter(fn ($v) => ! is_null($v))->sum();
        $totalSalah = $sessionsView->pluck('wrong')->filter(fn ($v) => ! is_null($v))->sum();
        $totalJawab = $totalBenar + $totalSalah;
        $akurasi = $totalJawab ? round(($totalBenar / $totalJawab) * 100) : 0;

        return view('dashboard', [
            'user' => $user,
            'sessions' => $sessionsView,
            'doneCount' => $doneCount,
            'totalSessions' => $totalSessions,
            'avgScore' => $avgScoreVal,
            'totalBenar' => $totalBenar,
            'totalSalah' => $totalSalah,
            'akurasi' => $akurasi,
            'allDone' => $doneCount === $totalSessions,
        ]);
    }

    public function resetAll()
    {
        ExamAttempt::where('user_id', Auth::id())->delete();

        return redirect()->route('dashboard')->with('status', 'Semua progres, nilai, dan riwayat telah direset.');
    }
}
