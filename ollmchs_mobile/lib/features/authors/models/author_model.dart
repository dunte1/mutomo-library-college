class AuthorModel {
  final int id;
  final String name;
  final String? biography;
  final String? photo;
  final int booksCount;
  final DateTime? birthDate;
  final DateTime? deathDate;
  final String? nationality;

  AuthorModel({
    required this.id,
    required this.name,
    this.biography,
    this.photo,
    this.booksCount = 0,
    this.birthDate,
    this.deathDate,
    this.nationality,
  });

  factory AuthorModel.fromJson(Map<String, dynamic> json) {
    return AuthorModel(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      biography: json['biography'] as String?,
      photo: json['photo'] as String? ?? json['avatar'] as String?,
      booksCount: json['books_count'] as int? ?? 0,
      birthDate: json['birth_date'] != null
          ? DateTime.tryParse(json['birth_date'] as String)
          : null,
      deathDate: json['death_date'] != null
          ? DateTime.tryParse(json['death_date'] as String)
          : null,
      nationality: json['nationality'] as String?,
    );
  }
}
