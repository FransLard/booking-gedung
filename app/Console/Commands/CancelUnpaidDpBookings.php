<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CancelUnpaidDpBookings extends Command
{
    protected $signature = 'bookings:cancel-unpaid-dp';
    protected $description = 'Batalkan booking DP yang belum dibayar H-1 sebelum jadwal';

    public function handle(): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $cancelled = Booking::whereIn('status', ['pending', 'confirmed'])
            ->where('payment_type', 'dp')
            ->where('payment_status', 'unpaid')
            ->where('tanggal', $tomorrow)
            ->update(['status' => 'cancelled']);

        $this->info("Berhasil membatalkan {$cancelled} booking DP unpaid untuk tanggal {$tomorrow}.");
    }
}
