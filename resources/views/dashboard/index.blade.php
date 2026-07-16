@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="page-header animate-slide-up">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <span class="badge-gray mb-3 inline-flex">ADMIN</span>
                <h1>Dashboard</h1>
                <p>Ringkasan keuangan dan seluruh gedung.</p>
            </div>
            <div class="flex gap-2 flex-wrap items-center">
                <form action="{{ route('admin.download-excel') }}" method="GET" class="flex items-center gap-2">
                    <input type="month" name="month" value="{{ $month }}" class="input-field !w-auto text-xs py-2">
                    <button type="submit" class="btn-primary text-sm py-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download Excel
                    </button>
                </form>
                <a href="{{ route('admin.bookings') }}" class="btn-secondary text-sm py-2">Kelola Booking</a>
                <a href="{{ route('admin.addons') }}" class="btn-secondary text-sm py-2">Kelola Add-On</a>
                <a href="{{ route('admin.users') }}" class="btn-secondary text-sm py-2">Kelola User</a>
            </div>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="content-card card-hover animate-slide-up stagger-1">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-mirage-100 text-mirage-600 text-lg">🏛️</span>
            </div>
            <p class="stat-value">{{ $totalGedung }}</p>
            <p class="stat-label">Total Gedung</p>
        </div>
        <div class="content-card card-hover animate-slide-up stagger-2">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 text-lg">📅</span>
            </div>
            <p class="stat-value">{{ $totalBooking }}</p>
            <p class="stat-label">Total Booking</p>
        </div>
        <div class="content-card card-hover animate-slide-up stagger-3">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-mirage-100 text-mirage-600 text-lg">👥</span>
            </div>
            <p class="stat-value">{{ $totalUser }}</p>
            <p class="stat-label">Total Pengguna</p>
        </div>
        <div class="content-card card-hover animate-slide-up stagger-4">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-yellow-50 text-yellow-600 text-lg">💰</span>
            </div>
            <p class="stat-value">Rp {{ number_format($thisMonthRevenue, 0, ',', '.') }}</p>
            <p class="stat-label flex items-center gap-1">
                @if ($revenueChange > 0)
                    <span class="text-emerald-600 font-medium">+{{ $revenueChange }}%</span>
                @elseif ($revenueChange < 0)
                    <span class="text-red-600 font-medium">{{ $revenueChange }}%</span>
                @else
                    <span class="text-gray-400">0%</span>
                @endif
                <span>vs bulan lalu</span>
            </p>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        @foreach ($gedungProfitLoss as $g)
            <a href="{{ route('admin.gedung', $g['id']) }}" class="content-card card-hover block group animate-slide-up stagger-{{ min($loop->iteration, 6) }}">
                <div class="flex items-center justify-between mb-3">
                    <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-mirage-100 text-mirage-600 text-lg">🏛️</span>
                    <svg class="w-4 h-4 text-mirage-300 group-hover:text-mirage-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 text-sm mb-2">{{ $g['name'] }}</h3>
                <div class="space-y-1 text-xs">
                    <div class="flex justify-between">
                        <span class="text-emerald-600">Profit</span>
                        <span class="text-emerald-600 font-medium">Rp {{ number_format($g['profit'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-red-600">Pembatalan</span>
                        <span class="text-red-600 font-medium">Rp {{ number_format($g['loss'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between pt-1 divider">
                        <span class="font-semibold text-gray-700">Bersih</span>
                        <span class="font-semibold {{ $g['net'] >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                            Rp {{ number_format($g['net'], 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-10">
        <div class="content-card animate-slide-up stagger-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Tren Pendapatan (12 Bulan)</h2>
            <canvas id="revenueChart"
                    data-labels='{{ json_encode(array_column($monthlyRevenue, 'month')) }}'
                    data-values='{{ json_encode(array_column($monthlyRevenue, 'revenue')) }}'
                    height="100"></canvas>
        </div>
        <div class="content-card animate-slide-up stagger-3">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Status Booking</h2>
            <canvas id="statusChart"
                    data-labels='{{ json_encode($statusCount->keys()) }}'
                    data-values='{{ json_encode($statusCount->values()) }}'
                    height="100"></canvas>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-10">
        <div class="content-card animate-slide-up stagger-3">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Keuangan</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between py-3 divider">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 flex items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 text-sm">💰</span>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Total Pendapatan</p>
                            <p class="text-xs text-gray-400">Semua waktu (lunas & DP)</p>
                        </div>
                    </div>
                    <p class="text-lg font-bold text-emerald-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="flex items-center justify-between py-3 divider">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 flex items-center justify-center rounded-xl bg-red-100 text-red-600 text-sm">📉</span>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Total Pembatalan</p>
                            <p class="text-xs text-gray-400">Potensi pendapatan dari booking batal</p>
                        </div>
                    </div>
                    <p class="text-lg font-bold text-red-600">Rp {{ number_format($totalLoss, 0, ',', '.') }}</p>
                </div>
                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 flex items-center justify-center rounded-xl bg-mirage-100 text-mirage-700 text-sm">📊</span>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Estimasi Bersih</p>
                            <p class="text-xs text-gray-400">Pendapatan - Pembatalan</p>
                        </div>
                    </div>
                    <p class="text-lg font-bold {{ $totalRevenue - $totalLoss >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                        Rp {{ number_format($totalRevenue - $totalLoss, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="content-card animate-slide-up stagger-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Profit / Loss per Gedung</h2>
            <canvas id="profitLossChart"
                    data-labels='{{ json_encode($gedungProfitLoss->pluck('name')) }}'
                    data-profits='{{ json_encode($gedungProfitLoss->pluck('profit')) }}'
                    data-losses='{{ json_encode($gedungProfitLoss->pluck('loss')) }}'
                    height="100"></canvas>
        </div>
    </div>

    <div class="content-card animate-slide-up stagger-4">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Booking Terbaru</h2>
            <span class="text-xs text-gray-400">{{ $recentBookings->count() }} data</span>
        </div>
        @if ($recentBookings->isEmpty())
            <div class="text-center py-12">
                <span class="text-5xl block mb-4">📭</span>
                <p class="text-gray-400">Belum ada booking.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="table-base">
                    <thead>
                        <tr class="table-head">
                            <th class="table-cell">Kode</th>
                            <th class="table-cell">User</th>
                            <th class="table-cell">Gedung</th>
                            <th class="table-cell">Tanggal</th>
                            <th class="table-cell">Total</th>
                            <th class="table-cell">Status</th>
                            <th class="table-cell">Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentBookings as $booking)
                            <tr class="table-row">
                                <td class="table-cell font-mono text-xs text-gray-500">{{ $booking->booking_code }}</td>
                                <td class="table-cell">
                                    <div class="flex items-center gap-2">
                                        <span class="w-7 h-7 flex items-center justify-center rounded-full bg-mirage-100 text-mirage-700 text-xs font-semibold">
                                            {{ substr($booking->user->name, 0, 1) }}
                                        </span>
                                        <span class="text-gray-700">{{ $booking->user->name }}</span>
                                    </div>
                                </td>
                                <td class="table-cell text-gray-700">{{ $booking->gedung->nama }}</td>
                                <td class="table-cell text-gray-500">{{ $booking->tanggal->format('d M Y') }}</td>
                                <td class="table-cell text-gray-700 font-medium">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</td>
                                <td class="table-cell">
                                    <span class="{{ $booking->status === 'confirmed' ? 'badge-green' : ($booking->status === 'pending' ? 'badge-yellow' : 'badge-red') }}">
                                        <span class="dot {{ $booking->status === 'confirmed' ? 'bg-emerald-500' : ($booking->status === 'pending' ? 'bg-yellow-500' : 'bg-red-500') }}"></span>
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td class="table-cell">
                                    <span class="{{ $booking->payment_status === 'paid_lunas' ? 'badge-green' : ($booking->payment_status === 'paid_dp' ? 'badge-gray' : 'badge-yellow') }}">
                                        {{ $booking->payment_status_label }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin.js'])
@endpush
