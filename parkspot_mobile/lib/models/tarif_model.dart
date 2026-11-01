class TarifModel {
  final int idTarif;
  final String jenisKendaraan; // 'mobil', 'motor'
  final String kategori; // 'reguler', 'booking'
  final int tarifPerJam;

  TarifModel({
    required this.idTarif,
    required this.jenisKendaraan,
    required this.kategori,
    required this.tarifPerJam,
  });

  factory TarifModel.fromMap(String key, Map<dynamic, dynamic> map) {
    return TarifModel(
      idTarif: int.tryParse(map['id_tarif']?.toString() ?? '1') ?? 1,
      jenisKendaraan: map['jenis_kendaraan']?.toString() ?? 'mobil',
      kategori: map['kategori']?.toString() ?? 'reguler',
      tarifPerJam: int.tryParse(map['tarif_per_jam']?.toString() ?? '5000') ?? 5000,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id_tarif': idTarif,
      'jenis_kendaraan': jenisKendaraan,
      'kategori': kategori,
      'tarif_per_jam': tarifPerJam,
    };
  }
}

