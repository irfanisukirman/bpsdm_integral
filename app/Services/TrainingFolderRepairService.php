<?php

namespace App\Services;

use App\Models\{Training,Folder,User,Schedule};
use Illuminate\Support\Facades\{DB,Storage};
use Illuminate\Support\Str;

class TrainingFolderRepairService
{
    public function repair(Training $training, bool $apply = false): array
    {
        return DB::transaction(function () use ($training,$apply) {
            if ($apply) Training::whereKey($training->id)->lockForUpdate()->firstOrFail();
            $root=Folder::where('training_id',$training->id)->whereNull('parent_id')->orderBy('id')->firstOrFail();
            $folders=Folder::where('training_id',$training->id)->with(['files','user'])->orderBy('id')->get();
            $originals=$folders->map(fn($f)=>$f->getAttributes())->all();
            $badNames=[$root->name,$training->nama_pelatihan,$training->nama_pelatihan.' - Angkatan '.$training->angkatan];
            $changes=[];
            foreach($folders->where('parent_id',$root->id) as $folder){
                if(!in_array($folder->name,$badNames,true))continue;
                $descendants=$this->descendants($folders,$folder->id);
                $files=$folder->files->concat($folders->whereIn('id',$descendants)->flatMap(fn($f)=>$f->files));
                $category=$this->category($files);
                $target=$folders->first(fn($f)=>$f->parent_id===$root->id && $f->id!==$folder->id && $f->name===$category);
                $name=$target ? 'Arsip sebelumnya - '.($folder->user?->name ?: 'Dokumen').' #'.$folder->id : $category;
                $changes[]=['id'=>$folder->id,'old_name'=>$folder->name,'name'=>Str::limit($name,255,''),'parent_id'=>$target?->id ?? $root->id];
                // Keep original folders/owners and URLs, preserving each source's sharing rules.
                foreach($folders->whereIn('id',$descendants) as $child){
                    if(!in_array($child->name,$badNames,true))continue;
                    $childName=Str::upper($child->user?->name ?: 'Dokumen');
                    if($category==='ADMINISTRASI PENGAJAR'){
                        $path=$child->files->first()?->file_path;
                        $document=$path ? DB::table('pengajar_schedule_documents')->where('bahan_ajar_path',$path)->orWhere('rbpmp_rp_path',$path)->orWhere('bukti_mengajar_path',$path)->first() : null;
                        $activity=$document ? Schedule::find($document->schedule_id)?->activity : null;
                        if($activity)$childName.=' - '.$activity;
                    }
                    $changes[]=['id'=>$child->id,'old_name'=>$child->name,'name'=>Str::limit($childName,255,''),'parent_id'=>$child->parent_id];
                }
                if(!$target)$folder->name=$category;
            }
            $backup=null;
            if($apply && $changes){
                $backup='folder-repair-backups/'.$training->id.'-'.now()->format('Ymd-His').'-'.Str::uuid().'.json';
                $snapshot=['training_id'=>$training->id,'folders'=>$originals,'changes'=>$changes];
                if(!Storage::disk('local')->put($backup,json_encode($snapshot,JSON_THROW_ON_ERROR|JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)))throw new \RuntimeException('Cadangan folder gagal dibuat.');
                foreach($changes as $change)Folder::whereKey($change['id'])->update(['name'=>$change['name'],'parent_id'=>$change['parent_id']]);
            }
            return compact('changes','backup','apply');
        });
    }

    private function descendants($folders,int $parent): array
    {
        $ids=[];$pending=[$parent];
        while($pending){$pending=$folders->whereIn('parent_id',$pending)->pluck('id')->diff($ids)->all();$ids=array_merge($ids,$pending);}
        return $ids;
    }

    private function category($files): string
    {
        $paths=mb_strtolower($files->pluck('file_path')->implode(' '));
        $names=mb_strtolower($files->pluck('display_name')->implode(' '));
        if(str_contains($paths,'pengajar/sesi/'))return 'ADMINISTRASI PENGAJAR';
        if(str_contains($paths,'pengajar/kelengkapan/'))return 'KELENGKAPAN NARASUMBER';
        if(preg_match('~documents/(?:biodata|pas_foto|surat_tugas)_~i',$paths))return 'KELENGKAPAN PESERTA';
        foreach([
            'rekap_kehadiran'=>'REKAP KEHADIRAN EXCEL',
            'evaluasi_lv1_lv2'=>'LAPORAN EVALUASI LEVEL 1 DAN 2',
            'laporan_kegiatan'=>'LAPORAN PENYELENGGARAAN PELATIHAN',
            'surat_undangan_evaluasi'=>'SURAT UNDANGAN EVALUASI',
            'laporan_dampak_l3_l4'=>'HASIL EVALUASI DAMPAK',
            'laporan_akhir_dampak'=>'LAPORAN AKHIR DAMPAK',
            'laporan_tindak_lanjut'=>'LAPORAN TINDAK LANJUT',
            'laporan_monitoring'=>'LAPORAN MONITORING',
            'ceklis_monitoring'=>'CEKLIS MONITORING',
            'jadwal_pelatihan'=>'JADWAL PELATIHAN',
        ] as $pattern=>$category)if(str_contains($names,$pattern))return $category;
        return 'ARSIP DOKUMEN LAINNYA';
    }
}
