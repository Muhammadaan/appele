<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestType extends Model
{
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function testSessions()
    {
        return $this->hasMany(TestSession::class);
    }
}
