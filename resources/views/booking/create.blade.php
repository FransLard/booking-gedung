@extends('layouts.app')

@section('title', 'Booking ' . $gedung->nama)

@section('content')

<div class="max-w-5xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-mirage-400 mb-6 animate-slide-up">
        <a href="{{ url('/') }}" class="hover:text-mirage-600 transition">Beranda</a>
        <span>/</span>
        <span class="text-mirage-600">Booking {{ $gedung->nama }}</span>
    </div>

    <div class="grid lg:grid-cols-5 gap-8 items-start">

        <div class="lg:col-span-2 animate-slide-up">
            <div class="content-card overflow-hidden sticky top-24 !p-0">
                <div class="h-44 bg-gradient-to-br from-mirage-100 to-mirage-50 flex items-center justify-center">
                    @if ($gedung->gambar_url)
                        <img src="{{ $gedung->gambar_url }}" alt="{{ $gedung->nama }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-6xl">🏛️</span>
                    @endif
                </div>
                <div class="p-5">
                    <h2 class="text-xl font-bold text-gray-900 mb-1">{{ $gedung->nama }}</h2>
                    <p class="text-sm text-gray-400 flex items-center gap-1 mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $gedung->alamat }}
                    </p>
                    <p class="text-sm text-gray-500 mb-3">{{ $gedung->deskripsi }}</p>
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
                        <span>👥 Kapasitas: <strong>{{ $gedung->kapasitas }}</strong> orang</span>
                    </div>
                    <div class="pt-4 divider">
                        <p class="text-xs text-gray-400">Harga Sewa</p>
                        <p class="text-2xl font-bold text-mirage-600">
                            Rp {{ number_format($gedung->harga_sewa, 0, ',', '.') }}
                        </p>
                        @if ($gedung->harga_per_jam)
                            <p class="text-xs text-mirage-400 mt-1">
                                atau Rp {{ number_format($gedung->harga_per_jam, 0, ',', '.') }}/jam
                                (min. {{ $gedung->minimum_jam }} jam)
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3 animate-slide-up stagger-2"
             x-data="bookingForm({{ $gedung->harga_sewa }}, {{ json_encode($bookedDates) }}, {{ $gedung->harga_per_jam ?: 'null' }}, {{ $gedung->minimum_jam ?? 2 }})"
             x-init="paymentType = '{{ old('payment_type') }}'">

            @if ($errors->has('jadwal'))
                <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 mb-6 flex items-start gap-3">
                    <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-red-100 text-red-600 text-lg mt-0.5">✕</span>
                    <div>
                        <p class="font-semibold text-sm text-red-800">Jadwal Bentrok</p>
                        <p class="text-sm text-red-600 mt-0.5">{{ $errors->first('jadwal') }}</p>
                    </div>
                </div>
            @endif

            <div class="content-card">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Pilih Jadwal Booking</h2>

                <form method="POST" action="{{ url('/booking') }}">
                    @csrf
                    <input type="hidden" name="gedung_id" value="{{ $gedung->id }}">
                    <input type="hidden" name="tanggal" value="{{ old('tanggal') }}">
                    <input type="hidden" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}">
                    <input type="hidden" name="jam_mulai" value="{{ old('jam_mulai') }}">
                    <input type="hidden" name="jam_selesai" value="{{ old('jam_selesai') }}">

                    <div class="mb-8">
                        <label class="label-field !text-sm font-semibold mb-3">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Pilih Tanggal
                            </span>
                        </label>

                        <div class="border border-mirage-200 rounded-xl p-4 bg-white">
                            <div class="flex items-center justify-between mb-4">
                                <button @click="prevMonth" type="button"
                                        class="p-1.5 rounded-lg hover:bg-mirage-50 transition text-mirage-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                                <span class="text-sm font-bold text-gray-800" x-text="monthNames[currentMonth] + ' ' + currentYear"></span>
                                <button @click="nextMonth" type="button"
                                        class="p-1.5 rounded-lg hover:bg-mirage-50 transition text-mirage-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-7 gap-1 mb-2">
                                <template x-for="(name, i) in dayNames" :key="i">
                                    <div class="text-center text-xs font-semibold text-mirage-400 py-1" x-text="name"></div>
                                </template>
                            </div>

                            <div class="grid grid-cols-7 gap-1">
                                <template x-for="(day, i) in calendarDays" :key="i">
                                    <div>
                                        <template x-if="day !== null">
                                            <button @click="selectDate(day)" type="button"
                                                    :disabled="isPast(day) || isBooked(day)"
                                                    class="w-full aspect-square flex items-center justify-center text-sm transition text-center"
                                                    :class="dayClass(day)">
                                                <span x-text="day"></span>
                                            </button>
                                        </template>
                                        <template x-if="day === null">
                                            <div class="w-full aspect-square"></div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <div class="flex items-center gap-4 mt-4 pt-3 divider text-xs text-mirage-400">
                                <span class="flex items-center gap-1">
                                    <span class="w-3 h-3 rounded-full bg-gradient-to-r from-mirage-800 to-mirage-900 inline-block"></span> Dipilih
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="w-3 h-3 rounded-full bg-red-50 border border-red-200 inline-block"></span> Dibooking
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="w-3 h-3 rounded-full border-2 border-mirage-300 inline-block"></span> Hari ini
                                </span>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center gap-2 text-sm" x-show="selectedDateStr">
                            <span class="text-mirage-400">Tanggal dipilih:</span>
                            <span class="font-semibold text-mirage-700" x-text="selectedDateStr"></span>
                        </div>
                        @error('tanggal')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-8" x-show="selectedDateStr">
                        <label class="label-field !text-sm font-semibold mb-3">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Lama Booking
                            </span>
                        </label>
                        <div class="flex items-center gap-2">
                            <template x-for="d in Array.from({length: 14}, (_, i) => i + 1)" :key="d">
                                <button @click="selectDuration(d)" type="button"
                                        class="w-9 h-9 rounded-lg text-xs font-semibold transition border"
                                        :class="duration === d ? 'bg-stone-900 text-white border-stone-900' : 'bg-white text-stone-600 border-stone-200 hover:border-stone-400'"
                                        x-text="d">
                                </button>
                            </template>
                            <span class="text-xs text-stone-400 ml-1">hari</span>
                        </div>
                        <p class="text-xs text-stone-400 mt-2" x-show="duration > 1" x-text="'Booking: ' + selectedDateStr + ' s.d. ' + selesaiDateStr"></p>
                        @error('tanggal_selesai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-8">
                        <label class="label-field !text-sm font-semibold mb-3">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Pilih Jam
                            </span>
                        </label>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-mirage-500 mb-2">Jam Mulai</p>
                                <div class="border border-mirage-200 rounded-xl p-2 max-h-48 overflow-y-auto scrollbar-thin">
                                    <template x-for="slot in timeSlots" :key="slot.value">
                                        <button @click="selectMulai(slot.value)" type="button"
                                                class="w-full text-left px-3 py-2 rounded-lg text-sm transition"
                                                :class="jamMulai === slot.value ? 'bg-mirage-100 text-mirage-700 font-semibold' : 'text-gray-600 hover:bg-mirage-50'"
                                                x-text="slot.label">
                                        </button>
                                    </template>
                                </div>
                                @error('jam_mulai')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <p class="text-xs font-medium text-mirage-500 mb-2">Jam Selesai</p>
                                <div class="border border-mirage-200 rounded-xl p-2 max-h-48 overflow-y-auto scrollbar-thin">
                                    <template x-if="availableEndSlots.length > 0">
                                        <template x-for="slot in availableEndSlots" :key="slot.value">
                                            <button @click="selectSelesai(slot.value)" type="button"
                                                    class="w-full text-left px-3 py-2 rounded-lg text-sm transition"
                                                    :class="jamSelesai === slot.value ? 'bg-mirage-100 text-mirage-700 font-semibold' : 'text-gray-600 hover:bg-mirage-50'"
                                                    x-text="slot.label">
                                            </button>
                                        </template>
                                    </template>
                                    <template x-if="availableEndSlots.length === 0">
                                        <p class="text-gray-400 text-sm text-center py-4" x-text="jamMulai ? 'Pilih jam mulai terlebih dahulu' : 'Tidak ada slot tersedia'"></p>
                                    </template>
                                </div>
                                @error('jam_selesai')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-3 flex items-center gap-2 text-sm" x-show="jamMulai && jamSelesai">
                            <span class="text-mirage-400">Jam dipilih:</span>
                            <span class="font-semibold text-mirage-700" x-text="jamMulai + ' — ' + jamSelesai"></span>
                        </div>
                    </div>

                    <div class="mb-8">
                        <label class="label-field !text-sm font-semibold mb-3">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                </svg>
                                Metode Pembayaran
                            </span>
                        </label>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-4 rounded-xl border border-mirage-100 hover:border-mirage-200 hover:bg-mirage-50/30 cursor-pointer transition"
                                   :class="paymentType === 'dp' ? 'border-mirage-300 bg-mirage-50/50' : ''">
                                <input type="radio" name="payment_type" value="dp"
                                       x-model="paymentType" required
                                       class="w-4 h-4 border-gray-300 text-mirage-600 focus:ring-mirage-500">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">DP 50%</p>
                                    <p class="text-xs text-gray-400">Bayar 50% dulu, sisanya nanti</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 rounded-xl border border-mirage-100 hover:border-mirage-200 hover:bg-mirage-50/30 cursor-pointer transition"
                                   :class="paymentType === 'lunas' ? 'border-mirage-300 bg-mirage-50/50' : ''">
                                <input type="radio" name="payment_type" value="lunas"
                                       x-model="paymentType" required
                                       class="w-4 h-4 border-gray-300 text-mirage-600 focus:ring-mirage-500">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Lunas</p>
                                    <p class="text-xs text-gray-400">Bayar penuh sekarang</p>
                                </div>
                            </label>
                        </div>
                        @error('payment_type')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-8">
                        <label class="label-field !text-sm font-semibold mb-3">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Layanan Tambahan
                            </span>
                            <span class="text-xs text-mirage-400 font-normal ml-1">(Opsional)</span>
                        </label>
                        <div class="space-y-2.5">
                            @forelse ($addOns as $addOn)
                                @if ($addOn->type === 'per_unit')
                                <div class="flex items-center gap-3 p-3.5 rounded-xl border border-mirage-100 hover:border-mirage-200 transition"
                                     :class="{ 'border-mirage-300 bg-mirage-50/50': (addonQtys[{{ $addOn->id }}] || 0) > 0 }">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800">{{ $addOn->nama }}</p>
                                        @if ($addOn->deskripsi)
                                            <p class="text-xs text-mirage-400 truncate">{{ $addOn->deskripsi }}</p>
                                        @endif
                                    </div>
                                    <span class="text-sm font-semibold text-mirage-600 flex-shrink-0 mr-2">Rp {{ number_format($addOn->harga, 0, ',', '.') }}/bh</span>
                                    <div class="flex items-center gap-1">
                                        <button @click="updateAddonQty({{ $addOn->id }}, {{ $addOn->harga }}, -1)" type="button"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg transition text-sm font-bold"
                                                :class="(addonQtys[{{ $addOn->id }}] || 0) > 0 ? 'bg-mirage-700 text-white' : 'bg-mirage-100 text-mirage-300'">
                                            −
                                        </button>
                                        <span class="w-8 text-center text-sm font-bold text-gray-800" x-text="addonQtys[{{ $addOn->id }}] || 0"></span>
                                        <button @click="updateAddonQty({{ $addOn->id }}, {{ $addOn->harga }}, 1)" type="button"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-mirage-700 text-white transition hover:bg-mirage-800 text-sm font-bold">
                                            +
                                        </button>
                                    </div>
                                    <input type="hidden" name="add_ons[{{ $addOn->id }}]" :value="addonQtys[{{ $addOn->id }}] || 0">
                                </div>
                                @else
                                <div @click="toggleFlatAddon({{ $addOn->id }}, {{ $addOn->harga }})"
                                     class="flex items-center gap-3 p-3.5 rounded-xl border border-mirage-100 hover:border-mirage-200 transition cursor-pointer"
                                     :class="{ 'border-mirage-300 bg-mirage-50/50': (addonQtys[{{ $addOn->id }}] || 0) > 0 }">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800">{{ $addOn->nama }}</p>
                                        @if ($addOn->deskripsi)
                                            <p class="text-xs text-mirage-400 truncate">{{ $addOn->deskripsi }}</p>
                                        @endif
                                    </div>
                                    <span class="text-sm font-semibold text-mirage-600 flex-shrink-0 mr-2">Rp {{ number_format($addOn->harga, 0, ',', '.') }} (flat)</span>
                                    <div class="w-6 h-6 rounded border-2 flex items-center justify-center transition"
                                         :class="(addonQtys[{{ $addOn->id }}] || 0) > 0 ? 'bg-mirage-700 border-mirage-700' : 'border-mirage-300'">
                                        <svg x-show="(addonQtys[{{ $addOn->id }}] || 0) > 0" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <input type="hidden" name="add_ons[{{ $addOn->id }}]" :value="addonQtys[{{ $addOn->id }}] || 0">
                                </div>
                                @endif
                            @empty
                                <p class="text-sm text-mirage-400 text-center py-4">Belum ada layanan tambahan.</p>
                            @endforelse
                        </div>
                        @error('add_ons')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="bg-gradient-to-r from-mirage-50 to-white rounded-xl border border-mirage-100 p-5 mb-6">
                        <div class="space-y-2">
                            <template x-if="duration === 1 && hargaPerJam">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-mirage-500" x-text="'Harga Sewa (' + jamPakai + ' jam)'"></span>
                                    <span class="font-medium text-gray-800">Rp <span x-text="formatRupiah(jumlahHargaGedung)"></span></span>
                                </div>
                            </template>
                            <template x-if="duration > 1 || !hargaPerJam">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-mirage-500" x-text="'Harga Sewa (' + duration + ' hari)'"></span>
                                    <span class="font-medium text-gray-800">Rp <span x-text="formatRupiah(hargaGedung * duration)"></span></span>
                                </div>
                            </template>
                            <template x-for="(qty, id) in addonQtys" :key="id">
                                <template x-if="qty > 0">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-mirage-400" x-text="(qty > 1) ? `Layanan (x${qty})` : 'Layanan'"></span>
                                        <span class="font-medium text-mirage-600">+Rp <span x-text="formatRupiah(qty * (addonPrices[id] || 0))"></span></span>
                                    </div>
                                </template>
                            </template>
                        </div>
                        <div class="flex items-center justify-between pt-3 mt-3 border-t border-mirage-200">
                            <span class="text-base font-bold text-gray-900">Total</span>
                            <span class="text-xl font-bold text-gray-800">Rp <span x-text="formatRupiah(totalHarga)"></span></span>
                        </div>
                        <template x-if="paymentType === 'dp'">
                            <div class="flex items-center justify-between pt-2 mt-2 border-t border-dashed border-mirage-200">
                                <span class="text-sm font-bold text-mirage-600">Yang dibayar (DP 50%)</span>
                                <span class="text-lg font-bold text-mirage-600">Rp <span x-text="formatRupiah(Math.ceil(totalHarga / 2))"></span></span>
                            </div>
                        </template>
                    </div>

                    <button type="submit" class="btn-primary w-full justify-center text-base py-3.5">
                        Konfirmasi Booking
                    </button>

                    <p class="text-xs text-mirage-400 text-center mt-3">
                        Dengan mengklik Konfirmasi, Anda menyetujui syarat & ketentuan yang berlaku.
                    </p>
                </form>
            </div>
        </div>

    </div>
</div>

@vite('resources/js/booking-form.js')

@endsection
