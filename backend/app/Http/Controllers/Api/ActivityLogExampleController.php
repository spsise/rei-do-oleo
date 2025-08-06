<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Activitylog\Models\Activity;

class ActivityLogExampleController extends Controller
{
    /**
     * Exemplo de como usar Activity Log manualmente
     */
    public function logExample(Request $request): JsonResponse
    {
        // Log simples
        activity()
            ->log('Exemplo de log manual');

        // Log com contexto
        activity()
            ->causedBy($request->user())
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'action' => 'log_example'
            ])
            ->log('Ação do usuário registrada');

        // Log de operação de negócio
        activity()
            ->causedBy($request->user())
            ->withProperties([
                'operation' => 'user_management',
                'details' => 'Usuário acessou exemplo de logging'
            ])
            ->useLogName('business_operations')
            ->log('Operação de negócio executada');

        return response()->json([
            'message' => 'Logs criados com sucesso',
            'total_logs' => Activity::count()
        ]);
    }

    /**
     * Exemplo de como consultar logs
     */
    public function getLogs(Request $request): JsonResponse
    {
        $logs = Activity::with(['causer', 'subject'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'log_name' => $activity->log_name,
                    'causer' => $activity->causer ? [
                        'id' => $activity->causer->id,
                        'name' => $activity->causer->name
                    ] : null,
                    'subject' => $activity->subject ? [
                        'type' => $activity->subject_type,
                        'id' => $activity->subject_id
                    ] : null,
                    'properties' => $activity->properties,
                    'created_at' => $activity->created_at->toISOString()
                ];
            });

        return response()->json([
            'logs' => $logs,
            'total' => Activity::count()
        ]);
    }

    /**
     * Exemplo de logs automáticos em modelos
     */
    public function createUser(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8'
        ]);

        // Criar usuário (logs automáticos serão criados)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        // Log adicional manual
        activity()
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties([
                'created_by' => $request->user()?->id,
                'ip' => $request->ip()
            ])
            ->useLogName('user_management')
            ->log('Usuário criado via API');

        return response()->json([
            'message' => 'Usuário criado com sucesso',
            'user' => $user,
            'logs_created' => Activity::where('subject_id', $user->id)->count()
        ], 201);
    }

    /**
     * Exemplo de logs de segurança
     */
    public function securityExample(Request $request): JsonResponse
    {
        // Log de tentativa de acesso
        activity()
            ->causedBy($request->user())
            ->withProperties([
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent()
            ])
            ->useLogName('security')
            ->log('Tentativa de acesso a recurso sensível');

        return response()->json([
            'message' => 'Acesso registrado',
            'security_logs' => Activity::inLog('security')->count()
        ]);
    }

    /**
     * Exemplo de logs de performance
     */
    public function performanceExample(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        // Simular operação pesada
        sleep(1);

        $duration = (microtime(true) - $startTime) * 1000;

        // Log de performance
        activity()
            ->causedBy($request->user())
            ->withProperties([
                'duration_ms' => $duration,
                'memory_peak' => memory_get_peak_usage(true),
                'endpoint' => $request->path()
            ])
            ->useLogName('performance')
            ->log('Operação de performance monitorada');

        return response()->json([
            'message' => 'Performance monitorada',
            'duration_ms' => $duration,
            'performance_logs' => Activity::inLog('performance')->count()
        ]);
    }
}
