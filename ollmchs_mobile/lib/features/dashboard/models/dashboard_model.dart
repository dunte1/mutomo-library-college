class DashboardModel {
  final int totalBooks;
  final int activeLoans;
  final int overdueLoans;
  final double totalFines;
  final int pendingFines;
  final int availableBooks;
  final int digitalAssets;
  final int activeReservations;
  final int unreadNotifications;
  final int unreadMessages;
  final List<DashboardLoan> recentLoans;
  final List<DashboardItem> dueSoonBooks;
  final List<DashboardItem> featuredBooks;

  DashboardModel({
    this.totalBooks = 0,
    this.activeLoans = 0,
    this.overdueLoans = 0,
    this.totalFines = 0,
    this.pendingFines = 0,
    this.availableBooks = 0,
    this.digitalAssets = 0,
    this.activeReservations = 0,
    this.unreadNotifications = 0,
    this.unreadMessages = 0,
    this.recentLoans = const [],
    this.dueSoonBooks = const [],
    this.featuredBooks = const [],
  });

  factory DashboardModel.fromJson(Map<String, dynamic> json) {
    final stats = json['stats'] as Map<String, dynamic>? ?? json;

    return DashboardModel(
      totalBooks: stats['total_books'] as int? ?? 0,
      activeLoans:
          stats['active_loans'] as int? ?? stats['active_borrows'] as int? ?? 0,
      overdueLoans:
          stats['overdue_loans'] as int? ??
          stats['overdue_borrows'] as int? ??
          0,
      totalFines:
          (stats['total_fines'] as num?)?.toDouble() ??
          (stats['pending_fines_total'] as num?)?.toDouble() ??
          0,
      pendingFines: stats['pending_fines'] as int? ?? 0,
      availableBooks: stats['available_books'] as int? ?? 0,
      digitalAssets: stats['digital_assets'] as int? ?? 0,
      activeReservations:
          stats['active_reservations'] as int? ??
          stats['pending_reservations'] as int? ??
          0,
      unreadNotifications:
          json['unread_notifications'] as int? ??
          stats['unread_notifications'] as int? ??
          0,
      unreadMessages: json['unread_messages'] as int? ?? 0,
      recentLoans:
          (json['recent_loans'] as List<dynamic>?)
              ?.map((e) => DashboardLoan.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      dueSoonBooks:
          (json['due_soon'] as List<dynamic>?)
              ?.map((e) => DashboardItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      featuredBooks:
          (json['featured_books'] as List<dynamic>?)
              ?.map((e) => DashboardItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }
}

class DashboardLoan {
  final int id;
  final String bookTitle;
  final String? bookCover;
  final DateTime dueAt;
  final String status;

  DashboardLoan({
    required this.id,
    required this.bookTitle,
    this.bookCover,
    required this.dueAt,
    required this.status,
  });

  factory DashboardLoan.fromJson(Map<String, dynamic> json) {
    final book = json['book'] as Map<String, dynamic>?;
    final bookCopy = json['book_copy'] as Map<String, dynamic>?;
    final bookData = book ?? bookCopy?['book'];

    return DashboardLoan(
      id: json['id'] as int,
      bookTitle: bookData?['title'] as String? ?? '',
      bookCover: bookData?['cover_image'] as String?,
      dueAt: DateTime.parse(json['due_at'] as String),
      status: json['status'] as String? ?? 'active',
    );
  }
}

class DashboardItem {
  final int id;
  final String title;
  final String? description;
  final String? imageUrl;

  DashboardItem({
    required this.id,
    required this.title,
    this.description,
    this.imageUrl,
  });

  factory DashboardItem.fromJson(Map<String, dynamic> json) {
    return DashboardItem(
      id: json['id'] as int,
      title: json['title'] as String? ?? '',
      description: json['description'] as String?,
      imageUrl:
          json['cover_image'] as String? ?? json['thumbnail_url'] as String?,
    );
  }
}
