<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $sessionFilter = $request->query('session'); // null|0|1|2|3

        $query = ExamAttempt::where('user_id', Auth::id())
            ->where('status', 'finished');

        if ($sessionFilter !== null && $sessionFilter !== '') {
            $query->where('session_id', (int) $sessionFilter);
        }

        $sort = $request->query('sort', 'terbaru');
        $query->orderBy('finished_at', $sort === 'terlama' ? 'asc' : 'desc');

        $attempts = $query->get();

        $all = ExamAttempt::where('user_id', Auth::id())->where('status', 'finished')->get();

        $recap = [
            'total_percobaan' => $all->count(),
            'nilai_terbaik' => $all->max('raw_score'),
            'rata_rata' => $all->count() ? round($all->avg('raw_score')) : null,
            'total_benar' => $all->sum('correct_count'),
            'total_salah' => $all->sum('wrong_count'),
        ];

        $perSesi = collect(range(0, 3))->map(function ($sid) use ($all) {
            $sessionAttempts = $all->where('session_id', $sid);

            return [
                'session_id' => $sid,
                'jumlah_percobaan' => $sessionAttempts->count(),
                'terbaik' => $sessionAttempts->max('raw_score'),
                'terakhir' => optional($sessionAttempts->sortByDesc('finished_at')->first())->raw_score,
                'selesai' => $sessionAttempts->count() > 0,
            ];
        });

        return view('history.index', [
            'attempts' => $attempts,
            'recap' => $recap,
            'perSesi' => $perSesi,
            'sessionFilter' => $sessionFilter,
            'sort' => $sort,
        ]);
    }

    /**
     * Unduh seluruh riwayat percobaan (semua sesi) sebagai file CSV.
     */
    public function exportCsv(Request $request)
    {
        $attempts = ExamAttempt::where('user_id', Auth::id())
            ->where('status', 'finished')
            ->orderBy('finished_at', 'asc')
            ->get();

        $filename = 'riwayat-ujian-'.now()->format('Y-m-d-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $columns = [
            'No',
            'Sesi',
            'Judul Sesi',
            'Tanggal Selesai',
            'Total Soal',
            'Benar',
            'Salah',
            'Nilai',
            'Waktu Pakai (menit)',
        ];

        $callback = function () use ($attempts, $columns) {
            $file = fopen('php://output', 'w');

            // BOM supaya karakter dibaca benar saat dibuka di Excel.
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach ($attempts as $i => $a) {
                fputcsv($file, [
                    $i + 1,
                    'Sesi '.($a->session_id + 1),
                    $a->session_title,
                    optional($a->finished_at)->format('d-m-Y H:i'),
                    $a->total_questions,
                    $a->correct_count,
                    $a->wrong_count,
                    $a->raw_score,
                    round($a->duration_seconds / 60, 1),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
