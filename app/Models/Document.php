<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Document extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'type',
        'note',
        'file_type',
        'issuance_date',
    ];

    protected $casts = [
        'issuance_date' => 'date',
    ];

    public function documentable()
    {
        return $this->morphTo();
    }

    // لو عايز تحدد تحويلات معينة للملفات (اختياري)
    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        if ($media && str_starts_with($media->mime_type, 'image/')) {
            $this->addMediaConversion('thumb')
                ->width(300)
                ->height(300)
                ->sharpen(10);
        }
    }
}
