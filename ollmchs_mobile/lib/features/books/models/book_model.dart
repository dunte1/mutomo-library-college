import 'book_copy_model.dart';

class BookModel {
  final int id;
  final String title;
  final String? isbn;
  final String? isbn13;
  final String? description;
  final int? publicationYear;
  final int? pageCount;
  final String? coverImage;
  final String? language;
  final String? publisher;
  final String? category;
  final int? categoryId;
  final List<String> authors;
  final int totalCopies;
  final int availableCopies;
  final bool isFeatured;
  final String? location;
  final String? deweyDecimal;
  final DateTime? createdAt;
  final List<BookCopyModel> copies;

  BookModel({
    required this.id,
    required this.title,
    this.isbn,
    this.isbn13,
    this.description,
    this.publicationYear,
    this.pageCount,
    this.coverImage,
    this.language,
    this.publisher,
    this.category,
    this.categoryId,
    this.authors = const [],
    this.totalCopies = 0,
    this.availableCopies = 0,
    this.isFeatured = false,
    this.location,
    this.deweyDecimal,
    this.createdAt,
    this.copies = const [],
  });

  factory BookModel.fromJson(Map<String, dynamic> json) {
    final copiesJson = json['copies'] as List<dynamic>?;
    return BookModel(
      id: json['id'] as int,
      title: json['title'] as String,
      isbn: json['isbn'] as String?,
      isbn13: json['isbn13'] as String?,
      description: json['description'] as String?,
      publicationYear: json['publication_year'] as int?,
      pageCount: json['page_count'] as int?,
      coverImage: json['cover_image'] as String?,
      language: json['language'] as String?,
      publisher: json['publisher'] is Map
          ? json['publisher']['name'] as String?
          : json['publisher'] as String?,
      category: json['category'] is Map
          ? json['category']['name'] as String?
          : json['category'] as String?,
      categoryId: json['category'] is Map
          ? json['category']['id'] as int?
          : json['category_id'] as int?,
      authors:
          (json['authors'] as List<dynamic>?)
              ?.map((a) => a is Map ? a['name'] as String : a.toString())
              .toList() ??
          [],
      totalCopies: json['total_copies'] as int? ?? 0,
      availableCopies: json['available_copies'] as int? ?? 0,
      isFeatured: json['is_featured'] as bool? ?? false,
      location: json['location'] as String?,
      deweyDecimal: json['dewey_decimal'] as String?,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'] as String)
          : null,
      copies:
          copiesJson
              ?.map((e) => BookCopyModel.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }
}
