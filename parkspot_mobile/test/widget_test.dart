import 'package:flutter_test/flutter_test.dart';
import 'package:parkspot_mobile/models/slot_model.dart';
import 'package:parkspot_mobile/models/area_model.dart';
import 'package:parkspot_mobile/models/tarif_model.dart';

void main() {
  test('SlotModel mapping test', () {
    final slot = SlotModel.fromMap('A-01', {
      'id_slot': 'A-01',
      'id_area': 1,
      'kode_slot': 'A-01',
      'urutan_jarak': 1,
      'status_lampu': 'hijau',
      'plat_nomor': '',
    });

    expect(slot.kodeSlot, 'A-01');
    expect(slot.isAvailable, true);
    expect(slot.isBooked, false);
    expect(slot.isOccupied, false);
  });

  test('AreaModel capacity test', () {
    final area = AreaModel.fromMap('1', {
      'id_area': 1,
      'nama_area': 'Lantai 1',
      'kapasitas': 20,
      'terisi': 5,
    });

    expect(area.tersedia, 15);
  });

  test('TarifModel mapping test', () {
    final tarif = TarifModel.fromMap('tarif_mobil_reguler', {
      'id_tarif': 1,
      'jenis_kendaraan': 'mobil',
      'kategori': 'reguler',
      'tarif_per_jam': 5000,
    });

    expect(tarif.tarifPerJam, 5000);
  });
}
