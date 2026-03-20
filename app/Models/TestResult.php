<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestResult extends Model
{
    protected $fillable = [
        'session_id',
        'category_id',
        'result_type',
        'result_value',
        'score',
    ];

    public function testSession()
    {
        return $this->belongsTo(TestSession::class, 'session_id');
    }

    public function testCategory()
    {
        return $this->belongsTo(TestCategory::class);
    }
}
