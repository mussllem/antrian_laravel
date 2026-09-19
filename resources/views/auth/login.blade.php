@extends('layouts.app')
@section('title', 'Login Admin')

@section('body')
<div class="wrap" style="max-width:420px;margin-top:8vh">
    <h1 style="text-align:center">Login Admin</h1>

    <div class="card">
        @if($errors->any())
            <div class="flash" style="background:var(--pill-off);border-color:var(--danger)">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>

            <label>Kata Sandi</label>
            <input type="password" name="password" required>

            <label style="display:flex;gap:6px;align-items:center;margin-top:12px;color:var(--text)">
                <input type="checkbox" name="remember" value="1" style="width:auto"> Ingat saya
            </label>

            <button class="btn green" style="width:100%;margin-top:16px">Masuk</button>
        </form>

        <p style="color:var(--muted);font-size:13px;margin-top:14px;text-align:center">
            Default (dari seeder): <b>admin@antrian.test</b> / <b>password</b>
        </p>
    </div>
</div>
@endsection
