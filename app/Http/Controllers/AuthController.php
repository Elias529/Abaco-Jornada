<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Escribe el correo.',
            'email.email' => 'El correo no es válido.',
            'password.required' => 'Escribe la contraseña.',
        ]);

        $persona = User::query()->where('email', $datos['email'])->first();

        if ($persona && ! $persona->active) {
            return back()->with('aviso', 'Tu acceso está cerrado. Tus registros se conservan.')->onlyInput('email');
        }

        if (! Auth::attempt(['email' => $datos['email'], 'password' => $datos['password']], false)) {
            return back()->with('aviso', 'Esos datos no coinciden.')->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route($request->user()->esJefe() ? 'jefe.index' : 'jornada');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
