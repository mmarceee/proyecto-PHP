<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pago Confirmado</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f3f4f6; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; border-top: 5px solid #10b981;">
        
        <h2 style="color: #1f2937; margin-top: 0;">¡Hola, {{ $reserva->cliente->user->name ?? 'Cliente' }}! ✅</h2>
        
        <p style="color: #4b5563; font-size: 16px; line-height: 1.5;">
            Queríamos avisarte que hemos recibido el pago para tu reserva del servicio de <strong>{{ $reserva->servicio->nombre ?? 'Servicio' }}</strong>.
        </p>

        <div style="background-color: #f0fdf4; border-left: 4px solid #10b981; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <p style="margin: 0 0 10px 0; color: #065f46;"><strong>💰 Total abonado:</strong> ${{ number_format($reserva->servicio->precio ?? 0, 2) }}</p>
            <p style="margin: 0 0 10px 0; color: #065f46;"><strong>📅 Fecha del turno:</strong> {{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}</p>
            <p style="margin: 0; color: #065f46;"><strong>⏰ Horario:</strong> {{ \Carbon\Carbon::parse($reserva->hora_inicio)->format('H:i') }} hs</p>
        </div>

        <p style="color: #6b7280; font-size: 14px;">Tu lugar ya está asegurado. Puedes revisar todos los detalles ingresando a tu panel en Gendar App.</p>

        <div style="margin-top: 30px; font-size: 12px; color: #9ca3af; text-align: center;">
            © {{ date('Y') }} Gendar App. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>