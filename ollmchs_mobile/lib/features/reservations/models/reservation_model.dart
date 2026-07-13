import '../../../core/utils/type_parsers.dart';

class ReservationModel {
  final int id;
  final int bookId;
  final String bookTitle;
  final String? bookCover;
  final String? author;
  final DateTime reservedAt;
  final DateTime? expiresAt;
  final int position;
  final String status;

  ReservationModel({
    required this.id,
    required this.bookId,
    required this.bookTitle,
    this.bookCover,
    this.author,
    required this.reservedAt,
    this.expiresAt,
    required this.position,
    required this.status,
  });

  Map<String, dynamic> toJson() => {
    'id': id,
    'book_id': bookId,
    'book_title': bookTitle,
    'book_cover': bookCover,
    'author': author,
    'reserved_at': reservedAt.toIso8601String(),
    'expires_at': expiresAt?.toIso8601String(),
    'position': position,
    'status': status,
  };

  bool get isActive => status == 'active' || status == 'pending';
  bool get isAvailable => status == 'available';

  factory ReservationModel.fromJson(Map<String, dynamic> json) {
    final book = json['book'] as Map<String, dynamic>?;
    final authors = book?['authors'] as List<dynamic>?;

    return ReservationModel(
      id: parseInt(json['id'], fieldName: 'id'),
      bookId: parseIntOrNull(book?['id']) ?? 0,
      bookTitle:
          book?['title'] as String? ?? json['book_title'] as String? ?? '',
      bookCover: book?['cover_image'] as String?,
      author: (authors != null && authors.isNotEmpty)
          ? (authors.first is Map
                ? authors.first['name'] as String?
                : authors.first.toString())
          : null,
      reservedAt: DateTime.parse(json['reserved_at'] as String),
      expiresAt: json['expires_at'] != null
          ? DateTime.tryParse(json['expires_at'] as String)
          : null,
      position: parseIntOrNull(json['position']) ?? 1,
      status: json['status'] as String? ?? 'active',
    );
  }
}
