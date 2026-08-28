<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\File;
use App\Models\User;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Halaman Utama Pengelolaan Dokumen
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $parentId = $request->query('folder');
        $targetBidang = $request->query('bidang');

        // 1. LOGIKA LANDING PAGE KHUSUS SUPERADMIN
        // Jika Superadmin baru masuk menu (belum pilih bidang & belum masuk folder)
        if ($user->role === 'superadmin' && !$targetBidang && !$parentId) {
            // Ambil daftar bidang unik dari user yang terdaftar
            $listBidang = User::where('role', 'admin_bidang')
                ->select('bidang')
                ->distinct()
                ->get();

            // Ambil Folder Global (yang dibuat superadmin di halaman depan)
            $globalFolders = Folder::where('bidang', 'Semua Bidang')
                ->where('parent_id', null)
                ->get();

            return view('documents.index', compact('listBidang', 'globalFolders'));
        }

        // 2. TENTUKAN BIDANG YANG SEDANG DIKELOLA
        // Jika Admin Bidang, paksa ke bidangnya sendiri. 
        // Jika Superadmin, ambil dari parameter URL ?bidang=...
        $currentBidang = ($user->role === 'superadmin') ? $targetBidang : $user->bidang;

        // Admin bidang hanya boleh membuka folder bidangnya, folder miliknya,
        // atau folder global. Superadmin tetap dapat membuka seluruh folder.
        if ($parentId && $user->role !== 'superadmin') {
            $requestedFolder = Folder::findOrFail($parentId);
            $isAllowed = $requestedFolder->bidang === 'Semua Bidang'
                || ($currentBidang && $requestedFolder->bidang === $currentBidang)
                || (int) $requestedFolder->user_id === (int) $user->id;
            abort_unless($isAllowed, 403);
        }

        // 3. QUERY DATA FOLDER (Bidang Terkait + Folder Global)
        $folders = Folder::where('parent_id', $parentId)
            ->when($user->role !== 'superadmin', function ($query) use ($currentBidang, $user) {
                $query->where(function ($scope) use ($currentBidang, $user) {
                    $scope->where('bidang', 'Semua Bidang')
                        ->orWhere('user_id', $user->id);
                    if ($currentBidang) {
                        $scope->orWhere('bidang', $currentBidang);
                    }
                });
            })
            ->orderBy('training_id', 'desc') // Folder pelatihan terbaru muncul duluan
            ->orderBy('name', 'asc')
            ->get();

        // 4. QUERY DATA FILE (Hanya dalam folder yang sedang dibuka)
        $files = $parentId ? File::where('folder_id', $parentId)->get() : collect();

        // Data pendukung breadcrumb & navigasi
        $currentFolder = $parentId ? Folder::with('parent')->findOrFail($parentId) : null;

        return view('documents.index', compact('folders', 'files', 'currentFolder', 'currentBidang'));
    }

    /**
     * Membuat Folder Baru
     */
    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'bidang' => 'required|string'
        ]);

        // Cek status parent jika folder dibuat di dalam folder lain
        $isPublic = false;
        if ($request->parent_id) {
            $parentFolder = Folder::find($request->parent_id);
            $isPublic = $parentFolder ? $parentFolder->is_public : false;
        }

        $folder = Folder::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id ?: null,
            'bidang' => $request->bidang,    // 'Semua Bidang' atau nama bidang spesifik
            'user_id' => Auth::id(),
            'is_public' => false
        ]);

        LogHelper::record('Dokumen', 'Membuat folder: ' . $folder->name . ' pada bidang: ' . $folder->bidang);

        return redirect()->back()->with('success', 'Folder berhasil dibuat.');
    }

    /**
     * Mengunggah File (Multiple Upload)
     */
    public function uploadFiles(Request $request)
    {
        $request->validate([
            'folder_id' => 'required|exists:folders,id',
            'attachments.*' => 'required|file|max:20480', // Max 20MB per file
        ]);

        $uploadedCount = 0;
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('documents', 'public');

                File::create([
                    'folder_id' => $request->folder_id,
                    'display_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'user_id' => Auth::id()
                ]);
                $uploadedCount++;
            }
        }

        LogHelper::record('Dokumen', 'Mengunggah ' . $uploadedCount . ' file ke folder ID: ' . $request->folder_id);

        return redirect()->back()->with('success', $uploadedCount . ' file berhasil diunggah.');
    }

    /**
     * Mengubah Status Publik/Private Folder
     */
    public function togglePrivacy($id)
    {
        $folder = Folder::findOrFail($id);

        // KEAMANAN: Cek apakah user adalah Superadmin atau Pemilik Folder
        if (Auth::user()->role !== 'superadmin' && $folder->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengubah privasi folder ini.');
        }

        $newStatus = !$folder->is_public;

        $folder->is_public = $newStatus;
        $folder->share_token = $folder->is_public ? Str::random(40) : null;
        $folder->save();

        $this->updateChildrenPrivacy($folder->id, $newStatus);

        $pesan = $newStatus ? 'Folder dan seluruh isinya berhasil dibuat Publik.' : 'Folder dan seluruh isinya berhasil dibuat Private.';
        return redirect()->back()->with('success', 'Privasi folder diperbarui.');
    }

    private function updateChildrenPrivacy($parentId, $isPublic)
    {
        // Ambil semua folder turunan langsung dari parentId
        $children = Folder::where('parent_id', $parentId)->get();

        foreach ($children as $child) {
            $child->is_public = $isPublic;
            // Jika public buat token baru (atau pertahankan jika sudah ada), jika private hapus token
            $child->share_token = $isPublic ? ($child->share_token ?? Str::random(40)) : null;
            $child->save();

            // Panggil kembali fungsi ini untuk mengecek jika ada subfolder di dalam child
            $this->updateChildrenPrivacy($child->id, $isPublic);
        }
    }

    /**
     * Menghapus File
     */
    public function destroyFile($id)
    {
        $file = File::findOrFail($id);

        // Hapus fisik file
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();
        LogHelper::record('Dokumen', 'Menghapus file: ' . $file->display_name);

        return redirect()->back()->with('success', 'File berhasil dihapus.');
    }

    /**
     * Menghapus Folder (Beserta isinya)
     */
    public function destroyFolder($id)
    {
        $folder = Folder::findOrFail($id);

        // KEAMANAN: Cek apakah user adalah Superadmin atau Pemilik Folder
        if (Auth::user()->role !== 'superadmin' && $folder->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak diperbolehkan menghapus folder yang dibuat oleh instansi/admin lain.');
        }

        // Hapus fisik file di dalamnya
        foreach ($folder->files as $file) {
            Storage::disk('public')->delete($file->file_path);
        }

        $folder->delete();
        return redirect()->back()->with('success', 'Folder berhasil dihapus.');
    }

    /**
     * Link Publik untuk akses folder bersama
     */
    public function share($token)
    {
        // Cari folder yang tokennya cocok dan statusnya memang publik
        $folder = Folder::where('share_token', $token)->firstOrFail();

        return view('documents.public_share', compact('folder'));
    }

    public static function archiveInternal($trainingId, $categoryName, $fileName, $content, $extension)
    {
        // 1. Cari Folder Utama Pelatihan tersebut
        $parentFolder = Folder::where('training_id', $trainingId)->whereNull('parent_id')->first();

        // Jika belum ada folder utamanya (backup), buat otomatis
        if (!$parentFolder) {
            $training = \App\Models\Training::find($trainingId);
            $parentFolder = Folder::create([
                'training_id' => $trainingId,
                'name' => $training->nama_pelatihan,
                'bidang' => $training->bidang,
                'user_id' => Auth::id() ?? 1,
            ]);
        }

        // 2. Buat/Cari Sub-Folder Kategori (Misal: LAPORAN MONITORING)
        $subFolder = Folder::firstOrCreate([
            'name' => strtoupper($categoryName),
            'parent_id' => $parentFolder->id,
            'training_id' => $trainingId,
            'bidang' => $parentFolder->bidang
        ], ['user_id' => Auth::id() ?? 1]);

        // 3. Simpan File Fisik
        $path = 'documents/' . $fileName;
        Storage::disk('public')->put($path, $content);

        // 4. Catat ke Tabel Files
        File::create([
            'folder_id' => $subFolder->id,
            'display_name' => $fileName,
            'file_path' => $path,
            'file_type' => $extension,
            'file_size' => strlen($content),
            'user_id' => Auth::id() ?? 1
        ]);
    }
}
