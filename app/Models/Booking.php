<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Booking extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tanggal'             => 'date',
            'tanggal_selesai'     => 'date',
            'jam_mulai'           => 'string',
            'jam_selesai'         => 'string',
            'payment_verified_at' => 'datetime',
            'batas_bayar_lunas'   => 'datetime',
        ];
    }

    public function isUnpaidLunas(): bool
    {
        return $this->payment_type === 'lunas' && $this->payment_status === 'unpaid' && $this->status !== 'cancelled';
    }

    public function getDpAmountAttribute(): float
    {
        return $this->total_harga * 0.5;
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'unpaid'     => 'Belum Dibayar',
            'waiting'    => 'Menunggu Verifikasi',
            'paid_dp'    => 'DP 50% Lunas',
            'paid_lunas' => 'Lunas',
            default      => 'Unknown',
        };
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        if (!$this->payment_proof) {
            return null;
        }
        return Storage::url($this->payment_proof);
    }

    public function isUnpaidDp(): bool
    {
        return $this->payment_type === 'dp' && $this->payment_status === 'unpaid' && $this->status !== 'cancelled';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gedung(): BelongsTo
    {
        return $this->belongsTo(Gedung::class);
    }

    public function addOns(): BelongsToMany
    {
        return $this->belongsToMany(AddOn::class, 'booking_add_ons')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
