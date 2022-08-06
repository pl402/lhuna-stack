<?php

namespace App\Http\Controllers;

use App\Models\Anexo;
use App\Models\Entrega;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EscritorioController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Escritorio');
    }
}