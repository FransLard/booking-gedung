import 'package:flutter/material.dart';

const _monthNames = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];
const _dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

class BookingCalendarSheet extends StatefulWidget {
  final Set<DateTime> bookedDates;
  final DateTimeRange? initial;
  const BookingCalendarSheet({super.key, required this.bookedDates, this.initial});

  @override
  State<BookingCalendarSheet> createState() => _BookingCalendarSheetState();
}

class _BookingCalendarSheetState extends State<BookingCalendarSheet> {
  late DateTime _currentMonth;
  DateTime? _selectedStart;
  int _duration = 1;

  @override
  void initState() {
    super.initState();
    final now = DateTime.now();
    _currentMonth = DateTime(now.year, now.month);
    if (widget.initial != null) {
      _selectedStart = DateTime(
        widget.initial!.start.year,
        widget.initial!.start.month,
        widget.initial!.start.day,
      );
      _duration = widget.initial!.duration.inDays + 1;
    }
  }

  DateTime? get _selectedEnd {
    if (_selectedStart == null) return null;
    return DateTime(
      _selectedStart!.year,
      _selectedStart!.month,
      _selectedStart!.day + _duration - 1,
    );
  }

  bool _isPast(DateTime d) {
    final today = DateTime.now();
    return DateTime(d.year, d.month, d.day)
        .isBefore(DateTime(today.year, today.month, today.day));
  }

  bool _isBooked(DateTime d) {
    final normalized = DateTime(d.year, d.month, d.day);
    return widget.bookedDates.contains(normalized);
  }

  bool _isSelected(DateTime d) {
    if (_selectedStart == null) return false;
    final end = _selectedEnd!;
    final n = DateTime(d.year, d.month, d.day);
    return !_isBooked(d) && !n.isBefore(_selectedStart!) && !n.isAfter(end);
  }

  bool get _rangeHasConflict {
    if (_selectedStart == null) return false;
    final end = _selectedEnd!;
    var d = DateTime(_selectedStart!.year, _selectedStart!.month, _selectedStart!.day);
    while (!d.isAfter(end)) {
      if (_isBooked(d)) return true;
      d = DateTime(d.year, d.month, d.day + 1);
    }
    return false;
  }

  bool _isToday(DateTime d) {
    final t = DateTime.now();
    return d.year == t.year && d.month == t.month && d.day == t.day;
  }


  List<DateTime?> _buildDays() {
    final first = DateTime(_currentMonth.year, _currentMonth.month, 1);
    final last = DateTime(_currentMonth.year, _currentMonth.month + 1, 0);
    final pad = first.weekday % 7;
    final days = <DateTime?>[];
    for (int i = 0; i < pad; i++) days.add(null);
    for (int d = 1; d <= last.day; d++) days.add(DateTime(_currentMonth.year, _currentMonth.month, d));
    return days;
  }

  void _prev() => setState(() => _currentMonth = DateTime(_currentMonth.year, _currentMonth.month - 1));
  void _next() => setState(() => _currentMonth = DateTime(_currentMonth.year, _currentMonth.month + 1));

