@extends('layouts.app')

@section('title', 'Beranda - Booking Gedung')

@section('content')

<section class="relative min-h-[90vh] flex items-center overflow-hidden hero-bg pt-20">
    <div class="absolute inset-0 opacity-[0.03] bg-[length:48px_48px]"
         style="background-image: radial-gradient(circle, rgba(255,255,255,0.3) 1px, transparent 1px);">
    </div>

    <div class="absolute top-20 left-10 w-96 h-96 bg-white/5 rounded-full blur-[100px]"></div>
    <div class="absolute bottom-20 right-20 w-[30rem] h-[30rem] bg-white/5 rounded-full blur-[120px]"></div>

    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-0 w-full h-px bg-gradient-to-r from-transparent via-white/5 to-transparent animate-shimmer"></div>
        <div class="absolute top-2/4 left-0 w-full h-px bg-gradient-to-r from-transparent via-white/5 to-transparent animate-shimmer" style="animation-delay: 1s"></div>
        <div class="absolute top-3/4 left-0 w-full h-px bg-gradient-to-r from-transparent via-white/5 to-transparent animate-shimmer" style="animation-delay: 2s"></div>
    </div>

    <div class="relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center text-center">

            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 backdrop-blur rounded-full text-white/60 text-sm mb-6 border border-white/10">
                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                Platform booking gedung
            </div>

            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold text-white leading-[1.1] mb-6 tracking-tight">
                Temukan & Sewa
                <br>
                <span class="text-white">Gedung Impian</span>
                <br>
                Anda
            </h1>

            <p class="text-lg text-white/50 max-w-xl mx-auto mb-10 leading-relaxed">
                Cari dan booking gedung terbaik untuk acara, seminar, pernikahan, dan pertemuan bisnis Anda dalam hitungan menit.
            </p>

            <div class="flex flex-col sm:flex-row items-center gap-4 justify-center">
                <a href="#daftar-gedung" class="btn-primary text-base px-8 py-4 shadow-2xl shadow-black/25">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Lihat Gedung
                </a>
                @guest
                    <a href="{{ url('/register') }}" class="inline-flex items-center gap-2 px-8 py-4 font-semibold rounded-xl text-white/70 border border-white/20 hover:bg-white hover:text-black transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Daftar
                    </a>
                @endguest
            </div>

            <div class="flex items-center gap-8 sm:gap-12 mt-14 justify-center">
                <div class="text-center">
                    <p class="text-3xl sm:text-4xl font-bold text-white">{{ count($gedungList) }}+</p>
                    <p class="text-sm text-white/40 mt-1">Gedung Tersedia</p>
                </div>
                <div class="w-px h-12 bg-white/10"></div>
                <div class="text-center">
                    <p class="text-3xl sm:text-4xl font-bold text-white">50+</p>
                    <p class="text-sm text-white/40 mt-1">Booking Aktif</p>
                </div>
                <div class="w-px h-12 bg-white/10"></div>
                <div class="text-center">
                    <p class="text-3xl sm:text-4xl font-bold text-white">4.9</p>
                    <p class="text-sm text-white/40 mt-1">Rating</p>
                </div>
            </div>
        </div>
    </div>

    <div class="absolute bottom-0 left-0 right-0 leading-none">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none" class="w-full h-[60px] sm:h-[80px]">
            <path d="M0 80V40C240 10 480 0 720 20C960 40 1200 30 1440 10V80H0Z" fill="#fafafa"/>
        </svg>
    </div>
</section>

