<?php

namespace App\Jobs;

use App\Models\Chat;
use App\Models\Contact;
use App\Models\Group;
use App\Models\GroupHasContacts;
use App\Models\HistoryJobs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OnParticipantsChangedServiceJob extends BaseJob
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
            $group = $this->dadosGroup();
            foreach ($this->dados['who'] as $number) {
                $contact = $this->dadosContact($number);

                $this->action($group, $contact, $this->dados['action']);
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    public function dadosGroup()
    {
        try {
            $group_id = strstr($this->dados['groupId'], '@', true);
            $grupo = Group::where('id', $group_id)->first();
            if (!$grupo) {
                Group::insert([
                    'id' => $group_id,
                    'name' => 'grupo',
                ]);
                $grupo = Group::where('id', $group_id)->first();
            }
            Chat::updateOrCreate(
                [
                    'sessionServer_id' => $this->dados['session'],
                    'group_id' => $group_id,
                ],
                [
                    'updated_at' => now(),
                ]
            );
            return $grupo;
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    public function dadosContact($number)
    {
        try {
            $contact = Contact::where('id', strstr($number, '@', true))->first();
            if (!$contact) {
                Contact::insert([
                    'id' => strstr($number, '@', true),
                ]);
                $contact = Contact::where('id', strstr($number, '@', true))->first();
            }
            return $contact;
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    public function action($group, $contact, $action)
    {
        try {
            switch ($action) {
                case 'add':
                    GroupHasContacts::create([
                        'group_id' => $group->id,
                        'contact_id' => $contact->id,
                    ]);
                    break;
                case 'remove':
                    $GroupHasContacts = GroupHasContacts::where('group_id', $group->id)
                        ->where('contact_id', $contact->id)
                        ->first();
                    if ($GroupHasContacts) {
                        $GroupHasContacts->delete();
                    }
                    break;
                case 'leave':
                    $GroupHasContacts = GroupHasContacts::where('group_id', $group->id)
                        ->where('contact_id', $contact->id)
                        ->first();
                    if ($GroupHasContacts) {
                        $GroupHasContacts->delete();
                    }
                    break;
                case 'promote':
                    $GroupHasContacts = GroupHasContacts::where('group_id', $group->id)
                        ->where('contact_id', $contact->id)
                        ->first();
                    if ($GroupHasContacts) {
                        $GroupHasContacts->isAdmin = true;
                        $GroupHasContacts->save();
                    }
                    break;
                case 'join':
                    //deleted
                    GroupHasContacts::withTrashed()
                        ->where('group_id', $group->id)
                        ->where('contact_id', $contact->id)
                        ->first();
                    $contact = Contact::where('id', $contact->id)->first();
                    break;
                default:
                    $client = new \GuzzleHttp\Client();
                    $client->post('https://webhook-test.com/217ccb170dd97aad3450a3c0766ba0fa', [
                        'json' => $this->dados // Encaminha todos os dados recebidos
                    ]);
                    return response()->json(['success' => false, 'message' => 'Action not found'], 404);
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }
}
