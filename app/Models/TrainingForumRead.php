<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TrainingForumRead extends Model
{
    protected $fillable = ['training_id', 'user_id', 'last_read_message_id'];
}
