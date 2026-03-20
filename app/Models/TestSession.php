<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class TestSession extends Model
{
    protected $fillable = [
        'participant_name',
        'participant_age',
        'certificate_number',
        'test_type_id',
        'started_at',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function testType()
    {
        return $this->belongsTo(TestType::class);
    }

    public function testAnswers()
    {
        return $this->hasMany(TestAnswer::class, 'session_id');
    }

    public function testResults()
    {
        return $this->hasMany(TestResult::class, 'session_id');
    }
}
