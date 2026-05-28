<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Actualización de Reserva</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f4f6; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; border-top: 5px solid #2563eb; }
        .header { font-size: 20px; font-weight: bold; color: #1f2937; margin-bottom: 20px; }
        .badge { display: inline-block; padding: 5px 10px; background-color: #e5e7eb; border-radius: 4px; font-size: 14px; font-weight: bold; color: #374151; }
        .details { margin-top: 20px; background-color: #f9fafb; padding: 15px; border-radius: 6px; }
        .footer { margin-top: 30px; font-size: 12px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            Hola, {{ $reserva->cliente->user->name ?? 'Cliente' }} 👋
        </div>
        
        <p>Te informamos que ha habido una actualización en tu reserva para el servicio de <strong>{{ $reserva->servicio->nombre ?? 'Servicio' }}</strong>.</p>
        
        <p>El nuevo estado de tu turno es: <span class="badge">{{ strtoupper($estado) }}</span></p>

        <div class="details">
            <p><strong>📅 Fecha:</strong> {{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}</p>
            <p><strong>⏰ Hora:</strong> {{ \Carbon\Carbon::parse($reserva->hora_inicio)->format('H:i') }}</p>
            <p><strong>👨‍🔧 Profesional:</strong> {{ $reserva->profesional->user->name ?? 'Profesional' }}</p>
        </div>

        <p style="margin-top: 20px;">Si tienes alguna duda, puedes ingresar a tu panel de control en la plataforma.</p>

        <div class="footer">
            © {{ date('Y') }} Gendar App. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>