import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'firebase_options.dart';
import 'models/slot_model.dart';
import 'services/firebase_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Inisialisasi Firebase menggunakan kredensial proyek parkspot-f925f
  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );

  runApp(const ParkSpotMobileApp());
}

class ParkSpotMobileApp extends StatelessWidget {
  const ParkSpotMobileApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'ParkSpot Member',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF2563EB),
          brightness: Brightness.light,
        ),
        fontFamily: GoogleFonts.plusJakartaSans().fontFamily,
        useMaterial3: true,
      ),
      home: const SlotMonitoringScreen(),
    );
  }
}

class SlotMonitoringScreen extends StatefulWidget {
  const SlotMonitoringScreen({super.key});

  @override
  State<SlotMonitoringScreen> createState() => _SlotMonitoringScreenState();
}

class _SlotMonitoringScreenState extends State<SlotMonitoringScreen> {
  final FlutterFirebaseService _firebaseService = FlutterFirebaseService();
  int _selectedArea = 1;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: const Color(0xFF2563EB).withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(
                Icons.local_parking_rounded,
                color: Color(0xFF2563EB),
              ),
            ),
            const SizedBox(width: 12),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'ParkSpot Live',
                  style: GoogleFonts.plusJakartaSans(
                    fontWeight: FontWeight.bold,
                    fontSize: 18,
                    color: const Color(0xFF0F172A),
                  ),
                ),
                Text(
                  'Real-time Firebase Listener',
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 12,
                    color: const Color(0xFF64748B),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
      body: StreamBuilder<List<SlotModel>>(
        stream: _firebaseService.streamSlots(idArea: _selectedArea),
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          if (snapshot.hasError) {
            return Center(
              child: Text(
                'Terjadi kesalahan memuat data:\n${snapshot.error}',
                textAlign: TextAlign.center,
                style: const TextStyle(color: Colors.red),
              ),
            );
          }

          final slots = snapshot.data ?? [];
          final total = slots.length;
          final hijau = slots.where((s) => s.isAvailable).length;
          final oranye = slots.where((s) => s.isBooked).length;
          final merah = slots.where((s) => s.isOccupied).length;

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              // Pemilih Area Lantai
              Row(
                children: [
                  _buildAreaChip(1, 'Lantai 1 (Zona A)'),
                  const SizedBox(width: 8),
                  _buildAreaChip(2, 'Lantai 2 (Zona B)'),
                ],
              ),
              const SizedBox(height: 16),

              // Kartu Status Ringkasan 3 Warna
              Card(
                elevation: 0,
                color: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                  side: const BorderSide(color: Color(0xFFE2E8F0)),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Indikator Slot Real-Time (Tanpa Sensor)',
                        style: GoogleFonts.plusJakartaSans(
                          fontWeight: FontWeight.w600,
                          fontSize: 14,
                          color: const Color(0xFF1E293B),
                        ),
                      ),
                      const SizedBox(height: 12),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceAround,
                        children: [
                          _buildStatusStat(
                            label: 'Tersedia',
                            count: hijau,
                            color: const Color(0xFF22C55E),
                          ),
                          _buildStatusStat(
                            label: 'Booking',
                            count: oranye,
                            color: const Color(0xFFF59E0B),
                          ),
                          _buildStatusStat(
                            label: 'Terisi',
                            count: merah,
                            color: const Color(0xFFEF4444),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 20),

              Text(
                'Denah Slot ($total Titik)',
                style: GoogleFonts.plusJakartaSans(
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                  color: const Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 12),

              // Grid Slot 3 Warna
              GridView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  crossAxisSpacing: 12,
                  mainAxisSpacing: 12,
                  childAspectRatio: 1.6,
                ),
                itemCount: slots.length,
                itemBuilder: (context, index) {
                  final slot = slots[index];
                  Color bgColor;
                  Color borderColor;
                  Color textColor;
                  String statusLabel;

                  if (slot.isAvailable) {
                    bgColor = const Color(0xFFF0FDF4);
                    borderColor = const Color(0xFF86EFAC);
                    textColor = const Color(0xFF15803D);
                    statusLabel = 'Tersedia';
                  } else if (slot.isBooked) {
                    bgColor = const Color(0xFFFFFBEB);
                    borderColor = const Color(0xFFFCD34D);
                    textColor = const Color(0xFFB45309);
                    statusLabel = 'Booking';
                  } else {
                    bgColor = const Color(0xFFFEF2F2);
                    borderColor = const Color(0xFFFCA5A5);
                    textColor = const Color(0xFFB91C1C);
                    statusLabel = 'Terisi';
                  }

                  return Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: bgColor,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: borderColor, width: 1.5),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              slot.kodeSlot,
                              style: GoogleFonts.plusJakartaSans(
                                fontWeight: FontWeight.bold,
                                fontSize: 18,
                                color: const Color(0xFF0F172A),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 6,
                                vertical: 2,
                              ),
                              decoration: BoxDecoration(
                                color: textColor.withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                statusLabel,
                                style: GoogleFonts.plusJakartaSans(
                                  fontWeight: FontWeight.w600,
                                  fontSize: 10,
                                  color: textColor,
                                ),
                              ),
                            ),
                          ],
                        ),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Jarak: ${slot.urutanJarak}m',
                              style: TextStyle(
                                fontSize: 11,
                                color: Colors.grey.shade600,
                              ),
                            ),
                            if (slot.platNomor.isNotEmpty)
                              Text(
                                slot.platNomor,
                                style: const TextStyle(
                                  fontWeight: FontWeight.w600,
                                  fontSize: 10,
                                  color: Colors.black87,
                                ),
                              ),
                          ],
                        ),
                      ],
                    ),
                  );
                },
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildAreaChip(int areaId, String label) {
    final isSelected = _selectedArea == areaId;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (selected) {
        if (selected) {
          setState(() {
            _selectedArea = areaId;
          });
        }
      },
      selectedColor: const Color(0xFF2563EB),
      labelStyle: TextStyle(
        color: isSelected ? Colors.white : const Color(0xFF334155),
        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
      ),
    );
  }

  Widget _buildStatusStat({
    required String label,
    required int count,
    required Color color,
  }) {
    return Column(
      children: [
        Container(
          width: 12,
          height: 12,
          decoration: BoxDecoration(
            color: color,
            shape: BoxShape.circle,
          ),
        ),
        const SizedBox(height: 6),
        Text(
          '$count',
          style: GoogleFonts.plusJakartaSans(
            fontWeight: FontWeight.bold,
            fontSize: 18,
            color: const Color(0xFF0F172A),
          ),
        ),
        Text(
          label,
          style: TextStyle(
            fontSize: 11,
            color: Colors.grey.shade600,
          ),
        ),
      ],
    );
  }
}
