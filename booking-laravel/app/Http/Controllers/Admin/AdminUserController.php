<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Admin baru hanya bisa dibuat oleh admin yang sudah login.
 * Versi lama membuka halaman registrasi admin untuk publik.
 */
class AdminUserController extends Controller
{
    public function create(): View
    {
        return view('admin.create-admin', [
            'admins' => User::where('role', 'admin')->orderBy('username')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $admin = new User($data);
        $admin->role = 'admin';
        $admin->save();

        return back()->with('success', "Admin {$admin->username} berhasil dibuat.");
    }
}
