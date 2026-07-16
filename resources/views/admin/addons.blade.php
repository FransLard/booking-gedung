@extends('layouts.app')

@section('title', 'Kelola Add-On')

@section('content')
    <div class="page-header animate-slide-up">
        <span class="badge-gray mb-3 inline-flex">ADMIN</span>
        <h1>Kelola Add-On</h1>
        <p>Tambah, edit, atau hapus layanan tambahan (add-on).</p>
    </div>

    <div class="grid lg:grid-cols-2 gap-8">
        <div class="animate-slide-up stagger-1">
            <form method="POST" action="{{ route('admin.addons.store') }}" class="content-card">
                @csrf
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Tambah Add-On Baru</h2>

                <div class="mb-4">
                    <label class="label-field">Nama Add-On</label>
                    <input type="text" name="nama" required class="input-field" placeholder="Contoh: Sound System">
                </div>

                <div class="mb-4">
                    <label class="label-field">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" class="textarea-field" placeholder="Deskripsi layanan tambahan"></textarea>
                </div>

                <div class="mb-4">
                    <label class="label-field">Harga (Rp)</label>
                    <input type="number" name="harga" required min="0" class="input-field" placeholder="500000">
                </div>

                <div class="mb-4">
                    <label class="label-field">Tipe</label>
                    <select name="type" required class="input-field">
                        <option value="flat">Flat — harga tetap (cocok: live music, dekorasi)</option>
                        <option value="per_unit">Per Unit — bisa pilih jumlah (cocok: kursi, meja)</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary text-sm w-full">Tambah Add-On</button>
            </form>
        </div>

        <div class="animate-slide-up stagger-2">
            <div class="content-card">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Daftar Add-On</h2>

                @if ($addOns->isEmpty())
                    <div class="text-center py-8">
                        <span class="text-4xl block mb-3">🧩</span>
                        <p class="text-gray-400">Belum ada add-on.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($addOns as $addOn)
                            <div class="border border-mirage-100 rounded-xl p-4 hover:border-mirage-200 transition">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $addOn->nama }}</h3>
                                        @if ($addOn->deskripsi)
                                            <p class="text-sm text-gray-500 mt-0.5">{{ $addOn->deskripsi }}</p>
                                        @endif
                                        <p class="text-sm font-medium text-mirage-600 mt-1">Rp {{ number_format($addOn->harga, 0, ',', '.') }}
                                            <span class="text-xs font-normal text-mirage-400 ml-1">{{ $addOn->type === 'per_unit' ? '/buah' : '(flat)' }}</span>
                                        </p>
                                    </div>
                                    <div class="flex gap-1">
                                        <button onclick="editAddOn({{ $addOn->id }})" class="btn-ghost text-xs">Edit</button>
                                        <form method="POST" action="{{ route('admin.addons.destroy', $addOn) }}" onsubmit="return confirm('Hapus add-on ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-ghost text-xs text-red-600 hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/40 hidden" onclick="if(event.target===this) closeEdit()">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4 animate-scale-in">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Add-On</h3>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="mb-4">
                    <label class="label-field">Nama Add-On</label>
                    <input type="text" name="nama" id="editNama" required class="input-field">
                </div>
                <div class="mb-4">
                    <label class="label-field">Deskripsi</label>
                    <textarea name="deskripsi" id="editDeskripsi" rows="3" class="textarea-field"></textarea>
                </div>
                <div class="mb-4">
                    <label class="label-field">Harga (Rp)</label>
                    <input type="number" name="harga" id="editHarga" required min="0" class="input-field">
                </div>
                <div class="mb-4">
                    <label class="label-field">Tipe</label>
                    <select name="type" id="editType" required class="input-field">
                        <option value="flat">Flat — harga tetap</option>
                        <option value="per_unit">Per Unit — bisa pilih jumlah</option>
                    </select>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="closeEdit()" class="btn-ghost">Batal</button>
                    <button type="submit" class="btn-primary text-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function editAddOn(id) {
    const addOns = @json($addOns);
    const item = addOns.find(a => a.id === id);
    if (!item) return;
    document.getElementById('editForm').action = '{{ url("admin/addons") }}/' + id;
    document.getElementById('editNama').value = item.nama;
    document.getElementById('editDeskripsi').value = item.deskripsi || '';
    document.getElementById('editHarga').value = item.harga;
    document.getElementById('editType').value = item.type || 'flat';
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEdit() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endpush
