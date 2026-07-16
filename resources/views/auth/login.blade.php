@extends('layouts.app')

@section('title', 'Login')

@section('content')

<div class="min-h-screen flex items-center justify-center px-4 dark-section relative overflow-hidden">
    <div class="absolute top-20 -left-20 w-96 h-96 bg-white/[0.03] rounded-full blur-[120px]"></div>
    <div class="absolute bottom-20 -right-20 w-96 h-96 bg-white/[0.03] rounded-full blur-[120px]"></div>

    <div class="relative w-full max-w-sm">
        <div class="bg-white/[0.07] backdrop-blur-2xl border border-white/[0.12] rounded-3xl p-8 shadow-2xl shadow-black/40">
            <div class="text-center mb-8">
                <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h1 class="text-xl font-bold text-white">Masuk</h1>
                <p class="text-sm text-white/40 mt-1">Masuk ke akun Anda</p>
            </div>

            <form method="POST" action="{{ url('/login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="text-xs font-medium text-white/50 mb-1.5 block">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="nama@email.com"
                           class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder:text-white/25 outline-none focus:border-white/40 focus:bg-white/[0.07] transition-all duration-300">
                    @error('email')
                        <p class="text-red-400 text-xs mt-1 pl-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-xs font-medium text-white/50 mb-1.5 block">Password</label>
                    <input type="password" name="password" required placeholder="Masukkan password"
                           class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder:text-white/25 outline-none focus:border-white/40 focus:bg-white/[0.07] transition-all duration-300">
                    @error('password')
                        <p class="text-red-400 text-xs mt-1 pl-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" name="remember"
                               class="w-4 h-4 rounded border-white/20 bg-white/5 text-white focus:ring-0 checked:bg-white checked:border-white transition-all">
                        <span class="text-xs text-white/40 group-hover:text-white/60 transition">Ingat saya</span>
                    </label>
                    <a href="#" class="text-xs text-white/30 hover:text-white/60 transition">Lupa password?</a>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-white text-black font-semibold text-sm hover:bg-white/90 transition-all duration-300 shadow-lg shadow-black/20 mt-2">
                    Masuk
                </button>

                <div class="relative">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-white/5"></div></div>
                    <div class="relative flex justify-center"><span class="text-xs text-white/20 bg-transparent px-3">atau</span></div>
                </div>

                <p class="text-center text-xs text-white/30">
                    Belum punya akun?
                    <a href="{{ url('/register') }}" class="text-white font-semibold hover:underline transition">Daftar</a>
                </p>
            </form>
        </div>
    </div>
</div>

@endsection