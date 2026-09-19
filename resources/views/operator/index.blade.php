@extends('layouts.app')
@section('title', 'Pilih Loket')

@section('body')
<div class="wrap">
    <div class="row" style="justify-content:space-between;align-items:center">
        <h1>Pilih Loket Anda</h1>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <span class="pill">{{ auth()->user()->name }}</span>
            <button class="btn gray" style="padding:6px 12px">Logout</button>
        </form>
    </div>

    @if($errors->any())
        <div class="flash" style="background:var(--pill-off);border-color:var(--danger)">
            {{ $errors->first() }}
        </div>
    @endif
    @if(session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    @if($myCounter)
        <div class="card" style="border-color:var(--accent)">
            Anda sedang menempati <b>{{ $myCounter->name }}</b>.
            <a class="btn green" href="{{ route('operator.show', $myCounter) }}" style="padding:6px 12px;margin-left:8px">Lanjut ke Panel</a>
            <form method="POST" action="{{ route('operator.release', $myCounter) }}" style="display:inline">
                @csrf
                <button class="btn red" style="padding:6px 12px">Lepas Loket</button>
            </form>
        </div>
    @endif

    <div class="row">
        @forelse($counters as $counter)
            @php
                $mine       = $counter->occupied_by === $userId;
                $byOther    = $counter->occupied_by !== null && ! $mine;
            @endphp
            <div class="card" style="min-width:220px;margin:8px;text-align:center
                {{ $byOther ? ';opacity:.6' : '' }}">
                <div style="font-size:18px;font-weight:700">{{ $counter->name }}</div>
                <div class="muted" style="color:var(--muted)">Nomor {{ $counter->number }}</div>

                @if($byOther)
                    <div class="pill off" style="margin:10px 0">
                        Dipakai: {{ $counter->occupant?->name ?? 'operator lain' }}
                    </div>
                    <button class="btn gray" disabled style="width:100%">Tidak tersedia</button>
                @elseif($mine)
                    <div class="pill on" style="margin:10px 0">Loket Anda</div>
                    <a class="btn green" href="{{ route('operator.show', $counter) }}" style="width:100%">Buka Panel</a>
                @else
                    <div class="pill on" style="margin:10px 0">Tersedia</div>
                    <form method="POST" action="{{ route('operator.claim', $counter) }}">
                        @csrf
                        <button class="btn" style="width:100%">Pilih Loket Ini</button>
                    </form>
                @endif
            </div>
        @empty
            <p>Belum ada loket aktif. Silakan buat di panel admin.</p>
        @endforelse
    </div>
</div>
@endsection
