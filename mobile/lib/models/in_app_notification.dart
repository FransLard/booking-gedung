import 'booking.dart';
import 'package:intl/intl.dart';

class InAppNotification {
  final int id;
  final int userId;
  final int bookingId;
  final String type;
  final String message;
  final bool isRead;
  final String createdAt;
  final Booking? booking;

  InAppNotification({
    required this.id,
    required this.userId,
    required this.bookingId,
    required this.type,
    required this.message,
    required this.isRead,
    required this.createdAt,
    this.booking,
  });

  String get timeAgo {
    final dt = DateTime.tryParse(createdAt);
    if (dt == null) return '';
    final now = DateTime.now();
    final diff = now.difference(dt);
    if (diff.inMinutes < 1) return 'Baru saja';
    if (diff.inMinutes < 60) return '${diff.inMinutes} menit lalu';
    if (diff.inHours < 24) return '${diff.inHours} jam lalu';
    if (diff.inDays < 7) return '${diff.inDays} hari lalu';
    return DateFormat('dd MMM yyyy', 'id').format(dt);
  }

  factory InAppNotification.fromJson(Map<String, dynamic> json) {
    return InAppNotification(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      userId: json['user_id'] is int ? json['user_id'] : int.tryParse(json['user_id'].toString()) ?? 0,
      bookingId: json['booking_id'] is int ? json['booking_id'] : int.tryParse(json['booking_id'].toString()) ?? 0,
      type: json['type']?.toString() ?? '',
      message: json['message']?.toString() ?? '',
      isRead: json['is_read'] == true || json['is_read'] == 1,
      createdAt: json['created_at']?.toString() ?? '',
      booking: json['booking'] != null && (json['booking'] as Map<String, dynamic>).isNotEmpty
          ? Booking.fromJson(json['booking'] as Map<String, dynamic>)
          : null,
    );
  }
}
