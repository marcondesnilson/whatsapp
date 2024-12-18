<?php

namespace App\Jobs;

use App\Models\Chat;
use App\Models\Contact;
use App\Models\Group;
use App\Models\GroupHasContacts;
use App\Models\HistoryJobs;
use App\Models\SessionServer;
use Exception;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;


class GroupMemberAddJob extends BaseJob
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
            $attempts = $this->adiar();
            if ($attempts['status'] == 'attempts') {
                return $attempts;
            }

            $addMembers = $this->addMemberGroup();
            if ($addMembers['status'] == 'error') {
                return $addMembers;
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function addMemberGroup()
    {
        try {
            $curl = curl_init();
            $sessionServer = SessionServer::where('id', $this->dados['session'])->first();
            $url = Env::get('WHATSAPP_API_URL') . "/api/" . $this->dados['session'] . "/add-participant-group";
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
            $responseData = json_decode($response, true);
            if ($responseData['status'] ?? false == 'Error') {
                throw new Exception('Error from API: ' . $responseData['response']['message']);
            }
            curl_close($curl);
            $data = json_decode($response, true);
            $result = $data['response']['result'][0][$this->dados['contact_id'] . '@c.us'] ?? array();

            if ($result['code'] == 200) {
                $this->insertContact();
                return array(
                    'status' => 'success'
                );
            } else {
                return array(
                    'status' => 'error',
                    'message' => $result['code'] ?? null . ' - ' . $result['message'] ?? $data['message'] ?? null
                );
            }
        } catch (\Throwable $e) {
            logError($e);
            throw $e;
        }
    }

    private function insertContact()
    {
        try {
            GroupHasContacts::withTrashed()
                ->updateOrCreate(
                    [
                        'group_id' => $this->dados['group_id'],
                        'contact_id' => $this->dados['contact_id'],
                    ],
                    [
                        'deleted_at' => null,
                    ]
                );
        } catch (\Throwable $e) {
            logError($e);
            throw $e;
        }
    }

    private function adiar()
    {
        try {
            $time = date('Y-m-d H:i');
            $exists = HistoryJobs::where('queue', 'GroupMemberAddJob')
                ->where('finish_at', 'like', "%{$time}%")
                ->exists();

            if ($exists) {
                return array(
                    'status' => 'attempts',
                    'message' => rand(30, 300)
                );
            } else {
                return array(
                    'status' => 'success'
                );
            }
        } catch (\Throwable $e) {
            logError($e);
            throw $e;
        }
    }
}
