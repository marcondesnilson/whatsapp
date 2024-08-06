<?php

namespace App\Http\Controllers\util;

use App\Http\Controllers\Controller;
use App\Models\HistoryJobs;
use Ramsey\Uuid\Uuid;

class HistoryJobsUtil extends Controller
{
    public function create($queue,$dados)
    {
        $uuid = Uuid::uuid4();
        HistoryJobs::insert([
            'uuid' => $uuid,
            'dados' => json_encode($dados),
            'queue' => $queue,
            'created_at' => now()
        ]);
        return $uuid;
    }
}
