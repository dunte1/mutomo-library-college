import '../../../core/utils/type_parsers.dart';

class AnnouncementModel {
  final int id;
  final String title;
  final String? body;
  final String? category;
  final String? authorName;
  final String? imageUrl;
  final DateTime createdAt;
  final bool isPinned;

  AnnouncementModel({
    required this.id,
    required this.title,
    this.body,
    this.category,
    this.authorName,
    this.imageUrl,
    required this.createdAt,
    this.isPinned = false,
  });

  factory AnnouncementModel.fromJson(Map<String, dynamic> json) {
    final author = json['author'] as Map<String, dynamic>?;
    return AnnouncementModel(
      id: parseInt(json['id'], fieldName: 'id'),
      title: json['title'] as String? ?? '',
      body: json['body'] as String?,
      category: json['category'] as String?,
      authorName: author?['name'] as String? ?? json['author_name'] as String?,
      imageUrl: json['image_url'] as String?,
      createdAt: DateTime.parse(json['created_at'] as String),
      isPinned: json['is_pinned'] as bool? ?? false,
    );
  }
}
