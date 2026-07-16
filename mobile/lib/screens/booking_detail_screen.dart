import 'dart:async';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import '../models/booking.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';

class BookingDetailScreen extends StatefulWidget {
  final int bookingId;
  const BookingDetailScreen({super.key, required this.bookingId});

  @override
  State<BookingDetailScreen> createState() => _BookingDetailScreenState();
}

class _BookingDetailScreenState extends State<BookingDetailScreen> {
  Booking? _b;
  bool _loading = true;
  final _picker = ImagePicker();

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.get('/booking/${widget.bookingId}', auth: true);
      setState(() { _b = Booking.fromJson(res as Map<String, dynamic>); _loading = false; });
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  void _showPicker() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 40, height: 4,
                decoration: BoxDecoration(
                  color: AppTheme.borderLight,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(height: 20),
              const Text('Pilih Sumber Foto',
                  style: TextStyle(
                      fontWeight: FontWeight.w700, fontSize: 17,
                      color: AppTheme.textPrimary, letterSpacing: -0.3)),
              const SizedBox(height: 20),
              ListTile(
                leading: Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppTheme.primary.withValues(alpha: 0.06),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.camera_alt_rounded,
                      color: AppTheme.gold, size: 22),
                ),
                title: const Text('Kamera',
                    style: TextStyle(fontWeight: FontWeight.w600)),
                subtitle: const Text('Ambil foto baru'),
                onTap: () { Navigator.pop(ctx); _upload(ImageSource.camera); },
              ),
              ListTile(
                leading: Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppTheme.primary.withValues(alpha: 0.06),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.photo_library_rounded,
                      color: AppTheme.gold, size: 22),
                ),
                title: const Text('Galeri',
                    style: TextStyle(fontWeight: FontWeight.w600)),
                subtitle: const Text('Pilih dari galeri'),
                onTap: () { Navigator.pop(ctx); _upload(ImageSource.gallery); },
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _upload(ImageSource src) async {
    try {
      final file = await _picker.pickImage(source: src, imageQuality: 70);
      if (file == null) return;
      await ApiService.postMultipart(
        '/booking/${widget.bookingId}/payment',
        {},
        'payment_proof',
        File(file.path),
        auth: true,
      );
      _load();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Bukti pembayaran berhasil diupload'),
          backgroundColor: AppTheme.success,
          behavior: SnackBarBehavior.floating));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(e.toString().contains('PlatformException')
              ? 'Gagal membuka kamera/galeri'
              : 'Gagal upload'),
          backgroundColor: AppTheme.error,
          behavior: SnackBarBehavior.floating));
    }
  }

  Future<void> _cancel() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Batalkan Booking',
            style: TextStyle(fontWeight: FontWeight.w700)),
        content: const Text('Yakin ingin membatalkan booking ini?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Tidak'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: Text('Ya, Batalkan',
                style: TextStyle(color: AppTheme.error)),
          ),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await ApiService.patch('/booking/${widget.bookingId}/cancel', auth: true);
      _load();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Booking dibatalkan'),
          backgroundColor: AppTheme.success,
          behavior: SnackBarBehavior.floating));
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(e.message),
          backgroundColor: AppTheme.error,
          behavior: SnackBarBehavior.floating));
    }
  }

  String _fmt(int n) => n.toString().replaceAllMapped(
      RegExp(r'(\d)(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.');

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Detail Booking'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_rounded, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _b == null
              ? const Center(child: Text('Booking tidak ditemukan'))
              : RefreshIndicator(
                  onRefresh: _load,
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      children: [
                        _infoCard(),
                        const SizedBox(height: 12),
                        _paymentCard(),
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _infoCard() {
    final b = _b!;
    return Container(
      width: double.infinity,
      padding: AppTheme.cardPadding,
      decoration: AppTheme.premiumCard,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppTheme.primary.withValues(alpha: 0.06),
                borderRadius: BorderRadius.circular(14),
              ),
              child: const Icon(Icons.meeting_room_rounded,
                  color: AppTheme.gold, size: 22),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(b.gedung?.nama ?? 'Gedung',
                      style: const TextStyle(
                          fontWeight: FontWeight.w700,
                          fontSize: 16,
                          color: AppTheme.textPrimary)),
                  const SizedBox(height: 2),
                  Text(b.bookingCode,
                      style: TextStyle(
                          fontSize: 12,
                          color: AppTheme.textMuted,
                          fontFamily: 'monospace')),
                ],
              ),
            ),
            _Badge(
              label: b.statusLabel,
              color: b.status == 'confirmed'
                  ? AppTheme.success
                  : b.status == 'cancelled'
                      ? AppTheme.error
                      : AppTheme.warning,
            ),
          ]),
          const Divider(height: 24),
          _detailRow('Tanggal', _dateStr(b)),
          const SizedBox(height: 6),
          _detailRow('Jam', '${b.jamMulai} - ${b.jamSelesai}'),
          const SizedBox(height: 6),
          _detailRow('Status', b.statusLabel),
        ],
      ),
    ).animate().fadeIn(duration: 400.ms).slideY(begin: 0.05, end: 0, duration: 400.ms);
  }

  Widget _detailRow(String label, String value) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 80,
          child: Text(label,
              style: const TextStyle(
                  color: AppTheme.textSecondary, fontSize: 13)),
        ),
        Expanded(
          child: Text(value,
              style: const TextStyle(
                  fontWeight: FontWeight.w600,
                  fontSize: 13,
                  color: AppTheme.textPrimary)),
        ),
      ],
    );
  }

  String _dateStr(Booking b) {
    final s = DateFormat('dd MMM yyyy', 'id').format(DateTime.parse(b.tanggal));
    if (b.tanggalSelesai == null) return s;
    final e = DateFormat('dd MMM yyyy', 'id').format(DateTime.parse(b.tanggalSelesai!));
    return '$s - $e';
  }

  Widget _paymentCard() {
    final b = _b!;
    final amount = b.paymentType == 'dp'
        ? (b.totalHarga * 0.5).toInt()
        : b.totalHarga.toInt();

    return Container(
      width: double.infinity,
      padding: AppTheme.cardPadding,
      decoration: AppTheme.premiumCard,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFE8F5E9),
                borderRadius: BorderRadius.circular(14),
              ),
              child: const Icon(Icons.payment_rounded,
                  color: AppTheme.success, size: 22),
            ),
            const SizedBox(width: 12),
            const Text('Pembayaran',
                style: TextStyle(
                    fontWeight: FontWeight.w700,
                    fontSize: 16,
                    color: AppTheme.textPrimary)),
          ]),
          const Divider(height: 20),
          _detailRow('Total Harga', 'Rp ${_fmt(b.totalHarga.toInt())}'),
          const SizedBox(height: 6),
          _detailRow('Metode',
              b.paymentType == 'dp' ? 'DP 50%' : 'Lunas'),
          const SizedBox(height: 12),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: AppTheme.surface,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppTheme.borderLight),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(b.paymentType == 'dp' ? 'DP Dibayar:' : 'Total Dibayar:',
                    style: const TextStyle(
                        fontWeight: FontWeight.w500,
                        color: AppTheme.textSecondary,
                        fontSize: 13)),
                Text('Rp ${_fmt(amount)}',
                    style: AppTheme.priceStyle.copyWith(fontSize: 18)),
              ],
            ),
          ),
          const SizedBox(height: 16),
          if (b.paymentType == 'dp') ...[
            _DpTimer(booking: b),
            const SizedBox(height: 16),
          ],
          if (b.paymentType == 'lunas' && b.paymentStatus == 'unpaid') ...[
            _LunasTimer(booking: b),
            const SizedBox(height: 16),
          ],
          _buildStatus(b, amount),
        ],
      ),
    ).animate().fadeIn(duration: 400.ms, delay: 150.ms).slideY(begin: 0.05, end: 0, duration: 400.ms);
  }

  Widget _buildStatus(Booking b, int amount) {
    if (b.paymentStatus == 'unpaid') {
      return Column(
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFFFF8E1),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                  color: AppTheme.goldLight.withValues(alpha: 0.5)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Instruksi Pembayaran',
                    style: TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 14,
                        color: AppTheme.textPrimary)),
                const SizedBox(height: 12),
                _bankRow('Bank', 'Bank Mandiri'),
                _bankRow('No. Rekening', '123-00-456789-0'),
                _bankRow('Atas Nama', 'PT Booking Gedung'),
                _bankRow('Nominal', 'Rp ${_fmt(amount)}', bold: true),
                const SizedBox(height: 12),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFF3CD),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Penting:',
                          style: TextStyle(
                              fontWeight: FontWeight.w600, fontSize: 12)),
                      SizedBox(height: 4),
                      Text('• Transfer sesuai nominal',
                          style: TextStyle(fontSize: 11)),
                      Text('• Simpan & upload bukti transfer',
                          style: TextStyle(fontSize: 11)),
                      Text('• Admin verifikasi maks 1x24 jam',
                          style: TextStyle(fontSize: 11)),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: _showPicker,
              icon: const Icon(Icons.add_a_photo_rounded, size: 20),
              label: const Text('Upload Bukti Bayar'),
            ),
          ),
          const SizedBox(height: 8),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: _cancel,
              icon: const Icon(Icons.cancel_outlined, size: 18),
              label: const Text('Batalkan Booking'),
              style: OutlinedButton.styleFrom(
                foregroundColor: AppTheme.error,
                side: const BorderSide(color: AppTheme.error),
              ),
            ),
          ),
        ],
      );
    }

    if (b.paymentStatus == 'waiting') {
      return Container(
        width: double.infinity,
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: const Color(0xFFE3F2FD),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: const Color(0xFF90CAF9)),
        ),
        child: Row(children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.5),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.access_time_rounded,
                color: Color(0xFF1565C0), size: 28),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Menunggu Verifikasi Admin',
                    style: TextStyle(
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1565C0))),
                const SizedBox(height: 2),
                Text('Bukti pembayaran sedang diperiksa. Maks 1x24 jam.',
                    style: TextStyle(
                        fontSize: 12, color: Colors.blue.shade600)),
              ],
            ),
          ),
        ]),
      );
    }

    if (b.paymentStatus == 'paid_dp') {
      final sisa = (b.totalHarga * 0.5).toInt();
      return Column(
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFE8F5E9),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFA5D6A7)),
            ),
            child: Row(children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.5),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.check_circle_rounded,
                    color: AppTheme.success, size: 28),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('DP 50% Terverifikasi',
                        style: TextStyle(
                            fontWeight: FontWeight.w700,
                            color: AppTheme.success)),
                    const SizedBox(height: 2),
                    Text('Booking telah dikonfirmasi.',
                        style: TextStyle(
                            fontSize: 12, color: Colors.green.shade600)),
                  ],
                ),
              ),
            ]),
          ),
          const SizedBox(height: 12),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFFFF8E1),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                  color: AppTheme.goldLight.withValues(alpha: 0.5)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(Icons.info_outline,
                        size: 16, color: AppTheme.warning),
                    const SizedBox(width: 6),
                    const Text('Bayar Sisa Pembayaran',
                        style: TextStyle(
                            fontWeight: FontWeight.w700,
                            fontSize: 14,
                            color: AppTheme.textPrimary)),
                  ],
                ),
                const SizedBox(height: 12),
                _bankRow('Bank', 'Bank Mandiri'),
                _bankRow('No. Rekening', '123-00-456789-0'),
                _bankRow('Atas Nama', 'PT Booking Gedung'),
                _bankRow('Sisa Bayar', 'Rp ${_fmt(sisa)}', bold: true),
                const SizedBox(height: 12),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFF3CD),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Catatan:',
                          style: TextStyle(
                              fontWeight: FontWeight.w600, fontSize: 12)),
                      SizedBox(height: 4),
                      Text('• Transfer sisa pembayaran ke rekening di atas',
                          style: TextStyle(fontSize: 11)),
                      Text('• Hubungi admin jika ada kendala',
                          style: TextStyle(fontSize: 11)),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      );
    }

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFE8F5E9),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFA5D6A7)),
      ),
      child: Row(children: [
        Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            color: Colors.white.withValues(alpha: 0.5),
            borderRadius: BorderRadius.circular(12),
          ),
          child: const Icon(Icons.check_circle_rounded,
              color: AppTheme.success, size: 28),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                b.paymentStatus == 'paid_lunas'
                    ? 'Pembayaran Lunas'
                    : 'DP 50% Terverifikasi',
                style: const TextStyle(
                    fontWeight: FontWeight.w700,
                    color: AppTheme.success),
              ),
              const SizedBox(height: 2),
              Text('Booking telah dikonfirmasi.',
                  style: TextStyle(
                      fontSize: 12, color: Colors.green.shade600)),
            ],
              ),
            ),
          ]),
          );
    }

  Widget _bankRow(String label, String value, {bool bold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label,
              style: const TextStyle(
                  color: AppTheme.textSecondary, fontSize: 12)),
          Text(value,
              style: TextStyle(
                  fontWeight: bold ? FontWeight.w700 : FontWeight.w500,
                  fontSize: 12,
                  color: bold ? AppTheme.gold : AppTheme.textPrimary,
                  fontFamily: bold ? 'monospace' : null)),
        ],
      ),
    );
  }
}

