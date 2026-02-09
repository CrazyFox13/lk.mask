<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'icon'];

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }
}
