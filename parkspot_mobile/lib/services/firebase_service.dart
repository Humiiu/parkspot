import 'package:firebase_database/firebase_database.dart';
import '../models/area_model.dart';
import '../models/slot_model.dart';
import '../models/tarif_model.dart';
import '../models/transaksi_model.dart';
import '../models/user_model.dart';

class FlutterFirebaseService {
  final FirebaseDatabase _db = FirebaseDatabase.instance;

  // ===========================================================================
  // REAL-TIME STREAMS (Sinkronisasi Otomatis Tanpa Refresh)
  // ===========================================================================

  /// Stream seluruh slot parkir secara real-time.
  /// Setiap kali petugas mengubah status di web kasir, Flutter langsung update!
  Stream<List<SlotModel>> streamSlots({int? idArea}) {
    return _db.ref('tb_slot_parkir').onValue.map((event) {
      final snapshot = event.snapshot;
      if (!snapshot.exists || snapshot.value == null) {
        return <SlotModel>[];
      }

      final dynamic data = snapshot.value;
      final List<SlotModel> list = [];

      if (data is Map) {
        data.forEach((key, val) {
          if (val is Map) {
            final slot = SlotModel.fromMap(key.toString(), val);
            if (idArea == null || slot.idArea == idArea) {
              list.add(slot);
            }
          }
        });
      }

      list.sort((a, b) => a.urutanJarak.compareTo(b.urutanJarak));
      return list;
    });
  }

  /// Stream data area & kapasitas lantai secara real-time.
  Stream<List<AreaModel>> streamAreas() {
    return _db.ref('tb_area_parkir').onValue.map((event) {
      final snapshot = event.snapshot;
      if (!snapshot.exists || snapshot.value == null) {
        return <AreaModel>[];
      }

      final dynamic data = snapshot.value;
      final List<AreaModel> list = [];

      if (data is Map) {
        data.forEach((key, val) {
          if (val is Map) {
            list.add(AreaModel.fromMap(key.toString(), val));
          }
        });
      }

      list.sort((a, b) => a.idArea.compareTo(b.idArea));
      return list;
    });
  }

  /// Stream data tiket aktif (untuk cek argo berjalan & status lunas).
  Stream<TransaksiModel?> streamTicket(String idParkir) {
    return _db.ref('tb_transaksi/$idParkir').onValue.map((event) {
      final snapshot = event.snapshot;
      if (!snapshot.exists || snapshot.value == null) {
        return null;
      }
      final dynamic data = snapshot.value;
      if (data is Map) {
        return TransaksiModel.fromMap(idParkir, data);
      }
      return null;
    });
  }

  // ===========================================================================
  // FUNGSI AKSI (Booking, Tarif, Login)
  // ===========================================================================

  /// Ambil daftar tarif resmi parkir.
  Future<List<TarifModel>> getTariffs() async {
    final snapshot = await _db.ref('tb_tarif').get();
    if (!snapshot.exists || snapshot.value == null) {
      return [];
    }

    final dynamic data = snapshot.value;
    final List<TarifModel> list = [];

    if (data is Map) {
      data.forEach((key, val) {
        if (val is Map) {
          list.add(TarifModel.fromMap(key.toString(), val));
        }
      });
    }

    return list;
  }

  /// Reservasi / Booking slot parkir berbatas waktu (Member).
  Future<bool> bookSlot({
    required String kodeSlot,
    required String platNomor,
    required String jamBooking,
    required String idUser,
    int idArea = 1,
    String jenisKendaraan = 'mobil',
  }) async {
    try {
      final slotRef = _db.ref('tb_slot_parkir/$kodeSlot');
      final slotSnap = await slotRef.get();

      if (!slotSnap.exists) return false;
      final slotData = slotSnap.value as Map?;
      if (slotData?['status_lampu'] != 'hijau') return false;

      final now = DateTime.now();
      final dateStr =
          "${now.year}${now.month.toString().padLeft(2, '0')}${now.day.toString().padLeft(2, '0')}";
      final kodeTiket =
          "BKG-$dateStr-${now.millisecondsSinceEpoch.toString().substring(8)}";

      // 1. Ubah warna slot menjadi ORANYE
      await slotRef.update({
        'status_lampu': 'oranye',
        'plat_nomor': platNomor.toUpperCase(),
      });

      // 2. Simpan transaksi booking
      final tarifPerJam = (jenisKendaraan == 'motor') ? 4000 : 8000;
      await _db.ref('tb_transaksi/$kodeTiket').set({
        'id_parkir': kodeTiket,
        'kode_qr': kodeTiket,
        'plat_nomor': platNomor.toUpperCase(),
        'jenis_kendaraan': jenisKendaraan,
        'id_slot': kodeSlot,
        'id_area': idArea,
        'jam_booking': jamBooking,
        'id_tarif': 'tarif_${jenisKendaraan}_booking',
        'tarif_per_jam': tarifPerJam,
        'durasi': 0,
        'biaya': tarifPerJam,
        'status': 'booking',
        'status_bayar': 'belum',
        'id_user': idUser,
      });

      // 3. Catat log aktivitas
      final logKey = "LOG-${DateTime.now().millisecondsSinceEpoch}";
      await _db.ref('tb_log_aktivitas/$logKey').set({
        'id_log': logKey,
        'id_user': idUser,
        'aktivitas':
            "Member $idUser booking slot $kodeSlot untuk kedatangan $jamBooking.",
        'waktu_aktivitas': DateTime.now().toIso8601String(),
      });

      return true;
    } catch (e) {
      return false;
    }
  }

  /// Login sederhana untuk Member.
  Future<UserModel?> loginMember(String username, String password) async {
    try {
      final snapshot = await _db.ref('tb_user').get();
      if (!snapshot.exists || snapshot.value == null) return null;

      final dynamic data = snapshot.value;
      if (data is Map) {
        for (var entry in data.entries) {
          final val = entry.value;
          if (val is Map) {
            final u = val['username']?.toString().toLowerCase();
            final r = val['role']?.toString().toLowerCase();
            if (u == username.toLowerCase() && r == 'member') {
              return UserModel.fromMap(entry.key.toString(), val);
            }
          }
        }
      }
      return null;
    } catch (_) {
      return null;
    }
  }
}

