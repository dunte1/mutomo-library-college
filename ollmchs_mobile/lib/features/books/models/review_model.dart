class ReviewModel {
  final int id;
  final int bookId;
  final int userId;
  final String? userName;
  final String? userAvatar;
  final int rating;
  final String? review;
  final bool isApproved;
  final DateTime? createdAt;

  ReviewModel({
    required this.id,
    required this.bookId,
    required this.userId,
    this.userName,
    this.userAvatar,
    required this.rating,
    this.review,
    this.isApproved = false,
    this.createdAt,
  });

  factory ReviewModel.fromJson(Map<String, dynamic> json) {
    return ReviewModel(
      id: json['id'] as int,
      bookId: json['book_id'] as int,
      userId: json['user_id'] as int? ?? 0,
      userName: json['user_name'] as String?,
      userAvatar: json['user_avatar'] as String?,
      rating: json['rating'] as int,
      review: json['review'] as String?,
      isApproved: json['is_approved'] as bool? ?? false,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'] as String)
          : null,
    );
  }
}

class ReviewStats {
  final double averageRating;
  final int totalReviews;
  final Map<int, int> distribution;

  const ReviewStats({
    this.averageRating = 0,
    this.totalReviews = 0,
    this.distribution = const {},
  });

  factory ReviewStats.fromJson(Map<String, dynamic> json) {
    final dist = json['distribution'] as Map<String, dynamic>? ?? {};
    return ReviewStats(
      averageRating: (json['average_rating'] as num?)?.toDouble() ?? 0,
      totalReviews: json['total_reviews'] as int? ?? 0,
      distribution: dist.map(
        (k, v) => MapEntry(int.parse(k), (v as num).toInt()),
      ),
    );
  }
}
