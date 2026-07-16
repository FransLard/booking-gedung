<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Append;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GedungImage extends Model
{
    protected $table = 'gedung_images';

    protected function gambarUrl(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->gambar) {
                return null;
            }
            if (filter_var($this->gambar, FILTER_VALIDATE_URL)) {
                return $this->gambar;
            }
            return Storage::url($this->gambar);
        });
    }

    public function gedung(): BelongsTo
    {
        return $this->belongsTo(Gedung::class);
    }
}
