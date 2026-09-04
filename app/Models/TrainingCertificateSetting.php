<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TrainingCertificateSetting extends Model
{
    protected $fillable=['training_id','name','template_path','number_format','start_sequence','issued_at','photo_size','created_by'];
    protected $casts=['issued_at'=>'date'];
    public function training(){return $this->belongsTo(Training::class);}
    public function certificates(){return $this->hasMany(ParticipantCertificate::class);}
}
