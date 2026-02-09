<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyType extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'is_worker'];

    protected $casts = [
        'is_worker' => 'boolean'
    ];

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public function publishedCompanies()
    {
        return $this->companies()->published();
    }

    public function setIsWorkerAttribute($value)
    {
        $this->attributes['is_worker'] = !!$value;
    }
}
