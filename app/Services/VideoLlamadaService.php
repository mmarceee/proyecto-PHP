<?php

namespace App\Services;

use App\Models\User;
use App\Models\Reserva;
use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;

class VideoLlamadaService
{
     /**
     * Valida si el usuario tiene permiso para acceder a la videollamada de una reserva.
     */

    public function validarAcceso(User $user, Reserva $reserva): bool
    {
        
        $esCliente = $reserva->cliente && $reserva->cliente->user_id === $user->id;
        
        $esProfesional = $reserva->profesional && $reserva->profesional->user_id === $user->id;
        
        return $esCliente || $esProfesional;
    }

    /**
     * Genera un Token seguro para que un usuario real pueda unirse a una sala.
     */
    public function generarToken(User $user, string $nombreSala): string
    {
        // 1. Opciones del Token (Usamos los datos REALES del usuario autenticado)
        $tokenOptions = (new AccessTokenOptions())
            ->setIdentity((string) $user->id)
            ->setName($user->name)
            ->setTtl(3600); // El token expira por seguridad en 1 hora

        // 2. Permisos de la sala
        $videoGrant = (new VideoGrant())
            ->setRoomJoin(true)
            ->setRoomName($nombreSala)
            ->setCanPublish(true) // Puede emitir video/audio
            ->setCanSubscribe(true); // Puede recibir video/audio

        // 3. Crear el Token
         $token = new AccessToken(
            config('services.livekit.api_key'),
            config('services.livekit.api_secret')
        );

        // Cargamos las opciones y permisos al token
        $token->init($tokenOptions);
        $token->setGrant($videoGrant);

        // 4. Firmar y devolver el JWT final
        return $token->toJwt();
    }
}