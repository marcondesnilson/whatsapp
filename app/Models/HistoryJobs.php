<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryJobs extends Model
{
    use HasFactory;

    protected $table = 'history_jobs';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
}
