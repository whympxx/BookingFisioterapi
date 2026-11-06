<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class TherapistController extends Controller
{
    public function index(): View
    {
        return view('admin.therapists.index', [
            'therapists' => Therapist::with('user')->withCount('bookings')->withAvg('bookings as rating_avg', 'rating')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.therapists.form', ['therapist' => new Therapist(['is_active' => true, 'experience_years' => 1])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $account = $this->validatedAccount($request);

        $therapist = new Therapist($data);
        $therapist->photo = $this->storePhoto($request);
        DB::transaction(function () use ($therapist, $account) {
            $therapist->save();
            $this->syncAccount($therapist, $account);
        });

        return redirect()->route('admin.therapists.index')->with('success', "Fisioterapis {$therapist->name} ditambahkan.");
    }

    public function edit(Therapist $therapist): View
    {
        return view('admin.therapists.form', compact('therapist'));
    }

    public function update(Request $request, Therapist $therapist): RedirectResponse
    {
        $therapist->fill($this->validated($request));
        $account = $this->validatedAccount($request, $therapist);
        if ($photo = $this->storePhoto($request)) {
            $therapist->photo = $photo;
        }
        DB::transaction(function () use ($therapist, $account) {
            $therapist->save();
            $this->syncAccount($therapist, $account);
        });

        return redirect()->route('admin.therapists.index')->with('success', "Data {$therapist->name} diperbarui.");
    }

    public function toggle(Therapist $therapist): RedirectResponse
    {
        $therapist->update(['is_active' => ! $therapist->is_active]);

        return back()->with('success', "{$therapist->name} ".($therapist->is_active ? 'diaktifkan' : 'dinonaktifkan').'.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'specialization' => ['required', 'string', 'max:100'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'bio' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($data['photo']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /** Akun login untuk halaman khusus terapis (opsional). */
    private function validatedAccount(Request $request, ?Therapist $therapist = null): ?array
    {
        $user = $therapist?->user;
        if (! $request->filled('username') && ! $user) {
            return null;
        }

        return $request->validate([
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('users', 'username')->ignore($user?->id)],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', Password::min(6)],
        ], ['password.required' => 'Isi password untuk akun login terapis.']);
    }

    private function syncAccount(Therapist $therapist, ?array $account): void
    {
        if (! $account) {
            return;
        }

        $user = $therapist->user ?? new User();
        $user->fill(['username' => $account['username'], 'email' => $account['email'], 'nama_lengkap' => $therapist->name]);
        if (! empty($account['password'])) {
            $user->password = $account['password'];
        }
        $user->role = 'terapis';
        $user->save();

        if ($therapist->user_id !== $user->id) {
            $therapist->user_id = $user->id;
            $therapist->save();
        }
    }

    private function storePhoto(Request $request): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        $name = 'therapist_'.time().'_'.Str::random(5).'.'.$request->file('photo')->extension();
        $request->file('photo')->move(public_path('uploads/therapists'), $name);

        return $name;
    }
}
