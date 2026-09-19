<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use App\Models\Service;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /** Halaman laporan dengan filter rentang tanggal. */
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);

        $base = Ticket::whereBetween('queue_date', [$from, $to]);

        // Ringkasan status
        $summary = [
            'total'   => (clone $base)->count(),
            'done'    => (clone $base)->where('status', 'done')->count(),
            'skipped' => (clone $base)->where('status', 'skipped')->count(),
            'waiting' => (clone $base)->where('status', 'waiting')->count(),
            'serving' => (clone $base)->whereIn('status', ['called', 'serving'])->count(),
        ];

        // Per layanan
        $perService = (clone $base)
            ->select('service_id', DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status='done' THEN 1 ELSE 0 END) as done"),
                DB::raw("SUM(CASE WHEN status='skipped' THEN 1 ELSE 0 END) as skipped"))
            ->groupBy('service_id')
            ->get()
            ->map(function ($row) {
                $row->service = Service::find($row->service_id)?->name ?? '-';
                return $row;
            });

        // Per loket
        $perCounter = (clone $base)->whereNotNull('counter_id')
            ->select('counter_id', DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status='done' THEN 1 ELSE 0 END) as done"))
            ->groupBy('counter_id')
            ->get()
            ->map(function ($row) {
                $row->counter = Counter::find($row->counter_id)?->name ?? '-';
                return $row;
            });

        // Rata-rata waktu layanan (menit) untuk tiket 'done'
        $avgServe = (clone $base)->where('status', 'done')
            ->whereNotNull('called_at')->whereNotNull('finished_at')
            ->get()
            ->map(fn ($t) => $t->called_at->diffInSeconds($t->finished_at))
            ->avg();
        $avgServeMinutes = $avgServe ? round($avgServe / 60, 1) : null;

        // Tren harian
        $daily = (clone $base)
            ->select('queue_date', DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status='done' THEN 1 ELSE 0 END) as done"))
            ->groupBy('queue_date')
            ->orderBy('queue_date')
            ->get();

        return view('admin.reports', compact(
            'from', 'to', 'summary', 'perService', 'perCounter', 'avgServeMinutes', 'daily'
        ));
    }

    /** Ekspor CSV detail tiket pada rentang tanggal. */
    public function exportCsv(Request $request)
    {
        [$from, $to] = $this->range($request);

        $tickets = Ticket::with(['service', 'counter'])
            ->whereBetween('queue_date', [$from, $to])
            ->orderBy('queue_date')->orderBy('id')
            ->get();

        $filename = "laporan-antrian_{$from}_sd_{$to}.csv";

        return response()->streamDownload(function () use ($tickets) {
            $out = fopen('php://output', 'w');
            // BOM agar Excel membaca UTF-8 dengan benar
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tanggal', 'Kode', 'Layanan', 'Loket', 'Status', 'Dipanggil', 'Selesai', 'Durasi (menit)']);

            foreach ($tickets as $t) {
                $durasi = ($t->called_at && $t->finished_at)
                    ? round($t->called_at->diffInSeconds($t->finished_at) / 60, 1)
                    : '';
                fputcsv($out, [
                    $t->queue_date->format('Y-m-d'),
                    $t->code,
                    $t->service?->name,
                    $t->counter?->name,
                    $t->status,
                    optional($t->called_at)->format('Y-m-d H:i:s'),
                    optional($t->finished_at)->format('Y-m-d H:i:s'),
                    $durasi,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Ambil rentang tanggal dari request (default: hari ini). */
    protected function range(Request $request): array
    {
        $from = $request->input('from', now()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        // Jaga urutan
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }
}
