class TransaksiModel {
  final String idParkir;
  final String kodeQr;
  final String platNomor;
  final String jenisKendaraan;
  final String idSlot;
  final int idArea;
  final String? waktuMasuk;
  final String? waktuKeluar;
  final String? jamBooking;
  final String idTarif;
  final int tarifPerJam;
  final int durasi;
  final int biaya;
  final String status; // 'aktif', 'booking', 'selesai'
  final String statusBayar; // 'belum', 'lunas'
  final String idUser;

  TransaksiModel({
    required this.idParkir,
    required this.kodeQr,
    required this.platNomor,
    required this.jenisKendaraan,
    required this.idSlot,
    required this.idArea,
    this.waktuMasuk,
    this.waktuKeluar,
    this.jamBooking,
    required this.idTarif,
    required this.tarifPerJam,
    required this.durasi,
    required this.biaya,
    required this.status,
    required this.statusBayar,
    required this.idUser,
  });

  bool get isActive => status == 'aktif';
  bool get isBooking => status == 'booking';
  bool get isCompleted => status == 'selesai';
  bool get isPaid => statusBayar == 'lunas';

  factory TransaksiModel.fromMap(String key, Map<dynamic, dynamic> map) {
    return TransaksiModel(
      idParkir: map['id_parkir']?.toString() ?? key,
      kodeQr: map['kode_qr']?.toString() ?? key,
      platNomor: map['plat_nomor']?.toString() ?? '',
      jenisKendaraan: map['jenis_kendaraan']?.toString() ?? 'mobil',
      idSlot: map['id_slot']?.toString() ?? '',
      idArea: int.tryParse(map['id_area']?.toString() ?? '1') ?? 1,
      waktuMasuk: map['waktu_masuk']?.toString(),
      waktuKeluar: map['waktu_keluar']?.toString(),
      jamBooking: map['jam_booking']?.toString(),
      idTarif: map['id_tarif']?.toString() ?? '',
      tarifPerJam: int.tryParse(map['tarif_per_jam']?.toString() ?? '5000') ?? 5000,
      durasi: int.tryParse(map['durasi']?.toString() ?? '0') ?? 0,
      biaya: int.tryParse(map['biaya']?.toString() ?? '0') ?? 0,
      status: map['status']?.toString() ?? 'aktif',
      statusBayar: map['status_bayar']?.toString() ?? 'belum',
      idUser: map['id_user']?.toString() ?? '',
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id_parkir': idParkir,
      'kode_qr': kodeQr,
      'plat_nomor': platNomor,
      'jenis_kendaraan': jenisKendaraan,
      'id_slot': idSlot,
      'id_area': idArea,
      'waktu_masuk': waktuMasuk,
      'waktu_keluar': waktuKeluar,
      'jam_booking': jamBooking,
      'id_tarif': idTarif,
      'tarif_per_jam': tarifPerJam,
      'durasi': durasi,
      'biaya': biaya,
      'status': status,
      'status_bayar': statusBayar,
      'id_user': idUser,
    };
  }
}
