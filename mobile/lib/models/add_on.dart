class AddOn {
  final int id;
  final String nama;
  final String deskripsi;
  final double harga;
  final String type;

  AddOn({
    required this.id,
    required this.nama,
    required this.deskripsi,
    required this.harga,
    this.type = 'flat',
  });

  bool get isPerUnit => type == 'per_unit';

  factory AddOn.fromJson(Map<String, dynamic> json) {
    return AddOn(
      id: (json['id'] ?? 0) is int
          ? json['id'] as int
          : int.tryParse(json['id'].toString()) ?? 0,
      nama: json['nama']?.toString() ?? '',
      deskripsi: json['deskripsi']?.toString() ?? '',
      harga: double.tryParse(json['harga']?.toString() ?? '0') ?? 0,
      type: json['type']?.toString() ?? 'flat',
    );
  }
}
