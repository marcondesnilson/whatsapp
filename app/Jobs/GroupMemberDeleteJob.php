<?php

namespace App\Jobs;

use App\Models\Chat;
use App\Models\Contact;
use App\Models\Group;
use App\Models\GroupHasContacts;
use App\Models\SessionServer;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;


class GroupMemberDeleteJob extends BaseJob
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
            $addMembers = $this->removeMemberGroup();
            if ($addMembers['status'] == 'error') {
                return $addMembers;
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function removeMemberGroup()
    {
        try {
            $curl = curl_init();
            $sessionServer = SessionServer::where('id', $this->dados['session'])->first();
            $url = Env::get('WHATSAPP_API_URL') . "/api/" . $this->dados['session'] . "/remove-participant-group";
            $body = [
                'groupId' => $this->dados['group_id'],
                'phone' => $this->dados['contact_id'],
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
            $result = $data['response'] ?? array();

            Log::info($result);
            if ($data['status'] == 'success') {
                $this->deleteContact();
                return;
            } else {
                return array(
                    'status' => 'error',
                    'message' => $result['code'] . ' - ' . $result['message'] ?? $data['message'] ?? null
                );
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function deleteContact()
    {
        try {
            GroupHasContacts::where('group_id', $this->dados['group_id'])
                ->where('contact_id', $this->dados['contact_id'])
                ->delete();
        } catch (\Throwable $e) {
            logError($e);
        }
    }
}
