<?php

namespace App\Http\Controllers\util;

use App\Http\Controllers\Controller;
use App\Models\HistoryJobs;
use Illuminate\Support\Facades\Cache;
use Ramsey\Uuid\Uuid;

class HistoryJobsUtil extends Controller
{
    public function create($queue, $dados)
    {
        for ($i = 0; $i < 3; $i++) {
            $lock = Cache::lock('history_jobs_lock', 10);
            if ($lock->get()) {
                $uuid = Uuid::uuid4();
                HistoryJobs::insert([
                    'uuid' => $uuid,
                    'dados' => json_encode($dados),
                    'queue' => $queue,
                    'created_at' => now()
                ]);
                return $uuid;
            }
            sleep(1);
        }
    }
}
