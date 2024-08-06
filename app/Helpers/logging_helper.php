<?php

use App\Jobs\LogSendTelegramJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

if (!function_exists('logError')) {
    /**
     * Log an error with detailed information.
     *
     * @param \Throwable $e
     * @return void
     */
    function logError(\Throwable $e)
    {
        $trace = $e->getTrace();
        $errorFile = $e->getFile();
        $errorLine = $e->getLine();

        foreach ($trace as $traceEntry) {
            if (isset($traceEntry['file']) && strpos($traceEntry['file'], 'vendor/laravel/framework') === false) {
                $errorFile = $traceEntry['file'];
                $errorLine = $traceEntry['line'];
                break;
            }
        }


        $errorMessage = "Erro capturado:\n";
        $errorMessage .= "Ambiente: " . env('APP_ENV') . "\n";
        $errorMessage .= "Arquivo: {$errorFile}:{$errorLine}\n";
        /* $errorMessage .= "Linha: {$errorLine}\n"; */
        $errorMessage .= "Mensagem: {$e->getMessage()}\n";

        Log::error($errorMessage);

        /* $dados = $errorMessage;
        $historyJobsUuid = app('App\Http\Controllers\util\HistoryJobsUtil')
                ->create('DocumentTypeUpdateJob', $dados);
        LogSendTelegramJob::dispatch($historyJobsUuid, $dados)->onQueue('LogSendTelegramJob'); */

        $errorDetails = [
            'type' => 'toast',
            'icon' => 'error',
            'title' => __('geral.erro'),
            'text' => $e->getMessage(),
        ];
        return $errorDetails ?? null;
    }
}
