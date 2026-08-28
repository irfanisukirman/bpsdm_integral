<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\File;
use App\Models\User;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // Pastikan ini ada

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $parentId = $request->query('folder');
        $targetBidang = $request->query('bidang');

        if ($user->role === 'superadmin' && !$targetBidang && !$parentId) {
            $listBidang = User::where('role', 'admin_bidang')->select('bidang')->distinct()->get();
            $globalFolders = Folder::where('bidang', 'Semua Bidang')->where('parent_id', null)->get();
            return view('documents.index', compact('listBidang', 'globalFolders'));
        }

        $currentBidang = ($user->role === 'superadmin') ? $targetBidang : $user->bidang;

        $folders = Folder::where('parent_id', $parentId)
            ->where(function($query) use ($currentBidang) {
                $query->where('bidang', $currentBidang)->orWhere('bidang', 'Semua Bidang');
            })
            ->orderBy('training_id', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        $files = $parentId ? File::where('folder_id', $parentId)->get() : collect();
        $currentFolder = $parentId ? Folder::with('parent')->findOrFail($parentId) : null;

        return view('documents.index', compact('folders', 'files', 'currentFolder', 'currentBidang'));
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'bidang' => 'required|string'
        ]);

        $folder = Folder::create([
            'name'      => $request->name,
            'parent_id' => $request->parent_id ?: null,
            'bidang'    => $request->bidang,
            'user_id'   => Auth::id(),
            'is_public' => false
        ]);

        LogHelper::record('Dokumen', 'Membuat folder: ' . $folder->name . ' pada bidang: ' . $folder->bidang);

        return redirect()->back()->with('success', 'Folder berhasil dibuat.');
    }

    public function uploadFiles(Request $request)
    {
        $request->validate([
            'folder_id' => 'required|exists:folders,id',
            'attachments.*' => 'required|file|max:20480',
        ]);

        $uploadedCount = 0;
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('documents', 'public');
                
                File::create([
                    'folder_id'    => $request->folder_id,
                    'display_name' => $file->getClientOriginalName(),
                    'file_path'    => $path,
                    'file_type'    => $file->getClientOriginalExtension(),
                    'file_size'    => $file->getSize(),
                    'user_id'      => Auth::id()
                ]);
                $uploadedCount++;
            }
        }

        LogHelper::record('Dokumen', 'Mengunggah ' . $uploadedCount . ' file ke folder ID: ' . $request->folder_id);

        return redirect()->back()->with('success', $uploadedCount . ' file berhasil diunggah.');
    }

    public function togglePrivacy($id)
    {
        $folder = Folder::findOrFail($id);
        $newStatus = !$folder->is_public;

        // Panggil fungsi rekursif
        $this->updatePrivacyRecursively($folder, $newStatus);

        $statusText = $newStatus ? 'Public' : 'Private';
        return redirect()->back()->with('success', "Folder beserta seluruh sub-foldernya berhasil diubah menjadi {$statusText}.");
    }

    private function updatePrivacyRecursively($folder, $status)
    {
        $folder->is_public = $status;
        
        // FIX: Jika diset public dan share_token kosong, otomatis buatkan token baru!
        if ($status === true && empty($folder->share_token)) {
            $folder->share_token = Str::random(40);
        }
        $folder->save();

        // Cari semua subfolder
        $subfolders = $folder->children()->get(); 
        if ($subfolders->isNotEmpty()) {
            foreach ($subfolders as $sub) {
                // Proses sub-foldernya secara berantai
                $this->updatePrivacyRecursively($sub, $status);
            }
        }
    }

    public function destroyFile($id)
    {
        $file = File::findOrFail($id);
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }
        $file->delete();
        LogHelper::record('Dokumen', 'Menghapus file: ' . $file->display_name);

        return redirect()->back()->with('success', 'File berhasil dihapus.');
    }

    public function destroyFolder($id)
    {
        $folder = Folder::findOrFail($id);
        if (Auth::user()->role !== 'superadmin' && $folder->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak diperbolehkan menghapus folder yang dibuat oleh instansi/admin lain.');
        }

        foreach ($folder->files as $file) {
            Storage::disk('public')->delete($file->file_path);
        }
        $folder->delete();
        return redirect()->back()->with('success', 'Folder berhasil dihapus.');
    }

    public function share($token)
    {
        $folder = Folder::with(['files', 'children'])->where('share_token', $token)->firstOrFail();
        return view('documents.public_share', compact('folder'));
    }

    public static function archiveInternal($trainingId, $categoryName, $fileName, $content, $extension)
    {
        $parentFolder = Folder::where('training_id', $trainingId)->whereNull('parent_id')->first();
        if (!$parentFolder) {
            $training = \App\Models\Training::find($trainingId);
            $parentFolder = Folder::create([
                'training_id' => $trainingId,
                'name' => $training->nama_pelatihan,
                'bidang' => $training->bidang,
                'user_id' => Auth::id() ?? 1,
            ]);
        }

        $subFolder = Folder::firstOrCreate([
            'name' => strtoupper($categoryName),
            'parent_id' => $parentFolder->id,
            'training_id' => $trainingId,
            'bidang' => $parentFolder->bidang
        ], ['user_id' => Auth::id() ?? 1]);

        $path = 'documents/' . $fileName;
        Storage::disk('public')->put($path, $content);

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