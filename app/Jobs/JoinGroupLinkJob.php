<?php

namespace App\Jobs;

use App\Exceptions\JoinGroupLinkException;
use App\Models\Chat;
use App\Models\Group;
use App\Models\SessionServer;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;


class JoinGroupLinkJob extends BaseJob
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
            $exist = Group::where('link', $this->dados['link'])->exists();
            if ($exist) {
                return array('status' => 'delete', 'message' => 'já existe');
            }
            //$join = $this->JoinGroup($this->dados['link']);

            $infoGroup = $this->infoGroup($this->dados['link']);

            if ($infoGroup != 'error') {
                $group_id = $this->insertGroup($infoGroup);
            }

            //if ($join['status'] == 'success') {
            //    $this->membersGroup($group_id);
            //} else {
            //    return $join;
            //}
        } catch (\Throwable $e) {
            logError($e);
            throw $e;
        }
    }

    private function JoinGroup($link)
    {
        try {
            $curl = curl_init();
            $sessionServer = SessionServer::where('id', $this->dados['session'])->first();
            $url = Env::get('WHATSAPP_API_URL') . "/api/" . $this->dados['session'] . "/join-code";
            $body = [
                'inviteCode' => $link
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
            return $data;
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function InfoGroup($link)
    {
        try {
            $curl = curl_init();
            $sessionServer = SessionServer::where('id', $this->dados['session'])->first();
            $url = Env::get('WHATSAPP_API_URL') . "/api/" . $this->dados['session'] . "/group-info-from-invite-link";
            $body = [
                'invitecode' => $link
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
            return $data['response'] ?? 'error';
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function insertGroup($infoGroup)
    {
        try {
            $group_id = strstr($infoGroup['id'], '@', true);
            $g = Group::updateOrCreate(
                ['id' => $group_id],
                [
                    'name' => $infoGroup['subject'] ?? null,
                    'description' => $infoGroup['desc'] ?? null,
                    'link' => $this->dados['link'],
                ]
            );
            Chat::updateOrCreate(
                [
                    'sessionServer_id' => $this->dados['session'],
                    'group_id' => $group_id
                ],
                [
                    'muted' => false,
                    'archived' => false,
                    'pinned' => false,
                ]
            );
            return $group_id;
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function membersGroup($group_id)
    {
        try {
            $dados = array(
                'session' => $this->dados['session'],
                'group_id' => $group_id
            );
            $historyJobsUuid = app('App\Http\Controllers\util\HistoryJobsUtil')
                ->create('GroupMembersJob', $dados);
            GroupMembersJob::dispatch($historyJobsUuid, $dados)
                ->onQueue('GroupMembersJob');
        } catch (\Throwable $e) {
            logError($e);
            throw $e;
        }
    }
}
