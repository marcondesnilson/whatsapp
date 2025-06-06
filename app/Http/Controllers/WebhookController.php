<?php

namespace App\Http\Controllers;

use App\Jobs\OnParticipantsChangedServiceJob;
use App\Jobs\OnMessageJob;
use App\Jobs\WebhookInSaveJob;
use Illuminate\Http\Request;
use App\Services\Webhook\StatusFindService;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{

    public function index(Request $request)
    {
        try {
            //if onmessage ou onpresencechanged
            /* if ($request->input('event') == 'onmessage' || $request->input('event') == 'onpresencechanged') {
                $dados = $request->all();
                $historyJobsUuid = app('App\Http\Controllers\util\HistoryJobsUtil')
                    ->create('WebhookInSaveJob', $dados);
                WebhookInSaveJob::dispatch($historyJobsUuid, $dados)
                    ->onQueue('WebhookInSaveJob');
            } */

                //Log::info($request->input('session'));
            if($request->input('session') == '556993258571') {
                //Log::info('Enviando para o webhook');
                $this->sendToWebhook($request);
            }
            else {
                //Log::info('Webhook não enviado');
            }
            switch ($request->input('event')) {
                case 'onmessage':
                    $this->onMessage($request);
                    break;
                case 'onparticipantschanged':
                    $this->onParticipantsChanged($request);
                    break;
                case 'status-find':
                    $this->statusFind($request);
                    break;
                default:
                    //$this->sendToWebhook($request);
                    break;
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    public function statusFind($request)
    {
        return StatusFindService::all($request);
    }

    public function onMessage($request)
    {
        try {
            $dados = $request->all();
            $historyJobsUuid = app('App\Http\Controllers\util\HistoryJobsUtil')
                ->create('OnMessageJob', $dados);
            OnMessageJob::dispatch($historyJobsUuid, $dados)
                ->onQueue('OnMessageJob');
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    public function onParticipantsChanged($request)
    {
        try {
            $dados = $request->all();
            $historyJobsUuid = app('App\Http\Controllers\util\HistoryJobsUtil')
                ->create('OnParticipantsChangedServiceJob', $dados);
            OnParticipantsChangedServiceJob::dispatch($historyJobsUuid, $dados)
                ->onQueue('OnParticipantsChangedServiceJob');
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    public function sendToWebhook(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $client->post('https://n8n.diegof.com.br/webhook-test/0f69e555-f69b-4099-8a35-a30b43d5e270', [
            'json' => $request->all() // Encaminha todos os dados recebidos
        ]);
    }
}