class _DpTimer extends StatefulWidget {
  final Booking booking;
  const _DpTimer({required this.booking});

  @override
  State<_DpTimer> createState() => _DpTimerState();
}

class _DpTimerState extends State<_DpTimer> {
  Timer? _timer;
  Duration _remaining = Duration.zero;

  @override
  void initState() {
    super.initState();
    _calculate();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) => _calculate());
  }

  void _calculate() {
    final tanggal =
        DateTime.tryParse(widget.booking.tanggal) ?? DateTime.now();
    final deadline = DateTime(tanggal.year, tanggal.month, tanggal.day - 1, 23, 59, 59);
    final now = DateTime.now();
    final remaining = deadline.difference(now);
    if (remaining.isNegative) {
      _timer?.cancel();
      setState(() => _remaining = Duration.zero);
    } else {
      setState(() => _remaining = remaining);
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (_remaining == Duration.zero) return const SizedBox.shrink();

    final days = _remaining.inDays;
    final hours = _remaining.inHours.remainder(24);
    final minutes = _remaining.inMinutes.remainder(60);
    final seconds = _remaining.inSeconds.remainder(60);
    final isUrgent = days == 0 && hours < 1;

    final bgColor = isUrgent ? Colors.red.shade50 : const Color(0xFFFFF8E1);
    final borderColor = isUrgent ? Colors.red.shade200 : const Color(0xFFF0E0A0);
    final textColor = isUrgent ? Colors.red.shade700 : AppTheme.warning;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: borderColor.withValues(alpha: 0.5)),
      ),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.timer_outlined, size: 16, color: textColor),
              const SizedBox(width: 6),
              Text(
                isUrgent ? 'Segera bayar DP!' : 'Batas pembayaran DP',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: textColor,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            '${days.toString().padLeft(2, '0')}:${hours.toString().padLeft(2, '0')}:${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}',
            style: TextStyle(
              fontSize: 28,
              fontWeight: FontWeight.w700,
              fontFamily: 'monospace',
              letterSpacing: 2,
              color: textColor,
            ),
          ),
        ],
      ),
    );
  }
}

