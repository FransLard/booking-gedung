<?php

namespace App\Http\Controllers;

use App\Exports\FinancialReportExport;
use App\Models\Booking;
use App\Models\InAppNotification;
use App\Models\Gedung;
use App\Models\GedungImage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $totalGedung   = Gedung::count();
        $totalBooking  = Booking::count();
        $totalUser     = User::where('role', 'user')->count();
        $totalRevenue  = Booking::whereIn('payment_status', ['paid_lunas', 'paid_dp'])->sum('total_harga');
        $totalLoss     = Booking::where('status', 'cancelled')->sum('total_harga');

        $thisMonthRevenue = Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereIn('payment_status', ['paid_lunas', 'paid_dp'])
            ->sum('total_harga');

        $lastMonthStart = $startOfMonth->copy()->subMonth();
        $lastMonthEnd   = $startOfMonth->copy()->subDay();
        $lastMonthRevenue = Booking::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->whereIn('payment_status', ['paid_lunas', 'paid_dp'])
            ->sum('total_harga');

        $revenueChange = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : ($thisMonthRevenue > 0 ? 100 : 0);

        $recentBookings = Booking::with(['user', 'gedung'])->latest()->take(10)->get();

        $gedungs = Gedung::all();

        $allBookingsForStats = Booking::selectRaw('gedung_id, status, payment_status, total_harga, created_at')
            ->get();

        $gedungProfitLoss = $gedungs->map(function ($gedung) use ($allBookingsForStats) {
            $gedungBookings = $allBookingsForStats->where('gedung_id', $gedung->id);
            $profit = $gedungBookings->whereIn('status', ['confirmed', 'pending'])
                ->whereIn('payment_status', ['paid_dp', 'paid_lunas'])
                ->sum('total_harga');
            $loss = $gedungBookings->where('status', 'cancelled')
                ->sum('total_harga');
            return [
                'id'     => $gedung->id,
                'name'   => $gedung->nama,
                'profit' => (int) $profit,
                'loss'   => (int) $loss,
                'net'    => (int) ($profit - $loss),
            ];
        });

        $statusCount = collect(['confirmed' => 0, 'pending' => 0, 'cancelled' => 0])
            ->merge($allBookingsForStats->groupBy('status')
                ->map(fn ($g) => $g->count()));

        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $monthBookings = $allBookingsForStats->filter(function ($b) use ($m) {
                $c = Carbon::parse($b->created_at);
                return $c->year == $m->year && $c->month == $m->month;
            });
            $rev = $monthBookings->whereIn('payment_status', ['paid_lunas', 'paid_dp'])->sum('total_harga');
            $monthlyRevenue[] = [
                'month'   => $m->format('M Y'),
                'revenue' => (int) $rev,
            ];
        }

        return view('dashboard.index', compact(
            'totalGedung', 'totalBooking', 'totalUser', 'totalRevenue', 'totalLoss',
            'thisMonthRevenue', 'lastMonthRevenue', 'revenueChange',
            'recentBookings', 'statusCount', 'monthlyRevenue',
            'gedungProfitLoss', 'month',
        ));
    }

    public function gedung(Request $request, Gedung $gedung)
    {
        $gedung->load('images');

        $month = $request->get('month', now()->format('Y-m'));

        $allBookings = Booking::with('user')
            ->where('gedung_id', $gedung->id)
            ->latest()
            ->get();

        $bookings = Booking::with('user')
            ->where('gedung_id', $gedung->id)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $totalBookings = $allBookings->count();
        $totalProfit = $allBookings->whereIn('status', ['confirmed', 'pending'])
            ->whereIn('payment_status', ['paid_dp', 'paid_lunas'])
            ->sum('total_harga');
        $totalLoss = $allBookings->where('status', 'cancelled')->sum('total_harga');
        $netProfit = $totalProfit - $totalLoss;

        $statusCount = collect(['confirmed' => 0, 'pending' => 0, 'cancelled' => 0])
            ->merge($allBookings->groupBy('status')
                ->map(fn ($g) => $g->count()));

        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $monthBookings = $allBookings->filter(function ($b) use ($m) {
                return Carbon::parse($b->created_at)->year == $m->year
                    && Carbon::parse($b->created_at)->month == $m->month;
            });
            $monthlyData[] = [
                'month'  => $m->format('M Y'),
                'profit' => (int) $monthBookings->whereIn('status', ['confirmed', 'pending'])
                    ->whereIn('payment_status', ['paid_dp', 'paid_lunas'])->sum('total_harga'),
                'loss'   => (int) $monthBookings->where('status', 'cancelled')->sum('total_harga'),
            ];
        }

        return view('admin.gedung', compact(
            'gedung', 'bookings', 'totalBookings', 'totalProfit', 'totalLoss', 'netProfit',
            'statusCount', 'monthlyData', 'month',
        ));
    }

    public function downloadExcel(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $gedungId = $request->get('gedung_id');
        return (new FinancialReportExport)->download($month, $gedungId);
    }

    public function bookings()
    {
        $bookings = Booking::with(['user', 'gedung'])->latest()->paginate(25);
        return view('admin.bookings', compact('bookings'));
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate(['status' => 'required|in:confirmed,pending,cancelled']);
        $booking->update(['status' => $data['status']]);
        return back()->with('success', 'Status booking berhasil diperbarui.');
    }

    public function updatePayment(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Tidak dapat mengubah pembayaran booking yang sudah dibatalkan.');
        }

        $data = $request->validate(['payment_status' => 'required|in:unpaid,paid_dp,paid_lunas,waiting']);
        $update = ['payment_status' => $data['payment_status']];

        if (in_array($data['payment_status'], ['paid_dp', 'paid_lunas'])) {
            $update['payment_verified_at'] = now();
            $update['status'] = 'confirmed';
            $update['batas_bayar_lunas'] = null;
        }

        $booking->update($update);
        return back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }

    public function verifyPayment(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'action' => 'required|in:approve,reject',
        ]);

        if ($data['action'] === 'approve') {
            $paymentStatus = $booking->payment_type === 'lunas' ? 'paid_lunas' : 'paid_dp';
            $booking->update([
                'payment_status'     => $paymentStatus,
                'payment_verified_at' => now(),
                'status'              => 'confirmed',
                'batas_bayar_lunas'   => null,
            ]);

            InAppNotification::create([
                'user_id'    => $booking->user_id,
                'booking_id' => $booking->id,
                'type'       => 'approved',
                'message'    => 'Pembayaran ' . ($booking->payment_type === 'lunas' ? 'Lunas' : 'DP 50%') . ' untuk booking ' . $booking->booking_code . ' telah diverifikasi.',
            ]);

            return back()->with('success', 'Pembayaran berhasil diverifikasi. Booking dikonfirmasi.');
        } else {
            if ($booking->payment_proof) {
                Storage::disk('public')->delete($booking->payment_proof);
            }
            $booking->update([
                'payment_proof'  => null,
                'payment_status' => 'unpaid',
            ]);

            InAppNotification::create([
                'user_id'    => $booking->user_id,
                'booking_id' => $booking->id,
                'type'       => 'rejected',
                'message'    => 'Bukti pembayaran untuk booking ' . $booking->booking_code . ' ditolak. Silakan upload ulang.',
            ]);

            return back()->with('success', 'Bukti pembayaran ditolak. Silakan upload ulang.');
        }
    }

    public function users()
    {
        $users = User::latest()->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function uploadImage(Request $request, Gedung $gedung): RedirectResponse
    {
        $data = $request->validate([
            'gambar'     => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $path = $request->file('gambar')->store('gedung', 'public');

        $maxUrutan = $gedung->images()->max('urutan') ?? 0;

        GedungImage::create([
            'gedung_id'  => $gedung->id,
            'gambar'     => $path,
            'keterangan' => $data['keterangan'] ?? null,
            'urutan'     => $maxUrutan + 1,
        ]);

        return back()->with('success', 'Gambar berhasil ditambahkan.');
    }

    public function deleteImage(GedungImage $gedungImage): RedirectResponse
    {
        if ($gedungImage->gambar && !filter_var($gedungImage->gambar, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($gedungImage->gambar);
        }
        $gedungImage->delete();
        return back()->with('success', 'Gambar berhasil dihapus.');
    }

    public function updateGedung(Request $request, Gedung $gedung): RedirectResponse
    {
        $data = $request->validate([
            'nama'           => 'required|string|max:255',
            'alamat'         => 'required|string|max:500',
            'deskripsi'      => 'nullable|string',
            'kapasitas'      => 'required|integer|min:1',
            'harga_sewa'     => 'required|numeric|min:0',
            'harga_per_jam'  => 'nullable|numeric|min:0',
            'minimum_jam'    => 'nullable|integer|min:1|max:24',
            'gambar'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_dalam'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'deskripsi_dalam' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('gambar')) {
            if ($gedung->gambar && !filter_var($gedung->gambar, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($gedung->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('gedung', 'public');
        } else {
            unset($data['gambar']);
        }

        if ($request->hasFile('gambar_dalam')) {
            if ($gedung->gambar_dalam && !filter_var($gedung->gambar_dalam, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($gedung->gambar_dalam);
            }
            $data['gambar_dalam'] = $request->file('gambar_dalam')->store('gedung', 'public');
        } else {
            unset($data['gambar_dalam']);
        }

        $gedung->update($data);
        return back()->with('success', 'Data gedung berhasil diperbarui.');
    }
}
