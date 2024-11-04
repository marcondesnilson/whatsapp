<?php

namespace App\Jobs;

use App\Models\SessionServer;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Log;

class SendMessageJob extends BaseJob
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
            if ($this->dados['url']) {
                $this->sendMessageUrl();
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function sendMessageUrl()
    {
        try {
            $curl = curl_init();
            $sessionServer = SessionServer::where('id', $this->dados['sender'])->first();
            $url = Env::get('WHATSAPP_API_URL') . "/api/" . $this->dados['sender'] . "/send-link-preview";
            $body = [
                'phone' => $this->dados["receiver"],
                'url' => $this->dados['url'],
                'caption' => $this->dados['message'],
                'isGroup' => $this->dados['isGroup'],
            ];

            Log::info('body: ' . json_encode($body));
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $sessionServer->token,
                ),
            ));

            $response = curl_exec($curl);
            Log::info($response);

            curl_close($curl);

            $data = json_decode($response, true);
            if($data['message']){
                throw new \Exception($data['message']);
            }
            /* echo $response; */
        } catch (\Throwable $e) {
            logError($e);
        }
    }
}
