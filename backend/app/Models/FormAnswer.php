<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormAnswer extends Model
{
    use HasFactory;


    public function question()
    {
        return $this->belongsTo(FormQuestion::class,'form_question_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getLabel(): string
    {
        $type = $this->question->type;
        $typeInfo = FormQuestion::getTypeInfo($type);
        $labelType = $typeInfo ? $typeInfo['label'] : null;
        return match ($labelType) {
            "question" => $this->question->label,
            "answer" => $this->value,
            "both" => $this->question->label . ": " . $this->value,
            default => "",
        };
    }
}
