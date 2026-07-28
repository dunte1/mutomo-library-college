import '../../../core/utils/type_parsers.dart';

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
  final int borrowLimit;
  final List<DashboardLoan> recentLoans;
  final List<DashboardItem> dueSoonBooks;
  final List<DashboardItem> featuredBooks;
  final List<DashboardItem> recentDigitalAssets;
  final List<DashboardItem> upcomingEvents;
  final List<DashboardItem> recentAnnouncements;

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
    this.borrowLimit = 5,
    this.recentLoans = const [],
    this.dueSoonBooks = const [],
    this.featuredBooks = const [],
    this.recentDigitalAssets = const [],
    this.upcomingEvents = const [],
    this.recentAnnouncements = const [],
  });

  factory DashboardModel.fromJson(Map<String, dynamic> json) {
    final stats = json['stats'] as Map<String, dynamic>? ?? json;

    return DashboardModel(
      totalBooks: parseIntOrNull(stats['total_books']) ?? 0,
      activeLoans:
          parseIntOrNull(stats['active_loans']) ?? parseIntOrNull(stats['active_borrows']) ?? 0,
      overdueLoans:
          parseIntOrNull(stats['overdue_loans']) ??
          parseIntOrNull(stats['overdue_borrows']) ??
          0,
      totalFines:
          parseDoubleOrNull(stats['total_fines']) ??
          parseDoubleOrNull(stats['pending_fines_total']) ??
          0,
      pendingFines: parseIntOrNull(stats['pending_fines']) ?? 0,
      availableBooks: parseIntOrNull(stats['available_books']) ?? 0,
      digitalAssets: parseIntOrNull(stats['digital_assets']) ?? 0,
      activeReservations:
          parseIntOrNull(stats['active_reservations']) ??
          parseIntOrNull(stats['pending_reservations']) ??
          0,
      unreadNotifications:
          parseIntOrNull(json['unread_notifications']) ??
          parseIntOrNull(stats['unread_notifications']) ??
          0,
      unreadMessages: parseIntOrNull(json['unread_messages']) ?? 0,
      borrowLimit: parseIntOrNull(stats['borrow_limit']) ?? 5,
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
      recentDigitalAssets:
          (json['recent_digital_assets'] as List<dynamic>?)
              ?.map((e) => DashboardItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      upcomingEvents:
          (json['upcoming_events'] as List<dynamic>?)
              ?.map((e) => DashboardItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      recentAnnouncements:
          (json['recent_announcements'] as List<dynamic>?)
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
      id: parseInt(json['id'], fieldName: 'id'),
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
      id: parseInt(json['id'], fieldName: 'id'),
      title: json['title'] as String? ?? '',
      description: json['description'] as String?,
      imageUrl:
          json['cover_image'] as String? ?? json['thumbnail_url'] as String?,
    );
  }
}
