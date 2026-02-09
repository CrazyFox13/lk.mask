<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoGroup extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'title'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function getLatestPhotosAttribute()
    {
        return $this->photos()->take(4)->get();
    }

    public function bulkUploadPhotos($photos)
    {
        $group = $this;
        $existingPhotosUrl = $this->photos()->pluck("url")->toArray();
        $deletedPhotos = array_diff($existingPhotosUrl, $photos);
        if (count($deletedPhotos) > 0) {
            Photo::query()->where("photo_group_id", $group->id)
                ->whereIn("url", $deletedPhotos)
                ->delete();
        }

        $data = array_map(function ($photo) use ($group) {
            return [
                'photo_group_id' => $group->id,
                'url' => $photo,
                'created_at' => Carbon::now()
            ];
        }, array_filter($photos, function ($item) use ($existingPhotosUrl) {
            return !in_array($item, $existingPhotosUrl);
        }));

        Photo::upsert($data, ['photo_group_id', 'url']);
    }
}
