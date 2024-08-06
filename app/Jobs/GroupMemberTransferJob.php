<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\SessionServer;
use Illuminate\Support\Env;



class GroupMemberTransferJob extends BaseJob
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
            $members = $this->membersGroup($this->dados['group_out']);

            foreach ($members as $member) {
                $contact_id = $this->insertContact($member);
                if ($contact_id) {
                    $this->addMemberGroup($contact_id);
                    ///$this->removeMemberGroup($contact_id);
                }
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
            $contact_id = $member['id']['user'];
            if ($contact_id) {
                Contact::updateOrCreate(
                    ['id' => $member['id']['user']],
                    [
                        'name' => $member['name'] ?? $member['shortName'] ?? $member['pushname'] ?? null,
                        'pushname' => $member['pushname'] ?? $member['shortName'] ?? $member['name'] ?? null,
                        'photo_url' => $member['profilePicThumbObj']['eurl'] ?? null,
                    ]
                );
                return $contact_id;
            } else {
                return false;
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    protected function addMemberGroup($contact_id)
    {
        try {
            $dados = array(
                'session' => $this->dados['session'],
                'group_id' => $this->dados['group_in'],
                'contact_id' => $contact_id
            );
            $historyJobsUuid = app('App\Http\Controllers\util\HistoryJobsUtil')
                ->create('GroupMemberAddJob', $dados);
            GroupMemberAddJob::dispatch($historyJobsUuid, $dados)
                ->onQueue('GroupMemberAddJob');
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function removeMemberGroup($contact_id)
    {
        try {
            $dados = array(
                'session' => $this->dados['session'],
                'group_id' => $this->dados['group_out'],
                'contact_id' => $contact_id
            );
            $historyJobsUuid = app('App\Http\Controllers\util\HistoryJobsUtil')
                ->create('GroupMemberDeleteJob', $dados);
            GroupMemberDeleteJob::dispatch($historyJobsUuid, $dados)
                ->onQueue('GroupMemberDeleteJob');
        } catch (\Throwable $e) {
            logError($e);
        }
    }


}
