<?php
namespace App\Mail;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class TicketUserReplyMail extends Mailable {
 use Queueable, SerializesModels;
 public function __construct(public Ticket $ticket,public TicketMessage $reply){}
 public function build():self{
  return $this->subject('Balasan Baru dari Pengguna - '.$this->ticket->ticket_number)
   ->view('emails.ticket-user-reply',['ticket'=>$this->ticket,'reply'=>$this->reply]);
 }
}