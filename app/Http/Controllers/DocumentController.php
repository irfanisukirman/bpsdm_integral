<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\File;
use App\Models\FileVersion;
use App\Models\FolderUserPermission;
use App\Models\User;
use App\Helpers\LogHelper;
use App\Support\SpreadsheetPageReadFilter;
use App\Services\DocumentAccessService;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            $globalFolders = Folder::with(['user'])->withCount(['files', 'children'])->where('bidang', 'Semua Bidang')
                ->where('parent_id', null)
                ->get();

            $documentStats = [
                'folders' => Folder::count(),
                'files' => File::count(),
                'size' => (int) File::sum('file_size'),
                'public' => Folder::where('is_public', true)->count(),
                'bidang' => $listBidang->count(),
            ];
            return view('documents.index', compact('listBidang', 'globalFolders', 'documentStats'));
        }

        // 2. TENTUKAN RUANG DOKUMEN DAN AKSES PENGGUNA
        $currentBidang = ($user->role === 'superadmin') ? $targetBidang : $user->bidang;
        $access = app(DocumentAccessService::class);
        $currentFolder = $parentId ? Folder::with('parent')->findOrFail($parentId) : null;
        if ($user->role === 'superadmin' && !$currentBidang && $currentFolder) {
            $currentBidang = $currentFolder->bidang;
        }
        if ($currentFolder) abort_unless($access->canView($user, $currentFolder), 403);
        if ($currentFolder) FolderUserPermission::where('folder_id',$currentFolder->id)->where('user_id',$user->id)->whereNull('seen_at')->update(['seen_at'=>now()]);

        $folderQuery = Folder::with(['user', 'permissions'])->withCount(['files', 'children']);
        if ($currentFolder) {$folderQuery->where('parent_id', $currentFolder->id);}
        elseif ($user->role !== 'superadmin') {$folderQuery->where(fn ($location) => $location->whereNull('parent_id')->orWhereHas('permissions', fn ($permission) => $permission->where('user_id', $user->id)));}
        else {$folderQuery->whereNull('parent_id');}
        if (!$currentFolder) {
            if ($user->role === 'superadmin' && $currentBidang) {
                $folderQuery->where(fn ($scope) => $scope->where('bidang', $currentBidang)->orWhere('bidang', 'Semua Bidang'));
            } elseif ($user->role !== 'superadmin') {
                $folderQuery->where(function ($scope) use ($currentBidang, $user) {
                    $scope->where('bidang', 'Semua Bidang')->orWhere('user_id', $user->id)
                        ->orWhereHas('permissions', fn ($permission) => $permission->where('user_id', $user->id));
                    if ($user->role === 'admin_bidang' && $currentBidang) $scope->orWhere('bidang', $currentBidang);
                });
            }
        }
        $folders = $folderQuery->orderBy('training_id', 'desc')->orderBy('name')->get();
        $files = $currentFolder ? File::with(['user', 'versions'])->where('folder_id', $currentFolder->id)->latest()->get() : collect();
        $currentPermission = $currentFolder ? $access->permission($user, $currentFolder) : null;
        $canContribute = $currentFolder ? $access->canContribute($user, $currentFolder) : in_array($user->role, ['superadmin', 'admin_bidang'], true);
        $canManageCurrent = $currentFolder ? $access->canManage($user, $currentFolder) : $user->role === 'superadmin';
        $documentStats = ['folders'=>$folders->count(),'files'=>$files->count(),'size'=>(int)$files->sum('file_size'),'public'=>$folders->where('is_public',true)->count()];
        return view('documents.index', compact('folders','files','currentFolder','currentBidang','documentStats','currentPermission','canContribute','canManageCurrent'));
    }
    /**
     * Membuat Folder Baru
     */
    public function createFolder(Request $request)
    {
        $data = $request->validate(['name'=>'required|string|max:255','parent_id'=>'nullable|exists:folders,id','bidang'=>'nullable|string|max:255']);
        $user = Auth::user();$parent = !empty($data['parent_id']) ? Folder::findOrFail($data['parent_id']) : null;
        if ($parent) {
            abort_unless(app(DocumentAccessService::class)->canContribute($user, $parent), 403);
            $attributes=['parent_id'=>$parent->id,'bidang'=>$parent->bidang,'is_public'=>$parent->is_public,'share_token'=>$parent->is_public?Str::random(40):null];
        } else {
            abort_unless(in_array($user->role,['superadmin','admin_bidang'],true),403);
            $attributes=['parent_id'=>null,'bidang'=>$user->role==='superadmin'?($data['bidang']?:'Semua Bidang'):$user->bidang,'is_public'=>false];
        }
        $folder=Folder::create($attributes+['name'=>$data['name'],'user_id'=>$parent?$parent->user_id:$user->id]);
        LogHelper::record('Dokumen','Membuat folder: '.$folder->name.' di '.($parent?->name?:'root'));
        return back()->with('success','Folder berhasil dibuat.');
    }

    /**
     * Mengunggah File (Multiple Upload)
     */
    public function uploadFiles(Request $request)
    {
        $request->validate(['folder_id'=>'required|exists:folders,id','attachments'=>'required|array','attachments.*'=>'required|file|max:20480']);
        $folder=Folder::findOrFail($request->folder_id);abort_unless(app(DocumentAccessService::class)->canContribute(Auth::user(),$folder),403);
        $uploadedCount=0;$versionCount=0;
        foreach($request->file('attachments',[]) as $upload){
            $path=$upload->store('documents','public');$existing=File::where('folder_id',$folder->id)->where('display_name',$upload->getClientOriginalName())->first();
            if($existing){
                DB::transaction(function()use($existing,$upload,$path){$number=((int)$existing->versions()->max('version_number'))+1;$existing->versions()->create(['version_number'=>$number,'file_path'=>$existing->file_path,'file_type'=>$existing->file_type,'file_size'=>$existing->file_size,'uploaded_by'=>$existing->user_id,'notes'=>'Versi disimpan otomatis sebelum pembaruan']);$existing->update(['file_path'=>$path,'file_type'=>$upload->getClientOriginalExtension(),'file_size'=>$upload->getSize(),'user_id'=>Auth::id()]);});$versionCount++;
            }else{File::create(['folder_id'=>$folder->id,'display_name'=>$upload->getClientOriginalName(),'file_path'=>$path,'file_type'=>$upload->getClientOriginalExtension(),'file_size'=>$upload->getSize(),'user_id'=>Auth::id()]);}
            $uploadedCount++;
        }
        LogHelper::record('Dokumen','Mengunggah '.$uploadedCount.' file ke folder '.$folder->name.($versionCount?' ('.$versionCount.' versi baru)':''));
        return back()->with('success',$uploadedCount.' file berhasil diunggah'.($versionCount?' dan '.$versionCount.' riwayat versi disimpan.':'.'));
    }

    /**
     * Mengubah Status Publik/Private Folder
     */
    public function togglePrivacy($id)
    {
        $folder = Folder::findOrFail($id);

        abort_unless(app(DocumentAccessService::class)->canManage(Auth::user(), $folder), 403, 'Hanya pemilik folder atau superadmin yang dapat mengubah privasi.');

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
        $file=File::with(['folder','versions'])->findOrFail($id);abort_unless(app(DocumentAccessService::class)->canManage(Auth::user(),$file->folder),403,'Kontributor tidak dapat menghapus file.');
        $paths=$file->versions->pluck('file_path')->push($file->file_path)->filter()->unique();DB::transaction(fn()=>$file->delete());Storage::disk('public')->delete($paths->all());LogHelper::record('Dokumen','Menghapus file beserta riwayat versi: '.$file->display_name);return back()->with('success','File dan seluruh riwayat versinya berhasil dihapus.');
    }
    /**
     * Menghapus Folder (Beserta isinya)
     */
    public function destroyFolder($id)
    {
        $folder = Folder::findOrFail($id);

        abort_unless(app(DocumentAccessService::class)->canManage(Auth::user(), $folder), 403, 'Folder yang dibagikan tidak dapat dihapus oleh kontributor.');

        // Hapus fisik file di dalamnya
        foreach ($folder->files as $file) {
            Storage::disk('public')->delete($file->file_path);
        }

        $folder->delete();
        return redirect()->back()->with('success', 'Folder berhasil dihapus.');
    }

    public function sharing(Folder $folder)
    {
        abort_unless(app(DocumentAccessService::class)->canManage(Auth::user(),$folder),403);$folder->load(['permissions.user','permissions.sharer']);return view('documents.sharing',compact('folder'));
    }

    public function searchShareUsers(Request $request)
    {
        $query=trim((string)$request->query('q'));if(mb_strlen($query)<2)return response()->json([]);
        return response()->json(User::whereKeyNot(Auth::id())->where(function($q)use($query){$q->where('name','like','%'.$query.'%')->orWhere('username','like','%'.$query.'%')->orWhere('nip_nik','like','%'.$query.'%');})->orderBy('name')->limit(15)->get(['id','name','username','nip_nik','role','bidang']));
    }

    public function shareWithUser(Request $request,Folder $folder)
    {
        abort_unless(app(DocumentAccessService::class)->canManage(Auth::user(),$folder),403);$data=$request->validate(['user_id'=>'required|exists:users,id','permission'=>'required|in:viewer,contributor']);abort_if((int)$data['user_id']===(int)$folder->user_id,422,'Pemilik folder tidak perlu ditambahkan sebagai kolaborator.');$target=User::findOrFail($data['user_id']);FolderUserPermission::updateOrCreate(['folder_id'=>$folder->id,'user_id'=>$target->id],['permission'=>$data['permission'],'shared_by'=>Auth::id(),'seen_at'=>null]);LogHelper::record('Dokumen','Membagikan folder '.$folder->name.' kepada '.$target->name.' sebagai '.$data['permission']);return back()->with('success','Akses untuk '.$target->name.' berhasil disimpan.');
    }

    public function revokeShare(Folder $folder,User $user)
    {
        abort_unless(app(DocumentAccessService::class)->canManage(Auth::user(),$folder),403);FolderUserPermission::where('folder_id',$folder->id)->where('user_id',$user->id)->delete();LogHelper::record('Dokumen','Mencabut akses '.$user->name.' dari folder '.$folder->name);return back()->with('success','Akses '.$user->name.' berhasil dicabut.');
    }

    public function fileVersions(File $file)
    {
        $file->load(['folder','versions.uploader','user']);abort_unless(app(DocumentAccessService::class)->canView(Auth::user(),$file->folder),403);$canRestore=app(DocumentAccessService::class)->canContribute(Auth::user(),$file->folder);return view('documents.versions',compact('file','canRestore'));
    }

    public function downloadVersion(FileVersion $version)
    {
        $version->load('file.folder');abort_unless(app(DocumentAccessService::class)->canView(Auth::user(),$version->file->folder),403);abort_unless(Storage::disk('public')->exists($version->file_path),404);return Storage::disk('public')->download($version->file_path,$version->file->display_name.' - versi '.$version->version_number.'.'.$version->file_type);
    }

    public function restoreVersion(FileVersion $version)
    {
        $version->load('file.folder');$file=$version->file;abort_unless(app(DocumentAccessService::class)->canContribute(Auth::user(),$file->folder),403);abort_unless(Storage::disk('public')->exists($version->file_path),404);$extension=$version->file_type?:pathinfo($version->file_path,PATHINFO_EXTENSION);$restoredPath='documents/'.Str::uuid().'.'.$extension;Storage::disk('public')->copy($version->file_path,$restoredPath);
        DB::transaction(function()use($file,$version,$restoredPath){$number=((int)$file->versions()->max('version_number'))+1;$file->versions()->create(['version_number'=>$number,'file_path'=>$file->file_path,'file_type'=>$file->file_type,'file_size'=>$file->file_size,'uploaded_by'=>$file->user_id,'notes'=>'Versi aktif sebelum pemulihan versi '.$version->version_number]);$file->update(['file_path'=>$restoredPath,'file_type'=>$version->file_type,'file_size'=>$version->file_size,'user_id'=>Auth::id()]);});LogHelper::record('Dokumen','Memulihkan '.$file->display_name.' ke versi '.$version->version_number);return redirect()->route('documents.file.versions',$file)->with('success','Versi '.$version->version_number.' berhasil dipulihkan sebagai versi aktif.');
    }
    /**
     * Link Publik untuk akses folder bersama
     */
    public function share($token)
    {
        $folder = Folder::with([
            'parent',
            'children' => fn ($query) => $query->where('is_public', true)->orderBy('name')->withCount(['files', 'children']),
            'files' => fn ($query) => $query->orderBy('display_name'),
        ])->where('share_token', $token)->where('is_public', true)->firstOrFail();

        $files = $folder->files;
        $children = $folder->children;
        $stats = [
            'folders' => $children->count(),
            'files' => $files->count(),
            'size' => (int) $files->sum('file_size'),
        ];

        return view('documents.public_share', compact('folder', 'files', 'children', 'stats'));
    }

    public function previewSharedFile(string $token, File $file)
    {
        $folder = $this->authorizeSharedFile($token, $file);
        $extension = strtolower($file->file_type ?: pathinfo($file->display_name, PATHINFO_EXTENSION));

        if (in_array($extension, ['xls', 'xlsx'], true)) {
            return redirect()->route('documents.public.excel', [$folder->share_token, $file]);
        }

        abort_unless(in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'], true), 415, 'Format berkas ini belum mendukung pratinjau.');
        $path = Storage::disk('public')->path($file->file_path);
        abort_unless(is_file($path), 404, 'Berkas tidak ditemukan di penyimpanan.');

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="'.addslashes($file->display_name).'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    public function previewSharedExcel(Request $request, string $token, File $file)
    {
        $folder = $this->authorizeSharedFile($token, $file);
        $extension = strtolower($file->file_type ?: pathinfo($file->display_name, PATHINFO_EXTENSION));
        abort_unless(in_array($extension, ['xls', 'xlsx'], true), 415, 'Berkas ini bukan dokumen Excel.');
        $path = Storage::disk('public')->path($file->file_path);
        abort_unless(is_file($path), 404, 'Berkas tidak ditemukan di penyimpanan.');

        try {
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $worksheets = collect($reader->listWorksheetInfo($path));
            abort_if($worksheets->isEmpty(), 422, 'Dokumen Excel tidak memiliki sheet yang dapat dibaca.');

            $requestedSheet = (string) $request->query('sheet', '');
            $sheetInfo = $worksheets->firstWhere('worksheetName', $requestedSheet) ?: $worksheets->first();
            $sheetName = $sheetInfo['worksheetName'];
            $perPage = 100;
            $totalRows = max(1, (int) $sheetInfo['totalRows']);
            $totalColumns = min(100, max(1, (int) $sheetInfo['totalColumns']));
            $lastColumn = Coordinate::stringFromColumnIndex($totalColumns);
            $dataRows = max(0, $totalRows - 1);
            $lastPage = max(1, (int) ceil($dataRows / $perPage));
            $page = min($lastPage, max(1, (int) $request->query('page', 1)));
            $startRow = 2 + (($page - 1) * $perPage);
            $endRow = min($totalRows, $startRow + $perPage - 1);

            $reader->setLoadSheetsOnly([$sheetName]);
            $reader->setReadFilter(new SpreadsheetPageReadFilter($sheetName, $startRow, $endRow));
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $header = $sheet->rangeToArray("A1:{$lastColumn}1", null, true, true, true)[1] ?? [];
            $rows = $dataRows > 0 && $startRow <= $endRow
                ? array_values($sheet->rangeToArray("A{$startRow}:{$lastColumn}{$endRow}", null, true, true, true))
                : [];
            $spreadsheet->disconnectWorksheets();
        } catch (\Throwable $exception) {
            report($exception);
            abort(422, 'Dokumen Excel tidak dapat dipratinjau. Pastikan file tidak rusak atau diproteksi kata sandi.');
        }

        return view('documents.excel_preview', compact('folder', 'file', 'worksheets', 'sheetName', 'header', 'rows', 'page', 'lastPage', 'totalRows', 'totalColumns'));
    }

    private function authorizeSharedFile(string $token, File $file): Folder
    {
        $folder = Folder::where('share_token', $token)->where('is_public', true)->firstOrFail();
        abort_unless((int) $file->folder_id === (int) $folder->id, 404);
        return $folder;
    }

    public function viewFile($id)
    {
        $file = File::with('folder')->findOrFail($id);
        $this->authorizeFileAccess($file);
        abort_unless(Storage::disk('public')->exists($file->file_path), 404, 'Berkas tidak ditemukan di penyimpanan.');

        return response()->file(Storage::disk('public')->path($file->file_path), [
            'Content-Disposition' => 'inline; filename="' . addslashes($file->display_name) . '"',
        ]);
    }

    public function downloadFile($id)
    {
        $file = File::with('folder')->findOrFail($id);
        $this->authorizeFileAccess($file);
        abort_unless(Storage::disk('public')->exists($file->file_path), 404, 'Berkas tidak ditemukan di penyimpanan.');

        return Storage::disk('public')->download($file->file_path, $file->display_name);
    }

    private function authorizeFileAccess(File $file): void
    {
        $folder=$file->folder;abort_unless($folder,404);if($folder->is_public)return;$user=Auth::user();abort_unless($user&&app(DocumentAccessService::class)->canView($user,$folder),403);
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
