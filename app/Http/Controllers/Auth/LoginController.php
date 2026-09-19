<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect($this->homeFor(Auth::user()));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Operator diarahkan ke pemilihan loket; admin ke panel admin.
            return redirect()->intended($this->homeFor(Auth::user()));
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi salah.',
        ])->onlyInput('email');
    }

    /** Tentukan halaman awal sesuai peran. */
    protected function homeFor($user): string
    {
        return $user->role === 'operator'
            ? route('operator.index')
            : route('admin.counters.index');
    }

    public function logout(Request $request)
    {
        // Lepas loket yang sedang ditempati operator ini agar tidak terkunci
        // setelah logout (operator lain langsung bisa memakainya).
        if ($user = $request->user()) {
            Counter::where('occupied_by', $user->id)
                ->update(['occupied_by' => null, 'occupied_at' => null]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
