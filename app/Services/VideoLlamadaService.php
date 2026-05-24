<?php

namespace App\Services;

use App\Models\User;
use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;

class VideoLlamadaService
{
    /**
     * Genera un Token seguro para que un usuario pueda unirse a una sala de LiveKit.
     * * @param User $user El usuario que se va a conectar (Cliente o Profesional)
     * @param string $nombreSala El identificador único de la reserva (ej: "reserva_123")
     * @return string El token JWT gigante
     */
    public function generarToken(User $user, string $nombreSala): string
    {
        // 1. Opciones del Token (Cuánto dura y quién es)
        $tokenOptions = (new AccessTokenOptions())
            ->setIdentity((string) $user->id) // El ID interno del usuario
            ->setName($user->name . ' ' . $user->apellido) // Su nombre real para mostrar en pantalla
            ->setTtl(3600); // El token expira en 1 hora (3600 segundos) por seguridad

        // 2. Permisos (Qué puede hacer en la sala)
        $videoGrant = (new VideoGrant())
            ->setRoomJoin(true) // Puede entrar a una sala
            ->setRoomName($nombreSala) // A esta sala específica
            ->setCanPublish(true) // Puede prender su cámara/micro
            ->setCanSubscribe(true); // Puede ver y escuchar al otro

        // 3. Crear el Token usando las llaves de nuestro .env
        $token = new AccessToken(
            env('LIVEKIT_API_KEY'),
            env('LIVEKIT_API_SECRET')
        );

        $token->setGrant($videoGrant);

        // 4. Firmar y devolver el "Pase VIP"
        return $token->toJwt($tokenOptions);
    }
}