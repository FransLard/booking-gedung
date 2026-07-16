<?php

namespace App\Http\Controllers;

use App\Models\AddOn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AddOnController extends Controller
{
    public function index()
    {
        $addOns = AddOn::latest()->get();
        return view('admin.addons', compact('addOns'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama'       => 'required|string|max:255',
            'deskripsi'  => 'nullable|string',
            'harga'      => 'required|numeric|min:0',
            'type'       => 'required|in:flat,per_unit',
        ]);

        AddOn::create($data);

        return back()->with('success', 'Add-on berhasil ditambahkan.');
    }

    public function update(Request $request, AddOn $addOn): RedirectResponse
    {
        $data = $request->validate([
            'nama'       => 'required|string|max:255',
            'deskripsi'  => 'nullable|string',
            'harga'      => 'required|numeric|min:0',
            'type'       => 'required|in:flat,per_unit',
        ]);

        $addOn->update($data);

        return back()->with('success', 'Add-on berhasil diperbarui.');
    }

    public function destroy(AddOn $addOn): RedirectResponse
    {
        $addOn->delete();

        return back()->with('success', 'Add-on berhasil dihapus.');
    }
}
