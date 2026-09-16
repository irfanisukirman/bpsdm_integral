<?php
namespace App\Services;
use App\Models\Ticket;
class TrackingTokenService {
 public static function generate():string{
  do{$token=bin2hex(random_bytes(32));}while(Ticket::where('tracking_token',$token)->exists());
  return $token;
 }
}