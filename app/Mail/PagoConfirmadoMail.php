<?php

namespace App\Mail;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Headers;

class PagoConfirmadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reserva;

    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Pago Confirmado! - Gendar App',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pago-confirmado',
        );
    }

    // Cabecera para identificar el tipo de acción según el estado
    public function headers(): Headers
    {
        return new Headers(
            text: [
                'X-Email-Action' => 'pago_confirmado',
            ],
        );
    }
}