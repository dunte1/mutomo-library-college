import '../../../core/utils/type_parsers.dart';

class LoanModel {
  final int id;
  final int bookCopyId;
  final int bookId;
  final String bookTitle;
  final String? bookCover;
  final String? barcode;
  final String? author;
  final DateTime borrowedAt;
  final DateTime dueAt;
  final DateTime? returnedAt;
  final DateTime? renewedAt;
  final int renewalCount;
  final int maxRenewals;
  final bool canRenew;
  final String status;
  final int? daysRemaining;
  final int? daysOverdue;

  LoanModel({
    required this.id,
    required this.bookCopyId,
    required this.bookId,
    required this.bookTitle,
    this.bookCover,
    this.barcode,
    this.author,
    required this.borrowedAt,
    required this.dueAt,
    this.returnedAt,
    this.renewedAt,
    this.renewalCount = 0,
    this.maxRenewals = 3,
    this.canRenew = false,
    required this.status,
    this.daysRemaining,
    this.daysOverdue,
  });

  Map<String, dynamic> toJson() => {
    'id': id,
    'book_copy_id': bookCopyId,
    'book_id': bookId,
    'book_title': bookTitle,
    'book_cover': bookCover,
    'barcode': barcode,
    'author': author,
    'borrowed_at': borrowedAt.toIso8601String(),
    'due_at': dueAt.toIso8601String(),
    'returned_at': returnedAt?.toIso8601String(),
    'renewed_at': renewedAt?.toIso8601String(),
    'renewal_count': renewalCount,
    'max_renewals': maxRenewals,
    'can_renew': canRenew,
    'status': status,
    'days_remaining': daysRemaining,
    'days_overdue': daysOverdue,
  };

  bool get isOverdue => status == 'overdue' || (daysOverdue ?? 0) > 0;
  bool get isActive => status == 'active' || status == 'borrowed';
  bool get isReturned => status == 'returned' || returnedAt != null;

  factory LoanModel.fromJson(Map<String, dynamic> json) {
    final book = json['book'] as Map<String, dynamic>?;
    final bookCopy = json['book_copy'] as Map<String, dynamic>?;
    final bookData = book ?? bookCopy?['book'] as Map<String, dynamic>?;

    return LoanModel(
      id: parseInt(json['id'], fieldName: 'id'),
      bookCopyId: parseIntOrNull(json['book_copy_id']) ?? 0,
      bookId: parseIntOrNull(bookData?['id']) ?? 0,
      bookTitle: _extractBookTitle(bookData, json),
      bookCover:
          bookData?['cover_image'] as String? ??
          bookCopy?['book']?['cover_image'] as String?,
      barcode: json['barcode'] as String? ?? bookCopy?['barcode'] as String?,
      author: _extractAuthor(bookData),
      borrowedAt: DateTime.parse(json['borrowed_at'] as String),
      dueAt: DateTime.parse(json['due_at'] as String),
      returnedAt: json['returned_at'] != null
          ? DateTime.tryParse(json['returned_at'] as String)
          : null,
      renewedAt: json['renewed_at'] != null
          ? DateTime.tryParse(json['renewed_at'] as String)
          : null,
      renewalCount: parseIntOrNull(json['renewal_count']) ?? 0,
      maxRenewals: parseIntOrNull(json['max_renewals']) ?? 3,
      canRenew: json['can_renew'] as bool? ?? false,
      status: json['status'] as String? ?? 'active',
      daysRemaining: parseIntOrNull(json['days_remaining']),
      daysOverdue: parseIntOrNull(json['days_overdue']),
    );
  }

  static String _extractBookTitle(
    Map<String, dynamic>? bookData,
    Map<String, dynamic> json,
  ) {
    if (bookData != null) return bookData['title'] as String? ?? '';
    final bookCopy = json['book_copy'] as Map<String, dynamic>?;
    final nestedBook = bookCopy?['book'] as Map<String, dynamic>?;
    return nestedBook?['title'] as String? ??
        json['book_title'] as String? ??
        '';
  }

  static String? _extractAuthor(Map<String, dynamic>? bookData) {
    if (bookData == null) return null;
    final authors = bookData['authors'] as List<dynamic>?;
    if (authors != null && authors.isNotEmpty) {
      final first = authors.first;
      return first is Map ? first['name'] as String? : first.toString();
    }
    return null;
  }
}

class LoanHistoryModel {
  final int id;
  final String bookTitle;
  final String? bookCover;
  final DateTime borrowedAt;
  final DateTime dueAt;
  final DateTime? returnedAt;
  final String status;

  LoanHistoryModel({
    required this.id,
    required this.bookTitle,
    this.bookCover,
    required this.borrowedAt,
    required this.dueAt,
    this.returnedAt,
    required this.status,
  });

  Map<String, dynamic> toJson() => {
    'id': id,
    'book_title': bookTitle,
    'book_cover': bookCover,
    'borrowed_at': borrowedAt.toIso8601String(),
    'due_at': dueAt.toIso8601String(),
    'returned_at': returnedAt?.toIso8601String(),
    'status': status,
  };

  factory LoanHistoryModel.fromJson(Map<String, dynamic> json) {
    final book = json['book'] as Map<String, dynamic>?;
    final bookCopy = json['book_copy'] as Map<String, dynamic>?;
    final bookData = book ?? bookCopy?['book'] as Map<String, dynamic>?;

    String title = '';
    String? cover;
    if (bookData != null) {
      title = bookData['title'] as String? ?? '';
      cover = bookData['cover_image'] as String?;
    } else {
      title = json['book_title'] as String? ?? '';
    }

    return LoanHistoryModel(
      id: parseInt(json['id'], fieldName: 'id'),
      bookTitle: title,
      bookCover: cover,
      borrowedAt: DateTime.parse(json['borrowed_at'] as String),
      dueAt: DateTime.parse(json['due_at'] as String),
      returnedAt: json['returned_at'] != null
          ? DateTime.tryParse(json['returned_at'] as String)
          : null,
      status: json['status'] as String? ?? 'unknown',
    );
  }
}
