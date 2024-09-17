<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ThrottleQueue
{
    public function handle($job, Closure $next)
    {
        // Nome da fila do job
        $queue = $job->queue;

        // Cache key com o nome da fila
        $cacheKey = 'queue_count_' . $queue;

        // Recuperar a contagem atual
        $count = Cache::get($cacheKey, 0);

        if ($count >= 2) {
            // Se a contagem for maior ou igual a 2, rejeitar o job
            Log::warning("ThrottleQueue: Job da fila $queue rejeitado. Limite de 2 por minuto atingido.");
            return false;
        }

        // Incrementar a contagem e definir o tempo de expiração para 1 minuto
        Cache::put($cacheKey, $count + 1, 60);

        // Permitir a execução do job
        return $next($job);
    }
}
