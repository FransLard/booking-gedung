@extends('layouts.app')

@section('title', 'Pembayaran - ' . $booking->booking_code)

@section('content')

<div class="max-w-2xl mx-auto">

    <div class="flex items-center gap-2 text-sm text-mirage-400 mb-6 animate-slide-up">
        <a href="{{ url('/') }}" class="hover:text-mirage-600 transition">Beranda</a>
        <span>/</span>
        <a href="{{ route('booking.my') }}" class="hover:text-mirage-600 transition">Booking Saya</a>
        <span>/</span>
        <a href="{{ route('booking.show', $booking) }}" class="hover:text-mirage-600 transition">Detail</a>
        <span>/</span>
        <span class="text-mirage-600">Pembayaran</span>
    </div>

    <div class="space-y-6 animate-slide-up">

        <div class="content-card">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-mirage-700 to-mirage-900 flex items-center justify-center text-white text-xl">💰</div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Konfirmasi Pembayaran</h1>
                    <p class="text-sm text-mirage-400">Kode Booking: <strong class="text-mirage-600 font-mono">{{ $booking->booking_code }}</strong></p>
                </div>
            </div>

            <div class="bg-mirage-50 rounded-xl p-5 mb-6">
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-mirage-500">Gedung</span>
                        <span class="font-semibold text-gray-800">{{ $booking->gedung->nama }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-mirage-500">Tanggal</span>
                        <span class="font-semibold text-gray-800">
                            {{ $booking->tanggal->format('d M Y') }}
                            @if ($booking->tanggal_selesai && $booking->tanggal_selesai->ne($booking->tanggal))
                                — {{ $booking->tanggal_selesai->format('d M Y') }}
                                <span class="text-xs text-mirage-400 font-normal">({{ $booking->tanggal->diffInDays($booking->tanggal_selesai) + 1 }} hari)</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-mirage-500">Jam</span>
                        <span class="font-semibold text-gray-800">{{ substr($booking->jam_mulai, 0, 5) }} - {{ substr($booking->jam_selesai, 0, 5) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-mirage-500">Total Harga</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-mirage-200">
                        <span class="text-mirage-500">Metode Pembayaran</span>
                        <span class="font-semibold text-mirage-600">{{ $booking->payment_type === 'dp' ? 'DP 50%' : 'Lunas' }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t-2 border-mirage-300">
                        <span class="text-base font-bold text-gray-900">Nominal yang harus dibayar</span>
                        <span class="text-lg font-bold text-mirage-700">
                            Rp {{ number_format($booking->payment_type === 'dp' ? $booking->dp_amount : $booking->total_harga, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="flex items-center gap-2 mb-4">
                <span class="text-lg">🏦</span>
                <h2 class="font-semibold text-gray-800">Transfer ke Rekening Berikut</h2>
            </div>
            <div class="space-y-3 text-sm mb-6">
                <div class="flex items-center justify-between bg-mirage-50 rounded-lg px-4 py-3">
                    <span class="text-mirage-500">Bank</span>
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🏛️</span>
                        <span class="font-semibold text-gray-800">Bank Mandiri</span>
                    </div>
                </div>
                <div class="flex items-center justify-between bg-mirage-50 rounded-lg px-4 py-3">
                    <span class="text-mirage-500">No. Rekening</span>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-800 font-mono text-base tracking-wider">123-00-456789-0</span>
                        <button onclick="navigator.clipboard.writeText('123-00-456789-0').then(() => { this.innerHTML='✅'; setTimeout(() => this.innerHTML='📋', 2000); })"
                                class="p-1.5 rounded-lg hover:bg-white transition text-sm cursor-pointer" title="Salin nomor rekening">📋</button>
                    </div>
                </div>
                <div class="flex items-center justify-between bg-mirage-50 rounded-lg px-4 py-3">
                    <span class="text-mirage-500">Atas Nama</span>
                    <span class="font-semibold text-gray-800">PT Booking Gedung</span>
                </div>
                <div class="flex items-center justify-between bg-mirage-50 rounded-lg px-4 py-3">
                    <span class="text-mirage-500">Nominal Transfer</span>
                    <span class="font-bold text-mirage-600 text-base">
                        Rp {{ number_format($booking->payment_type === 'dp' ? $booking->dp_amount : $booking->total_harga, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-700 mb-6">
                <div class="flex items-start gap-2">
                    <span class="text-lg">📌</span>
                    <div>
                        <p class="font-semibold mb-1">Penting:</p>
                        <ul class="space-y-1 list-disc list-inside text-amber-600">
                            <li>Transfer sesuai nominal yang tertera</li>
                            <li>Simpan bukti transfer (screenshot / foto)</li>
                            <li>Upload bukti transfer di halaman ini</li>
                            <li>Admin akan verifikasi maksimal 1x24 jam</li>
                            @if ($booking->payment_type === 'dp')
                                <li>Jika DP belum dibayar H-1 jadwal, booking otomatis dibatalkan</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <div class="border-t border-mirage-200 pt-5">
                <h3 class="font-semibold text-gray-800 mb-3">Upload Bukti Transfer</h3>
                <form action="{{ route('booking.payment.submit', $booking) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-mirage-600 mb-1.5">Foto / Screenshot Bukti Transfer</label>
                        <div class="border-2 border-dashed border-mirage-200 rounded-xl p-6 text-center hover:border-mirage-400 transition cursor-pointer"
                             onclick="document.getElementById('proof-input').click()">
                            <div class="text-4xl mb-2">📷</div>
                            <p class="text-sm text-mirage-400 mb-1">Klik untuk upload foto bukti transfer</p>
                            <p class="text-xs text-mirage-300">Format: JPEG, PNG, JPG. Maks: 2MB</p>
                        </div>
                        <input id="proof-input" type="file" name="payment_proof" accept="image/jpeg,image/png,image/jpg"
                               class="hidden" required
                               onchange="document.getElementById('file-name').textContent = this.files[0]?.name || 'Belum ada file'">
                        <p id="file-name" class="text-xs text-mirage-400 mt-2"></p>
                        @error('payment_proof')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 w-full px-5 py-3 font-semibold rounded-xl bg-black text-white hover:bg-stone-800 transition-all duration-300 shadow-md text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Kirim Bukti Pembayaran
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('booking.show', $booking) }}" class="text-sm text-mirage-400 hover:text-mirage-600 transition">
                Kembali ke detail booking
            </a>
        </div>

    </div>
</div>

@endsection