import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:shimmer/shimmer.dart';
import '../models/booking.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';
import '../widgets/notification_bell.dart';
import 'package:intl/intl.dart';
import 'booking_detail_screen.dart';

class MyBookingsScreen extends StatefulWidget {
  const MyBookingsScreen({super.key});

  @override
  State<MyBookingsScreen> createState() => _MyBookingsScreenState();
}

class _MyBookingsScreenState extends State<MyBookingsScreen> {
  List<Booking> _list = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.get('/bookings', auth: true);
      _list = (res['data'] as List<dynamic>)
          .map((e) => Booking.fromJson(e as Map<String, dynamic>))
          .toList();
      setState(() => _loading = false);
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  String _fmt(int n) => n.toString().replaceAllMapped(
      RegExp(r'(\d)(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.');

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Booking Saya'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_rounded, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 8),
            child: NotificationBell(),
          ),
        ],
      ),
      body: _loading
          ? _buildShimmer()
          : _list.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(24),
                        decoration: BoxDecoration(
                          color: AppTheme.surface,
                          borderRadius: BorderRadius.circular(24),
                        ),
                        child: const Icon(Icons.event_busy_rounded,
                            size: 56, color: AppTheme.textMuted),
                      ),
                      const SizedBox(height: 16),
                      const Text('Belum ada booking',
                          style: TextStyle(
                              fontSize: 16, color: AppTheme.textSecondary)),
                      const SizedBox(height: 4),
                      const Text('Mulai booking gedung favorit Anda',
                          style: TextStyle(
                              fontSize: 13, color: AppTheme.textMuted)),
                    ],
                  ).animate().fadeIn(duration: 400.ms),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView.builder(
                    padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                    itemCount: _list.length,
                    itemBuilder: (_, i) {
                      final b = _list[i];
                      return _BookingCard(
                        booking: b,
                        index: i,
                        onTap: () => Navigator.push(
                          context,
                          PageRouteBuilder(
                            pageBuilder: (_, __, ___) =>
                                BookingDetailScreen(bookingId: b.id),
                            transitionsBuilder: (_, a, __, child) =>
                                SlideTransition(
                                    position: Tween(
                                      begin: const Offset(0.25, 0),
                                      end: Offset.zero,
                                    ).animate(a),
                                    child: child),
                            transitionDuration:
                                const Duration(milliseconds: 300),
                          ),
                        ).then((_) => _load()),
                        fmt: _fmt,
                      );
                    },
                  ),
                ),
    );
  }

  Widget _buildShimmer() {
    return Shimmer.fromColors(
      baseColor: Colors.grey.shade200,
      highlightColor: Colors.grey.shade100,
      child: ListView.builder(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
        itemCount: 4,
        itemBuilder: (_, i) => Container(
          margin: const EdgeInsets.only(bottom: 12),
          height: 150,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
          ),
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                const SizedBox(width: 10),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      height: 14,
                      width: 120,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(4),
                      ),
                    ),
                    const SizedBox(height: 6),
                    Container(
                      height: 10,
                      width: 80,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(4),
                      ),
                    ),
                  ],
                ),
              ]),
              const Divider(height: 20),
              Container(
                height: 12,
                width: 180,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(4),
                ),
              ),
              const SizedBox(height: 6),
              Container(
                height: 12,
                width: 140,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(4),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _BookingCard extends StatelessWidget {
  final Booking booking;
  final int index;
  final VoidCallback onTap;
  final String Function(int) fmt;
  const _BookingCard({required this.booking, required this.index, required this.onTap, required this.fmt});

  @override
  Widget build(BuildContext context) {
    final b = booking;
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        padding: AppTheme.cardPadding,
        decoration: AppTheme.premiumCard,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppTheme.primary.withValues(alpha: 0.06),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.meeting_room_rounded,
                      size: 20, color: AppTheme.gold),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(b.gedung?.nama ?? 'Gedung',
                          style: const TextStyle(
                              fontWeight: FontWeight.w700,
                              fontSize: 15,
                              color: AppTheme.textPrimary)),
                      const SizedBox(height: 2),
                      Text(b.bookingCode,
                          style: TextStyle(
                              fontSize: 11,
                              color: AppTheme.textMuted,
                              fontFamily: 'monospace',
                              letterSpacing: 0.5)),
                    ],
                  ),
                ),
                _StatusBadge(status: b.status, label: b.statusLabel),
              ],
            ),
            const Divider(height: 20),
            _infoRow(Icons.calendar_today_rounded, _dateStr(b)),
            const SizedBox(height: 4),
            _infoRow(Icons.access_time_rounded, '${b.jamMulai} - ${b.jamSelesai}'),
            const SizedBox(height: 4),
            Row(children: [
              Icon(Icons.payment_rounded,
                  size: 14, color: AppTheme.textMuted),
              const SizedBox(width: 6),
              Text(b.paymentLabel,
                  style: const TextStyle(
                      fontSize: 12, color: AppTheme.textSecondary)),
              const Spacer(),
              Text('Rp ${fmt(b.totalHarga.toInt())}',
                  style: AppTheme.priceStyle.copyWith(fontSize: 15)),
            ]),
            if (b.status == 'pending' && b.paymentStatus == 'unpaid')
              Container(
                margin: const EdgeInsets.only(top: 10),
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFF8E1),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(
                      color: const Color(0xFFF0E0A0).withValues(alpha: 0.5)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.info_outline,
                        size: 14, color: AppTheme.warning),
                    const SizedBox(width: 6),
                    const Text('Tap untuk bayar',
                        style: TextStyle(
                            fontSize: 12,
                            color: AppTheme.warning,
                            fontWeight: FontWeight.w500)),
                  ],
                ),
              ),
          ],
        ),
      ),
    ).animate().fadeIn(delay: (50 * index).ms, duration: 400.ms, curve: Curves.easeOut);
  }

  Widget _infoRow(IconData icon, String text) {
    return Row(children: [
      Icon(icon, size: 14, color: AppTheme.textMuted),
      const SizedBox(width: 6),
      Text(text,
          style: const TextStyle(
              fontSize: 12, color: AppTheme.textSecondary)),
    ]);
  }

  String _dateStr(Booking b) {
    final s = DateFormat('dd MMM yyyy', 'id').format(DateTime.parse(b.tanggal));
    if (b.tanggalSelesai == null) return s;
    final e = DateFormat('dd MMM yyyy', 'id').format(DateTime.parse(b.tanggalSelesai!));
    return '$s - $e';
  }
}

class _StatusBadge extends StatelessWidget {
  final String status;
  final String label;
  const _StatusBadge({required this.status, required this.label});

  @override
  Widget build(BuildContext context) {
    final box = AppTheme.statusBadge(status);
    final textStyle = AppTheme.statusText(status);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: box,
      child: Text(label, style: textStyle),
    );
  }
}
