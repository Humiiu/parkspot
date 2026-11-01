class AreaModel {
  final int idArea;
  final String namaArea;
  final int kapasitas;
  final int terisi;

  AreaModel({
    required this.idArea,
    required this.namaArea,
    required this.kapasitas,
    required this.terisi,
  });

  int get tersedia => (kapasitas - terisi).clamp(0, kapasitas);

  factory AreaModel.fromMap(String key, Map<dynamic, dynamic> map) {
    return AreaModel(
      idArea: int.tryParse(map['id_area']?.toString() ?? '1') ?? 1,
      namaArea: map['nama_area']?.toString() ?? 'Area $key',
      kapasitas: int.tryParse(map['kapasitas']?.toString() ?? '0') ?? 0,
      terisi: int.tryParse(map['terisi']?.toString() ?? '0') ?? 0,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id_area': idArea,
      'nama_area': namaArea,
      'kapasitas': kapasitas,
      'terisi': terisi,
    };
  }
}
