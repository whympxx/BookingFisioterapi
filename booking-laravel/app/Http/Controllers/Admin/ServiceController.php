<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public const ICONS = [
        'fa-person-walking' => 'Berjalan', 'fa-heart-pulse' => 'Detak jantung', 'fa-hands' => 'Tangan',
        'fa-bone' => 'Tulang', 'fa-brain' => 'Otak', 'fa-child' => 'Anak', 'fa-dumbbell' => 'Latihan',
        'fa-wheelchair' => 'Kursi roda', 'fa-spa' => 'Relaksasi', 'fa-user-doctor' => 'Dokter',
    ];

    public function index(): View
    {
        return view('admin.services.index', [
            'services' => Service::withCount('bookings')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.form', ['service' => new Service(['is_active' => true]), 'icons' => self::ICONS]);
    }

    public function store(Request $request): RedirectResponse
    {
        $service = new Service($this->validated($request));
        $service->slug = $this->uniqueSlug($service->name);
        $service->image = $this->storeImage($request) ?? 'ikon_fisioterapi.jpg';
        $service->save();

        return redirect()->route('admin.services.index')->with('success', "Layanan {$service->name} ditambahkan.");
    }

    public function edit(Service $service): View
    {
        return view('admin.services.form', ['service' => $service, 'icons' => self::ICONS]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $service->fill($this->validated($request, $service));
        if ($image = $this->storeImage($request)) {
            $service->image = $image;
        }
        $service->save();

        return redirect()->route('admin.services.index')->with('success', "Layanan {$service->name} diperbarui.");
    }

    public function toggle(Service $service): RedirectResponse
    {
        $service->update(['is_active' => ! $service->is_active]);

        return back()->with('success', "Layanan {$service->name} ".($service->is_active ? 'diaktifkan' : 'dinonaktifkan').'.');
    }

    private function validated(Request $request, ?Service $service = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('services', 'name')->ignore($service?->id)],
            'description' => ['required', 'string', 'max:500'],
            'features' => ['required', 'string', 'max:1000'],
            'duration' => ['required', 'string', 'max:50'],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'icon' => ['required', Rule::in(array_keys(self::ICONS))],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($data['image']);
        $data['features'] = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $data['features']))));
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_popular'] = $request->boolean('is_popular');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $name = 'service_'.time().'_'.Str::random(5).'.'.$request->file('image')->extension();
        $request->file('image')->move(public_path('uploads/services'), $name);

        return $name;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while (Service::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
