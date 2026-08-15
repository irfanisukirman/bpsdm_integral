<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Participant;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        $user = Auth::user();

        // Jika input kosong, balikkan ke halaman sebelumnya
        if (!$query) {
            return redirect()->back();
        }

        // 1. Pencarian Pelatihan (Hanya Bidang Sendiri)
        $trainings = Training::query()
            ->when($user->role !== 'superadmin', function($q) use ($user) {
                return $q->where('bidang', $user->bidang);
            })
            ->where(function($q) use ($query) {
                $q->where('nama_pelatihan', 'LIKE', "%$query%")
                  ->orWhere('lokasi', 'LIKE', "%$query%");
            })
            ->take(10)->get();

        // 2. Pencarian Peserta (Hanya dari pelatihan milik bidang sendiri)
        $participants = Participant::query()
            ->whereHas('training', function($q) use ($user) {
                if ($user->role !== 'superadmin') {
                    $q->where('bidang', $user->bidang);
                }
            })
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%$query%")
                  ->orWhere('nip_nik', 'LIKE', "%$query%")
                  ->orWhere('instansi', 'LIKE', "%$query%");
            })
            ->take(15)->get();

        // 3. Pencarian Dokumen (Hanya dari folder milik bidang sendiri)
        $files = File::query()
            ->whereHas('folder', function($q) use ($user) {
                if ($user->role !== 'superadmin') {
                    $q->where('bidang', $user->bidang);
                }
            })
            ->where('display_name', 'LIKE', "%$query%")
            ->take(10)->get();

        return view('search.results', compact('trainings', 'participants', 'files', 'query'));
    }
}