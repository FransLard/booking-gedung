<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use App\Models\Booking;
use App\Models\Gedung;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'gedung_id'      => 'required|exists:gedung,id',
            'tanggal'        => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal',
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
                return response()->json(['message' => 'Add-on tidak valid: ' . implode(', ', $invalid)], 422);
            }
        }

        $tanggalMulai = Carbon::parse($data['tanggal']);
        $tanggalSelesai = Carbon::parse($data['tanggal_selesai']);
        $jamMulai = $data['jam_mulai'];
        $jamSelesai = $data['jam_selesai'];
        $jumlahHari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;

        try {
            $booking = DB::transaction(function () use ($data, $gedung, $tanggalMulai, $tanggalSelesai, $jamMulai, $jamSelesai, $jumlahHari) {

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
                        ->lockForUpdate()
                        ->exists();

                    if ($bentrok) {
                        throw new \Exception('Gedung sudah dibooking pada tanggal dan jam tersebut.');
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

                if (!empty($data['add_ons'])) {
                    $addOns = AddOn::whereIn('id', array_keys($data['add_ons']))->get()->keyBy('id');
                    foreach ($data['add_ons'] as $id => $qty) {
                        if (isset($addOns[$id])) {
                            $qty = $addOns[$id]->type === 'flat' ? 1 : (int) $qty;
                            $totalHarga += $addOns[$id]->harga * $qty;
                        }
                    }
                }

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

            $booking->load(['gedung', 'addOns']);

            return response()->json([
                'message' => 'Booking berhasil! Silakan lakukan pembayaran.',
                'booking' => $booking,
            ], 201);

        } catch (\Exception $e) {
            if ($e->getMessage() === 'Gedung sudah dibooking pada tanggal dan jam tersebut.') {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 409);
            }
            throw $e;
        }
    }

    public function myBookings(Request $request): JsonResponse
    {
        $bookings = Booking::with(['gedung', 'addOns'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return response()->json($bookings);
    }

    public function show(Booking $booking): JsonResponse
    {
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $booking->load(['gedung', 'addOns', 'user']);

        return response()->json($booking);
    }

    public function submitPayment(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($booking->payment_status !== 'unpaid') {
            return response()->json(['message' => 'Pembayaran sudah dikonfirmasi.'], 400);
        }

        $data = $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = $request->file('payment_proof')->store('payment-proofs', 'public');

        $booking->update([
            'payment_proof'  => $path,
            'payment_status' => 'waiting',
            'batas_bayar_lunas' => null,
        ]);

        return response()->json([
            'message' => 'Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.',
        ]);
    }

    public function cancel(Booking $booking): JsonResponse
    {
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Booking ini sudah dibatalkan.'], 400);
        }

        $booking->update([
            'status'         => 'cancelled',
            'payment_status' => 'unpaid',
            'batas_bayar_lunas' => null,
        ]);

        return response()->json(['message' => 'Booking berhasil dibatalkan.']);
    }

    public function checkAvailableDates(Gedung $gedung): JsonResponse
    {
        $bookings = Booking::where('gedung_id', $gedung->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('tanggal_selesai')
                       ->where('tanggal_selesai', '>=', now()->startOfDay());
                })->orWhere(function ($q2) {
                    $q2->whereNull('tanggal_selesai')
                       ->where('tanggal', '>=', now()->startOfDay());
                });
            })
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

        return response()->json($bookedDates);
    }
}
