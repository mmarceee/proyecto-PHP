<?php

namespace App\Mail;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Headers;

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

    // Cabecera para identificar el tipo de acción según el estado
    public function headers(): Headers
    {
        $action = match (strtolower($this->estado)) {
            'pendiente' => 'reserva_creada',
            'confirmada', 'confirmado' => 'reserva_confirmada',
            'cancelada', 'cancelado' => 'reserva_cancelada',
            'reprogramada', 'reprogramado' => 'reserva_reprogramada',
            default => 'reserva_status_cambiado',
        };
        return new Headers(
            text: [
                'X-Email-Action' => $action,
            ],
        );
    }

}