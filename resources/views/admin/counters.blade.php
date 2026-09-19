@extends('layouts.app')
@section('title', 'Admin — Loket')

@section('body')
<div class="topbar">
    <a href="{{ route('admin.counters.index') }}" class="active">Loket</a>
    <a href="{{ route('admin.services.index') }}">Layanan</a>
    <a href="{{ route('admin.reports.index') }}">Laporan</a>
    <a href="{{ route('display.index') }}" target="_blank">Layar Display</a>
    <a href="{{ route('operator.index') }}" target="_blank">Operator</a>
    <form method="POST" action="{{ route('logout') }}" style="margin-left:auto">
        @csrf
        <button class="btn gray" style="padding:6px 12px">Logout</button>
    </form>
</div>

<div class="wrap">
    <h1>Kelola Loket</h1>
    @if(session('status'))<div class="flash">{{ session('status') }}</div>@endif

    <div class="card">
        <h3>Tambah Loket</h3>
        <form method="POST" action="{{ route('admin.counters.store') }}">
            @csrf
            <div class="row">
                <div class="col"><label>Nama Loket</label><input name="name" required placeholder="Loket 1"></div>
                <div class="col"><label>Nomor</label><input name="number" type="number" min="1" required></div>
                <div class="col"><label>Urutan</label><input name="sort_order" type="number" value="0"></div>
            </div>
            <label>Layanan yang ditangani (loket dinamis)</label>
            <div class="row">
                @foreach($services as $s)
                    <label style="display:flex;gap:6px;align-items:center;min-width:160px;color:var(--text)">
                        <input type="checkbox" name="services[]" value="{{ $s->id }}" style="width:auto"> {{ $s->name }}
                    </label>
                @endforeach
            </div>
            <label style="display:flex;gap:6px;align-items:center;margin-top:10px;color:var(--text)">
                <input type="checkbox" name="is_active" value="1" checked style="width:auto"> Aktif
            </label>
            <button class="btn green" style="margin-top:12px">Simpan</button>
        </form>
    </div>

    <div class="card">
        <h3>Daftar Loket</h3>
        <table>
            <thead><tr><th>Nama</th><th>No.</th><th>Layanan</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($counters as $c)
                <tr>
                    <td>
                        <form method="POST" action="{{ route('admin.counters.update',$c) }}" id="cf{{ $c->id }}">
                            @csrf @method('PUT')
                            <input name="name" value="{{ $c->name }}">
                    </td>
                    <td><input name="number" type="number" value="{{ $c->number }}" style="width:70px"></td>
                    <td>
                        @foreach($services as $s)
                            <label style="display:inline-flex;gap:4px;align-items:center;margin-right:8px;font-size:12px;color:var(--text)">
                                <input type="checkbox" name="services[]" value="{{ $s->id }}" style="width:auto"
                                    {{ $c->services->contains($s->id) ? 'checked' : '' }}> {{ $s->prefix }}
                            </label>
                        @endforeach
                    </td>
                    <td>
                        <label style="display:flex;gap:4px;align-items:center;color:var(--text)">
                            <input type="checkbox" name="is_active" value="1" style="width:auto" {{ $c->is_active ? 'checked' : '' }}> Aktif
                        </label>
                    </td>
                    <td style="white-space:nowrap">
                            <button class="btn" style="padding:6px 10px">Update</button>
                        </form>
                        <form method="POST" action="{{ route('admin.counters.destroy',$c) }}" style="display:inline"
                              onsubmit="return confirm('Hapus loket ini?')">
                            @csrf @method('DELETE')
                            <button class="btn red" style="padding:6px 10px">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada loket.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
