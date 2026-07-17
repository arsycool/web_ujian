<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Login sederhana: NIP dipakai sebagai identitas unik.
     * - Jika NIP sudah pernah dipakai, PIN harus cocok (login).
     * - Jika NIP baru, otomatis membuat akun baru (self-register) dengan PIN tsb.
     * Ini meniru perilaku versi statis sebelumnya yang tidak punya proses
     * pendaftaran terpisah — cukup "Masuk & Mulai Ujian".
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'nip' => ['required', 'string', 'max:50'],
            'pin' => ['required', 'string', 'min:4', 'max:50'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nip.required' => 'NIP / Nomor Peserta wajib diisi.',
            'pin.required' => 'PIN / kata sandi wajib diisi.',
            'pin.min' => 'PIN minimal 4 karakter.',
        ]);

        $user = User::where('nip', $validated['nip'])->first();

        if ($user) {
            if (! Hash::check($validated['pin'], $user->password)) {
                return back()->withErrors([
                    'pin' => 'NIP sudah terdaftar tetapi PIN tidak cocok.',
                ])->withInput();
            }
        } else {
            $user = User::create([
                'name' => $validated['name'],
                'nip' => $validated['nip'],
                'password' => Hash::make($validated['pin']),
                'is_guest' => false,
            ]);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * Masuk sebagai tamu: dibuatkan akun sementara agar tetap bisa
     * memakai fitur simpan progres/DB, tapi ditandai is_guest.
     */
    public function guest(Request $request)
    {
        $user = User::create([
            'name' => 'Tamu',
            'nip' => 'GUEST-'.strtoupper(Str::random(8)),
            'password' => Hash::make(Str::random(12)),
            'is_guest' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
