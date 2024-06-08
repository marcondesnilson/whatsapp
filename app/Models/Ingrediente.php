<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingrediente extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'alimento_id',
        'quantidade',
    ];

    public function alimento()
    {
        return $this->belongsTo(Alimento::class);
    }
}
