<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SendMessageJob;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $dados = $request->all();
            $historyJobsUuid = app('App\Http\Controllers\util\HistoryJobsUtil')
                ->create('SendMessageJob', $dados);
            SendMessageJob::dispatch($historyJobsUuid, $dados)
                ->onQueue('SendMessageJob');
        } catch (\Throwable $e) {
            logError($e);
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {}
}
