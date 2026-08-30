@extends('layouts.auth')

@section('content')
@php
    $previewable = ['pdf','jpg','jpeg','png','gif','webp','xls','xlsx'];
    $iconFor = function ($extension) {
        return match (strtolower($extension)) {
            'pdf' => ['bxs-file-pdf', 'danger'],
            'xls', 'xlsx' => ['bxs-spreadsheet', 'success'],
            'jpg', 'jpeg', 'png', 'gif', 'webp' => ['bx-image-alt', 'info'],
            'doc', 'docx' => ['bxs-file-doc', 'primary'],
            'zip', 'rar' => ['bx-archive', 'warning'],
            default => ['bx-file', 'secondary'],
        };
    };
    $formatSize = function ($bytes) {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2).' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2).' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 1).' KB';
        return number_format($bytes).' byte';
    };
@endphp
<div class="share-page py-4 py-lg-5">
 <div class="container-fluid share-container px-3 px-lg-5">
  @if($folder->parent && $folder->parent->is_public && $folder->parent->share_token)
   <a href="{{route('documents.public',$folder->parent->share_token)}}" class="btn btn-sm btn-light border rounded-pill mb-3"><i class="bx bx-arrow-back me-1"></i>{{Str::limit($folder->parent->name,45)}}</a>
  @endif

  <section class="share-hero shadow-sm mb-4">
   <div class="text-center position-relative">
    <img class="share-logo" src="https://res.cloudinary.com/dnwyqw6gn/image/upload/v1786770700/Integral_1_ykmzxx.png" alt="INTEGRAL">
    <span class="read-only-badge"><i class="bx bx-show me-1"></i>Hanya lihat</span>
    <h2 class="text-white fw-bold mt-3 mb-1">{{$folder->name}}</h2>
    <p class="text-white-50 mb-0">Folder dokumen bersama · BPSDM Provinsi Jawa Barat</p>
   </div>
  </section>

  <div class="row g-3 mb-4">
   <div class="col-4"><div class="summary-card"><i class="bx bxs-folder text-warning"></i><div><strong>{{$stats['folders']}}</strong><span>Folder</span></div></div></div>
   <div class="col-4"><div class="summary-card"><i class="bx bx-file text-primary"></i><div><strong>{{$stats['files']}}</strong><span>Berkas</span></div></div></div>
   <div class="col-4"><div class="summary-card"><i class="bx bx-data text-info"></i><div><strong>{{$formatSize($stats['size'])}}</strong><span>Total ukuran</span></div></div></div>
  </div>

  @if($children->isNotEmpty())
   <section class="content-section mb-4">
    <div class="section-heading"><div><h5><i class="bx bxs-folder text-warning me-2"></i>Folder</h5><p>{{$children->count()}} folder tersedia</p></div></div>
    <div class="row g-3">
     @foreach($children as $sub)
      <div class="col-sm-6 col-lg-4 col-xl-3">
       <a href="{{route('documents.public',$sub->share_token)}}" class="folder-tile">
        <span class="folder-icon"><i class="bx bxs-folder"></i></span>
        <span class="folder-info"><strong title="{{$sub->name}}">{{$sub->name}}</strong><small>{{$sub->files_count}} berkas · {{$sub->children_count}} folder</small></span>
        <i class="bx bx-chevron-right ms-auto text-muted"></i>
       </a>
      </div>
     @endforeach
    </div>
   </section>
  @endif

  @if($files->isNotEmpty())
   <section class="content-section">
    <div class="section-heading"><div><h5><i class="bx bx-file-blank text-primary me-2"></i>Berkas</h5><p>{{$files->count()}} berkas tersedia untuk dilihat</p></div><span class="badge bg-label-primary"><i class="bx bx-lock-alt me-1"></i>Read-only</span></div>
    <div class="file-list">
     @foreach($files as $file)
      @php([$fileIcon,$fileColor]=$iconFor($file->file_type))
      <div class="file-item">
       <span class="file-icon bg-label-{{$fileColor}}"><i class="bx {{$fileIcon}}"></i></span>
       <div class="file-details"><strong title="{{$file->display_name}}">{{$file->display_name}}</strong><span>{{strtoupper($file->file_type)}} · {{$formatSize($file->file_size)}} · diperbarui {{$file->updated_at->translatedFormat('d M Y')}}</span></div>
       @if(in_array(strtolower($file->file_type),$previewable,true))
        <a href="{{route('documents.public.file',[$folder->share_token,$file])}}" target="_blank" rel="noopener" class="btn btn-outline-primary text-nowrap"><i class="bx bx-show-alt me-1"></i>Lihat</a>
       @else
        <span class="badge bg-label-secondary text-wrap text-center">Pratinjau belum tersedia</span>
       @endif
      </div>
     @endforeach
    </div>
   </section>
  @elseif($children->isEmpty())
   <section class="content-section text-center py-5"><i class="bx bx-folder-open empty-icon"></i><h5 class="mt-3">Folder masih kosong</h5><p class="text-muted mb-0">Belum ada folder atau berkas yang dibagikan.</p></section>
  @endif

  <footer class="text-center text-muted small py-4">&copy; {{date('Y')}} INTEGRAL · BPSDM Provinsi Jawa Barat</footer>
 </div>
