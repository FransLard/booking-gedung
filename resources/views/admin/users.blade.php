@extends('layouts.app')

@section('title', 'Kelola User')

@section('content')
    <div class="page-header animate-slide-up">
        <span class="badge-gray mb-3 inline-flex">ADMIN</span>
        <h1>Kelola User</h1>
        <p>Daftar seluruh pengguna aplikasi.</p>
    </div>

    <div class="content-card animate-slide-up stagger-1">
        @if ($users->isEmpty())
            <div class="text-center py-12">
                <span class="text-5xl block mb-4">👥</span>
                <p class="text-gray-400">Belum ada pengguna.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="table-base">
                    <thead>
                        <tr class="table-head">
                            <th class="table-cell">Nama</th>
                            <th class="table-cell">Email</th>
                            <th class="table-cell">Role</th>
                            <th class="table-cell">Daftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="table-row">
                                <td class="table-cell">
                                    <div class="flex items-center gap-2">
                                        <span class="w-8 h-8 flex items-center justify-center rounded-full bg-gradient-to-br from-mirage-800 to-mirage-900 text-white text-xs font-bold">
                                            {{ substr($user->name, 0, 1) }}
                                        </span>
                                        <span class="text-gray-700 font-medium">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="table-cell text-gray-500">{{ $user->email }}</td>
                                <td class="table-cell">
                                    <span class="{{ $user->role === 'admin' ? 'badge-blue' : 'badge-gray' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="table-cell text-gray-500">{{ $user->created_at->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
