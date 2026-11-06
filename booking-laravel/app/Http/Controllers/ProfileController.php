<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'totalBooking' => $user->bookings()->count(),
            'totalSelesai' => $user->bookings()->where('status', 'Selesai')->count(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'nama_lengkap' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp' => ['nullable', 'regex:/^08[0-9]{8,11}$/'],
        ], [
            'no_hp.regex' => 'Format nomor HP tidak valid. Gunakan format 08xxxxxxxxxx.',
        ]);

        $user->update($data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'profile_pic' => ['required', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
        ], [
            'profile_pic.*' => 'File tidak valid (hanya jpg, jpeg, png, gif, maksimal 2MB).',
        ]);

        $user = $request->user();
        $file = $request->file('profile_pic');
        $name = 'user'.$user->id.'_'.time().'.'.$file->extension();
        $file->move(public_path('uploads'), $name);

        // Hapus foto lama supaya folder uploads tidak menumpuk.
        if ($user->profile_pic && is_file(public_path('uploads/'.$user->profile_pic))) {
            @unlink(public_path('uploads/'.$user->profile_pic));
        }

        $user->update(['profile_pic' => $name]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function editPassword(): View
    {
        return view('auth.change-password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password_lama' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(6), 'different:password_lama'],
        ], [
            'password_lama.current_password' => 'Password lama salah.',
            'password.different' => 'Password baru harus berbeda dari password lama.',
        ]);

        $request->user()->update(['password' => $request->input('password')]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
