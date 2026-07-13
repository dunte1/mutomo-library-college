import '../../../core/utils/type_parsers.dart';

class RecommendationItem {
  final String type;
  final RecommendationData? item;
  final String reason;
  final double score;

  const RecommendationItem({
    required this.type,
    this.item,
    required this.reason,
    required this.score,
  });

  factory RecommendationItem.fromJson(Map<String, dynamic> json) {
    return RecommendationItem(
      type: json['type'] as String? ?? 'unknown',
      item: json['item'] != null ? RecommendationData.fromJson(json['item']) : null,
      reason: json['reason'] as String? ?? '',
      score: (json['score'] as num?)?.toDouble() ?? 0,
    );
  }
}

class RecommendationData {
  final int id;
  final String title;
  final String type;
  final String? coverImage;
  final String? fileType;
  final List<Map<String, String>>? authors;

  const RecommendationData({
    required this.id,
    required this.title,
    required this.type,
    this.coverImage,
    this.fileType,
    this.authors,
  });

  factory RecommendationData.fromJson(Map<String, dynamic> json) {
    return RecommendationData(
      id: parseInt(json['id'], fieldName: 'id'),
      title: json['title'] as String? ?? 'Untitled',
      type: json['type'] as String? ?? 'book',
      coverImage: json['cover_image'] as String?,
      fileType: json['file_type'] as String?,
      authors: (json['authors'] as List<dynamic>?)
          ?.map((a) => Map<String, String>.from(a as Map))
          .toList(),
    );
  }
}
