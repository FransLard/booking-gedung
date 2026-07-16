<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class CancelUnpaidLunasBookings extends Command
{
    protected $signature = 'bookings:cancel-unpaid-lunas';
    protected $description = 'Batalkan booking lunas yang belum dibayar melewati batas 1 jam';

    public function handle(): void
    {
        $cancelled = Booking::whereIn('status', ['pending', 'confirmed'])
            ->where('payment_type', 'lunas')
            ->where('payment_status', 'unpaid')
            ->where('batas_bayar_lunas', '<', now())
            ->update(['status' => 'cancelled']);

        $this->info("Berhasil membatalkan {$cancelled} booking lunas unpaid yang melewati batas bayar.");
    }
}
