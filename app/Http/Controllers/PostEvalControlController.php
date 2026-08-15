<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostEvalControlController extends Controller
{
    public function index()
    {
        $query = Training::query();

        if (Auth::user()->role !== 'superadmin') {
            $query->where('bidang', Auth::user()->bidang);
        }

        $trainings = $query->latest()->get();

        return view('evaluasi.control_l34', compact('trainings'));
    }
}