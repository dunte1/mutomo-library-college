import '../../../core/utils/type_parsers.dart';

class BookmarkModel {
  final int id;
  final int? bookId;
  final int? authorId;
  final String title;
  final String? subtitle;
  final String? coverImage;
  final String? type;
  final DateTime createdAt;

  BookmarkModel({
    required this.id,
    this.bookId,
    this.authorId,
    required this.title,
    this.subtitle,
    this.coverImage,
    this.type,
    required this.createdAt,
  });

  bool get isBook => type == 'book' || bookId != null;
  bool get isAuthor => type == 'author' || authorId != null;

  factory BookmarkModel.fromJson(Map<String, dynamic> json) {
    final book = json['book'] as Map<String, dynamic>?;
    final author = json['author'] as Map<String, dynamic>?;
    final parsedBookId = parseIntOrNull(json['book_id']);
    final parsedAuthorId = parseIntOrNull(json['author_id']);
    final typeStr = json['type'] as String? ??
        (parsedBookId != null ? 'book' : 'author');

    return BookmarkModel(
      id: parseInt(json['id'], fieldName: 'id'),
      bookId: parsedBookId,
      authorId: parsedAuthorId,
      title: book?['title'] as String? ??
          author?['name'] as String? ??
          json['title'] as String? ??
          '',
      subtitle: book?['authors'] != null
          ? (book!['authors'] as List).map((a) => a is Map ? a['name'] : a.toString()).join(', ')
          : author?['name'] as String?,
      coverImage: book?['cover_image'] as String?,
      type: typeStr,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'] as String) ?? DateTime.now()
          : DateTime.now(),
    );
  }
}
