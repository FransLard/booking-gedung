<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Append;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Gedung extends Model
{
    use HasFactory;

    protected $table = 'gedung';

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

    protected function gambarDalamUrl(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->gambar_dalam) {
                return null;
            }
            if (filter_var($this->gambar_dalam, FILTER_VALIDATE_URL)) {
                return $this->gambar_dalam;
            }
            return Storage::url($this->gambar_dalam);
        });
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(GedungImage::class)->orderBy('urutan');
    }
}
