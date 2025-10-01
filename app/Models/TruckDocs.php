<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TruckDocs extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $guarded = [];

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Your existing image conversion
        $this->addMediaConversion('thumbnail')
            ->width(150)
            ->height(150);

        // This is the new conversion for PDF thumbnails!
        $this->addMediaConversion('pdf-thumbnail')
            ->width(150)
            ->height(150)
            ->pdfPageNumber(1)
           ; // Convert the thumbnail to a JPG
    }
}
