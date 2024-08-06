<?php

namespace App\Http\Controllers;

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
            'session' => '556993718498',
            'group_in' => '120363038757400641',
            'group_out' => '120363040159954276'
        );
        $historyJobsUuid = app('App\Http\Controllers\util\HistoryJobsUtil')
            ->create('GroupMemberTransferJob', $dados);
            
        $job = new GroupMemberTransferJob($historyJobsUuid, $dados);

        // Executa a job diretamente
        $job->handle();
        /* GroupMemberTransferJob::dispatch($historyJobsUuid, $dados)
            ->onQueue('GroupMemberTransferJob'); */

        //////////////////////////////////////////

    }
}
