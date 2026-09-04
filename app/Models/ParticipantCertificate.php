<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ParticipantCertificate extends Model
{
    protected $fillable=['training_certificate_setting_id','training_id','participant_id','sequence_number','certificate_number','generated_file_path','final_file_path','generated_at','uploaded_at','downloaded_at','uploaded_by'];
    protected $casts=['generated_at'=>'datetime','uploaded_at'=>'datetime','downloaded_at'=>'datetime'];
    public function setting(){return $this->belongsTo(TrainingCertificateSetting::class,'training_certificate_setting_id');}
    public function training(){return $this->belongsTo(Training::class);}
    public function participant(){return $this->belongsTo(Participant::class);}
}
