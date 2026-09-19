<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use App\Models\Service;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public function index()
    {
        $counters = Counter::with('services')->orderBy('sort_order')->orderBy('number')->get();
        $services = Service::orderBy('sort_order')->get();

        return view('admin.counters', compact('counters', 'services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'number'       => ['required', 'integer', 'min:1'],
            'is_active'    => ['nullable', 'boolean'],
            'sort_order'   => ['nullable', 'integer'],
            'services'     => ['array'],
            'services.*'   => ['integer', 'exists:services,id'],
        ]);

        $counter = Counter::create([
            'name'       => $data['name'],
            'number'     => $data['number'],
            'is_active'  => (bool) ($data['is_active'] ?? true),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        $counter->services()->sync($data['services'] ?? []);

        return back()->with('status', 'Loket berhasil ditambahkan.');
    }

    public function update(Request $request, Counter $counter)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'number'       => ['required', 'integer', 'min:1'],
            'is_active'    => ['nullable', 'boolean'],
            'sort_order'   => ['nullable', 'integer'],
            'services'     => ['array'],
            'services.*'   => ['integer', 'exists:services,id'],
        ]);

        $counter->update([
            'name'       => $data['name'],
            'number'     => $data['number'],
            'is_active'  => (bool) ($data['is_active'] ?? false),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        $counter->services()->sync($data['services'] ?? []);

        return back()->with('status', 'Loket berhasil diperbarui.');
    }

    public function destroy(Counter $counter)
    {
        $counter->delete();

        return back()->with('status', 'Loket berhasil dihapus.');
    }
}
