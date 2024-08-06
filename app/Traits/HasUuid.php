<?php

namespace App\Traits;
use Ramsey\Uuid\Uuid;
trait HasUuid
{
    public static function bootHasUuid()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Uuid::uuid4()->toString();
            }
        });
    }
}
