<?php
namespace App\Services;
use App\Models\Ticket;
class TicketNumberGenerator {
 public static function generate():string{
  $year=date('Y');
  $prefix='INT-'.$year.'-';
  $last=Ticket::where('ticket_number','like',$prefix.'%')->latest('ticket_number')->value('ticket_number');
  if($last){$seq=(int)substr($last,-6)+1;}else{$seq=1;}
  return $prefix.str_pad($seq,6,'0',STR_PAD_LEFT);
 }
}