@extends('layouts.app')

@section('title', 'Booking Saya')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="page-header animate-slide-up flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1>Booking Saya</h1>
            <p>Daftar booking gedung Anda</p>
        </div>
        <a href="{{ url('/') }}" class="btn-primary text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Booking Baru
        </a>
    </div>

    @if ($bookings->isEmpty())
        <div class="content-card p-12 text-center animate-slide-up">
            <span class="text-6xl block mb-4">📭</span>
            <p class="text-gray-400 text-lg mb-4">Belum ada booking.</p>
            <a href="{{ url('/') }}" class="btn-primary">
                Cari Gedung
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($bookings as $booking)
                <div class="content-card card-hover animate-slide-up stagger-{{ min($loop->iteration, 6) }}">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <span class="text-xs font-mono font-bold text-mirage-400">{{ $booking->booking_code }}</span>
                                <span class="
                                    @if ($booking->payment_status === 'paid_lunas') badge-green
                                    @elseif ($booking->payment_status === 'paid_dp') badge-gray
                                    @elseif ($booking->payment_status === 'waiting') badge-blue
                                    @else badge-yellow
                                    @endif
                                ">
                                    {{ $booking->payment_status_label }}
                                </span>
                                @if ($booking->status === 'cancelled')
                                    <span class="badge-red">Dibatalkan</span>
                                @endif
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $booking->gedung->nama }}</h3>
                            <p class="text-sm text-mirage-500">
                                {{ $booking->tanggal->format('d M Y') }}
                                @if ($booking->tanggal_selesai && $booking->tanggal_selesai->ne($booking->tanggal))
                                    — {{ $booking->tanggal_selesai->format('d M Y') }}
                                @endif
                                | {{ substr($booking->jam_mulai, 0, 5) }} - {{ substr($booking->jam_selesai, 0, 5) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <div class="text-right">
                                <p class="text-xs text-mirage-400">{{ $booking->payment_type === 'dp' ? 'DP 50%' : 'Lunas' }}</p>
                                <p class="font-bold text-mirage-600">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</p>
                            </div>
                            <a href="{{ route('booking.show', $booking) }}" class="btn-outline text-sm">
                                Detail
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $bookings->links() }}
        </div>
    @endif

</div>

@endsection
