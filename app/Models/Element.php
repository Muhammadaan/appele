<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Element extends Model
{
    public function options()
    {
        return $this->hasMany(Option::class);
    }
}
