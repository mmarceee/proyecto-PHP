<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Actualización de Reserva</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f4f6; padding: 20px; }
        /* NUEVO: La línea superior cambia de color dinámicamente según el estado */
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background-color: #ffffff; 
            padding: 30px; 
            border-radius: 8px; 
            border-top: 5px solid {{ strtolower($estado) === 'cancelada' ? '#ef4444' : '#2563eb' }}; 
        }
        .header { font-size: 20px; font-weight: bold; color: #1f2937; margin-bottom: 20px; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 4px; font-size: 14px; font-weight: bold; }
        /* Estilos dinámicos para los badges */
        .badge-confirmada { background-color: #d1fae5; color: #065f46; }
        .badge-cancelada { background-color: #fee2e2; color: #991b1b; }
        .badge-default { background-color: #e5e7eb; color: #374151; }
        
        .details { margin-top: 20px; background-color: #f9fafb; padding: 15px; border-radius: 6px; }
        .details-cancelada { border-left: 4px solid #ef4444; background-color: #fef2f2; }
        .footer { margin-top: 30px; font-size: 12px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            Hola, {{ $reserva->cliente->user->name ?? 'Cliente' }} 👋
        </div>
        
        @if(strtolower($estado) === 'cancelada')
            {{-- ==================== VISTA PARA RESERVA CANCELADA ==================== --}}
            <p>Lamentamos informarte que tu reserva para el servicio de <strong>{{ $reserva->servicio->nombre ?? 'Servicio' }}</strong> ha sido 
                <span class="badge badge-cancelada">CANCELADA</span>.
            </p>
            
            <div class="details details-cancelada">
                <p style="margin: 0 0 8px 0; font-weight: bold; color: #991b1b;">⚠️ Motivo de la cancelación:</p>
                <p style="margin: 0; font-style: italic; color: #4b5563; line-height: 1.5;">
                    "{{ $reserva->motivo_cancelacion ?? $reserva->motivo ?? 'No especificado por el profesional.' }}"
                </p>
            </div>
            
            {{-- Una pequeña referencia al final para que el cliente sepa qué turno exacto era el que se canceló --}}
            <p style="font-size: 12px; color: #6b7280; margin-top: 20px; font-style: italic;">
                * Esta consulta estaba programada originalmente para el día {{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }} a las {{ \Carbon\Carbon::parse($reserva->hora_inicio)->format('H:i') }}.
            </p>

        @else
            {{-- ==================== VISTA PARA RESERVA CONFIRMADA / OTROS ==================== --}}
            <p>Te informamos que ha habido una actualización en tu reserva para el servicio de <strong>{{ $reserva->servicio->nombre ?? 'Servicio' }}</strong>.</p>
            
            <p>El nuevo estado de tu turno es: 
                <span class="badge {{ strtolower($estado) === 'confirmada' ? 'badge-confirmada' : 'badge-default' }}">
                    {{ strtoupper($estado) }}
                </span>
            </p>

            <div class="details">
                <p style="margin: 0 0 10px 0;"><strong>📅 Fecha:</strong> {{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}</p>
                <p style="margin: 0 0 10px 0;"><strong>⏰ Hora:</strong> {{ \Carbon\Carbon::parse($reserva->hora_inicio)->format('H:i') }}</p>
                <p style="margin: 0;"><strong>👨‍🔧 Profesional:</strong> {{ $reserva->profesional->user->name ?? 'Profesional' }}</p>
            </div>
        @endif

        <p style="margin-top: 25px;">Si tienes alguna duda, puedes ingresar a la plataforma.</p>

        <div class="footer">
            © {{ date('Y') }} Gendar App. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>