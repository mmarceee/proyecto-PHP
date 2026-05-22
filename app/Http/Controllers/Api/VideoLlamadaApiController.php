<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\VideoLlamadaService;
use App\Models\Reserva; // Asegúrate de importar el modelo

class VideoLlamadaController extends Controller
{
    protected $videoService;

    public function __construct(VideoLlamadaService $videoService)
    {
        $this->videoService = $videoService;
    }

    public function obtenerToken(Request $request, Reserva $reserva)
    {
        $user = $request->user(); // El usuario autenticado

        // Aquí deberías agregar lógica de seguridad para comprobar si este usuario
        // realmente pertenece a esta reserva (es el cliente que la pidió, o el profesional asignado).
        // if (!$user->perteneceAReserva($reserva)) { return abort(403); }

        // El nombre de la sala será "reserva_" + el ID de la reserva.
        $nombreSala = 'reserva_' . $reserva->id;

        // Generamos el token mágico
        $token = $this->videoService->generarToken($user, $nombreSala);

        return response()->json([
            'token' => $token,
            'url' => env('LIVEKIT_URL'), // Le mandamos también la URL al frontend
            'sala' => $nombreSala
        ]);
    }
}