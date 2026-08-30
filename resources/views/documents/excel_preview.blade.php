@extends('layouts.auth')
@section('content')
<div class="excel-page py-4">
 <div class="container-fluid excel-container px-3 px-lg-4">
  <header class="viewer-header mb-3">
   <img src="https://res.cloudinary.com/dnwyqw6gn/image/upload/v1786770700/Integral_1_ykmzxx.png" alt="INTEGRAL" class="viewer-logo">
   <div class="viewer-title"><span class="badge bg-label-success mb-2"><i class="bx bx-spreadsheet me-1"></i>Pratinjau Excel · Hanya lihat</span><h4 class="fw-bold mb-1 text-truncate" title="{{$file->display_name}}">{{$file->display_name}}</h4><p class="text-muted mb-0">{{$folder->name}} · {{max(0,$totalRows-1)}} baris data · {{$totalColumns}} kolom</p></div>
   <a href="{{route('documents.public',$folder->share_token)}}" class="btn btn-outline-secondary viewer-back"><i class="bx bx-arrow-back me-1"></i>Kembali ke folder</a>
  </header>

  <nav class="sheet-tabs mb-3" aria-label="Daftar sheet">
   @foreach($worksheets as $worksheet)
    <a class="sheet-tab {{$worksheet['worksheetName']===$sheetName?'active':''}}" href="{{route('documents.public.excel',[$folder->share_token,$file])}}?{{http_build_query(['sheet'=>$worksheet['worksheetName'],'page'=>1])}}"><i class="bx bx-table me-1"></i>{{$worksheet['worksheetName']}}<small>{{$worksheet['totalRows']}} baris</small></a>
   @endforeach
  </nav>

  <section class="excel-card">
   <div class="table-responsive excel-table-wrap">
    <table class="table table-bordered table-hover mb-0 excel-table">
     <thead><tr><th class="row-number">#</th>@foreach($header as $column=>$value)<th>{{filled($value)?$value:'Kolom '.$column}}</th>@endforeach</tr></thead>
     <tbody>
      @forelse($rows as $index=>$row)
       <tr><td class="row-number">{{2+(($page-1)*100)+$index}}</td>@foreach($row as $value)<td title="{{is_scalar($value)?$value:''}}">{{is_scalar($value)?$value:''}}</td>@endforeach</tr>
      @empty
       <tr><td colspan="{{count($header)+1}}" class="text-center py-5 text-muted"><i class="bx bx-data fs-1 d-block mb-2"></i>Sheet ini tidak memiliki baris data.</td></tr>
      @endforelse
     </tbody>
    </table>
   </div>
   @if($lastPage>1)
    <div class="viewer-pagination"><span>Halaman {{$page}} dari {{$lastPage}}</span><div class="btn-group"><a class="btn btn-outline-primary {{$page<=1?'disabled':''}}" href="{{$page>1?route('documents.public.excel',[$folder->share_token,$file]).'?'.http_build_query(['sheet'=>$sheetName,'page'=>$page-1]):'#'}}"><i class="bx bx-chevron-left"></i> Sebelumnya</a><a class="btn btn-outline-primary {{$page>=$lastPage?'disabled':''}}" href="{{$page<$lastPage?route('documents.public.excel',[$folder->share_token,$file]).'?'.http_build_query(['sheet'=>$sheetName,'page'=>$page+1]):'#'}}">Berikutnya <i class="bx bx-chevron-right"></i></a></div></div>
   @endif
  </section>
  <p class="text-center text-muted small mt-3"><i class="bx bx-lock-alt me-1"></i>Dokumen ditampilkan dalam mode baca saja. Data tidak dapat diedit dari halaman ini.</p>
 </div>
</div>
<style>
 body{background:#f4f6fb}.excel-page{min-height:100vh}.excel-container{max-width:1600px}.viewer-header{position:relative;text-align:center;padding:18px 210px;background:#fff;border:1px solid #e7e9f2;border-radius:18px;box-shadow:0 5px 20px rgba(67,89,113,.06)}.viewer-logo{display:block;width:auto;height:auto;max-width:170px;max-height:68px;object-fit:contain;margin:auto}.viewer-title{min-width:0;margin-top:10px}.viewer-back{position:absolute;right:22px;top:50%;transform:translateY(-50%)}.sheet-tabs{display:flex;gap:8px;overflow-x:auto;padding:4px 2px}.sheet-tab{display:flex;align-items:center;white-space:nowrap;padding:10px 14px;border:1px solid #dfe3ec;border-radius:11px;background:#fff;color:#566a7f;font-weight:600}.sheet-tab small{margin-left:8px;color:#8592a3;font-size:.68rem}.sheet-tab:hover,.sheet-tab.active{border-color:#696cff;background:#696cff;color:#fff}.sheet-tab.active small,.sheet-tab:hover small{color:rgba(255,255,255,.75)}.excel-card{overflow:hidden;background:#fff;border:1px solid #e2e5ed;border-radius:15px;box-shadow:0 5px 20px rgba(67,89,113,.05)}.excel-table-wrap{max-height:68vh}.excel-table{font-size:.82rem;white-space:nowrap}.excel-table thead th{position:sticky;top:0;z-index:3;background:#eef1f7;color:#445166;min-width:140px;max-width:320px}.excel-table td{max-width:360px;overflow:hidden;text-overflow:ellipsis;background:#fff}.excel-table .row-number{position:sticky;left:0;z-index:2;min-width:54px;width:54px;text-align:center;background:#f5f6fa;color:#8592a3}.excel-table thead .row-number{z-index:4}.viewer-pagination{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-top:1px solid #e7e9f2;color:#8592a3;font-size:.85rem}@media(max-width:767.98px){.viewer-header{padding:18px}.viewer-back{position:static;transform:none;width:100%;margin-top:14px}.viewer-pagination{gap:12px;flex-direction:column}.viewer-pagination .btn{font-size:.75rem;padding:.45rem .6rem}}
</style>
@endsection