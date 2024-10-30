<?php

namespace App\Jobs;

use App\Models\Chat;
use App\Models\Contact;
use App\Models\Group;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Storage;

class OnMessageJob extends BaseJob
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
            $contato = explode('@', $this->dados['from']);
            if ($contato[1] != 'c.us') {
                if ($this->dados['author'] ?? false) {
                    $contato = explode('@', $this->dados['author']);
                } else {
                    $contato = null;
                }
            }

            $contact_id = $this->contato($contato);

            $chat_uuid = $this->chat($contact_id);

            //$this->clearChat($chat_uuid);
            $type = $this->type($contato);

            $datatime = $this->datatime($this->dados['t']);

            $message_text = $this->dados['type'] == 'chat' ?  $this->dados['body'] ?? null : null;
            Message::insert([
                'uuid' => Uuid::uuid4(),
                'message_id' => $this->dados['id']['_serialized'] ?? $this->dados['id'],
                'message_type' => $this->dados['type'],
                'message_body' => $message_text,
                'contact_id' => $contact_id,
                'chat_uuid' => $chat_uuid,
                'type' => $type,
                'sent_at' => $datatime,
                'created_at' => now()
            ]);

            $this->linkGroup($message_text);
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function contato($contato)
    {
        try {
            if (!$contato) {
                return null;
            }
            $contact_id = Contact::where('id', $contato[0])->value('id');
            if (!$contact_id) {
                Contact::insert([
                    'id' => $contato[0],
                    'name' => null,
                    'created_at' => now()
                ]);
                $contact_id = Contact::where('id', $contato[0])->value('id');
            }
            return $contact_id;
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function chat($contact_id)
    {
        try {
            $type = explode('@', $this->dados['chatId'] ?? $this->dados['to']);
            switch ($type[1]) {
                case 'g.us':
                    $group_id = $this->grupo($type);
                    $chat = Chat::withTrashed()
                        ->where('sessionServer_id', $this->dados['session'])
                        ->where('group_id', $type[0])
                        ->first();
                    if (!$chat) {
                        $chat_uuid = Uuid::uuid4();
                        Chat::insert([
                            'uuid' => $chat_uuid,
                            'sessionServer_id' => $this->dados['session'],
                            'group_id' => $type[0],
                            'last_message_at' => now(),
                            'created_at' => now()
                        ]);
                    } else {
                        $chat->deleted_at = null;
                        $chat->last_message_at = now();
                        $chat->save();
                        $chat_uuid = $chat->uuid;
                    }
                    $this->countMessage($contact_id, $group_id);
                    return $chat_uuid;
                case 'c.us':
                    $chat = Chat::withTrashed()
                        ->where('sessionServer_id', $this->dados['session'])
                        ->where('contact_id', $type[0])
                        ->first();
                    if (!$chat) {
                        $chat_uuid = Uuid::uuid4();
                        Chat::insert([
                            'uuid' => $chat_uuid,
                            'sessionServer_id' => $this->dados['session'],
                            'contact_id' => $type[0],
                            'last_message_at' => now(),
                            'created_at' => now()
                        ]);
                    } else {
                        $chat->deleted_at = null;
                        $chat->last_message_at = now();
                        $chat->save();
                        $chat_uuid = $chat->uuid;
                    }
                    return $chat_uuid;
                default:
                    return null;
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function type($contato)
    {
        try {
            $fromMe = $this->dados['id']['fromMe'] ?? false;

            if ($fromMe) {
                return 'sender';
            } else {
                return 'receiver';
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function grupo($type)
    {
        try {
            $group_id = $type[0];
            $group = Group::where('id', $group_id)->first();
            if (!$group) {
                Group::insert([
                    'id' => $group_id,
                    'name' => 'new_group',
                ]);
                $group = Group::where('id', $group_id)->first();
            }
            return $group->id;

            //inserir funcao para pegar dados do novo grupo(futuro)
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function datatime($t)
    {
        try {

            // Converte o timestamp para o formato yyyy-mm-dd hh:ii:ss
            $dataFormatada = date('Y-m-d H:i:s', $t);

            // Exibe a data formatada
            return $dataFormatada;
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function countMessage($contact_id, $group_id)
    {
        try {
            $dados = array(
                'contact_id' => "{$contact_id}",
                'group_id' => "{$group_id}"
            );
            $historyJobsUuid = app('App\Http\Controllers\util\HistoryJobsUtil')
                ->create('CountMessageJob', $dados);
            CountMessageJob::dispatch($historyJobsUuid, $dados)
                ->onQueue('CountMessageJob');
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function linkGroup($message)
    {
        try {
            // Expressão regular para encontrar URLs
            $regex = '/https?:\/\/[^\s]+/';

            // Encontrar todas as URLs no texto
            preg_match_all($regex, $message, $matches);

            // Verificar se algum dos links contém 'chat.whatsapp.com'
            foreach ($matches[0] as $url) {
                if (strpos($url, 'chat.whatsapp.com') !== false) {
                    $this->linkWhatsapp($url);
                    $exist = Group::where('link', $url)->exists();
                    if (!$exist) {

                        $dados = array(
                            'session' => $this->dados['session'],
                            'link' => $url
                        );
                        $historyJobsUuid = app('App\Http\Controllers\util\HistoryJobsUtil')
                            ->create('JoinGroupLinkJob', $dados);
                        JoinGroupLinkJob::dispatch($historyJobsUuid, $dados)
                            ->onQueue('JoinGroupLinkJob');
                    }
                }
            }

            return false;
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function linkWhatsapp($url)
    {
        try {

            // Definir o caminho do arquivo
            $filePath = date('Y-m').'.txt';

            // Verificar se o arquivo existe
            if (!Storage::exists($filePath)) {
                // Se o arquivo não existir, criar e escrever a URL
                Storage::put($filePath, $url . "\n");
            } else {
                // Se o arquivo existir, ler o conteúdo
                $fileContent = Storage::get($filePath);
                $lines = explode("\n", trim($fileContent));

                // Verificar se a URL já existe no arquivo
                if (!in_array($url, $lines)) {
                    // Se a URL não estiver no arquivo, adicioná-la
                    Storage::append($filePath, $url);
                    Log::info('URL adicionada ao arquivo.');
                }
                else {
                    // Se a URL já estiver no arquivo, exibir uma mensagem
                    Log::info('A URL já existe no arquivo.');
                }
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

    private function clearCha7t($chat_uuid)
    {
        try {
            $dados = array(
                'session' => $this->dados['session'],
                'chat_uuid' => $chat_uuid
            );
            $historyJobsUuid = app('App\Http\Controllers\util\HistoryJobsUtil')
                ->create('ClearChatJob', $dados);
            ClearChatJob::dispatch($historyJobsUuid, $dados)
            ->onQueue('ClearChatJob');
        } catch (\Throwable $e) {
            logError($e);
        }
    }
}
