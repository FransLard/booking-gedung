<?php

namespace App\Http\Controllers;

use App\Models\Gedung;

class HomeController extends Controller
{
    public function index()
    {
        $gedungList = Gedung::with('images')->take(4)->get();

        return view('beranda', compact('gedungList'));
    }
}
