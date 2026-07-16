<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use App\Models\Gedung;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GedungController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Gedung::with('images');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $gedungList = $query->get()->each(function ($g) {
            $g->append(['gambar_url', 'gambar_dalam_url']);
            $g->images->each(fn ($img) => $img->append('gambar_url'));
        });

        return response()->json($gedungList);
    }

    public function show(Gedung $gedung): JsonResponse
    {
        $gedung->load('images');
        $gedung->append(['gambar_url', 'gambar_dalam_url']);
        $gedung->images->each(fn ($img) => $img->append('gambar_url'));
        $addOns = AddOn::all();

        return response()->json([
            'gedung' => $gedung,
            'add_ons' => $addOns,
        ]);
    }
}
