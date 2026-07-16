@extends('layouts.app')

@section('title', $gedung->nama)

@section('content')
    <div class="page-header animate-slide-up">
        <div class="flex items-center gap-2 text-sm text-mirage-400 mb-3">
            <a href="{{ route('dashboard') }}" class="hover:text-mirage-600 transition">Dashboard</a>
            <span>/</span>
            <span class="text-mirage-600">{{ $gedung->nama }}</span>
        </div>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1>{{ $gedung->nama }}</h1>
                <p>{{ $gedung->alamat }} &middot; Kapasitas {{ $gedung->kapasitas }} orang</p>
            </div>
            <button type="button" onclick="document.getElementById('editGedungModal').classList.remove('hidden')"
                    class="btn-secondary text-sm py-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Gedung
            </button>
            <div class="flex gap-2">
                <a href="{{ route('admin.download-excel', ['month' => $month, 'gedung_id' => $gedung->id]) }}" class="btn-primary text-sm py-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download Excel
                </a>
                <a href="{{ route('booking.create', $gedung) }}" target="_blank" class="btn-secondary text-sm py-2">
                    Lihat Halaman Booking
                </a>
            </div>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="content-card animate-slide-up stagger-1">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-mirage-100 text-mirage-600 text-lg">📅</span>
            </div>
            <p class="stat-value">{{ $totalBookings }}</p>
            <p class="stat-label">Total Booking</p>
        </div>
        <div class="content-card animate-slide-up stagger-2">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 text-lg">💰</span>
            </div>
            <p class="stat-value text-emerald-600">Rp {{ number_format($totalProfit, 0, ',', '.') }}</p>
            <p class="stat-label">Total Profit</p>
        </div>
        <div class="content-card animate-slide-up stagger-3">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-100 text-red-600 text-lg">📉</span>
            </div>
            <p class="stat-value text-red-600">Rp {{ number_format($totalLoss, 0, ',', '.') }}</p>
            <p class="stat-label">Total Pembatalan</p>
        </div>
        <div class="content-card animate-slide-up stagger-4">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-mirage-100 text-mirage-600 text-lg">📊</span>
            </div>
            <p class="stat-value {{ $netProfit >= 0 ? 'text-gray-900' : 'text-red-600' }}">Rp {{ number_format($netProfit, 0, ',', '.') }}</p>
            <p class="stat-label">Net Profit</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-10">
        <div class="content-card animate-slide-up stagger-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Profit / Loss Bulanan</h2>
            <canvas id="plChart"
                    data-labels='{{ json_encode(array_column($monthlyData, 'month')) }}'
                    data-profits='{{ json_encode(array_column($monthlyData, 'profit')) }}'
                    data-losses='{{ json_encode(array_column($monthlyData, 'loss')) }}'
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

    <div class="content-card mb-10 animate-slide-up stagger-4">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-semibold text-gray-900">Galeri Gambar</h2>
            <button type="button" onclick="document.getElementById('uploadImageInput').click()" class="btn-primary text-sm py-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Gambar
            </button>
        </div>

        <form action="{{ route('admin.gedung.images.upload', $gedung) }}" method="POST" enctype="multipart/form-data" class="hidden">
            @csrf
            <input type="file" name="gambar" id="uploadImageInput" accept="image/jpeg,image/png,image/jpg,image/webp" onchange="this.form.submit()">
        </form>

        @if ($gedung->images->isEmpty())
            <div class="text-center py-8 text-gray-400 text-sm">Belum ada gambar tambahan.</div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                <div class="relative group rounded-xl overflow-hidden border border-gray-200 bg-gray-50">
                    <img src="{{ $gedung->gambar_url }}" alt="Main image"
                         class="w-full h-28 object-cover">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition flex items-center justify-center">
                        <span class="text-[10px] bg-white/90 px-2 py-0.5 rounded-full text-gray-600 font-medium shadow-sm">Utama</span>
                    </div>
                </div>
                @foreach ($gedung->images as $img)
                    <div class="relative group rounded-xl overflow-hidden border border-gray-200 bg-gray-50">
                        <img src="{{ $img->gambar_url }}" alt="{{ $img->keterangan ?? 'Gambar' }}"
                             class="w-full h-28 object-cover">
                        @if ($img->keterangan)
                            <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/60 to-transparent p-1.5">
                                <p class="text-[10px] text-white truncate">{{ $img->keterangan }}</p>
                            </div>
                        @endif
                        <form action="{{ route('admin.gedung.images.delete', $img) }}" method="POST"
                              onsubmit="return confirm('Hapus gambar ini?')"
                              class="absolute top-1.5 right-1.5 opacity-0 group-hover:opacity-100 transition">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center hover:bg-red-600 shadow-sm text-xs">
                                ✕
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="content-card mb-10 animate-slide-up stagger-5">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Booking {{ $gedung->nama }}</h2>
        @if ($bookings->isEmpty())
            <div class="text-center py-12">
                <span class="text-5xl block mb-4">📭</span>
                <p class="text-gray-400">Belum ada booking untuk gedung ini.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="table-base">
                    <thead>
                        <tr class="table-head">
                            <th class="table-cell">Kode</th>
                            <th class="table-cell">User</th>
                            <th class="table-cell">Tanggal</th>
                            <th class="table-cell">Jam</th>
                            <th class="table-cell">Total</th>
                            <th class="table-cell">Status</th>
                            <th class="table-cell">Ket</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $b)
                            <tr class="table-row">
                                <td class="table-cell font-mono text-xs text-gray-500">{{ $b->booking_code }}</td>
                                <td class="table-cell">
                                    <div class="flex items-center gap-2">
                                        <span class="w-7 h-7 flex items-center justify-center rounded-full bg-mirage-100 text-mirage-700 text-xs font-semibold">
                                            {{ substr($b->user->name, 0, 1) }}
                                        </span>
                                        <span class="text-gray-700">{{ $b->user->name }}</span>
                                    </div>
                                </td>
                                <td class="table-cell text-gray-500">
                                    {{ $b->tanggal->format('d M Y') }}
                                    @if ($b->tanggal_selesai && $b->tanggal->format('Y-m-d') !== $b->tanggal_selesai->format('Y-m-d'))
                                        — {{ $b->tanggal_selesai->format('d M Y') }}
                                    @endif
                                </td>
                                <td class="table-cell text-gray-500">{{ substr($b->jam_mulai, 0, 5) }} - {{ substr($b->jam_selesai, 0, 5) }}</td>
                                <td class="table-cell text-gray-700 font-medium">Rp {{ number_format($b->total_harga, 0, ',', '.') }}</td>
                                <td class="table-cell">
                                    <span class="{{ $b->status === 'confirmed' ? 'badge-green' : ($b->status === 'pending' ? 'badge-yellow' : 'badge-red') }}">
                                        {{ ucfirst($b->status) }}
                                    </span>
                                </td>
                                <td class="table-cell">
                                    <span class="text-xs font-medium {{ $b->status === 'cancelled' ? 'text-red-500' : ($b->status === 'confirmed' && in_array($b->payment_status, ['paid_dp', 'paid_lunas']) ? 'text-emerald-500' : 'text-yellow-500') }}">
                                        {{ $b->status === 'confirmed' && in_array($b->payment_status, ['paid_dp', 'paid_lunas']) ? 'Profit' : ($b->status === 'cancelled' ? 'Loss' : '-') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    </table>
            </div>
        @endif
    </div>

    <div id="editGedungModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="document.getElementById('editGedungModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-gray-900">Edit Gedung</h2>
                <button type="button" onclick="document.getElementById('editGedungModal').classList.add('hidden')"
                        class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('admin.gedung.update', $gedung) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Gedung</label>
                    <input type="text" name="nama" value="{{ old('nama', $gedung->nama) }}" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <input type="text" name="alamat" value="{{ old('alamat', $gedung->alamat) }}" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="3"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900">{{ old('deskripsi', $gedung->deskripsi) }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas (orang)</label>
                        <input type="number" name="kapasitas" value="{{ old('kapasitas', $gedung->kapasitas) }}" required min="1"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Sewa /hari (Rp)</label>
                        <input type="number" name="harga_sewa" value="{{ old('harga_sewa', $gedung->harga_sewa) }}" required min="0"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga /jam (Rp)</label>
                        <input type="number" name="harga_per_jam" value="{{ old('harga_per_jam', $gedung->harga_per_jam) }}" min="0"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900"
                               placeholder="Otomatis dr harga_sewa/8">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Jam</label>
                        <input type="number" name="minimum_jam" value="{{ old('minimum_jam', $gedung->minimum_jam ?? 2) }}" min="1" max="24"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Utama</label>
                    <input type="file" name="gambar" accept="image/jpeg,image/png,image/jpg,image/webp"
                           class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                    <p class="text-[11px] text-gray-400 mt-1">Kosongkan jika tidak ingin mengubah gambar.</p>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary text-sm py-2 flex-1">Simpan Perubahan</button>
                    <button type="button" onclick="document.getElementById('editGedungModal').classList.add('hidden')"
                            class="btn-secondary text-sm py-2 flex-1">Batal</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const plCanvas = document.getElementById('plChart');
    if (plCanvas) {
                new Chart(plCanvas, {
                        type: 'bar',
                        data: {
                            labels: JSON.parse(plCanvas.dataset.labels),
                            datasets: [
                                {
                                    label: 'Profit (Pendapatan)',
                                    data: JSON.parse(plCanvas.dataset.profits),
                                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                    borderColor: 'rgba(34, 197, 94, 1)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                },
                                {
                                    label: 'Loss (Pembatalan)',
                                    data: JSON.parse(plCanvas.dataset.losses),
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') },
                    },
                },
            },
        });
    }

    const statusCanvas = document.getElementById('statusChart');
    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: JSON.parse(statusCanvas.dataset.labels),
                datasets: [{
                    data: JSON.parse(statusCanvas.dataset.values),
                    backgroundColor: ['rgba(34,197,94,0.8)', 'rgba(234,179,8,0.8)', 'rgba(239,68,68,0.8)'],
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }
});
</script>
@endpush
