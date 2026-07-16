import 'gedung.dart';

class Booking {
  final int id;
  final String bookingCode;
  final int userId;
  final int gedungId;
  final String tanggal;
  final String? tanggalSelesai;
  final String jamMulai;
  final String jamSelesai;
  final double totalHarga;
  final String paymentType;
  final String paymentStatus;
  final String status;
  final String? paymentProof;
  final String? paymentVerifiedAt;
  final Gedung? gedung;
  final List<dynamic>? addOns;

  final String? batasBayarLunas;

  Booking({
    required this.id,
    required this.bookingCode,
    required this.userId,
    required this.gedungId,
    required this.tanggal,
    this.tanggalSelesai,
    required this.jamMulai,
    required this.jamSelesai,
    required this.totalHarga,
    required this.paymentType,
    required this.paymentStatus,
    required this.status,
    this.paymentProof,
    this.paymentVerifiedAt,
    this.batasBayarLunas,
    this.gedung,
    this.addOns,
  });

  String get statusLabel {
    switch (status) {
      case 'pending':
        return 'Menunggu';
      case 'confirmed':
        return 'Dikonfirmasi';
      case 'cancelled':
        return 'Dibatalkan';
      default:
        return status;
    }
  }

  String get paymentLabel {
    switch (paymentStatus) {
      case 'unpaid':
        return 'Belum Dibayar';
      case 'waiting':
        return 'Menunggu Verifikasi';
      case 'paid_dp':
        return 'DP 50% Lunas';
      case 'paid_lunas':
        return 'Lunas';
      default:
        return paymentStatus;
    }
  }

  factory Booking.fromJson(Map<String, dynamic> json) {
    return Booking(
      id: json['id'],
      bookingCode: json['booking_code'],
      userId: json['user_id'],
      gedungId: json['gedung_id'],
      tanggal: json['tanggal'],
      tanggalSelesai: json['tanggal_selesai'],
      jamMulai: json['jam_mulai'],
      jamSelesai: json['jam_selesai'],
      totalHarga: double.parse(json['total_harga'].toString()),
      paymentType: json['payment_type'],
      paymentStatus: json['payment_status'],
      status: json['status'],
      paymentProof: json['payment_proof'],
      paymentVerifiedAt: json['payment_verified_at'],
      batasBayarLunas: json['batas_bayar_lunas'],
      gedung: json['gedung'] != null ? Gedung.fromJson(json['gedung']) : null,
      addOns: json['add_ons'],
    );
  }
}
