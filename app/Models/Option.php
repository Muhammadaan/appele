<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable = [
        'question_id',
        'option_label',
        'option_text',
        'element_id',
        'score',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function element()
    {
        return $this->belongsTo(Element::class);
    }
}
