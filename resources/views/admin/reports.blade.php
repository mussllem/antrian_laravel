@extends('layouts.app')
@section('title', 'Admin — Laporan')

@section('body')
<div class="topbar">
    <a href="{{ route('admin.counters.index') }}">Loket</a>
    <a href="{{ route('admin.services.index') }}">Layanan</a>
    <a href="{{ route('admin.reports.index') }}" class="active">Laporan</a>
    <a href="{{ route('display.index') }}" target="_blank">Layar Display</a>
    <form method="POST" action="{{ route('logout') }}" style="margin-left:auto">
        @csrf
        <button class="btn gray" style="padding:6px 12px">Logout</button>
    </form>
</div>

<div class="wrap">
    <h1>Laporan Antrian</h1>

    <div class="card">
        <form method="GET" action="{{ route('admin.reports.index') }}">
            <div class="row" style="align-items:flex-end">
                <div class="col"><label>Dari tanggal</label>
                    <input type="date" name="from" value="{{ $from }}"></div>
                <div class="col"><label>Sampai tanggal</label>
                    <input type="date" name="to" value="{{ $to }}"></div>
                <div class="col" style="max-width:160px">
                    <button class="btn" style="width:100%">Tampilkan</button>
                </div>
                <div class="col" style="max-width:200px">
                    <a class="btn green" style="width:100%;text-align:center"
                       href="{{ route('admin.reports.export', ['from' => $from, 'to' => $to]) }}">Ekspor CSV</a>
                </div>
            </div>
        </form>
    </div>

    <div class="row">
        @php
            $cards = [
                ['Total Tiket', $summary['total'], 'var(--accent2)'],
                ['Selesai', $summary['done'], 'var(--accent)'],
                ['Dilewati', $summary['skipped'], 'var(--warn)'],
                ['Menunggu', $summary['waiting'], '#64748b'],
                ['Sedang dilayani', $summary['serving'], '#a855f7'],
            ];
        @endphp
        @foreach($cards as [$label, $val, $color])
            <div class="card" style="flex:1;min-width:150px;text-align:center">
                <div style="color:var(--muted);font-size:13px">{{ $label }}</div>
                <div style="font-size:34px;font-weight:800;color:{{ $color }}">
                    {{ number_format($val, 0, ',', '.') }}
                </div>
            </div>
        @endforeach
    </div>

    @if(! is_null($avgServeMinutes))
        <div class="card">
            Rata-rata waktu layanan (tiket selesai):
            <b>{{ number_format($avgServeMinutes, 1, ',', '.') }} menit</b>
        </div>
    @endif

    <div class="row">
        <div class="card" style="flex:1;min-width:320px">
            <h3>Per Layanan</h3>
            <table>
                <thead><tr><th>Layanan</th><th style="text-align:right">Total</th><th style="text-align:right">Selesai</th><th style="text-align:right">Dilewati</th></tr></thead>
                <tbody>
                @forelse($perService as $r)
                    <tr>
                        <td>{{ $r->service }}</td>
                        <td style="text-align:right">{{ number_format($r->total, 0, ',', '.') }}</td>
                        <td style="text-align:right">{{ number_format($r->done, 0, ',', '.') }}</td>
                        <td style="text-align:right">{{ number_format($r->skipped, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Tidak ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card" style="flex:1;min-width:320px">
            <h3>Per Loket</h3>
            <table>
                <thead><tr><th>Loket</th><th style="text-align:right">Total</th><th style="text-align:right">Selesai</th></tr></thead>
                <tbody>
                @forelse($perCounter as $r)
                    <tr>
                        <td>{{ $r->counter }}</td>
                        <td style="text-align:right">{{ number_format($r->total, 0, ',', '.') }}</td>
                        <td style="text-align:right">{{ number_format($r->done, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Tidak ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h3>Tren Harian</h3>
        <table>
            <thead><tr><th>Tanggal</th><th style="text-align:right">Total</th><th style="text-align:right">Selesai</th></tr></thead>
            <tbody>
            @forelse($daily as $d)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($d->queue_date)->format('d/m/Y') }}</td>
                    <td style="text-align:right">{{ number_format($d->total, 0, ',', '.') }}</td>
                    <td style="text-align:right">{{ number_format($d->done, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Tidak ada data pada rentang ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
