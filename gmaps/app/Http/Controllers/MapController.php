<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MapController extends Controller
{
    /**
     * Tampilkan halaman peta interaktif.
     */
    public function index()
    {
        return view('map');
    }
}