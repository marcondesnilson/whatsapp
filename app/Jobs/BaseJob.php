<?php

namespace App\Jobs;

use App\Exceptions\JoinGroupLinkException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\HistoryJobs;
use Exception;
use Illuminate\Support\Facades\Log;

abstract class BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $historyJobsUuid;

    public function __construct($historyJobsUuid)
    {
        $this->historyJobsUuid = $historyJobsUuid;
    }

    public function handle()
    {
        $historyJobs = HistoryJobs::where('uuid', $this->historyJobsUuid)->first();
        if ($historyJobs->status == 'cancelled') {
            return;
        }

        try {
            // Calcula o tempo de espera
            $insertedAt = $historyJobs->reserved_at ?? $historyJobs->created_at;
            $startTime = now();
            $waitTime = $insertedAt->diffInMilliseconds($startTime);

            // Atualiza o status para "in_progress"
            $historyJobs->status = 'in_progress';
            $historyJobs->start_at = $startTime;
            $historyJobs->wait_time = $waitTime;
            $historyJobs->save();

            // Executa o job específico
            $result = $this->executeJob();

            $this->jobResult($startTime, $result);
        } catch (\Throwable $e) {
            // Calcula o tempo de execução
            $finishTime = now();
            $executionTime = $startTime->diffInMilliseconds($finishTime);

            // Atualiza o status para "failed" e salva o tempo de execução e espera
            $historyJobs->status = 'failed';
            $historyJobs->error_message = $e->getMessage();
            $historyJobs->finish_at = $finishTime;
            $historyJobs->execution_time = $executionTime;
            $historyJobs->save();

            logError($e);
        }
    }

    abstract protected function executeJob();

    private function SendPusher($dados)
    {
        Log::info('SendPusher');
    }

    private function JobResult($startTime, $result)
    {
        try {
            $historyJobs = HistoryJobs::where('uuid', $this->historyJobsUuid)->first();
            $status = $result['status'] ?? 'success';
            if ($status == 'error') {
                $finishTime = now();
                $executionTime = $startTime->diffInMilliseconds($finishTime);
                $historyJobs->status = 'failed';
                $historyJobs->error_message = $result['message'];
                $historyJobs->finish_at = $finishTime;
                $historyJobs->execution_time = $executionTime;
                $historyJobs->save();
            } else if ($status == 'delete') {
                HistoryJobs::where('uuid', $this->historyJobsUuid)->delete();
            } else if ($status == 'attempts') {
                $attempts = intval($result['message']);
                $historyJobs->status = 'attempts';
                $historyJobs->save();
                return $this->release($attempts);
            } else {
                // Calcula o tempo de execução
                $finishTime = now();
                $executionTime = $startTime->diffInMilliseconds($finishTime);

                // Atualiza o status para "completed" e salva o tempo de execução
                $historyJobs->status = 'completed';
                $historyJobs->finish_at = $finishTime;
                $historyJobs->execution_time = $executionTime;
                $historyJobs->error_message = null;
                $historyJobs->save();
                $historyJobs->delete();
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    public function failed(Exception $exception)
    {
        try {
            // Lógica de tratamento quando o job falha após todas as tentativas
            // Exemplo: Registrar log, enviar notificação, etc.
            Log::error('Job failed after maximum attempts.', [
                'exception' => $exception,
                'job' => 'SendMessageJob',
            ]);
        } catch (\Throwable $e) {
            logError($e);
        }
    }
}


//pending: O job foi criado e está aguardando para ser processado.
//in_progress: O job está sendo executado.
//completed: O job foi concluído com sucesso.
//failed: O job falhou durante a execução.
//cancelled: O job foi cancelado antes ou durante a execução.
