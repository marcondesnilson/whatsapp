<?php

namespace App\Jobs;

use App\Models\Chat;
use App\Models\Contact;
use App\Models\Group;
use App\Models\GroupHasContacts;
use App\Models\SessionServer;
use Illuminate\Support\Env;
use Ramsey\Uuid\Uuid;


class GroupMembersJob extends BaseJob
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
            $members = $this->membersGroup($this->dados['group_id']);

            foreach ($members as $member) {
                $this->insertContact($member);
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function membersGroup($group_id)
    {
        try {
            $curl = curl_init();
            $sessionServer = SessionServer::where('id', $this->dados['session'])->first();
            $url = Env::get('WHATSAPP_API_URL') . "/api/" . $this->dados['session'] . "/group-members/" . $group_id;
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
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
            return $data['response'] ?? array();
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function insertContact($member)
    {
        try {
            Contact::updateOrCreate(
                ['id' => $member['id']['user']],
                [
                    'name' => $member['name'] ?? $member['shortName'] ?? $member['pushname'] ?? null,
                    'pushname' => $member['pushname'] ?? $member['shortName'] ?? $member['name'] ?? null,
                    'photo_url' => $member['profilePicThumbObj']['eurl'] ?? null,
                ]
            );

            GroupHasContacts::create([
                'group_id' => $this->dados['group_id'],
                'contact_id' => $member['id']['user'],
            ]);
        } catch (\Throwable $e) {
            logError($e);
        }
    }
}
