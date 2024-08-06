<?php

namespace App\Jobs;

use App\Models\HistoryJobs;
use App\Models\SessionServer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Chat;
use App\Models\Contact;
use App\Models\Group;
use App\Models\GroupHasContacts;
use Illuminate\Support\Env;

class ListarAllChatsJobs extends BaseJob
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
            $sessionServer = SessionServer::where('id', $this->dados['session'])->first();
            $curl = curl_init();

            $url = Env::get('WHATSAPP_API_URL') . "/api/" . $sessionServer->id . "/all-chats";
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 120,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $sessionServer->token,
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $data = json_decode($response, true);
            foreach ($data['response'] as $item) {
                switch ($item['id']['server']) {
                    case 'g.us':
                        $this->grupo($item);
                        break;
                    case 'c.us':
                        $this->contato($item);
                        break;
                    default:
                        return $item['id']['server'];
                }
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    public function grupo($item)
    {
        try {
            Group::updateOrCreate(
                ['id' => $item['id']['user']],
                [
                    'name' => $item['name'] ?? $item['groupMetadata']['subject'] ?? $item['contact']['formattedName'] ?? 865,
                    'description' => $item['groupMetadata']['desc'] ?? null,
                    'server' => $item['id']['server'],
                ]
            );


            Chat::updateOrCreate(
                [
                    'sessionServer_id' => $this->dados['session'],
                    'group_id' => $item['id']['user']
                ],
                [
                    'muted' => false,
                    'archived' => false,
                    'pinned' => false,
                ]
            );
            foreach ($item['groupMetadata']['participants'] as $contato) {
                $this->grupo_has_contato($item['id']['user'], $contato);
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    public function contato($item)
    {
        try {
            $contact = Contact::where('id', $item['id']['user'])->first();
            if (!$contact) {
                Contact::create([
                    'id' => $item['id']['user']
                ]);
                $contact = Contact::where('id', $item['id']['user'])->first();
            }

            if ($item['pushname'] ?? false) {
                $contact->pushname = $item['pushname'];
                $contact->save();
            }

            Chat::updateOrCreate(
                [
                    'sessionServer_id' => $this->dados['session'],
                    'contact_id' => $item['id']['user']
                ],
                [
                    'last_message_at' => date('Y-m-d H:i:s', $item['t'] ?? time()),
                    'muted' => false,
                    'archived' => false,
                    'pinned' => false,
                ]
            );

            return $contact;
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    public function grupo_has_contato($grupo, $contato)
    {
        try{
        $contact = Contact::where('id', $contato['id']['user'])->first();
        if (!$contact) {
            Contact::create([
                'id' => $contato['id']['user']
            ]);
            $contact = Contact::where('id', $contato['id']['user'])->first();
        }

        GroupHasContacts::updateOrCreate(
            [
                'group_id' => $grupo,
                'contact_id' => $contact->id
            ],
            [
                'isAdmin' => $contato['isAdmin'],
                'isSuperAdmin' => $contato['isSuperAdmin'] ?? false,
            ]
        );
    } catch (\Throwable $e) {
        logError($e);
    }
    }
}