</div>
<style>
 body{background:#f4f6fb}.share-page{min-height:100vh}.share-container{max-width:1440px}.share-hero{position:relative;overflow:hidden;border-radius:22px;padding:30px 24px;background:linear-gradient(135deg,#3247c5,#696cff 60%,#38a4e8)}.share-hero:before,.share-hero:after{content:"";position:absolute;border-radius:50%;background:rgba(255,255,255,.08)}.share-hero:before{width:260px;height:260px;right:-80px;top:-140px}.share-hero:after{width:180px;height:180px;left:-70px;bottom:-120px}.share-logo{display:block;width:auto;height:auto;max-width:180px;max-height:76px;object-fit:contain;margin:0 auto;filter:drop-shadow(0 4px 10px rgba(0,0,0,.18))}.read-only-badge{position:absolute;right:0;top:0;padding:8px 13px;border-radius:999px;background:rgba(255,255,255,.95);color:#5660d8;font-weight:700;font-size:.78rem}.summary-card{height:100%;display:flex;align-items:center;justify-content:center;gap:12px;padding:16px;border:1px solid #e7e9f2;border-radius:16px;background:#fff}.summary-card>i{font-size:1.7rem}.summary-card strong,.summary-card span{display:block}.summary-card strong{font-size:1.05rem}.summary-card span{font-size:.76rem;color:#8592a3}.content-section{background:#fff;border:1px solid #e7e9f2;border-radius:18px;padding:22px;box-shadow:0 5px 20px rgba(67,89,113,.05)}.section-heading{display:flex;justify-content:space-between;align-items:center;margin-bottom:17px}.section-heading h5{margin:0;font-weight:700}.section-heading p{margin:3px 0 0;color:#8592a3;font-size:.84rem}.folder-tile{display:flex;align-items:center;gap:12px;height:100%;padding:15px;border:1px solid #e7e9f2;border-radius:14px;color:#566a7f;background:#fff;transition:.2s}.folder-tile:hover{transform:translateY(-2px);border-color:#c8cdfc;box-shadow:0 8px 20px rgba(67,89,113,.09);color:#566a7f}.folder-icon{font-size:2.15rem;color:#ffab00;line-height:1}.folder-info{min-width:0}.folder-info strong,.folder-info small{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.folder-info small{color:#8592a3;font-size:.76rem;margin-top:3px}.file-list{border:1px solid #edf0f5;border-radius:14px;overflow:hidden}.file-item{display:flex;align-items:center;gap:14px;padding:13px 16px;border-bottom:1px solid #edf0f5}.file-item:last-child{border-bottom:0}.file-item:hover{background:#fafbff}.file-icon{width:46px;height:46px;display:grid;place-items:center;border-radius:12px;flex:0 0 auto}.file-icon i{font-size:1.5rem}.file-details{min-width:0;flex:1}.file-details strong,.file-details span{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.file-details span{font-size:.76rem;color:#8592a3;margin-top:3px}.empty-icon{font-size:4.5rem;color:#c7cbd5}@media(max-width:767.98px){.share-hero{padding-top:60px}.read-only-badge{right:50%;transform:translateX(50%)}.summary-card{display:block;text-align:center;padding:12px 5px}.file-item{flex-wrap:wrap}.file-details{width:calc(100% - 64px)}.file-item .btn{width:100%}.content-section{padding:16px}}
</style>
@endsection