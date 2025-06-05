<?php

namespace App\Http\Controllers;

use App\Jobs\OnParticipantsChangedServiceJob;
use App\Jobs\OnMessageJob;
use App\Jobs\WebhookInSaveJob;
use Illuminate\Http\Request;
use App\Services\Webhook\StatusFindService;

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

            if($request->input('session') == '556993258571') {
                $this->sendToWebhook($request);
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
                    $this->sendToWebhook($request);
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
        /* $client = new \GuzzleHttp\Client();
        $client->post('https://webhook-test.com/217ccb170dd97aad3450a3c0766ba0fa', [
            'json' => $request->all() // Encaminha todos os dados recebidos
        ]); */
    }
}
