<?php

namespace App\Jobs;

use App\Models\Chat;
use App\Models\Message;
use App\Models\SessionServer;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Log;

class ClearChatJob extends BaseJob
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
            $dadosClear = $this->chatDados();
            if ($dadosClear['clear']) {
                $this->clearChat($dadosClear);
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function chatDados()
    {
        $chat = Chat::where('uuid', $this->dados['chat_uuid'])->first();
        $dados  = array();

        $messages = Message::where('created_at', '>', $chat->clean_at ?? '2024-01-01')
            ->where('chat_uuid', $chat->uuid)
            ->count();

        $messages > 100 ? $dados['clear'] = true : $dados['clear'] = false;

        //Log::info('Chat: ' . $chat->uuid . ' - Messages: ' . $messages);

        if ($chat['group_id'] != null) {
            $dados['id'] = $chat['group_id'];
            $dados['isGroup'] = true;
        } else {
            $dados['id'] = $chat['contact_id'];
            $dados['isGroup'] = false;
        }

        return $dados;
    }

    private function clearChat($dadosClear)
    {
        try {
            $curl = curl_init();
            $sessionServer = SessionServer::where('id', $this->dados['session'])->first();
            $url = Env::get('WHATSAPP_API_URL') . "/api/" . $this->dados['session'] . "/clear-chat";
            $body = [
                'phone' => $dadosClear['id'],
                'isGroup' => $dadosClear['isGroup']
            ];
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

            curl_close($curl);
            $data = json_decode($response, true);
            if($data['status'] ?? null == 'Success'){
                return Chat::where('uuid', $this->dados['chat_uuid'])->update(['clean_at' => now()]);
            }
            else{
                throw new Exception('Error from API: ' . $data['response']['message']);
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }
}
