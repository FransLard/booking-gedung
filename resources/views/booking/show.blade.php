@extends('layouts.app')

@section('title', 'Detail Booking - ' . $booking->booking_code)

@section('content')

<div class="max-w-3xl mx-auto">

    <div class="flex items-center gap-2 text-sm text-mirage-400 mb-6 animate-slide-up">
        <a href="{{ url('/') }}" class="hover:text-mirage-600 transition">Beranda</a>
        <span>/</span>
        @auth
            @if (Auth::user()->isAdmin())
                <a href="{{ route('admin.bookings') }}" class="hover:text-mirage-600 transition">Kelola Booking</a>
            @else
                <a href="{{ route('booking.my') }}" class="hover:text-mirage-600 transition">Booking Saya</a>
            @endif
        @endauth
        <span>/</span>
        <span class="text-mirage-600">Detail</span>
    </div>

    <div class="content-card mb-6 animate-slide-up">
        <div class="flex items-center justify-between mb-6">
            <div>
                <span class="text-xs text-mirage-400">Kode Booking</span>
                <p class="text-lg font-bold text-gray-900 font-mono">{{ $booking->booking_code }}</p>
            </div>
            <span class="
                @if ($booking->payment_status === 'paid_lunas') badge-green
                @elseif ($booking->payment_status === 'paid_dp') badge-gray
                @elseif ($booking->payment_status === 'waiting') badge-blue
                @else badge-yellow
                @endif
            ">
                {{ $booking->payment_status_label }}
            </span>
        </div>

        <div class="grid sm:grid-cols-2 gap-4 mb-6">
            <div>
                <span class="text-xs text-mirage-400">Gedung</span>
                <p class="font-semibold text-gray-800">{{ $booking->gedung->nama }}</p>
            </div>
            <div>
                <span class="text-xs text-mirage-400">Tanggal</span>
                <p class="font-semibold text-gray-800">
                    {{ $booking->tanggal->format('d M Y') }}
                    @if ($booking->tanggal_selesai && $booking->tanggal_selesai->ne($booking->tanggal))
                        — {{ $booking->tanggal_selesai->format('d M Y') }}
                        <span class="text-xs text-mirage-400 font-normal">({{ $booking->tanggal->diffInDays($booking->tanggal_selesai) + 1 }} hari)</span>
                    @endif
                </p>
            </div>
            <div>
                <span class="text-xs text-mirage-400">Jam</span>
                <p class="font-semibold text-gray-800">{{ substr($booking->jam_mulai, 0, 5) }} - {{ substr($booking->jam_selesai, 0, 5) }}</p>
            </div>
            <div>
                <span class="text-xs text-mirage-400">Status</span>
                <p class="font-semibold text-gray-800">{{ ucfirst($booking->status) }}</p>
            </div>
        </div>

        <div class="bg-mirage-50 rounded-xl p-4 mb-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-mirage-600">Total Harga</span>
                <span class="text-lg font-bold text-gray-900">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-mirage-600">Metode Pembayaran</span>
                <span class="text-sm font-semibold text-gray-800">
                    {{ $booking->payment_type === 'dp' ? 'DP 50%' : 'Lunas' }}
                </span>
            </div>
            @if ($booking->payment_type === 'dp')
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-mirage-200">
                    <span class="text-sm text-mirage-600">Yang harus dibayar (DP 50%)</span>
                    <span class="text-base font-bold text-mirage-600">Rp {{ number_format($booking->dp_amount, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        @php
            $deadline = $booking->payment_type === 'dp' && in_array($booking->payment_status, ['unpaid', 'waiting'])
                ? $booking->tanggal->copy()->subDay()->endOfDay()->timestamp * 1000
                : 0;
            $lunasDeadline = $booking->payment_type === 'lunas' && $booking->payment_status === 'unpaid' && $booking->batas_bayar_lunas
                ? $booking->batas_bayar_lunas->timestamp * 1000
                : 0;
        @endphp

        @if ($booking->payment_type === 'dp' && in_array($booking->payment_status, ['unpaid', 'waiting']))
            <div class="rounded-xl p-4 mb-6 border"
                 x-data="timer({{ $deadline }})"
                 x-show="remaining > 0"
                 x-cloak
                 :class="hours < 1 ? 'bg-red-50 border-red-200' : 'bg-amber-50 border-amber-200'">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">⏳</span>
                        <div>
                            <p class="text-sm font-semibold" :class="hours < 1 ? 'text-red-800' : 'text-amber-800'">
                                Sisa Waktu Pembayaran DP
                            </p>
                            <p class="text-xs" :class="hours < 1 ? 'text-red-600' : 'text-amber-600'">
                                DP harus dibayar sebelum <strong>{{ $booking->tanggal->copy()->subDay()->isoFormat('D MMMM YYYY') }} pukul 23:59</strong>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xl font-bold font-mono tabular-nums"
                         :class="hours < 1 ? 'text-red-600' : 'text-amber-700'">
                        <span x-text="String(days).padStart(2,'0')"></span>
                        <span class="text-sm">:</span>
                        <span x-text="String(hours).padStart(2,'0')"></span>
                        <span class="text-sm">:</span>
                        <span x-text="String(minutes).padStart(2,'0')"></span>
                        <span class="text-sm">:</span>
                        <span x-text="String(seconds).padStart(2,'0')"></span>
                    </div>
                </div>
            </div>
        @endif

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('timer', (deadline) => ({
                    deadline,
                    remaining: 0,
                    days: 0,
                    hours: 0,
                    minutes: 0,
                    seconds: 0,
                    init() {
                        this.tick();
                        setInterval(() => this.tick(), 1000);
                    },
                    tick() {
                        this.remaining = Math.max(0, this.deadline - Date.now());
                        const totalSec = Math.floor(this.remaining / 1000);
                        this.days = Math.floor(totalSec / 86400);
                        this.hours = Math.floor((totalSec % 86400) / 3600);
                        this.minutes = Math.floor((totalSec % 3600) / 60);
                        this.seconds = totalSec % 60;
                    },
                }));
            });
        </script>

        @if ($booking->payment_type === 'lunas' && $booking->payment_status === 'unpaid' && $booking->batas_bayar_lunas)
            <div class="rounded-xl p-4 mb-6 border"
                 x-data="timer({{ $lunasDeadline }})"
                 x-show="remaining > 0"
                 x-cloak
                 :class="hours < 1 ? 'bg-red-50 border-red-200' : 'bg-amber-50 border-amber-200'">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">⏳</span>
                        <div>
                            <p class="text-sm font-semibold" :class="hours < 1 ? 'text-red-800' : 'text-amber-800'">
                                Sisa Waktu Pembayaran
                            </p>
                            <p class="text-xs" :class="hours < 1 ? 'text-red-600' : 'text-amber-600'">
                                Lunas harus dibayar sebelum <strong>{{ $booking->batas_bayar_lunas->isoFormat('D MMMM YYYY HH:mm') }}</strong>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xl font-bold font-mono tabular-nums"
                         :class="hours < 1 ? 'text-red-600' : 'text-amber-700'">
                        <span x-text="String(days).padStart(2,'0')"></span>
                        <span class="text-sm">:</span>
                        <span x-text="String(hours).padStart(2,'0')"></span>
                        <span class="text-sm">:</span>
                        <span x-text="String(minutes).padStart(2,'0')"></span>
                        <span class="text-sm">:</span>
                        <span x-text="String(seconds).padStart(2,'0')"></span>
                    </div>
                </div>
            </div>
        @endif

        @if ($booking->payment_status === 'unpaid')
            <div class="border border-dashed border-mirage-200 bg-mirage-50/50 rounded-xl p-5 mb-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-lg">🏦</span>
                    <h3 class="font-semibold text-gray-800">Instruksi Pembayaran</h3>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between bg-white rounded-lg px-4 py-3">
                        <span class="text-mirage-500">Bank</span>
                        <span class="font-semibold text-gray-800">Bank Mandiri</span>
                    </div>
                    <div class="flex items-center justify-between bg-white rounded-lg px-4 py-3">
                        <span class="text-mirage-500">No. Rekening</span>
                        <span class="font-semibold text-gray-800 font-mono">123-00-456789-0</span>
                    </div>
                    <div class="flex items-center justify-between bg-white rounded-lg px-4 py-3">
                        <span class="text-mirage-500">Atas Nama</span>
                        <span class="font-semibold text-gray-800">PT Booking Gedung</span>
                    </div>
                    <div class="flex items-center justify-between bg-white rounded-lg px-4 py-3">
                        <span class="text-mirage-500">Nominal Transfer</span>
                        <span class="font-semibold text-mirage-600">
                            Rp {{ number_format($booking->payment_type === 'dp' ? $booking->dp_amount : $booking->total_harga, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                <p class="text-xs text-mirade-400 mt-3">
                    * Konfirmasi pembayaran akan diproses oleh admin maksimal 1x24 jam.
                    @if ($booking->payment_type === 'dp')
                        <br>* Jika DP belum dibayar H-1 jadwal, booking akan otomatis dibatalkan.
                    @else
                        <br>* Jika pembayaran lunas belum diterima dalam 1 jam, booking akan otomatis dibatalkan.
                    @endif
                </p>

                <div class="mt-5 pt-4 border-t border-dashed border-mirage-200">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Upload Bukti Pembayaran</h4>
                    <form action="{{ route('booking.payment.submit', $booking) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-mirage-600 mb-1">Foto / Screenshot Bukti Transfer</label>
                            <input type="file" name="payment_proof" accept="image/jpeg,image/png,image/jpg"
                                   class="block w-full text-sm text-mirage-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-mirage-100 file:text-mirage-700 hover:file:bg-mirage-200 transition cursor-pointer" required>
                            @error('payment_proof')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="btn-primary w-full justify-center text-sm py-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Konfirmasi Pembayaran
                        </button>
                    </form>
                </div>
            </div>
        @endif

        @if ($booking->payment_status === 'waiting')
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-blue-800">Menunggu Verifikasi Admin</h3>
                        <p class="text-sm text-blue-600">Bukti pembayaran sedang diperiksa. Maksimal 1x24 jam.</p>
                    </div>
                </div>
                @if ($booking->payment_proof_url)
                    <a href="{{ $booking->payment_proof_url }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 underline">
                        Lihat Bukti Pembayaran
                    </a>
                @endif
            </div>
        @endif

        @if (in_array($booking->payment_status, ['paid_dp', 'paid_lunas']))
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-5 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-emerald-800">Pembayaran {{ $booking->payment_status === 'paid_lunas' ? 'Lunas' : 'DP 50%' }} Terverifikasi</h3>
                        <p class="text-sm text-emerald-600">Booking telah dikonfirmasi.</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row items-center gap-3">
            @if ($booking->payment_status === 'unpaid')
                <a href="{{ route('booking.payment', $booking) }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 font-semibold rounded-xl w-full justify-center bg-black text-white hover:bg-stone-800 transition-all duration-300 shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    Bayar Sekarang
                </a>
            @endif
            @auth
                @if (Auth::user()->isAdmin())
                    <a href="{{ route('admin.bookings') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 font-semibold rounded-xl w-full justify-center bg-stone-800 text-white hover:bg-black transition-all duration-300 shadow-sm">
                        Kembali ke Kelola Booking
                    </a>
                @else
                    <a href="{{ route('booking.my') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 font-semibold rounded-xl w-full justify-center bg-stone-800 text-white hover:bg-black transition-all duration-300 shadow-sm">
                        Lihat Semua Booking Saya
                    </a>
                @endif
            @endauth
            <a href="{{ url('/') }}" class="btn-ghost w-full justify-center">
                Kembali ke Beranda
            </a>
            @if ($booking->status !== 'cancelled')
                <form action="{{ route('booking.cancel', $booking) }}" method="POST"
                      onsubmit="return confirm('Yakin ingin membatalkan booking ini?')" class="w-full sm:w-auto">
                    @csrf @method('PATCH')
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 font-semibold rounded-xl w-full justify-center bg-red-600 text-white hover:bg-red-700 transition-all duration-300 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Batalkan Booking
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if ($booking->addOns->isNotEmpty())
        <div class="content-card animate-slide-up stagger-2">
            <h3 class="font-semibold text-gray-800 mb-4">Layanan Tambahan</h3>
            <div class="space-y-2">
                @foreach ($booking->addOns as $addOn)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-stone-800 font-medium">{{ $addOn->pivot->quantity }}x {{ $addOn->nama }}</span>
                        <span class="font-medium text-gray-800">Rp {{ number_format($addOn->harga * $addOn->pivot->quantity, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

@endsection
