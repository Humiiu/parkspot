class SlotModel {
  final String idSlot;
  final int idArea;
  final String kodeSlot;
  final int urutanJarak;
  final String statusLampu; // 'hijau', 'oranye', 'merah'
  final String platNomor;

  SlotModel({
    required this.idSlot,
    required this.idArea,
    required this.kodeSlot,
    required this.urutanJarak,
    required this.statusLampu,
    this.platNomor = '',
  });

  bool get isAvailable => statusLampu.toLowerCase() == 'hijau';
  bool get isBooked => statusLampu.toLowerCase() == 'oranye';
  bool get isOccupied => statusLampu.toLowerCase() == 'merah';

  factory SlotModel.fromMap(String key, Map<dynamic, dynamic> map) {
    return SlotModel(
      idSlot: map['id_slot']?.toString() ?? key,
      idArea: int.tryParse(map['id_area']?.toString() ?? '1') ?? 1,
      kodeSlot: map['kode_slot']?.toString() ?? key,
      urutanJarak: int.tryParse(map['urutan_jarak']?.toString() ?? '99') ?? 99,
      statusLampu: map['status_lampu']?.toString() ?? 'hijau',
      platNomor: map['plat_nomor']?.toString() ?? '',
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id_slot': idSlot,
      'id_area': idArea,
      'kode_slot': kodeSlot,
      'urutan_jarak': urutanJarak,
      'status_lampu': statusLampu,
      'plat_nomor': platNomor,
    };
  }
}
