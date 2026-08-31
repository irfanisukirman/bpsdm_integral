<?php
namespace App\Imports;
use App\Models\CertificationEvent;
use App\Models\CertificationParticipant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CertificationParticipantImport implements ToCollection,WithHeadingRow {
 public int $created=0;public int $updated=0;public int $skipped=0;
 public function __construct(private readonly CertificationEvent $event){}
 public function collection(Collection $rows){
  foreach($rows as $row){$nip=trim(ltrim((string)($row['nip_nik']??''),"'"));$name=trim((string)($row['nama']??''));if($nip===''||$name===''){$this->skipped++;continue;}
   $participant=CertificationParticipant::updateOrCreate(['certification_event_id'=>$this->event->id,'nip_nik'=>$nip],['name'=>$name,'position'=>trim((string)($row['jabatan']??''))?:null,'institution'=>trim((string)($row['instansi']??''))?:null,'province'=>trim((string)($row['provinsi']??''))?:null,'city'=>trim((string)($row['kabupaten_kota']??$row['kabupaten_atau_kota']??''))?:null,'phone'=>trim((string)($row['nomor_hp']??''))?:null,'email'=>trim((string)($row['email']??''))?:null]);
   $participant->wasRecentlyCreated?$this->created++:$this->updated++;
  }
 }
}
