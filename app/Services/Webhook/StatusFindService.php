<?php

namespace App\Services\Webhook;

use App\Jobs\ListarAllChatsJobs;
use App\Models\SessionServer;

abstract class StatusFindService
{
    public static function all($request)
    {
        try {
            $SessionServer = SessionServer::where('id', $request->input('session'))->first();

            if ($SessionServer) {
                if ($request->input('status') == 'inChat' && $request->input('status') != $SessionServer->status) {
                    $dados = $request->all();
                    $historyJobsUuid = app('App\Http\Controllers\util\HistoryJobsUtil')
                        ->create('ListarAllChatsJobs', $dados);
                    ListarAllChatsJobs::dispatch($historyJobsUuid, $dados)
                        ->onQueue('ListarAllChatsJobs');
                }

                $SessionServer->status = $request->input('status');
                $SessionServer->save();
                return response()->json(['success' => true, 'message' => 'Status updated'], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Session not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
