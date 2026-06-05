<?php
namespace App\Http\Controllers\Api;

use App\Services\NotificacionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notificacion;

class NotificacionApiController extends Controller
{
    public function __construct(
        private NotificacionService $notificacionService
    ) {}


    public function index(Request $request) // listar notificaciones del usuario autenticado
    {
        $notificaciones = $this->notificacionService->listarParaUsuario($request->user());

        return response()->json([
            'data' => $notificaciones,
        ]);
    }

    public function unreadCount(Request $request) // contar no leídas
    {
        $cantidad = $this->notificacionService->contarNoLeidasParaUsuario($request->user());

        return response()->json([
            'count' => $cantidad,
        ]);
    }

    public function markAsRead(Request $request, Notificacion $notificacion) // marcar una notificación como leída
    {
        try {
            $notificacion = $this->notificacionService->marcarComoLeida(
                $notificacion,
                $request->user()
            );

            return response()->json([
                'message' => 'Notificación marcada como leída.',
                'data' => $notificacion,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 403);
        }

    }

    public function markAllAsRead(Request $request) // marcar todas como leídas
    {
        $actualizadas = $this->notificacionService->marcarTodasComoLeidas(
            $request->user()
        );

        return response()->json([
            'message' => 'Notificaciones marcadas como leídas.',
            'updated' => $actualizadas,
        ]);
    }

}