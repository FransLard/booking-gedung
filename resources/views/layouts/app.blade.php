<!DOCTYPE html>
<html lang="id" x-data="{ mobileOpen: false, userOpen: false, adminOpen: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Booking Gedung')</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $waitingCount = auth()->check() && auth()->user()->isAdmin()
        ? \App\Models\Booking::where('payment_status', 'waiting')->count()
        : 0;
@endphp
<body class="bg-gray-50" :class="{ 'overflow-hidden': mobileOpen }">

    @if (!request()->is('login') && !request()->is('register'))
    <nav class="fixed top-0 inset-x-0 z-50 card-glass border-b border-gray-100/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-18">

                <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-black flex items-center justify-center shadow-sm group-hover:shadow-md transition-all group-hover:scale-105">
                        <span class="text-white text-sm font-bold">BG</span>
                    </div>
                    <span class="text-lg font-bold text-stone-800 tracking-tight">BookingGedung</span>
                </a>

                <div class="hidden md:flex items-center gap-1">
                    <x-nav-link href="{{ url('/') }}" :active="request()->is('/')">
                        Beranda
                    </x-nav-link>

                    @auth
                        @if (Auth::user()->isAdmin())

                            <div class="relative" @click.outside="adminOpen = false">
                                <button @click="adminOpen = !adminOpen"
                                        class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition {{ request()->is('dashboard*') || request()->is('admin/*') ? 'text-mirage-800 bg-mirage-100' : 'text-gray-600 hover:text-mirage-700 hover:bg-mirage-50' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                    Admin
                                    @if ($waitingCount > 0)
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    @endif
                                    <svg class="w-3.5 h-3.5 text-gray-400 transition-transform" :class="{ 'rotate-180': adminOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div x-show="adminOpen" x-cloak @click.outside="adminOpen = false"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                                     class="absolute top-full mt-2 left-0 w-56 rounded-xl bg-white shadow-xl border border-gray-100 py-1.5 overflow-hidden">
                                    <div class="px-4 py-2 border-b border-gray-50">
                                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Panel Admin</p>
                                    </div>
                                    <a href="{{ url('/dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                        Dashboard
                                    </a>
                                    <a href="{{ route('admin.bookings') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Kelola Booking
                                        @if ($waitingCount > 0)
                                            <span class="w-2 h-2 rounded-full bg-red-500 ml-auto"></span>
                                        @endif
                                    </a>
                                    <a href="{{ route('admin.addons') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                        Kelola Add-On
                                    </a>
                                    <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                                        Kelola User
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if (!Auth::user()->isAdmin())
                            <x-nav-link href="{{ route('booking.my') }}" :active="request()->is('bookings*') || request()->is('booking/detail*')">
                                Booking Saya
                            </x-nav-link>
                        @endif

                        <div class="ml-4 pl-4 border-l border-gray-200 relative" @click.outside="userOpen = false">
                            <button @click="userOpen = !userOpen"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-gray-50 transition group">
                                <div class="w-8 h-8 rounded-full bg-black flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 hidden sm:block">{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': userOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="userOpen" x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                                 class="absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-xl border border-gray-100 py-1.5">
                                <div class="px-4 py-2 border-b border-gray-50">
                                    <p class="text-xs text-gray-400">Masuk sebagai</p>
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ Auth::user()->name }}</p>
                                </div>
                                @if (!Auth::user()->isAdmin())
                                    <a href="{{ route('booking.my') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Booking Saya
                                    </a>
                                @endif
                                <form method="POST" action="{{ url('/logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ url('/login') }}" class="btn-ghost text-sm">Login</a>
                        <a href="{{ url('/register') }}" class="btn-primary text-sm py-2 px-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            Daftar
                        </a>
                    @endauth
                </div>

                <button @click="mobileOpen = !mobileOpen"
                        class="md:hidden relative w-10 h-10 rounded-xl hover:bg-gray-100 transition flex items-center justify-center">
                    <div class="w-5 h-4 relative">
                        <span class="absolute top-0 left-0 w-full h-0.5 bg-gray-600 rounded-full transition-all duration-300"
                              :class="{ 'top-1/2 -translate-y-1/2 rotate-45': mobileOpen }"></span>
                        <span class="absolute top-1/2 -translate-y-1/2 left-0 w-full h-0.5 bg-gray-600 rounded-full transition-all duration-300"
                              :class="{ 'opacity-0 scale-0': mobileOpen }"></span>
                        <span class="absolute bottom-0 left-0 w-full h-0.5 bg-gray-600 rounded-full transition-all duration-300"
                              :class="{ 'top-1/2 -translate-y-1/2 -rotate-45': mobileOpen }"></span>
                    </div>
                </button>
            </div>
        </div>

        <div x-show="mobileOpen" @click.outside="mobileOpen = false" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="md:hidden border-t border-gray-100 bg-white/98 backdrop-blur-xl max-h-[80vh] overflow-y-auto">
            <div class="px-4 py-5 space-y-1">
                <a href="{{ url('/') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium {{ request()->is('/') ? 'bg-gray-100 text-gray-800' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Beranda
                </a>

                @auth
                    @if (Auth::user()->isAdmin())
                        <div class="pt-2 pb-1"><p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Admin</p></div>
                        <a href="{{ url('/dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium {{ request()->is('dashboard*') ? 'bg-gray-100 text-gray-800' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            Dashboard
                        </a>
                        <a href="{{ route('admin.bookings') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Kelola Booking
                            @if ($waitingCount > 0)
                                <span class="w-2 h-2 rounded-full bg-red-500 ml-auto"></span>
                            @endif
                        </a>
                        <a href="{{ route('admin.addons') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            Kelola Add-On
                        </a>
                        <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                            Kelola User
                        </a>
                    @endif
                    @if (!Auth::user()->isAdmin())
                        <a href="{{ route('booking.my') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium {{ request()->is('bookings*') || request()->is('booking/detail*') ? 'bg-gray-100 text-gray-800' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Booking Saya
                        </a>
                    @endif
                    <div class="border-t border-gray-100 pt-4 mt-4">
                        <div class="flex items-center gap-3 px-4 mb-3">
                            <div class="w-9 h-9 rounded-full bg-black flex items-center justify-center text-white text-sm font-bold">{{ substr(Auth::user()->name, 0, 1) }}</div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-400">{{ Auth::user()->email }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ url('/logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Logout
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ url('/login') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        Login
                    </a>
                    <a href="{{ url('/register') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-white bg-gray-900 shadow-sm mt-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Daftar
                    </a>
                @endauth
            </div>
        </div>
    </nav>
    @endif

    @if (session('success'))
        <div id="toast"
             class="fixed top-24 right-4 z-[999] flex items-center gap-3 bg-white border border-emerald-100 shadow-xl shadow-emerald-500/5 rounded-2xl px-5 py-3.5 animate-scale-in max-w-sm">
            <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900">Berhasil!</p>
                <p class="text-sm text-gray-500">{{ session('success') }}</p>
            </div>
            <button onclick="this.closest('#toast').remove()" class="ml-auto flex-shrink-0 w-6 h-6 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div id="toast"
             class="fixed top-24 right-4 z-[999] flex items-center gap-3 bg-white border border-red-100 shadow-xl shadow-red-500/5 rounded-2xl px-5 py-3.5 animate-scale-in max-w-sm">
            <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-xl bg-red-100 text-red-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900">Error!</p>
                <p class="text-sm text-gray-500">{{ session('error') }}</p>
            </div>
            <button onclick="this.closest('#toast').remove()" class="ml-auto flex-shrink-0 w-6 h-6 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if (request()->is('/') || request()->is('login') || request()->is('register'))
        @yield('content')
    @else
        <main class="pt-20 pb-16 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto animate-fade-in">
            @yield('content')
        </main>
    @endif

    @if (!request()->is('login') && !request()->is('register'))
    <footer class="bg-gray-900 text-gray-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid sm:grid-cols-3 gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center shadow-sm">
                            <span class="text-black text-xs font-bold">BG</span>
                        </div>
                        <span class="text-base font-bold text-white">BookingGedung</span>
                    </div>
                    <p class="text-sm leading-relaxed text-gray-500">Platform booking gedung terpercaya untuk acara, seminar, pernikahan, dan pertemuan bisnis Anda.</p>
                </div>
                <div>
                    <h4 class="text-xs font-semibold text-gray-300 uppercase tracking-wider mb-4">Navigasi</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ url('/') }}" class="text-gray-400 hover:text-white transition">Beranda</a></li>
                        <li><a href="{{ url('/') }}#daftar-gedung" class="text-gray-400 hover:text-white transition">Gedung</a></li>
                        @guest
                            <li><a href="{{ url('/login') }}" class="text-gray-400 hover:text-white transition">Login</a></li>
                            <li><a href="{{ url('/register') }}" class="text-gray-400 hover:text-white transition">Register</a></li>
                        @endguest
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-semibold text-gray-300 uppercase tracking-wider mb-4">Kontak</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li class="flex items-center gap-2 text-gray-400">📧 hello@bookinggedung.com</li>
                        <li class="flex items-center gap-2 text-gray-400">📞 (021) 1234-5678</li>
                        <li class="flex items-center gap-2 text-gray-400">📍 Jakarta, Indonesia</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-6 text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} BookingGedung. All rights reserved.</p>
            </div>
        </div>
    </footer>
    @endif

    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const t = document.getElementById('toast');
            if (t) setTimeout(() => { t.remove(); }, 5000);
        });
    </script>
</body>
</html>
