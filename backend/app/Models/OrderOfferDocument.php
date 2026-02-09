<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OrderOfferDocument extends Model
{
    use HasFactory;

    protected $fillable = ['order_offer_id', 'type', 'url', 'file_name', 'file_size', 'mime_type'];

    const TYPE_PRINT_FORM = 'print_form';
    const TYPE_SIGNED_DOCUMENT = 'signed_document';
    const TYPE_APPLICATION = 'application';
    const TYPE_INVOICE = 'invoice';

    public static function boot()
    {
        parent::boot();

        self::deleting(function (OrderOfferDocument $document) {
            $parsed = parse_url($document->url);
            $path = $parsed['path'] ?? '';
            // Убираем начальный слэш, если есть
            $path = ltrim($path, '/');
            if ($path) {
                Storage::disk('s3')->delete($path);
            }
        });
    }

    public function orderOffer()
    {
        return $this->belongsTo(OrderOffer::class);
    }
}
