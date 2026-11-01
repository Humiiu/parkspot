class UserModel {
  final String idUser;
  final String namaLengkap;
  final String username;
  final String role; // 'admin', 'petugas', 'owner', 'member'
  final int statusAktif;

  UserModel({
    required this.idUser,
    required this.namaLengkap,
    required this.username,
    required this.role,
    required this.statusAktif,
  });

  bool get isActive => statusAktif == 1;
  bool get isMember => role.toLowerCase() == 'member';

  factory UserModel.fromMap(String key, Map<dynamic, dynamic> map) {
    return UserModel(
      idUser: map['id_user']?.toString() ?? key,
      namaLengkap: map['nama_lengkap']?.toString() ?? '',
      username: map['username']?.toString() ?? '',
      role: map['role']?.toString() ?? 'member',
      statusAktif: int.tryParse(map['status_aktif']?.toString() ?? '1') ?? 1,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id_user': idUser,
      'nama_lengkap': namaLengkap,
      'username': username,
      'role': role,
      'status_aktif': statusAktif,
    };
  }
}

