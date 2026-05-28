<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;">
    <h2 style="color: #1f2937;">¡Hola, {{ $reserva->cliente->user->name ?? 'Cliente' }}! ⏰</h2>
    <p style="color: #4b5563; font-size: 16px;">
        Te escribimos para recordarte que mañana tienes un turno confirmado para <strong>{{ $reserva->servicio->nombre ?? 'tu servicio' }}</strong>.
    </p>
    
    <div style="background-color: #f9fafb; padding: 15px; border-radius: 6px; margin: 20px 0;">
        <p style="margin: 0 0 10px 0;"><strong>📅 Fecha:</strong> Mañana, {{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}</p>
        <p style="margin: 0 0 10px 0;"><strong>⏰ Hora:</strong> {{ \Carbon\Carbon::parse($reserva->hora_inicio)->format('H:i') }}</p>
        <p style="margin: 0;"><strong>👨‍🔧 Con:</strong> {{ $reserva->profesional->user->name ?? 'Profesional' }}</p>
    </div>

    <p style="color: #6b7280; font-size: 14px;">Te pedimos puntualidad. Si por algún motivo de fuerza mayor no puedes asistir, recuerda cancelar el turno desde la plataforma.</p>
</div>