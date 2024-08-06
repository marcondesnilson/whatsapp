<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\WebhookIn;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\DB;

class WebhookInSaveJob extends BaseJob
{
    protected $dados;

    public function __construct($historyJobsUuid, $dados)
    {
        parent::__construct($historyJobsUuid);
        $this->dados = $dados;
    }

    protected function executeJob()
    {
        try {
            DB::transaction(function () {
                WebhookIn::insert([
                    'uuid' => Uuid::uuid4(),
                    'sessionServer_id' => $this->dados['session'] ?? null,
                    'event' => $this->dados['event'],
                    'dados' => json_encode($this->dados),
                    'created_at' => now(),
                ]);
            }, 10);
        } catch (\Throwable $e) {
            logError($e);
        }
    }
}
