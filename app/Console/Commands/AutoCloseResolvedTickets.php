<?php
namespace App\Console\Commands;
use App\Models\Ticket;
use App\Services\TicketingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketResolvedMail;
class AutoCloseResolvedTickets extends Command {
 protected $signature='tickets:auto-close';
 protected $description='Menutup tiket RESOLVED yang melewati batas auto-close tanpa balasan pengguna';
 public function handle():int{
  $closed=0;
  Ticket::where('status','RESOLVED')->whereNotNull('auto_close_at')->where('auto_close_at','<=',now())
   ->chunkById(50,function($tickets)use(&$closed){
    foreach($tickets as $ticket){
     TicketingService::changeStatus($ticket,'CLOSED','System',null,'Auto-close: tiket ditutup otomatis karena tidak ada balasan pengguna');
     if($ticket->email){try{Mail::to($ticket->email)->send(new TicketResolvedMail($ticket));}catch(\Exception $e){}}
     $closed++;
    }
   });
  $this->info($closed.' tiket berhasil ditutup otomatis.');
  return Command::SUCCESS;
 }
}