<section id="daftar-gedung" class="py-20 bg-stone-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-14">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-white text-stone-700 text-xs font-semibold rounded-full border border-stone-200">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                PILIHAN TERBAIK
            </span>
            <h2 class="text-4xl sm:text-5xl font-bold text-stone-900 mb-4 tracking-tight">
                Gedung Populer
            </h2>
            <p class="text-stone-400 max-w-xl mx-auto text-lg">
                Berbagai pilihan gedung terbaik untuk setiap kebutuhan acara Anda
            </p>
        </div>

        @php
            $cardData = [];
            foreach ($gedungList as $g) {
                $cardData[$g->id] = [
                    'id'         => $g->id,
                    'nama'       => $g->nama,
                    'alamat'     => $g->alamat,
                    'deskripsi'  => $g->deskripsi,
                    'kapasitas'  => $g->kapasitas,
                    'harga'      => (int) $g->harga_sewa,
                    'gambar'     => $g->gambar_url,
                    'images'     => $g->images->pluck('gambar_url')->prepend($g->gambar_url)->filter()->values()->toArray(),
                    'bookingUrl' => Auth::check() ? url('/booking/' . $g->id) : url('/login'),
                ];
            }
        @endphp
         <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5" id="gedungGrid"
               x-data="{
                   open: false,
                   currentIdx: 0,
                   d: {},
                   px: 0,
                   py: 0,
                   detailOpen: false,
                   detailData: {},
                   currentIdxDetail: 0,
                   openCard(data, event) {
                       const rect = event.currentTarget.getBoundingClientRect();
                       const pw = 380;
                       const vw = window.innerWidth;
                       let left = rect.right + 16;
                       if (left + pw > vw - 16) {
                           left = Math.max(16, (vw - pw) / 2);
                       }
                       this.px = left;
                       this.py = Math.min(rect.top, Math.max(16, window.innerHeight - 500));
                       this.d = data;
                       this.currentIdx = 0;
                       this.open = true;
                       this.detailOpen = false;
                   },
                   openDetail(data) {
                       this.detailData = data;
                       this.currentIdxDetail = 0;
                       this.detailOpen = true;
                   }
              }">
            @forelse ($gedungList as $i => $gedung)
                <div class="content-card card-hover !p-0 overflow-hidden cursor-pointer group"
                     data-nama="{{ strtolower($gedung->nama) }}"
                     @click='openCard(@json($cardData[$gedung->id]), $event)'>

                    <div class="relative h-40 overflow-hidden bg-stone-100">
                         @if ($gedung->gambar_url)
                             <img src="{{ $gedung->gambar_url }}" alt="{{ $gedung->nama }}"
                                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-stone-300 text-5xl font-thin">?</div>
                        @endif
                        <span class="absolute top-2.5 left-2.5 inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-white/90 text-stone-600">
                            👥 {{ $gedung->kapasitas }}
                        </span>
                    </div>

                    <div class="p-3.5 flex flex-col gap-1.5 flex-1">
                        <h3 class="text-sm font-bold text-stone-800 truncate leading-tight">
                            {{ $gedung->nama }}
                        </h3>
                        <p class="text-[11px] text-stone-400 truncate flex items-center gap-1">
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $gedung->alamat }}
                        </p>
                        <p class="text-[11px] text-stone-400 line-clamp-2 leading-relaxed flex-1">
                            {{ $gedung->deskripsi }}
                        </p>
                        <div class="flex items-center justify-between pt-2.5 mt-0.5 border-t border-stone-200">
                            <p class="text-sm font-bold text-stone-700">
                                Rp {{ number_format($gedung->harga_sewa, 0, ',', '.') }}
                            </p>
                            <button type="button" @click.stop='openCard(@json($cardData[$gedung->id]), $event)' class="inline-flex items-center gap-1 text-[11px] font-semibold px-3 py-1.5 rounded-lg text-stone-600 bg-stone-100 hover:bg-black hover:text-white transition-colors">
                                Detail
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16">
                    <p class="text-xl text-stone-400 font-medium">Belum ada gedung tersedia.</p>
                </div>
            @endforelse

            <div x-show="open"
                 class="fixed inset-0 z-[9999] flex items-start justify-center sm:items-center sm:p-4"
                 style="display: none;"
                 @click.self="open = false">

                <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" @click="open = false"></div>

                <div class="relative w-[calc(100vw-1.5rem)] sm:w-[380px] bg-white rounded-2xl shadow-2xl shadow-black/15 overflow-hidden border border-stone-200 max-h-[90vh] overflow-y-auto animate-scale-up"
                     @click.away="open = false">

                    <button type="button" @click="open = false"
                            class="absolute top-2.5 right-2.5 z-20 w-7 h-7 rounded-full bg-white/80 flex items-center justify-center shadow-sm hover:bg-white transition border border-stone-200">
                        <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <div class="relative bg-stone-800">
                        <template x-for="(src, i) in (d.images || [d.gambar])" :key="i">
                            <img :src="src" :alt="'Foto ' + (i + 1)"
                                 x-show="currentIdx === i"
                                 x-transition:enter="transition-opacity duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 class="w-full h-36 object-cover">
                        </template>

                        <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex items-center justify-between px-2 pointer-events-none">
                            <button type="button" @click.stop="currentIdx = Math.max(0, currentIdx - 1)"
                                    class="w-7 h-7 bg-white/80 rounded-full flex items-center justify-center shadow-sm hover:bg-white transition pointer-events-auto"
                                    :class="currentIdx === 0 ? 'opacity-30 cursor-not-allowed' : 'opacity-100'">
                                <svg class="w-3.5 h-3.5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" @click.stop="currentIdx = Math.min((d.images || []).length - 1, currentIdx + 1)"
                                    class="w-7 h-7 bg-white/80 rounded-full flex items-center justify-center shadow-sm hover:bg-white transition pointer-events-auto"
                                    :class="currentIdx >= (d.images || []).length - 1 ? 'opacity-30 cursor-not-allowed' : 'opacity-100'">
                                <svg class="w-3.5 h-3.5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>

                        <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex items-center gap-1"
                             x-show="(d.images || []).length > 1">
                            <template x-for="(_, i) in (d.images || [])" :key="i">
                                <button type="button" @click="currentIdx = i"
                                        class="w-1.5 h-1.5 rounded-full transition-all duration-300"
                                        :class="currentIdx === i ? 'bg-white w-3' : 'bg-white/40'">
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h2 class="text-sm font-bold text-stone-800 truncate" x-text="d.nama"></h2>
                                <p class="text-[11px] text-stone-400 mt-0.5" x-text="d.alamat"></p>
                            </div>
                            <span class="text-sm font-bold text-stone-700 shrink-0"
                                  x-text="'Rp ' + Number(d.harga).toLocaleString('id-ID')"></span>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 text-[10px] font-medium px-2 py-0.5 rounded-full bg-stone-100 text-stone-600">👥 <span x-text="d.kapasitas"></span> orang</span>
                        </div>

                        <div class="bg-stone-50 rounded-lg p-3">
                            <p class="text-[11px] text-stone-500 leading-relaxed" x-text="d.deskripsi"></p>
                        </div>

                        <div class="flex gap-2">
                            <a :href="d.bookingUrl" class="btn-primary text-sm py-2.5 flex-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Booking Sekarang
                            </a>
                            <button type="button" @click.stop="openDetail(d)"
                                    class="btn-primary text-sm py-2.5 flex-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                                Detail Selengkapnya
                            </button>
                        </div>
                </div>
            </div>

            <div x-show="detailOpen"
                 class="fixed inset-0 z-[99999] flex items-center justify-center p-4"
                 style="display: none;">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="detailOpen = false"></div>
                <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl shadow-black/15 overflow-hidden border border-stone-200 animate-scale-up max-h-[90vh] overflow-y-auto"
                     @click.away="detailOpen = false">
                    <button type="button" @click="detailOpen = false"
                            class="absolute top-3 right-3 z-20 w-7 h-7 rounded-full bg-white/80 flex items-center justify-center shadow-sm hover:bg-white transition border border-stone-200">
                        <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <div class="relative bg-stone-800">
                        <template x-for="(src, i) in (detailData.images || [detailData.gambar])" :key="i">
                            <img :src="src" :alt="'Foto ' + (i + 1)"
                                 x-show="currentIdxDetail === i"
                                 x-transition:enter="transition-opacity duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 class="w-full h-80 object-cover">
                        </template>

                        <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex items-center justify-between px-2 pointer-events-none"
                             x-show="(detailData.images || []).length > 1">
                            <button type="button" @click.stop="currentIdxDetail = Math.max(0, currentIdxDetail - 1)"
                                    class="w-7 h-7 bg-white/80 rounded-full flex items-center justify-center shadow-sm hover:bg-white transition pointer-events-auto"
                                    :class="currentIdxDetail === 0 ? 'opacity-30 cursor-not-allowed' : 'opacity-100'">
                                <svg class="w-3.5 h-3.5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" @click.stop="currentIdxDetail = Math.min((detailData.images || []).length - 1, currentIdxDetail + 1)"
                                    class="w-7 h-7 bg-white/80 rounded-full flex items-center justify-center shadow-sm hover:bg-white transition pointer-events-auto"
                                    :class="currentIdxDetail >= (detailData.images || []).length - 1 ? 'opacity-30 cursor-not-allowed' : 'opacity-100'">
                                <svg class="w-3.5 h-3.5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>

                        <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex items-center gap-1"
                             x-show="(detailData.images || []).length > 1">
                            <template x-for="(_, i) in (detailData.images || [])" :key="i">
                                <button type="button" @click="currentIdxDetail = i"
                                        class="w-1.5 h-1.5 rounded-full transition-all duration-300"
                                        :class="currentIdxDetail === i ? 'bg-white w-3' : 'bg-white/40'">
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="p-5 space-y-4">
                        <h2 class="text-base font-bold text-stone-800" x-text="detailData.nama"></h2>
                        <p class="text-sm text-stone-500 leading-relaxed" x-text="detailData.deskripsi"></p>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</section>

