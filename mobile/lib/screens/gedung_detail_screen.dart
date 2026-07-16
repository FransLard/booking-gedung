import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../models/gedung.dart';
import '../models/add_on.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../widgets/network_image_loader.dart';
import '../widgets/booking_calendar.dart';
import '../theme/app_theme.dart';
import 'login_screen.dart';
import 'my_bookings_screen.dart';

class GedungDetailScreen extends StatefulWidget {
  final Gedung gedung;
  const GedungDetailScreen({super.key, required this.gedung});

  @override
  State<GedungDetailScreen> createState() => _GedungDetailScreenState();
}

class _GedungDetailScreenState extends State<GedungDetailScreen> {
  List<AddOn> _addOns = [];
  bool _loadingAddOns = true;
  final Map<int, int> _selectedAddOns = {};

  DateTimeRange? _dateRange;
  TimeOfDay? _startTime;
  TimeOfDay? _endTime;
  String _paymentType = 'dp';
  bool _submitting = false;
  Set<DateTime> _bookedDates = {};
  final PageController _imageController = PageController();
  int _currentImageIndex = 0;

  List<String> get _images {
    final list = <String>[];
    if (widget.gedung.gambar != null) list.add(widget.gedung.gambar!);
    if (widget.gedung.gambarDalam != null) list.add(widget.gedung.gambarDalam!);
    for (final img in widget.gedung.images) {
      if (img.gambar != null && !list.contains(img.gambar)) {
        list.add(img.gambar!);
      }
    }
    return list;
  }

  @override
  void initState() {
    super.initState();
    _loadAddOns();
  }

  @override
  void dispose() {
    _imageController.dispose();
    super.dispose();
  }

  Future<void> _loadAddOns() async {
    try {
      final res = await ApiService.get('/gedung/${widget.gedung.id}');
      final list = (res['add_ons'] as List<dynamic>)
          .map((e) => AddOn.fromJson(e as Map<String, dynamic>))
          .toList();
      setState(() { _addOns = list; _loadingAddOns = false; });
    } catch (_) {
      setState(() => _loadingAddOns = false);
    }
  }

  int get _total {
    int t;
    if (_dateRange != null && _startTime != null && _endTime != null) {
      final hari = _dateRange!.duration.inDays + 1;
      if (hari == 1 && widget.gedung.hargaPerJam != null && widget.gedung.hargaPerJam! > 0) {
        final jamMulai = _startTime!.hour + _startTime!.minute / 60;
        final jamSelesai = _endTime!.hour + _endTime!.minute / 60;
        var jamPakai = (jamSelesai - jamMulai).ceil();
        jamPakai = jamPakai.clamp(widget.gedung.minimumJam, 24);
        final byJam = (widget.gedung.hargaPerJam! * jamPakai).toInt();
        t = byJam.clamp(0, widget.gedung.hargaSewa.toInt());
      } else {
        t = widget.gedung.hargaSewa.toInt() * hari;
      }
    } else {
      t = widget.gedung.hargaSewa.toInt();
    }
    _selectedAddOns.forEach((id, qty) {
      t += (_addOns.where((a) => a.id == id).firstOrNull?.harga.toInt() ?? 0) * qty;
    });
    return t;
  }

