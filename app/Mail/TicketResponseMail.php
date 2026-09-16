<?php
namespace App\Mail;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class TicketResponseMail extends Mailable {
 use Queueable, SerializesModels;
 public function __construct(public Ticket $ticket,public TicketMessage $message){}
 public function build():self{
return $this->subject('Respons Tiket Hotline - '.$this->ticket->ticket_number)
    ->view('emails.ticket-response',['ticket'=>$this->ticket,'response'=>$this->message]);
 }
}