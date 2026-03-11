<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $lapangan = Lapangan::all();
        return view('home', compact('lapangan'));
    }

    public function detail($id)
    {
        $lapangan = Lapangan::findOrFail($id);
        return view('detail', compact('lapangan'));
    }
}