  Future<void> _pickDate() async {
    try {
      final res = await ApiService.get('/gedung/${widget.gedung.id}/available-dates');
      final dates = (res as List<dynamic>).map((e) {
        final d = DateTime.parse(e as String);
        return DateTime(d.year, d.month, d.day);
      }).toSet();
      setState(() => _bookedDates = dates);
    } catch (_) {}

    final range = await showModalBottomSheet<DateTimeRange>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => BookingCalendarSheet(
        bookedDates: _bookedDates,
        initial: _dateRange,
      ),
    );
    if (range != null) setState(() => _dateRange = range);
  }

  Future<void> _pickTime(bool start) async {
    final result = await _showTimeSlotPicker(
      context: context,
      title: start ? 'Pilih Jam Mulai' : 'Pilih Jam Selesai',
      initial: start
          ? (_startTime ?? const TimeOfDay(hour: 8, minute: 0))
          : (_endTime ?? const TimeOfDay(hour: 17, minute: 0)),
    );
    if (result != null) {
      setState(() => start ? _startTime = result : _endTime = result);
    }
  }

  Future<TimeOfDay?> _showTimeSlotPicker({
    required BuildContext context,
    required String title,
    TimeOfDay? initial,
  }) async {
    final slots = <TimeOfDay>[];
    for (int h = 8; h <= 21; h++) {
      slots.add(TimeOfDay(hour: h, minute: 0));
      if (h < 21) slots.add(TimeOfDay(hour: h, minute: 30));
    }

    return showModalBottomSheet<TimeOfDay>(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 24, 16, 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title,
                  style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                      color: AppTheme.textPrimary,
                      letterSpacing: -0.3)),
              const SizedBox(height: 16),
              Container(
                constraints: const BoxConstraints(maxHeight: 260),
                decoration: BoxDecoration(
                  border: Border.all(color: AppTheme.borderLight),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: ListView.separated(
                  shrinkWrap: true,
                  padding: const EdgeInsets.symmetric(vertical: 6),
                  itemCount: slots.length,
                  separatorBuilder: (_, __) =>
                      Divider(height: 1, color: AppTheme.borderLight),
                  itemBuilder: (_, i) {
                    final slot = slots[i];
                    final isSelected = slot.hour == initial?.hour &&
                        slot.minute == initial?.minute;
                    final label =
                        '${slot.hour.toString().padLeft(2, '0')}:${slot.minute.toString().padLeft(2, '0')}';
                    return InkWell(
                      onTap: () => Navigator.pop(ctx, slot),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 16, vertical: 14),
                        color: isSelected
                            ? AppTheme.primary.withValues(alpha: 0.06)
                            : null,
                        child: Row(
                          children: [
                            Icon(Icons.schedule_rounded,
                                size: 18,
                                color: isSelected
                                    ? AppTheme.gold
                                    : AppTheme.textMuted),
                            const SizedBox(width: 12),
                            Text(label,
                                style: TextStyle(
                                  fontSize: 15,
                                  fontWeight: isSelected
                                      ? FontWeight.w700
                                      : FontWeight.w500,
                                  color: isSelected
                                      ? AppTheme.primary
                                      : AppTheme.textPrimary,
                                )),
                            const Spacer(),
                            if (isSelected)
                              const Icon(Icons.check_circle_rounded,
                                  size: 20, color: AppTheme.gold),
                          ],
                        ),
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _submit() async {
    final auth = context.read<AuthProvider>();
    if (!auth.isLoggedIn) {
      Navigator.push(context, _slideRight(const LoginScreen()));
      return;
    }
    if (_dateRange == null || _startTime == null || _endTime == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Lengkapi semua data booking'),
          backgroundColor: AppTheme.warning));
      return;
    }
    setState(() => _submitting = true);
    try {
      final addOnsPayload = <String, dynamic>{};
      _selectedAddOns.forEach((id, qty) {
        addOnsPayload[id.toString()] = qty;
      });
      await ApiService.post('/booking', {
        'gedung_id': widget.gedung.id.toString(),
        'tanggal': DateFormat('yyyy-MM-dd').format(_dateRange!.start),
        'tanggal_selesai': DateFormat('yyyy-MM-dd').format(_dateRange!.end),
        'jam_mulai': '${_startTime!.hour.toString().padLeft(2, '0')}:${_startTime!.minute.toString().padLeft(2, '0')}',
        'jam_selesai': '${_endTime!.hour.toString().padLeft(2, '0')}:${_endTime!.minute.toString().padLeft(2, '0')}',
        'payment_type': _paymentType,
        'add_ons': addOnsPayload,
      }, auth: true);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Booking berhasil!'),
          backgroundColor: AppTheme.success,
          behavior: SnackBarBehavior.floating));
      Navigator.push(context, _slideRight(const MyBookingsScreen()));
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(e.message),
          backgroundColor: AppTheme.error,
          behavior: SnackBarBehavior.floating));
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Gagal booking. Coba lagi.'),
          backgroundColor: AppTheme.error,
          behavior: SnackBarBehavior.floating));
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  String _fmt(int n) => n.toString().replaceAllMapped(
      RegExp(r'(\d)(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.');

  Route _slideRight(Widget page) => PageRouteBuilder(
        pageBuilder: (_, __, ___) => page,
        transitionsBuilder: (_, a, __, child) =>
            SlideTransition(position: Tween(begin: const Offset(0.25, 0), end: Offset.zero).animate(a), child: child),
        transitionDuration: const Duration(milliseconds: 300),
      );

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            expandedHeight: 250,
            pinned: true,
            backgroundColor: AppTheme.primary,
            foregroundColor: Colors.white,
            leading: IconButton(
              icon: const Icon(Icons.arrow_back_ios_rounded, size: 20),
              onPressed: () => Navigator.pop(context),
            ),
            flexibleSpace: FlexibleSpaceBar(
              background: _buildImageHeader(),
            ),
            bottom: PreferredSize(
              preferredSize: const Size.fromHeight(0),
              child: Container(
                height: 20,
                decoration: const BoxDecoration(
                  color: AppTheme.surface,
                  borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
                ),
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildTitleSection(),
                  const SizedBox(height: 20),
                  _buildSection('Pilih Tanggal & Waktu', Icons.date_range_rounded, [
                    _SelectionCard(
                      icon: Icons.date_range_rounded,
                      label: 'Tanggal Sewa',
                      value: _dateRange != null
                          ? '${DateFormat('dd MMM', 'id').format(_dateRange!.start)} - ${DateFormat('dd MMM yyyy', 'id').format(_dateRange!.end)}'
                          : 'Pilih tanggal',
                      onTap: _pickDate,
                    ).animate().fadeIn(duration: 300.ms, delay: 100.ms).slideX(begin: 0.05, end: 0, duration: 300.ms),
                    const SizedBox(height: 8),
                    Row(children: [
                      Expanded(
                        child: _SelectionCard(
                          icon: Icons.schedule_rounded,
                          label: 'Jam Mulai',
                          value: _startTime?.format(context) ?? 'Pilih',
                          onTap: () => _pickTime(true),
                        ).animate().fadeIn(duration: 300.ms, delay: 200.ms).slideX(begin: 0.05, end: 0, duration: 300.ms),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: _SelectionCard(
                          icon: Icons.schedule_rounded,
                          label: 'Jam Selesai',
                          value: _endTime?.format(context) ?? 'Pilih',
                          onTap: () => _pickTime(false),
                        ).animate().fadeIn(duration: 300.ms, delay: 300.ms).slideX(begin: 0.05, end: 0, duration: 300.ms),
                      ),
                    ]),
                  ]),
                  const SizedBox(height: 24),
                  _buildSection('Layanan Tambahan', Icons.extension_rounded, [
                    _loadingAddOns
                        ? const Center(child: Padding(
                            padding: EdgeInsets.all(20),
                            child: CircularProgressIndicator(),
                          ))
                        : _addOns.isEmpty
                            ? Padding(
                                padding: const EdgeInsets.symmetric(vertical: 12),
                                child: Text('Tidak ada add-on',
                                    style: TextStyle(color: AppTheme.textMuted)),
                              )
              : Column(
                                  children: _addOns
                                      .asMap()
                                      .entries
                                      .map((e) => e.value.isPerUnit
                                          ? _AddOnQuantityTile(
                                              index: e.key,
                                              addOn: e.value,
                                              quantity: _selectedAddOns[e.value.id] ?? 0,
                                              onChanged: (qty) {
                                                setState(() {
                                                  if (qty > 0) {
                                                    _selectedAddOns[e.value.id] = qty;
                                                  } else {
                                                    _selectedAddOns.remove(e.value.id);
                                                  }
                                                });
                                              },
                                            )
                                          : _AddOnFlatTile(
                                              index: e.key,
                                              addOn: e.value,
                                              selected: _selectedAddOns.containsKey(e.value.id),
                                              onChanged: (selected) {
                                                setState(() {
                                                  if (selected) {
                                                    _selectedAddOns[e.value.id] = 1;
                                                  } else {
                                                    _selectedAddOns.remove(e.value.id);
                                                  }
                                                });
                                              },
                                            ))
                                      .toList(),
                                ),
                  ]),
                  const SizedBox(height: 24),
                  _buildSection('Metode Pembayaran', Icons.payment_rounded, [
                    Row(children: [
                      Expanded(
                        child: _PaymentOption(
                          title: 'DP 50%',
                          subtitle: 'Bayar setengah',
                          value: 'dp',
                          groupValue: _paymentType,
                          icon: Icons.money_off_rounded,
                          onChanged: (v) => setState(() => _paymentType = v),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: _PaymentOption(
                          title: 'Lunas',
                          subtitle: 'Bayar penuh',
                          value: 'lunas',
                          groupValue: _paymentType,
                          icon: Icons.payment_rounded,
                          onChanged: (v) => setState(() => _paymentType = v),
                        ),
                      ),
                    ]),
                  ]),
                  const SizedBox(height: 20),
                  Container(
                    width: double.infinity,
                    padding: AppTheme.cardPadding,
                    decoration: BoxDecoration(
                      color: AppTheme.surface,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppTheme.borderLight),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Total Pembayaran',
                                style: AppTheme.labelStyle),
                            SizedBox(height: 2),
                            Text('Termasuk PPN',
                                style: TextStyle(
                                    fontSize: 11, color: AppTheme.textMuted)),
                          ],
                        ),
                        Text('Rp ${_fmt(_total)}',
                            style: AppTheme.priceStyle.copyWith(
                                fontSize: 22)),
                      ],
                    ),
                  ).animate().fadeIn(duration: 400.ms, delay: 200.ms).scale(begin: const Offset(0.97, 0.97), end: const Offset(1, 1), duration: 400.ms),
                  const SizedBox(height: 16),
                  SizedBox(
                    width: double.infinity,
                    height: 54,
                    child: ElevatedButton(
                      onPressed: _submitting ? null : _submit,
                      child: _submitting
                          ? const SizedBox(
                              height: 22,
                              width: 22,
                              child: CircularProgressIndicator(
                                  strokeWidth: 2.5, color: Colors.white))
                          : const Text('Booking Sekarang'),
                    ),
                  ).animate().fadeIn(duration: 400.ms, delay: 300.ms),
                  const SizedBox(height: 32),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildImageHeader() {
    final images = _images;
    if (images.isEmpty) {
      return Container(
        color: AppTheme.primary,
        child: const Center(
          child: Icon(Icons.image_outlined, size: 48, color: AppTheme.goldLight),
        ),
      );
    }
    return Stack(
      fit: StackFit.expand,
      children: [
        PageView(
          controller: _imageController,
          onPageChanged: (i) => setState(() => _currentImageIndex = i),
          children: images.map((url) => Stack(
            fit: StackFit.expand,
            children: [
              NetworkImageLoader(url: url, fit: BoxFit.cover, width: double.infinity, height: 250),
              Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [
                      Colors.transparent,
                      AppTheme.primary.withValues(alpha: 0.6),
                    ],
                  ),
                ),
              ),
            ],
          )).toList(),
        ),
        if (images.length > 1) ...[
          Positioned(
            left: 8,
            top: 0,
            bottom: 0,
            child: Center(
              child: IconButton(
                icon: const Icon(Icons.chevron_left_rounded, color: Colors.white, size: 28),
                style: IconButton.styleFrom(
                  backgroundColor: Colors.black26,
                  padding: const EdgeInsets.all(6),
                  minimumSize: const Size(36, 36),
                ),
                onPressed: () {
                  if (_currentImageIndex > 0) {
                    _imageController.previousPage(duration: 300.ms, curve: Curves.easeInOut);
                  }
                },
              ),
            ),
          ),
          Positioned(
            right: 8,
            top: 0,
            bottom: 0,
            child: Center(
              child: IconButton(
                icon: const Icon(Icons.chevron_right_rounded, color: Colors.white, size: 28),
                style: IconButton.styleFrom(
                  backgroundColor: Colors.black26,
                  padding: const EdgeInsets.all(6),
                  minimumSize: const Size(36, 36),
                ),
                onPressed: () {
                  if (_currentImageIndex < images.length - 1) {
                    _imageController.nextPage(duration: 300.ms, curve: Curves.easeInOut);
                  }
                },
              ),
            ),
          ),
          Positioned(
            bottom: 12,
            left: 0,
            right: 0,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(images.length, (i) => AnimatedContainer(
                duration: 200.ms,
                margin: const EdgeInsets.symmetric(horizontal: 3),
                width: i == _currentImageIndex ? 20 : 7,
                height: 7,
                decoration: BoxDecoration(
                  color: i == _currentImageIndex ? AppTheme.gold : Colors.white60,
                  borderRadius: BorderRadius.circular(4),
                ),
              )),
            ),
          ),
        ],
      ],
    );
  }

  Widget _buildTitleSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(widget.gedung.nama,
            style: const TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.w700,
                color: AppTheme.textPrimary,
                letterSpacing: -0.5)),
        const SizedBox(height: 8),
        Row(children: [
          _infoChip(Icons.people_outline, '${widget.gedung.kapasitas} orang'),
          const SizedBox(width: 16),
          _infoChip(Icons.attach_money, 'Rp ${_fmt(widget.gedung.hargaSewa.toInt())}/hari'),
        ]),
        const SizedBox(height: 6),
        Row(children: [
          Icon(Icons.location_on_outlined,
              size: 14, color: AppTheme.textMuted),
          const SizedBox(width: 4),
          Expanded(
              child: Text(widget.gedung.alamat,
                  style: const TextStyle(
                      fontSize: 13, color: AppTheme.textSecondary))),
        ]),
        if (widget.gedung.deskripsi.isNotEmpty) ...[
          const SizedBox(height: 12),
          Container(
            width: double.infinity,
            padding: AppTheme.cardPadding,
            decoration: BoxDecoration(
              color: AppTheme.surface,
              borderRadius: BorderRadius.circular(14),
            ),
            child: Text(widget.gedung.deskripsi,
                style: const TextStyle(
                    color: AppTheme.textSecondary,
                    height: 1.6,
                    fontSize: 13)),
          ),
        ],
      ],
    );
  }

  Widget _infoChip(IconData icon, String label) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: AppTheme.textMuted),
        const SizedBox(width: 4),
        Text(label,
            style: const TextStyle(
                fontSize: 12, color: AppTheme.textSecondary)),
      ],
    );
  }

  Widget _buildSection(String title, IconData icon, List<Widget> children) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppTheme.primary.withValues(alpha: 0.06),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, size: 18, color: AppTheme.gold),
            ),
            const SizedBox(width: 10),
            Text(title,
                style: AppTheme.sectionTitle),
          ],
        ),
        const SizedBox(height: 12),
        ...children,
      ],
    );
  }
}

