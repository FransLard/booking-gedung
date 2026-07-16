@extends('layouts.app')

@section('title', 'Kelola Booking')

@section('content')
    <div class="page-header animate-slide-up">
        <span class="badge-gray mb-3 inline-flex">ADMIN</span>
        <h1>Kelola Booking</h1>
        <p>Atur status booking dan pembayaran pengguna.</p>
    </div>

    @php
        $waitingCount = \App\Models\Booking::where('payment_status', 'waiting')->count();
    @endphp

    @if ($waitingCount > 0)
        <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 mb-6 flex items-center gap-3 animate-slide-up stagger-1">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-red-800">{{ $waitingCount }} pembayaran menunggu verifikasi</p>
                <p class="text-xs text-red-600">Segera setujui atau tolak bukti pembayaran di tabel bawah.</p>
            </div>
        </div>
    @endif

    <div class="content-card animate-slide-up stagger-1">
        @if ($bookings->isEmpty())
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
                                <th class="table-cell">Jenis</th>
                                <th class="table-cell">Status</th>
                                <th class="table-cell">Pembayaran</th>
                                <th class="table-cell">Bukti</th>
                                <th class="table-cell">Aksi</th>
                            </tr>
                        </thead>
                    <tbody>
                        @foreach ($bookings as $booking)
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
                                <td class="table-cell text-gray-500">
                                    {{ $booking->tanggal->format('d M Y') }}
                                    @if ($booking->tanggal_selesai && $booking->tanggal_selesai->ne($booking->tanggal))
                                        — {{ $booking->tanggal_selesai->format('d M Y') }}
                                    @endif
                                </td>
                                <td class="table-cell text-gray-700 font-medium">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</td>
                                <td class="table-cell">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $booking->payment_type === 'dp' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ $booking->payment_type === 'dp' ? 'DP 50%' : 'Lunas' }}
                                    </span>
                                </td>
                                <td class="table-cell">
                                    <form method="POST" action="{{ route('admin.booking.status', $booking) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <select name="status" onchange="confirm('Ubah status booking ini?') && this.form.submit()" class="select-field text-xs py-1.5">
                                            <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                            <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="table-cell">
                                    <form method="POST" action="{{ route('admin.booking.payment', $booking) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <select name="payment_status" onchange="confirm('Ubah status pembayaran booking ini?') && this.form.submit()" class="select-field text-xs py-1.5">
                                            <option value="unpaid" {{ $booking->payment_status === 'unpaid' ? 'selected' : '' }}>Belum Dibayar</option>
                                            <option value="paid_dp" {{ $booking->payment_status === 'paid_dp' ? 'selected' : '' }}>DP 50%</option>
                                            <option value="paid_lunas" {{ $booking->payment_status === 'paid_lunas' ? 'selected' : '' }}>Lunas</option>
                                            <option value="waiting" {{ $booking->payment_status === 'waiting' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="table-cell">
                                    @if ($booking->payment_status === 'waiting' && $booking->payment_proof_url)
                                        <div class="flex items-center gap-2">
                                            <a href="{{ $booking->payment_proof_url }}" target="_blank"
                                               class="text-xs text-blue-600 hover:text-blue-800 underline">
                                                Lihat Bukti
                                            </a>
                                        </div>
                                    @elseif ($booking->payment_proof_url)
                                        <a href="{{ $booking->payment_proof_url }}" target="_blank"
                                           class="text-xs text-mirage-400 hover:text-mirage-600 underline">
                                            Lihat
                                        </a>
                                    @else
                                        <span class="text-xs text-mirage-300">-</span>
                                    @endif
                                </td>
                                <td class="table-cell">
                                    <div class="flex items-center gap-1">
                                        @if ($booking->payment_status === 'waiting')
                                            <form method="POST" action="{{ route('admin.booking.verify', $booking) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn-primary text-xs py-1 px-2"
                                                        onclick="return confirm('Setujui pembayaran ini? Booking akan dikonfirmasi.')">
                                                    Setujui
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.booking.verify', $booking) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn-danger text-xs py-1 px-2"
                                                        onclick="return confirm('Tolak bukti pembayaran ini?')">
                                                    Tolak
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('booking.show', $booking) }}" class="btn-ghost text-xs">Detail</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
@endsection
