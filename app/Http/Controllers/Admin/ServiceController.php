<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('sort_order')->get();

        return view('admin.services', compact('services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'prefix'      => ['required', 'string', 'max:5'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['nullable', 'boolean'],
            'sort_order'  => ['nullable', 'integer'],
        ]);

        Service::create([
            'name'        => $data['name'],
            'prefix'      => strtoupper($data['prefix']),
            'description' => $data['description'] ?? null,
            'is_active'   => (bool) ($data['is_active'] ?? true),
            'sort_order'  => $data['sort_order'] ?? 0,
        ]);

        return back()->with('status', 'Layanan berhasil ditambahkan.');
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'prefix'      => ['required', 'string', 'max:5'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['nullable', 'boolean'],
            'sort_order'  => ['nullable', 'integer'],
        ]);

        $service->update([
            'name'        => $data['name'],
            'prefix'      => strtoupper($data['prefix']),
            'description' => $data['description'] ?? null,
            'is_active'   => (bool) ($data['is_active'] ?? false),
            'sort_order'  => $data['sort_order'] ?? 0,
        ]);

        return back()->with('status', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return back()->with('status', 'Layanan berhasil dihapus.');
    }
}