class _AddOnFlatTile extends StatelessWidget {
  final int index;
  final AddOn addOn;
  final bool selected;
  final ValueChanged<bool> onChanged;
  const _AddOnFlatTile({required this.index, required this.addOn, required this.selected, required this.onChanged});

  String _fmt(int n) => n.toString().replaceAllMapped(
      RegExp(r'(\d)(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.');

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 6),
      decoration: BoxDecoration(
        color: selected ? AppTheme.primary.withValues(alpha: 0.04) : Colors.transparent,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: selected ? AppTheme.gold.withValues(alpha: 0.3) : AppTheme.borderLight,
        ),
      ),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => onChanged(!selected),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
          child: Row(
            children: [
              Container(
                width: 22,
                height: 22,
                decoration: BoxDecoration(
                  color: selected ? AppTheme.gold : Colors.transparent,
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(
                    color: selected ? AppTheme.gold : AppTheme.textMuted,
                    width: selected ? 1 : 1.5,
                  ),
                ),
                child: selected
                    ? const Icon(Icons.check_rounded, size: 16, color: Colors.white)
                    : null,
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(addOn.nama,
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                    const SizedBox(height: 2),
                    Text('Rp ${_fmt(addOn.harga.toInt())} (flat)',
                        style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary)),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    ).animate().fadeIn(delay: (50 * index).ms, duration: 300.ms);
  }
}

