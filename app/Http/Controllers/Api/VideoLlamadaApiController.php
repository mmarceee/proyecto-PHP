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

        // El nombre de la sala ahora sí será el ID real de la base de datos
        $nombreSala = 'reserva_' . $reserva->id;

        $token = $this->videoService->generarToken($user, $nombreSala);

        return response()->json([
            'token' => $token,
            'url' => env('LIVEKIT_URL'),
            'sala' => $nombreSala
        ]);
    }
}