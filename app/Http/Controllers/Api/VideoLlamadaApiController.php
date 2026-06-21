<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\VideoLlamadaService;
use App\Models\Reserva; 

class VideoLlamadaApiController extends Controller
{
    protected $videoService;

    public function __construct(VideoLlamadaService $videoService)
    {
        $this->videoService = $videoService;
    }

    public function obtenerToken(Request $request, Reserva $reserva)
    {
        $user = $request->user();

        // VALIDACIÓN DE SEGURIDAD (IDOR)
        if (!$this->videoService->validarAcceso($user, $reserva)) {
            return response()->json([
                'error' => 'No tienes permiso para acceder a esta videollamada.'
            ], 403);
        }

        // El nombre de la sala ahora será el ID de la base de datos
        $nombreSala = 'reserva_' . $reserva->id;

        $token = $this->videoService->generarToken($user, $nombreSala);

        return response()->json([
            'token' => $token,
            'url' => config('services.livekit.url'),
            'sala' => $nombreSala
        ]);
    }
}