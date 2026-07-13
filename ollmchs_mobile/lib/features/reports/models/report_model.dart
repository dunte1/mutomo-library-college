import '../../../core/utils/type_parsers.dart';

class ReadingSummary {
  final int totalBorrowed;
  final int activeLoans;
  final int completedLoans;
  final int overdueCount;
  final double totalFines;
  final double pendingFines;
  final int digitalReadCount;
  final int digitalAssetsDownloaded;
  final Map<String, int> booksByCategory;
  final Map<String, int> monthlyBorrowingTrend;

  const ReadingSummary({
    required this.totalBorrowed,
    required this.activeLoans,
    required this.completedLoans,
    required this.overdueCount,
    required this.totalFines,
    required this.pendingFines,
    required this.digitalReadCount,
    required this.digitalAssetsDownloaded,
    required this.booksByCategory,
    required this.monthlyBorrowingTrend,
  });

  factory ReadingSummary.fromJson(Map<String, dynamic> json) {
    return ReadingSummary(
      totalBorrowed: parseIntOrNull(json['total_borrowed']) ?? 0,
      activeLoans: parseIntOrNull(json['active_loans']) ?? 0,
      completedLoans: parseIntOrNull(json['completed_loans']) ?? 0,
      overdueCount: parseIntOrNull(json['overdue_count']) ?? 0,
      totalFines: parseDoubleOrNull(json['total_fines']) ?? 0,
      pendingFines: parseDoubleOrNull(json['pending_fines']) ?? 0,
      digitalReadCount: parseIntOrNull(json['digital_read_count']) ?? 0,
      digitalAssetsDownloaded: parseIntOrNull(json['digital_assets_downloaded']) ?? 0,
      booksByCategory: Map<String, int>.from(json['books_by_category'] as Map? ?? {}),
      monthlyBorrowingTrend: Map<String, int>.from(json['monthly_borrowing_trend'] as Map? ?? {}),
    );
  }
}

class LoanReportItem {
  final int id;
  final String bookTitle;
  final String? bookCover;
  final String? authorName;
  final String? categoryName;
  final DateTime? borrowedAt;
  final DateTime? dueAt;
  final DateTime? returnedAt;
  final String status;

  const LoanReportItem({
    required this.id,
    required this.bookTitle,
    this.bookCover,
    this.authorName,
    this.categoryName,
    this.borrowedAt,
    this.dueAt,
    this.returnedAt,
    required this.status,
  });

  factory LoanReportItem.fromJson(Map<String, dynamic> json) {
    return LoanReportItem(
      id: parseInt(json['id'], fieldName: 'id'),
      bookTitle: json['book_copy']?['book']?['title'] as String? ?? 'Unknown',
      bookCover: json['book_copy']?['book']?['cover_image'] as String?,
      authorName: (json['book_copy']?['book']?['authors'] as List?)?.firstOrNull?['name'] as String?,
      categoryName: json['book_copy']?['book']?['category']?['name'] as String?,
      borrowedAt: json['borrowed_at'] != null ? DateTime.tryParse(json['borrowed_at']) : null,
      dueAt: json['due_at'] != null ? DateTime.tryParse(json['due_at']) : null,
      returnedAt: json['returned_at'] != null ? DateTime.tryParse(json['returned_at']) : null,
      status: json['status'] as String? ?? 'unknown',
    );
  }
}

class FineReportItem {
  final int id;
  final String? bookTitle;
  final double amount;
  final String reason;
  final String status;
  final DateTime? createdAt;

  const FineReportItem({
    required this.id,
    this.bookTitle,
    required this.amount,
    required this.reason,
    required this.status,
    this.createdAt,
  });

  factory FineReportItem.fromJson(Map<String, dynamic> json) {
    return FineReportItem(
      id: parseInt(json['id'], fieldName: 'id'),
      bookTitle: json['borrow_record']?['book_copy']?['book']?['title'] as String?,
      amount: parseDoubleOrNull(json['amount']) ?? 0,
      reason: json['reason'] as String? ?? '',
      status: json['status'] as String? ?? 'pending',
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at']) : null,
    );
  }
}
