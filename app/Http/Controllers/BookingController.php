<?php

namespace App\Http\Controllers;

use App\Models\AddOn;
use App\Models\Booking;
use App\Models\Gedung;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Gedung $gedung): View
    {
        $addOns = AddOn::all();

        $bookings = Booking::where('gedung_id', $gedung->id)
            ->where('status', '!=', 'cancelled')
            ->where('tanggal_selesai', '>=', now()->subDay())
            ->get(['tanggal', 'tanggal_selesai']);

        $bookedDates = collect();
        foreach ($bookings as $b) {
            $start = Carbon::parse($b->tanggal);
            $end = Carbon::parse($b->tanggal_selesai ?? $b->tanggal);
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $bookedDates->push($d->format('Y-m-d'));
            }
        }
        $bookedDates = $bookedDates->unique()->values()->toArray();

        return view('booking.create', compact('gedung', 'addOns', 'bookedDates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'gedung_id'      => 'required|exists:gedung,id',
            'tanggal'        => 'required|date|after_or_equal:today',
            'tanggal_selesai'=> 'required|date|after_or_equal:tanggal',
            'jam_mulai'      => 'required|date_format:H:i',
            'jam_selesai'    => 'required|date_format:H:i|after:jam_mulai',
            'payment_type'   => 'required|in:dp,lunas',
            'add_ons'        => 'nullable|array',
        ]);

        $gedung = Gedung::findOrFail($data['gedung_id']);

        if (!empty($data['add_ons'])) {
            $addOnIds = array_keys($data['add_ons']);
            $existingIds = AddOn::whereIn('id', $addOnIds)->pluck('id')->toArray();
            $invalid = array_diff($addOnIds, $existingIds);
            if (!empty($invalid)) {
                return back()->withErrors(['add_ons' => 'Add-on tidak valid.'])->withInput();
            }
        }

        $tanggalMulai = Carbon::parse($data['tanggal']);
        $tanggalSelesai = Carbon::parse($data['tanggal_selesai']);
        $jamMulai = $data['jam_mulai'];
        $jamSelesai = $data['jam_selesai'];
        $jumlahHari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;

        for ($day = $tanggalMulai->copy(); $day->lte($tanggalSelesai); $day->addDay()) {
            $dayStr = $day->format('Y-m-d');

            $bentrok = Booking::where('gedung_id', $gedung->id)
                ->where('status', '!=', 'cancelled')
                ->where(function ($q) use ($dayStr, $jamMulai, $jamSelesai) {
                    $q->where(function ($q2) use ($dayStr) {
                        $q2->where('tanggal', '<=', $dayStr)
                           ->where('tanggal_selesai', '>=', $dayStr);
                    })->orWhere(function ($q2) use ($dayStr) {
                        $q2->where('tanggal', '<=', $dayStr)
                           ->whereNull('tanggal_selesai')
                           ->where('tanggal', $dayStr);
                    });
                })
                ->where(function ($q) use ($jamMulai, $jamSelesai) {
                    $q->whereTime('jam_mulai', '<', $jamSelesai)
                      ->whereTime('jam_selesai', '>', $jamMulai);
                })
                ->exists();

            if ($bentrok) {
                $tanggalBentrok = Carbon::parse($dayStr)->locale('id')->isoFormat('D MMMM YYYY');
                return back()
                    ->withErrors(['jadwal' => "Gedung sudah dibooking pada {$tanggalBentrok} jam tersebut."])
                    ->with('error', "Gedung sudah dibooking pada {$tanggalBentrok} jam tersebut.")
                    ->withInput();
            }
        }

                if ((int) $jumlahHari === 1 && $gedung->harga_per_jam) {
            $jamMulaiDt = Carbon::parse($jamMulai);
            $jamSelesaiDt = Carbon::parse($jamSelesai);
            $jamPakai = (int) ceil($jamMulaiDt->diffInMinutes($jamSelesaiDt) / 60);
            $jamPakai = max($jamPakai, $gedung->minimum_jam ?? 2);
            $totalHarga = min($gedung->harga_per_jam * $jamPakai, $gedung->harga_sewa);
        } else {
            $totalHarga = $gedung->harga_sewa * $jumlahHari;
        }
        $addOns = collect();

        if (!empty($data['add_ons'])) {
            $addOns = AddOn::whereIn('id', array_keys($data['add_ons']))->get()->keyBy('id');
            foreach ($data['add_ons'] as $id => $qty) {
                if (isset($addOns[$id])) {
                    $qty = $addOns[$id]->type === 'flat' ? 1 : (int) $qty;
                    $totalHarga += $addOns[$id]->harga * $qty;
                }
            }
        }

        $booking = DB::transaction(function () use ($data, $gedung, $totalHarga, $addOns) {
            $booking = Booking::create([
                'booking_code'    => 'BKG-' . strtoupper(\Str::random(8)),
                'user_id'         => Auth::id(),
                'gedung_id'       => $data['gedung_id'],
                'tanggal'         => $data['tanggal'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'jam_mulai'       => $data['jam_mulai'],
                'jam_selesai'     => $data['jam_selesai'],
                'total_harga'     => $totalHarga,
                'batas_bayar_lunas' => $data['payment_type'] === 'lunas' ? now()->addHour() : null,
                'payment_type'    => $data['payment_type'],
                'payment_status'  => 'unpaid',
                'status'          => 'pending',
            ]);

            if (!empty($data['add_ons'])) {
                $syncData = [];
                foreach ($data['add_ons'] as $addOnId => $qty) {
                    $qty = isset($addOns[$addOnId]) && $addOns[$addOnId]->type === 'flat' ? 1 : (int) $qty;
                    $syncData[$addOnId] = ['quantity' => $qty];
                }
                $booking->addOns()->attach($syncData);
            }

            return $booking;
        });

        return redirect()->route('booking.show', $booking)
            ->with('success', 'Booking berhasil! Silakan lakukan pembayaran.');
    }

    public function paymentForm(Booking $booking): View
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->payment_status !== 'unpaid') {
            return redirect()->route('booking.show', $booking)
                ->with('info', 'Pembayaran sudah dikonfirmasi.');
        }

        $booking->load('gedung');

        return view('booking.payment', compact('booking'));
    }

    public function submitPayment(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->payment_status !== 'unpaid') {
            return back()->with('error', 'Pembayaran sudah dikonfirmasi.');
        }

        $data = $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = $request->file('payment_proof')->store('payment-proofs', 'public');

        $booking->update([
            'payment_proof'   => $path,
            'payment_status'  => 'waiting',
            'batas_bayar_lunas' => null,
        ]);

        return redirect()->route('booking.show', $booking)
            ->with('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.');
    }

    public function show(Booking $booking): View
    {
        if ($booking->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $booking->load(['gedung', 'addOns', 'user']);

        return view('booking.show', compact('booking'));
    }

    public function myBookings(): View
    {
        $bookings = Booking::with('gedung')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('booking.my-bookings', compact('bookings'));
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        if ($booking->user_id !== Auth::id() && !Auth::user()?->isAdmin()) {
            abort(403);
        }

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Booking ini sudah dibatalkan sebelumnya.');
        }

        $booking->update([
            'status'         => 'cancelled',
            'payment_status' => 'unpaid',
            'batas_bayar_lunas' => null,
        ]);

        return back()->with('success', 'Booking berhasil dibatalkan.');
    }
}
