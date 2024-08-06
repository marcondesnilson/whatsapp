<?php

namespace App\Jobs;

use App\Models\Group;
use App\Models\GroupHasContacts;
use Illuminate\Support\Facades\DB;

class CountMessageJob extends BaseJob
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
            DB::transaction(function () {
                $group = Group::where('id', $this->dados['group_id'])->first();
                $ghc = GroupHasContacts::where('group_id', $group->id ?? null)
                    ->where('contact_id', $this->dados['contact_id'])
                    ->first();
                if (!$group || !$ghc) {
                    return;
                }
                $group->number_of_messages = $group->number_of_messages + 1;
                $group->save();

                $ghc->number_of_messages = $ghc->number_of_messages + 1;
                $ghc->save();
            }, 5);
        } catch (\Throwable $e) {

            logError($e);
        }
    }
}
