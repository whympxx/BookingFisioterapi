<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MedicineController extends Controller
{
    public function index(): View
    {
        return view('admin.medicines.index', [
            'medicines' => Medicine::withCount('bookings')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.medicines.form', ['medicine' => new Medicine(['is_active' => true, 'golongan' => 'bebas', 'icon' => 'fa-tablets'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $medicine = new Medicine($this->validated($request));
        $medicine->slug = $this->uniqueSlug($medicine->name);
        $medicine->image = $this->storeImage($request);
        $medicine->save();

        return redirect()->route('admin.medicines.index')->with('success', "{$medicine->name} ditambahkan.");
    }

    public function edit(Medicine $medicine): View
    {
        return view('admin.medicines.form', compact('medicine'));
    }

    public function update(Request $request, Medicine $medicine): RedirectResponse
    {
        $medicine->fill($this->validated($request, $medicine));
        if ($image = $this->storeImage($request)) {
            // Hapus foto unggahan lama (foto bawaan di assets/img/obat dibiarkan).
            if ($medicine->image && is_file(public_path('uploads/obat/'.$medicine->image))) {
                @unlink(public_path('uploads/obat/'.$medicine->image));
            }
            $medicine->image = $image;
        }
        $medicine->save();

        return redirect()->route('admin.medicines.index')->with('success', "{$medicine->name} diperbarui.");
    }

    public function toggle(Medicine $medicine): RedirectResponse
    {
        $medicine->update(['is_active' => ! $medicine->is_active]);

        return back()->with('success', $medicine->name.' '.($medicine->is_active ? 'ditampilkan' : 'disembunyikan').'.');
    }

    private function validated(Request $request, ?Medicine $medicine = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('medicines', 'name')->ignore($medicine?->id)],
            'golongan' => ['required', Rule::in(array_keys(config('obat.golongan')))],
            'kategori' => ['required', 'string', 'max:80'],
            'bentuk' => ['required', 'string', 'max:60'],
            'harga' => ['required', 'integer', 'min:0', 'max:10000000'],
            'satuan' => ['required', 'string', 'max:60'],
            'kegunaan' => ['required', 'string', 'max:1000'],
            'dosis' => ['required', 'string', 'max:1000'],
            'peringatan' => ['required', 'string', 'max:1000'],
            'kebutuhan' => ['required', 'array', 'min:1'],
            'kebutuhan.*' => [Rule::in(array_keys(config('obat.kebutuhan')))],
            'hindari_jika' => ['nullable', 'array'],
            'hindari_jika.*' => [Rule::in(array_keys(config('obat.kondisi')))],
            'icon' => ['required', Rule::in(array_keys(config('obat.icons')))],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], ['kebutuhan.required' => 'Pilih minimal satu keluhan yang cocok.']);

        unset($data['image']);
        $data['hindari_jika'] = array_values($data['hindari_jika'] ?? []);
        $data['kebutuhan'] = array_values($data['kebutuhan']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $name = 'obat_'.time().'_'.Str::random(5).'.'.$request->file('image')->extension();
        $request->file('image')->move(public_path('uploads/obat'), $name);

        return $name;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while (Medicine::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
