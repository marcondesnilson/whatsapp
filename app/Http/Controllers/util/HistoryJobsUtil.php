<?php

namespace App\Http\Controllers\Util;

use App\Http\Controllers\Controller;
use App\Models\HistoryJobs;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;

class HistoryJobsUtil extends Controller
{
    public function create($queue, $dados)
    {
        return Cache::lock('history_jobs_lock', 10)->get(function () use ($queue, $dados) {
            $uuid = Uuid::uuid4()->toString(); // Certifique-se de gerar o UUID como string
            HistoryJobs::insert([
                'uuid' => $uuid,
                'dados' => json_encode($dados),
                'queue' => $queue,
                'created_at' => now(),
                'updated_at' => now(), // Incluído para suportar timestamps padrão
            ]);
            return $uuid;
        });
    }
}