class _LunasTimer extends StatefulWidget {
  final Booking booking;
  const _LunasTimer({required this.booking});

  @override
  State<_LunasTimer> createState() => _LunasTimerState();
}

class _LunasTimerState extends State<_LunasTimer> {
  Timer? _timer;
  Duration _remaining = Duration.zero;

  @override
  void initState() {
    super.initState();
    _calculate();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) => _calculate());
  }

  void _calculate() {
    if (widget.booking.batasBayarLunas == null) {
      _timer?.cancel();
      setState(() => _remaining = Duration.zero);
      return;
    }
    final deadline = DateTime.tryParse(widget.booking.batasBayarLunas!) ?? DateTime.now();
    final now = DateTime.now();
    final remaining = deadline.difference(now);
    if (remaining.isNegative) {
      _timer?.cancel();
      setState(() => _remaining = Duration.zero);
    } else {
      setState(() => _remaining = remaining);
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (_remaining == Duration.zero) return const SizedBox.shrink();

    final hours = _remaining.inHours;
    final minutes = _remaining.inMinutes.remainder(60);
    final seconds = _remaining.inSeconds.remainder(60);
    final isUrgent = hours < 1;

    final bgColor = isUrgent ? Colors.red.shade50 : const Color(0xFFFFF8E1);
    final borderColor = isUrgent ? Colors.red.shade200 : const Color(0xFFF0E0A0);
    final textColor = isUrgent ? Colors.red.shade700 : AppTheme.warning;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: borderColor.withValues(alpha: 0.5)),
      ),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.timer_outlined, size: 16, color: textColor),
              const SizedBox(width: 6),
              Text(
                isUrgent ? 'Segera bayar!' : 'Batas pembayaran Lunas',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: textColor,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            '${hours.toString().padLeft(2, '0')}:${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}',
            style: TextStyle(
              fontSize: 28,
              fontWeight: FontWeight.w700,
              fontFamily: 'monospace',
              letterSpacing: 2,
              color: textColor,
            ),
          ),
        ],
      ),
    );
  }
}

class _Badge extends StatelessWidget {
  final String label;
  final Color color;
  const _Badge({required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Text(label,
          style: TextStyle(
              color: color, fontSize: 11, fontWeight: FontWeight.w600)),
    );
  }
}
