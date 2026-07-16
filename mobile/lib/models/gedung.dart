import '../services/api_service.dart';

class Gedung {
  final int id;
  final String nama;
  final String alamat;
  final String deskripsi;
  final int kapasitas;
  final double hargaSewa;
  final double? hargaPerJam;
  final int minimumJam;
  final String? gambar;
  final String? gambarDalam;
  final String? deskripsiDalam;
  final List<GedungImage> images;

  Gedung({
    required this.id,
    required this.nama,
    required this.alamat,
    required this.deskripsi,
    required this.kapasitas,
    required this.hargaSewa,
    this.hargaPerJam,
    this.minimumJam = 2,
    this.gambar,
    this.gambarDalam,
    this.deskripsiDalam,
    this.images = const [],
  });

  factory Gedung.fromJson(Map<String, dynamic> json) {
    return Gedung(
      id: (json['id'] ?? 0) is int
          ? json['id'] as int
          : int.tryParse(json['id'].toString()) ?? 0,
      nama: json['nama']?.toString() ?? '',
      alamat: json['alamat']?.toString() ?? '',
      deskripsi: json['deskripsi']?.toString() ?? '',
      kapasitas: (json['kapasitas'] ?? 0) is int
          ? json['kapasitas'] as int
          : int.tryParse(json['kapasitas'].toString()) ?? 0,
      hargaSewa: double.tryParse(json['harga_sewa']?.toString() ?? '0') ?? 0,
      hargaPerJam: double.tryParse(json['harga_per_jam']?.toString() ?? ''),
      minimumJam: (json['minimum_jam'] ?? 2) is int
          ? json['minimum_jam'] as int
          : int.tryParse(json['minimum_jam']?.toString() ?? '2') ?? 2,
      gambar: _resolveUrl(json['gambar_url'] ?? json['gambar']),
      gambarDalam: _resolveUrl(json['gambar_dalam_url'] ?? json['gambar_dalam']),
      deskripsiDalam: json['deskripsi_dalam']?.toString(),
      images: (json['images'] as List<dynamic>?)
              ?.map((e) => GedungImage.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }

  static String? _resolveUrl(String? url) {
    if (url == null || url.isEmpty) return null;
    if (url.startsWith('http://') || url.startsWith('https://')) return url;
    final base = ApiService.baseUrl.replaceFirst('/api', '');
    if (url.startsWith('/')) return '$base$url';
    return '$base/storage/$url';
  }
}

class GedungImage {
  final int id;
  final String? gambar;
  final String? keterangan;

  GedungImage({required this.id, this.gambar, this.keterangan});

  factory GedungImage.fromJson(Map<String, dynamic> json) {
    return GedungImage(
      id: (json['id'] ?? 0) is int
          ? json['id'] as int
          : int.tryParse(json['id'].toString()) ?? 0,
      gambar: Gedung._resolveUrl(json['gambar_url'] ?? json['gambar']),
      keterangan: json['keterangan']?.toString(),
    );
  }
}
