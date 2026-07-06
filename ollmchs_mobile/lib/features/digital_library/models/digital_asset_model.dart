class DigitalAssetModel {
  final int id;
  final String title;
  final String? description;
  final String? fileType;
  final String? fileUrl;
  final String? thumbnailUrl;
  final String? category;
  final List<String> authors;
  final String? fileSize;
  final int? pageCount;
  final double? averageRating;
  final int? downloadCount;
  final DateTime? createdAt;

  DigitalAssetModel({
    required this.id,
    required this.title,
    this.description,
    this.fileType,
    this.fileUrl,
    this.thumbnailUrl,
    this.category,
    this.authors = const [],
    this.fileSize,
    this.pageCount,
    this.averageRating,
    this.downloadCount,
    this.createdAt,
  });

  bool get isPdf => fileType == 'pdf' || fileType == 'application/pdf';
  bool get isVideo => fileType?.startsWith('video') == true;
  bool get isAudio => fileType?.startsWith('audio') == true;

  factory DigitalAssetModel.fromJson(Map<String, dynamic> json) {
    final authors =
        (json['authors'] as List<dynamic>?)
            ?.map((a) => a is Map ? a['name'] as String? ?? '' : a.toString())
            .where((n) => n.isNotEmpty)
            .toList() ??
        [];

    return DigitalAssetModel(
      id: json['id'] as int,
      title: json['title'] as String? ?? '',
      description: json['description'] as String?,
      fileType: json['file_type'] as String?,
      fileUrl: json['file_url'] as String?,
      thumbnailUrl:
          json['thumbnail_url'] as String? ?? json['cover_image'] as String?,
      category: json['category'] is Map
          ? json['category']['name'] as String?
          : json['category'] as String?,
      authors: authors,
      fileSize: json['file_size'] != null
          ? _formatFileSize(json['file_size'] as int)
          : null,
      pageCount: json['page_count'] as int?,
      averageRating: json['average_rating'] != null
          ? (json['average_rating'] as num).toDouble()
          : null,
      downloadCount: json['download_count'] as int?,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'] as String)
          : null,
    );
  }

  static String _formatFileSize(int bytes) {
    if (bytes < 1024) return '$bytes B';
    if (bytes < 1024 * 1024) return '${(bytes / 1024).toStringAsFixed(1)} KB';
    return '${(bytes / (1024 * 1024)).toStringAsFixed(1)} MB';
  }
}

class ReadingHistoryModel {
  final int id;
  final int assetId;
  final String assetTitle;
  final String? assetCover;
  final double? progress;
  final String? lastPage;
  final DateTime? lastReadAt;
  final int? totalPages;

  ReadingHistoryModel({
    required this.id,
    required this.assetId,
    required this.assetTitle,
    this.assetCover,
    this.progress,
    this.lastPage,
    this.lastReadAt,
    this.totalPages,
  });

  factory ReadingHistoryModel.fromJson(Map<String, dynamic> json) {
    final asset = json['digital_asset'] as Map<String, dynamic>?;

    return ReadingHistoryModel(
      id: json['id'] as int,
      assetId: asset?['id'] as int? ?? json['digital_asset_id'] as int? ?? 0,
      assetTitle:
          asset?['title'] as String? ?? json['asset_title'] as String? ?? '',
      assetCover:
          asset?['thumbnail_url'] as String? ??
          asset?['cover_image'] as String?,
      progress: json['progress'] != null
          ? (json['progress'] as num).toDouble()
          : null,
      lastPage: json['last_page'] as String?,
      lastReadAt: json['last_read_at'] != null
          ? DateTime.tryParse(json['last_read_at'] as String)
          : null,
      totalPages: json['total_pages'] as int? ?? asset?['page_count'] as int?,
    );
  }
}

class RecommendationModel {
  final int id;
  final String title;
  final String? description;
  final String? coverImage;
  final String? type;
  final double? score;
  final String reason;

  RecommendationModel({
    required this.id,
    required this.title,
    this.description,
    this.coverImage,
    this.type,
    this.score,
    required this.reason,
  });

  factory RecommendationModel.fromJson(Map<String, dynamic> json) {
    final book = json['book'] as Map<String, dynamic>?;
    final asset = json['digital_asset'] as Map<String, dynamic>?;

    return RecommendationModel(
      id: json['id'] as int,
      title:
          book?['title'] as String? ??
          asset?['title'] as String? ??
          json['title'] as String? ??
          '',
      description:
          book?['description'] as String? ?? asset?['description'] as String?,
      coverImage:
          book?['cover_image'] as String? ?? asset?['thumbnail_url'] as String?,
      type:
          json['type'] as String? ?? (book != null ? 'book' : 'digital_asset'),
      score: json['score'] != null ? (json['score'] as num).toDouble() : null,
      reason: json['reason'] as String? ?? 'Recommended for you',
    );
  }
}
