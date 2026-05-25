<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GestionUsuariosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsuarioAdminApiController extends Controller
{
    protected GestionUsuariosService $usuarioAdminService;

    public function __construct(GestionUsuariosService $usuarioAdminService)
    {
        $this->usuarioAdminService = $usuarioAdminService;
    }

    public function index(Request $request): JsonResponse
    {
        $usuarios = $this->usuarioAdminService->listarUsuarios($request->user());

        return response()->json([
            'usuarios' => $usuarios,
        ]);
    }

    public function bloquear(Request $request, User $user): JsonResponse
    {
        try {
            $usuario = $this->usuarioAdminService->bloquear($request->user(), $user);

            return response()->json([
                'message' => 'Usuario bloqueado correctamente.',
                'usuario' => [
                    'id' => $usuario->id,
                    'estado_usuario' => $usuario->estado_usuario,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    public function desbloquear(Request $request, User $user): JsonResponse
    {
        try {
            $usuario = $this->usuarioAdminService->desbloquear($request->user(), $user);

            return response()->json([
                'message' => 'Usuario desbloqueado correctamente.',
                'usuario' => [
                    'id' => $usuario->id,
                    'estado_usuario' => $usuario->estado_usuario,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    public function hacerAdmin(Request $request, User $user): JsonResponse
    {
        try {
            $usuario = $this->usuarioAdminService->hacerAdmin($request->user(), $user);

            return response()->json([
                'message' => 'Usuario convertido en administrador correctamente.',
                'usuario' => [
                    'id' => $usuario->id,
                    'es_admin' => $usuario->admin !== null,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }
    }
}