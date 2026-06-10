<?php

namespace App\Services;

use App\Models\User;
use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;

class VideoLlamadaService
{
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
            env('LIVEKIT_API_KEY'),
            env('LIVEKIT_API_SECRET')
        );

        // Cargamos las opciones y permisos al token
        $token->init($tokenOptions);
        $token->setGrant($videoGrant);

        // 4. Firmar y devolver el JWT final
        return $token->toJwt();
    }
}