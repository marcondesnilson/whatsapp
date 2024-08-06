<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'link',
        'description',
        'number_of_members',
        'number_of_messages',
        'created_at',
        'updated_at'
    ];
}
