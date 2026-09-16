<?php
namespace App\Mail;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class TicketResolvedMail extends Mailable {
 use Queueable, SerializesModels;
 public function __construct(public Ticket $ticket){}
 public function build():self{
  $statusLabel=match($this->ticket->status){'RESOLVED'=>'Diselesaikan','CLOSED'=>'Ditutup',default=>ucfirst($this->ticket->status)};
  return $this->subject('Tiket Hotline '.$statusLabel.' - '.$this->ticket->ticket_number)
   ->view('emails.ticket-resolved',['ticket'=>$this->ticket,'statusLabel'=>$statusLabel]);
 }
}