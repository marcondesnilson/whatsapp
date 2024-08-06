<?php

namespace App\Services\Webhook;

use App\Models\GroupHasContacts;
use App\Models\SessionServer;

abstract class OnParticipantsChangedService
{
    public static function all($request)
    {
        try {
            switch ($request['action']) {
                case 'add':
                    $SessionServer = SessionServer::where('id', $request['session'])->first();
                    GroupHasContacts::where('group_id', strstr($request['groupId'], '@', true))
                        ->where('contact_id', $request['contact'])->delete();
                    break;
                default:
                    self::sendToWebhook($request);
                    return response()->json(['success' => false, 'message' => 'Action not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public static function sendToWebhook($request)
    {
        $client = new \GuzzleHttp\Client();
        $client->post('https://webhook-test.com/217ccb170dd97aad3450a3c0766ba0fa', [
            'json' => $request->all() // Encaminha todos os dados recebidos
        ]);
    }
}
