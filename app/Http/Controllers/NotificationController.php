<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{
    /**
     * Disparar notificação
     *
     * Recebe uma notificação, junto dos usuários/grupos que a receberão e dispara-a.
     */
    public function dispararNotificacao(Request $request)
    {
        try {
            $request->validate([
                'title' => ['nullable', 'string'],
                'message' => ['required', 'string'],
                'type' => ['required', 'string', 'in:info,warning,error,success'],
                'menu_id' => ['nullable', 'exists:menus,id'],
                'link' => ['nullable', 'string'],
                'users' => ['array'],
                'users.*' => ['exists:users,id'],
            ]);

            $usersIds = $request->input('users', []);
            $query = User::query();

            if (empty($usersIds)) {
                $users = $query->get();
            }
            else {
                $query->where(function ($q) use ($usersIds) {
                    if (!empty($usersIds)) {
                        $q->orWhereIn('id', $usersIds);
                    }
                });

                $users = $query->get()->unique('id')->values();
            }

            $requestData = $request->only(['title', 'message', 'type', 'menu_id', 'link']);

            foreach ($users as $user) {
                $data = array_merge($requestData, [
                    'user_id' => $user->id,
                    'id' => Str::uuid()->toString(),
                ]);

                $user->notify(new UserNotification($data));
            }

            return response()->json([
                'success' => true,
                'message' => 'Notificação disparada com sucesso.',
                'data' => $users,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao disparar notificação: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buscar notificações
     *
     * Busca as notificações do usuário autenticado.
     */
    public function getAll(Request $request)
    {

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $notifications = Notification::query()
            ->with('menu')
            ->where('user_id', $user->id)
            ->orderBy('sent_at', 'asc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'link' => $notification->menu ? $notification->menu->route : null,
                    'sent_at' => $notification->sent_at,
                    'read' => $notification->read_date !== null,
                ];
            })
        ;

        return response()->json([
            'success' => true,
            'message' => 'Notificações consultadas com sucesso.',
            'data' => $notifications,
        ]);
    }

    /**
     * Marcar como lida
     *
     * Recebe o ID de uma notificação e a marca como lida.
     */
    public function marcarComoLida(Request $request)
    {
        try {
            $request->validate([
                'id' => ['required', 'array'],
                'id.*' => ['required', 'string', 'exists:notifications,id'],
            ]);

            $notifications = Notification::query()
                ->whereIn('id', $request->input('id'))
                ->get()
            ;

            foreach ($notifications as $notification) {
                if ($notification->user_id === $request->user()->id) {
                    $notification->read_date = now();
                    $notification->save();
                }
            }

            return response()->json([
                'success' => true,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar notificação como lida: ' . $e->getMessage(),
            ], 500);
        }
    }
}
