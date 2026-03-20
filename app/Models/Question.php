<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'category_id',
        'test_type_id',
        'question_text',
        'question_type',
        'order_number',
        'is_active',
    ];

    protected static function booted()
    {
        static::deleting(function (Question $question) {
            $question->options()->delete();
        });
    }

    public function testCategory()
    {
        return $this->belongsTo(TestCategory::class);
    }

    public function testType()
    {
        return $this->belongsTo(TestType::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }
}
