<?php

namespace App\Http\Controllers;

use App\Jobs\ClearChatJob;
use Illuminate\Http\Request;
use App\Jobs\ListarAllChatsJobs;
use App\Jobs\GroupMemberTransferJob;
use App\Jobs\OnParticipantsChangedServiceJob;
use App\Models\HistoryJobs;

class teste extends Controller
{
    
    public function index()
    {

        
        set_time_limit(300);
        // JSON string

        $dados = array(
            'session' => $this->dados['session'],
            'chat_uuid' => 'a7587bbd-82be-44b8-ace2-1002f3eea09a'
        );
        $historyJobsUuid = app('App\Http\Controllers\util\HistoryJobsUtil')
            ->create('ClearChatJob', $dados);
        ClearChatJob::dispatch($historyJobsUuid, $dados)
        ->onQueue('ClearChatJob');

    }
}