<section class="py-20 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-white text-stone-700 text-xs font-semibold rounded-full border border-stone-200">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                KENAPA MEMILIH KAMI
            </span>
            <h2 class="text-4xl sm:text-5xl font-bold text-stone-900 mb-4 tracking-tight">
                Kenapa BookingGedung?
            </h2>
            <p class="text-stone-400 max-w-xl mx-auto text-lg">
                Kami hadir untuk memudahkan Anda menemukan dan booking gedung terbaik
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            @php
                $fitur = [
                    ['1', 'Mudah Dicari', 'Cari gedung berdasarkan lokasi, kapasitas, dan harga dengan cepat dan akurat.'],
                    ['2', 'Booking Instan', 'Booking langsung dapat konfirmasi tanpa perlu menunggu lama.'],
                    ['3', 'Aman & Terpercaya', 'Data Anda aman dengan sistem booking yang transparan dan terenkripsi.'],
                    ['4', 'Harga Transparan', 'Harga jelas tanpa biaya tersembunyi, lengkap dengan opsi add-on.'],
                    ['5', 'Add-On Lengkap', 'Sound system, catering, dekorasi, dan berbagai layanan tambahan lainnya.'],
                    ['6', '24/7 Support', 'Tim support siap membantu Anda kapan pun dibutuhkan.'],
                ];
            @endphp
            @foreach ($fitur as $i => [$num, $title, $desc])
                <div class="content-card hover:shadow-lg transition-all duration-300 group text-center sm:text-left"
                     data-aos="fade-up" data-aos-delay="{{ 50 + ($i * 50) }}">
                    <div class="w-12 h-12 rounded-xl bg-stone-900 text-white flex items-center justify-center text-sm font-bold mb-4 group-hover:bg-white group-hover:text-stone-900 group-hover:ring-2 group-hover:ring-stone-900 transition-all duration-300">
                        {{ $num }}
                    </div>
                    <h3 class="text-lg font-semibold text-stone-900 mb-2">{{ $title }}</h3>
                    <p class="text-sm text-stone-500 leading-relaxed">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="relative py-28 overflow-hidden hero-bg">
    <div class="absolute top-10 left-1/4 w-80 h-80 bg-white/5 rounded-full blur-[100px]"></div>
    <div class="absolute bottom-10 right-1/4 w-80 h-80 bg-white/5 rounded-full blur-[100px]"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 backdrop-blur rounded-full text-white/60 text-sm mb-6 border border-white/10">
            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
            SIAP MEMULAI?
        </span>
        <h2 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-4 tracking-tight">
            Siap Booking Gedung?
        </h2>
        <p class="text-lg text-white/50 max-w-lg mx-auto mb-10 leading-relaxed">
            Daftar sekarang dan mulai booking gedung impian Anda untuk acara terbaik.
        </p>
        @guest
            <a href="{{ url('/register') }}" class="btn-primary text-base px-10 py-4 shadow-2xl shadow-black/25">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Daftar Gratis
            </a>
        @else
            <a href="#daftar-gedung" class="btn-primary text-base px-10 py-4 shadow-2xl shadow-black/25">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Mulai Booking
            </a>
        @endguest
    </div>

    <div class="absolute bottom-0 left-0 right-0 leading-none">
        <svg viewBox="0 0 1440 60" preserveAspectRatio="none" class="w-full h-[40px]">
            <path d="M0 60V30C240 0 480 0 720 20C960 40 1200 30 1440 20V60H0Z" fill="#fafafa"/>
        </svg>
    </div>
</section>

@endsection