  @override
  Widget build(BuildContext context) {
    final days = _buildDays();
    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHeader(),
            const SizedBox(height: 12),
            _buildDayNames(),
            const SizedBox(height: 4),
            _buildGrid(days),
            const SizedBox(height: 12),
            if (_selectedStart != null) _buildSelectedInfo(),
            if (_selectedStart != null) ...[
              if (_rangeHasConflict) ...[
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  decoration: BoxDecoration(
                    color: Colors.red.shade50,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: Colors.red.shade200),
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.warning_amber_rounded, size: 18, color: Colors.red.shade700),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text('Rentang tanggal mengandung tanggal yang sudah dibooking. Perpendek durasi sewa.',
                            style: TextStyle(fontSize: 12, color: Colors.red.shade800)),
                      ),
                    ],
                  ),
                ),
              ],
              const SizedBox(height: 12),
              _buildDurationSelector(),
            ],
            const SizedBox(height: 12),
            _buildLegend(),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton(
                onPressed: (_selectedStart == null || _rangeHasConflict)
                    ? null
                    : () => Navigator.pop(context, DateTimeRange(start: _selectedStart!, end: _selectedEnd!)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF1A237E),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
                child: const Text('Konfirmasi', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
              ),
            ),
          ],
        ),
      ),
    ),
    );
  }

  Widget _buildHeader() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        IconButton(icon: const Icon(Icons.chevron_left), onPressed: _prev),
        Text('${_monthNames[_currentMonth.month - 1]} ${_currentMonth.year}',
            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1A237E))),
        IconButton(icon: const Icon(Icons.chevron_right), onPressed: _next),
      ],
    );
  }

  Widget _buildDayNames() {
    return Row(
      children: _dayNames
          .map((n) => Expanded(
                child: Center(
                  child: Text(n, style: TextStyle(fontSize: 12, color: Colors.grey.shade500, fontWeight: FontWeight.w600)),
                ),
              ))
          .toList(),
    );
  }

  Widget _buildGrid(List<DateTime?> days) {
    return Column(
      children: [
        for (int row = 0; row < days.length; row += 7)
          Row(
            children: [
              for (int col = 0; col < 7; col++)
                Expanded(child: _buildDayCell(row + col < days.length ? days[row + col] : null)),
            ],
          ),
      ],
    );
  }

  Widget _buildDayCell(DateTime? d) {
    if (d == null) return const AspectRatio(aspectRatio: 1, child: SizedBox.shrink());

    final selected = _isSelected(d);
    final today = _isToday(d);
    final past = _isPast(d);
    final booked = _isBooked(d);

    const selectedStyle = TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.white);
    const todayStyle = TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1A237E));
    const bookedStyle = TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: Colors.white);

    Widget cell;
    if (booked) {
      cell = Container(
        margin: const EdgeInsets.all(2),
        decoration: const BoxDecoration(color: Colors.red, shape: BoxShape.circle),
        child: Center(child: Text('${d.day}', style: bookedStyle)),
      );
    } else if (selected) {
      cell = Container(
        margin: const EdgeInsets.all(2),
        decoration: BoxDecoration(
          gradient: const LinearGradient(colors: [Color(0xFF283593), Color(0xFF42A5F5)]),
          shape: BoxShape.circle,
          boxShadow: [BoxShadow(color: Colors.blue.shade200, blurRadius: 4, offset: const Offset(0, 2))],
        ),
        child: Center(child: Text('${d.day}', style: selectedStyle)),
      );
    } else if (past) {
      cell = Center(
        child: Text('${d.day}', style: TextStyle(fontSize: 13, color: Colors.grey.shade300)),
      );
    } else if (today) {
      cell = Container(
        margin: const EdgeInsets.all(2),
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          border: Border.all(color: const Color(0xFF1A237E), width: 2),
        ),
        child: Center(child: Text('${d.day}', style: todayStyle)),
      );
    } else {
      cell = InkWell(
        customBorder: const CircleBorder(),
        onTap: () => setState(() {
          _selectedStart = DateTime(d.year, d.month, d.day);
          _duration = 1;
        }),
        child: Container(
          margin: const EdgeInsets.all(2),
          child: Center(child: Text('${d.day}', style: TextStyle(fontSize: 13, color: Colors.grey.shade700))),
        ),
      );
    }

    return AspectRatio(aspectRatio: 1, child: cell);
  }

  Widget _buildSelectedInfo() {
    final start = _selectedStart!;
    final end = _selectedEnd!;
    final startFmt = _formatDate(start);
    final endFmt = _formatDate(end);
    final hari = _duration;
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFF1A237E).withValues(alpha: 0.06),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          const Icon(Icons.event_rounded, size: 18, color: Color(0xFF1A237E)),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              hari > 1 ? '$startFmt — $endFmt ($hari hari)' : startFmt,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF1A237E)),
            ),
          ),
        ],
      ),
    );
  }

  String _formatDate(DateTime d) {
    const months = [
      'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
      'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'
    ];
    return '${d.day} ${months[d.month - 1]} ${d.year}';
  }

  Widget _buildDurationSelector() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Lama Sewa', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
        const SizedBox(height: 8),
        Wrap(
          spacing: 6,
          runSpacing: 6,
          children: List.generate(14, (i) {
            final d = i + 1;
            final isActive = _duration == d;
            return GestureDetector(
              onTap: () => setState(() => _duration = d),
              child: Container(
                width: 40,
                height: 36,
                decoration: BoxDecoration(
                  color: isActive ? const Color(0xFF1A237E) : Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(8),
                  border: isActive ? null : Border.all(color: Colors.grey.shade300),
                ),
                child: Center(
                  child: Text('$d',
                      style: TextStyle(
                        fontWeight: isActive ? FontWeight.bold : FontWeight.w500,
                        color: isActive ? Colors.white : Colors.grey.shade700,
                        fontSize: 13,
                      )),
                ),
              ),
            );
          }),
        ),
      ],
    );
  }

  Widget _buildLegend() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        _legendDot(Colors.blue.shade400, 'Dipilih'),
        const SizedBox(width: 16),
        _legendDot(Colors.red, 'Dibooking'),
        const SizedBox(width: 16),
        _legendDot(const Color(0xFF1A237E), 'Hari ini'),
      ],
    );
  }

  Widget _legendDot(Color color, String label) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(width: 10, height: 10, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
        const SizedBox(width: 4),
        Text(label, style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
      ],
    );
  }
}