class _AddOnQuantityTile extends StatelessWidget {
  final int index;
  final AddOn addOn;
  final int quantity;
  final ValueChanged<int> onChanged;
  const _AddOnQuantityTile({required this.index, required this.addOn, required this.quantity, required this.onChanged});

  String _fmt(int n) => n.toString().replaceAllMapped(
      RegExp(r'(\d)(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.');

  @override
  Widget build(BuildContext context) {
    final selected = quantity > 0;
    return Container(
      margin: const EdgeInsets.only(bottom: 6),
      decoration: BoxDecoration(
        color: selected ? AppTheme.primary.withValues(alpha: 0.04) : Colors.transparent,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: selected ? AppTheme.gold.withValues(alpha: 0.3) : AppTheme.borderLight,
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(addOn.nama,
                      style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                  const SizedBox(height: 2),
                  Text('Rp ${_fmt(addOn.harga.toInt())}${addOn.isPerUnit ? '/buah' : ''}',
                      style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary)),
                ],
              ),
            ),
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                InkWell(
                  onTap: quantity > 0 ? () => onChanged(quantity - 1) : null,
                  borderRadius: BorderRadius.circular(8),
                  child: Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: quantity > 0 ? AppTheme.gold : AppTheme.borderLight,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Center(
                      child: Icon(Icons.remove_rounded, size: 18, color: Colors.white),
                    ),
                  ),
                ),
                SizedBox(
                  width: 36,
                  child: Center(
                    child: Text('$quantity',
                        style: const TextStyle(
                            fontWeight: FontWeight.w700, fontSize: 15, color: AppTheme.textPrimary)),
                  ),
                ),
                InkWell(
                  onTap: () => onChanged(quantity + 1),
                  borderRadius: BorderRadius.circular(8),
                  child: Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: AppTheme.gold,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Center(
                      child: Icon(Icons.add_rounded, size: 18, color: Colors.white),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    ).animate().fadeIn(delay: (50 * index).ms, duration: 300.ms);
  }
}

