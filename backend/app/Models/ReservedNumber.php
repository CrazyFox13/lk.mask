<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservedNumber extends Model
{
    use HasFactory;

    protected $fillable=['number'];

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function scopeFree($q){
        return $q->whereNull("company_id");
    }

    public function scopeBusy($q){
        return $q->whereNotNull("company_id");
    }

}
