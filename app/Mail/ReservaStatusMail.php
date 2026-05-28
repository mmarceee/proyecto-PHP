<?php

namespace App\Mail;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservaStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reserva;
    public $estado;

    public function __construct(Reserva $reserva, string $estado)
    {
        $this->reserva = $reserva;
        $this->estado = $estado;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Actualización de tu turno - Gendar App',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reserva-status', // Apunta a la vista Blade 
        );
    }

    public function attachments(): array
    {
        return [];
    }
}