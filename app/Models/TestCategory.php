<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestCategory extends Model
{
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function testResults()
    {
        return $this->hasMany(TestResult::class);
    }
}
