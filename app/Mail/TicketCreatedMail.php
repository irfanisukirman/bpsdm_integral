<?php
namespace App\Mail;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class TicketCreatedMail extends Mailable {
 use Queueable, SerializesModels;
 public function __construct(public Ticket $ticket){}
 public function build():self{
  return $this->subject('Tiket Hotline Diterima - '.$this->ticket->ticket_number)
   ->view('emails.ticket-created',['ticket'=>$this->ticket]);
 }
}