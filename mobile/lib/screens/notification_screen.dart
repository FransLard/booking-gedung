import 'package:flutter/material.dart';
import '../models/in_app_notification.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';
import 'booking_detail_screen.dart';

class NotificationScreen extends StatefulWidget {
  const NotificationScreen({super.key});

  @override
  State<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends State<NotificationScreen> {
  List<InAppNotification> _list = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.get('/notifications', auth: true);
      _list = (res['data'] as List<dynamic>)
          .map((e) => InAppNotification.fromJson(e as Map<String, dynamic>))
          .toList();
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  Future<void> _markRead(int id) async {
    try {
      await ApiService.patch('/notifications/$id/read', auth: true);
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifikasi'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_rounded, size: 20),
          onPressed: () => Navigator.pop(context, true),
        ),
        actions: [
          if (_list.any((n) => !n.isRead))
            IconButton(
              onPressed: () async {
                try {
                  await ApiService.patch('/notifications/read-all', auth: true);
                  _load();
                } catch (_) {}
              },
              icon: const Text('Tandai sudah dibaca', style: TextStyle(fontSize: 12)),
            ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _list.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.notifications_off_rounded,
                          size: 56, color: AppTheme.textMuted.withValues(alpha: 0.5)),
                      const SizedBox(height: 16),
                      const Text('Tidak ada notifikasi',
                          style: TextStyle(fontSize: 16, color: AppTheme.textSecondary)),
                      const SizedBox(height: 4),
                      const Text('Notifikasi akan muncul di sini',
                          style: TextStyle(fontSize: 13, color: AppTheme.textMuted)),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView.separated(
                    padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                    itemCount: _list.length,
                    separatorBuilder: (_, __) => const Divider(height: 1),
                    itemBuilder: (_, i) {
                      final n = _list[i];
                      return _NotificationTile(
                        notification: n,
                        onTap: () {
                          if (!n.isRead) _markRead(n.id);
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => BookingDetailScreen(bookingId: n.bookingId),
                            ),
                          );
                        },
                      );
                    },
                  ),
                ),
    );
  }
}

class _NotificationTile extends StatelessWidget {
  final InAppNotification notification;
  final VoidCallback onTap;

  const _NotificationTile({required this.notification, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final bg = notification.isRead ? Colors.transparent : AppTheme.primary.withValues(alpha: 0.04);
    final icon = notification.type == 'approved'
        ? Icons.check_circle_rounded
        : Icons.cancel_rounded;
    final iconColor = notification.type == 'approved'
        ? AppTheme.success
        : AppTheme.error;

    return Material(
      color: bg,
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 12),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: iconColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, size: 22, color: iconColor),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(notification.message,
                        style: TextStyle(
                          fontSize: 13,
                          color: notification.isRead
                              ? AppTheme.textMuted
                              : AppTheme.textPrimary,
                        )),
                    const SizedBox(height: 4),
                    Text(notification.timeAgo,
                        style: const TextStyle(
                            fontSize: 11, color: AppTheme.textMuted)),
                  ],
                ),
              ),
              if (!notification.isRead)
                Container(
                  width: 8,
                  height: 8,
                  margin: const EdgeInsets.only(left: 8, top: 4),
                  decoration: const BoxDecoration(
                    color: AppTheme.primary,
                    shape: BoxShape.circle,
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}
