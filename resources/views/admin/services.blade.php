@extends('layouts.app')
@section('title', 'Admin — Layanan')

@section('body')
<div class="topbar">
    <a href="{{ route('admin.counters.index') }}">Loket</a>
    <a href="{{ route('admin.services.index') }}" class="active">Layanan</a>
    <a href="{{ route('admin.reports.index') }}">Laporan</a>
    <a href="{{ route('display.index') }}" target="_blank">Layar Display</a>
    <a href="{{ route('operator.index') }}" target="_blank">Operator</a>
    <form method="POST" action="{{ route('logout') }}" style="margin-left:auto">
        @csrf
        <button class="btn gray" style="padding:6px 12px">Logout</button>
    </form>
</div>

<div class="wrap">
    <h1>Kelola Layanan</h1>
    @if(session('status'))<div class="flash">{{ session('status') }}</div>@endif

    <div class="card">
        <h3>Tambah Layanan</h3>
        <form method="POST" action="{{ route('admin.services.store') }}">
            @csrf
            <div class="row">
                <div class="col"><label>Nama Layanan</label><input name="name" required placeholder="Layanan Umum"></div>
                <div class="col"><label>Awalan (prefix)</label><input name="prefix" maxlength="5" required placeholder="A"></div>
                <div class="col"><label>Urutan</label><input name="sort_order" type="number" value="0"></div>
            </div>
            <label>Deskripsi</label><input name="description" placeholder="Pendaftaran & informasi">
            <label style="display:flex;gap:6px;align-items:center;margin-top:10px;color:var(--text)">
                <input type="checkbox" name="is_active" value="1" checked style="width:auto"> Aktif
            </label>
            <button class="btn green" style="margin-top:12px">Simpan</button>
        </form>
    </div>

    <div class="card">
        <h3>Daftar Layanan</h3>
        <table>
            <thead><tr><th>Nama</th><th>Prefix</th><th>Deskripsi</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($services as $s)
                <tr>
                    <td>
                        <form method="POST" action="{{ route('admin.services.update',$s) }}">
                            @csrf @method('PUT')
                            <input name="name" value="{{ $s->name }}">
                    </td>
                    <td><input name="prefix" value="{{ $s->prefix }}" maxlength="5" style="width:70px"></td>
                    <td><input name="description" value="{{ $s->description }}"></td>
                    <td>
                        <label style="display:flex;gap:4px;align-items:center;color:var(--text)">
                            <input type="checkbox" name="is_active" value="1" style="width:auto" {{ $s->is_active ? 'checked' : '' }}> Aktif
                        </label>
                    </td>
                    <td style="white-space:nowrap">
                            <button class="btn" style="padding:6px 10px">Update</button>
                        </form>
                        <form method="POST" action="{{ route('admin.services.destroy',$s) }}" style="display:inline"
                              onsubmit="return confirm('Hapus layanan ini?')">
                            @csrf @method('DELETE')
                            <button class="btn red" style="padding:6px 10px">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada layanan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
