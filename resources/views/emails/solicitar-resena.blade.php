<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Califica tu atención</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f3f4f6; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; border-top: 5px solid #8b5cf6;">
        
        <h2 style="color: #1f2937; margin-top: 0;">¡Hola, {{ $reserva->cliente->user->name ?? 'Cliente' }}! 🌟</h2>
        
        <p style="color: #4b5563; font-size: 16px; line-height: 1.5;">
            Esperamos que tu turno de <strong>{{ $reserva->servicio->nombre ?? 'Servicio' }}</strong> haya salido de diez.
        </p>

        <p style="color: #4b5563; font-size: 16px; line-height: 1.5;">
            Para ayudar a <strong>{{ $reserva->profesional->user->name ?? 'el profesional' }}</strong> a seguir creciendo y a otros clientes a elegir mejor, nos encantaría que nos dejes tu opinión sobre la atención recibida. Solo te tomará un minuto.
        </p>

        <div style="text-align: center; margin: 35px 0;">
            <a href="{{ url('/reservas/historial') }}" 
               style="background-color: #8b5cf6; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">
                CALIFICAR ATENCIÓN
            </a>
        </div>

        <p style="color: #9ca3af; font-size: 14px;">Si ya lo calificaste, por favor ignora este correo. ¡Gracias por usar Gendar App!</p>

        <div style="margin-top: 30px; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 15px; text-align: center;">
            © {{ date('Y') }} Gendar App. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>