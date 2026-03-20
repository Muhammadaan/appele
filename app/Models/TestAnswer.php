<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestAnswer extends Model
{
    protected $fillable = [
        'session_id',
        'question_id',
        'option_id',
        'rank',
        'score',
    ];

    public function testSession()
    {
        return $this->belongsTo(TestSession::class, 'session_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
