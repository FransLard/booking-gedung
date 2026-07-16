<?php

namespace App\Exports;

use App\Models\Booking;
use App\Models\Gedung;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialReportExport
{
    public function download(?string $month = null, $gedungId = null): StreamedResponse
    {
        $month = $month ?: now()->format('Y-m');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Keuangan');

        $row = 1;
        $title = 'LAPORAN KEUANGAN BOOKING GEDUNG';
        if ($gedungId) {
            $g = Gedung::find($gedungId);
            $title .= ' - ' . ($g ? $g->nama : '');
        }
        $sheet->setCellValue("A{$row}", $title);
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        $sheet->setCellValue("A{$row}", 'Periode: ' . \Carbon\Carbon::parse($month . '-01')->format('F Y'));
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row += 2;

        $startOfMonth = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $gedungs = $gedungId ? Gedung::where('id', $gedungId)->get() : Gedung::all();
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '262626']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $borderStyle = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

        $headers = ['Gedung', 'Total Booking', 'Confirmed', 'Pending', 'Cancelled', 'Pendapatan (Profit)', 'Kerugian (Loss)', 'Net Profit / Loss'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}{$row}", $h);
        }
        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($headerStyle);
        $row++;

        $totalBookings = 0;
        $totalConfirmed = 0;
        $totalPending = 0;
        $totalCancelled = 0;
        $totalProfit = 0;
        $totalLoss = 0;

        foreach ($gedungs as $gedung) {
            $bookings = Booking::where('gedung_id', $gedung->id)
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->get();

            $count = $bookings->count();
            $confirmed = $bookings->where('status', 'confirmed')->count();
            $pending = $bookings->where('status', 'pending')->count();
            $cancelled = $bookings->where('status', 'cancelled')->count();
            $profit = $bookings->whereIn('status', ['confirmed', 'pending'])
                ->whereIn('payment_status', ['paid_dp', 'paid_lunas'])
                ->sum('total_harga');
            $loss = $bookings->where('status', 'cancelled')->sum('total_harga');
            $net = $profit - $loss;

            $totalBookings += $count;
            $totalConfirmed += $confirmed;
            $totalPending += $pending;
            $totalCancelled += $cancelled;
            $totalProfit += $profit;
            $totalLoss += $loss;

            $vals = [$gedung->nama, $count, $confirmed, $pending, $cancelled, $profit, $loss, $net];
            foreach ($vals as $i => $v) {
                $col = chr(65 + $i);
                $sheet->setCellValue("{$col}{$row}", $v);
                if (in_array($i, [5, 6, 7])) {
                    $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0');
                }
            }
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($borderStyle);
            $row++;
        }

        if (!$gedungId) {
            $sheet->setCellValue("A{$row}", 'TOTAL');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $totals = ['', $totalBookings, $totalConfirmed, $totalPending, $totalCancelled, $totalProfit, $totalLoss, $totalProfit - $totalLoss];
            foreach ($totals as $i => $v) {
                $col = chr(65 + $i);
                if ($v !== '') {
                    $sheet->setCellValue("{$col}{$row}", $v);
                    if (in_array($i, [5, 6, 7])) {
                        $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    }
                }
            }
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($borderStyle);
            $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
            $row += 2;
        }

        $row++;
        $sheet->setCellValue("A{$row}", 'RINCIAN PER TANGGAL');
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $row++;

        $detailHeaders = ['Tanggal', 'Kode Booking', 'User', 'Gedung', 'Status', 'Pembayaran', 'Total', 'Ket'];
        foreach ($detailHeaders as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}{$row}", $h);
        }
        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($headerStyle);
        $row++;

        $query = Booking::with(['user', 'gedung'])
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->orderBy('tanggal');
        if ($gedungId) {
            $query->where('gedung_id', $gedungId);
        }
        $details = $query->get();

        foreach ($details as $b) {
            $vals = [
                $b->tanggal->format('d-m-Y'),
                $b->booking_code,
                $b->user->name,
                $b->gedung->nama,
                ucfirst($b->status),
                $b->payment_status_label,
                $b->total_harga,
                $b->status === 'confirmed' ? 'Profit' : ($b->status === 'cancelled' ? 'Loss' : 'Pending'),
            ];
            foreach ($vals as $i => $v) {
                $col = chr(65 + $i);
                $sheet->setCellValue("{$col}{$row}", $v);
                if ($i === 6) {
                    $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0');
                }
            }
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($borderStyle);
            $row++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan-keuangan-' . $month . ($gedungId ? '-gedung-' . $gedungId : '') . '.xlsx';

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
