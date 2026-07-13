import '../../../core/utils/type_parsers.dart';

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
      id: parseInt(json['id'], fieldName: 'id'),
      name: json['name'] as String? ?? '',
      biography: json['bio'] as String?,
      booksCount: parseIntOrNull(json['books_count']) ?? 0,
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