class _PaymentOption extends StatelessWidget {
  final String title;
  final String subtitle;
  final String value;
  final String groupValue;
  final IconData icon;
  final ValueChanged<String> onChanged;
  const _PaymentOption({required this.title, required this.subtitle, required this.value, required this.groupValue, required this.icon, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    final selected = value == groupValue;
    return GestureDetector(
      onTap: () => onChanged(value),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: selected ? AppTheme.primary.withValues(alpha: 0.06) : AppTheme.surface,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: selected ? AppTheme.gold : AppTheme.borderLight,
            width: selected ? 1.5 : 1,
          ),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: selected ? AppTheme.gold.withValues(alpha: 0.15) : Colors.transparent,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, size: 20,
                  color: selected ? AppTheme.gold : AppTheme.textMuted),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title,
                      style: TextStyle(
                          fontWeight: FontWeight.w600,
                          fontSize: 13,
                          color: selected ? AppTheme.textPrimary : AppTheme.textSecondary)),
                  Text(subtitle,
                      style: TextStyle(
                          fontSize: 10, color: AppTheme.textMuted)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SelectionCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final VoidCallback onTap;
  const _SelectionCard(
      {required this.icon,
      required this.label,
      required this.value,
      required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppTheme.borderLight),
      ),
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppTheme.primary.withValues(alpha: 0.06),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, size: 18, color: AppTheme.gold),
              ),
              const SizedBox(width: 12),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(label,
                      style: const TextStyle(
                          fontSize: 11,
                          color: AppTheme.textSecondary,
                          letterSpacing: 0.3)),
                  const SizedBox(height: 2),
                  Text(value,
                      style: const TextStyle(
                          fontWeight: FontWeight.w600,
                          fontSize: 13)),
                ],
              ),
              const Spacer(),
              Icon(Icons.chevron_right_rounded,
                  color: AppTheme.textMuted, size: 20),
            ],
          ),
        ),
      ),
    );
  }
}
