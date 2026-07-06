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

  bool get isOverdue => status == 'overdue' || (daysOverdue ?? 0) > 0;
  bool get isActive => status == 'active' || status == 'borrowed';
  bool get isReturned => status == 'returned' || returnedAt != null;

  factory LoanModel.fromJson(Map<String, dynamic> json) {
    final book = json['book'] as Map<String, dynamic>?;
    final bookCopy = json['book_copy'] as Map<String, dynamic>?;
    final bookData = book ?? bookCopy?['book'] as Map<String, dynamic>?;

    return LoanModel(
      id: json['id'] as int,
      bookCopyId: json['book_copy_id'] as int? ?? 0,
      bookId: bookData?['id'] as int? ?? 0,
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
      renewalCount: json['renewal_count'] as int? ?? 0,
      maxRenewals: json['max_renewals'] as int? ?? 3,
      canRenew: json['can_renew'] as bool? ?? false,
      status: json['status'] as String? ?? 'active',
      daysRemaining: json['days_remaining'] as int?,
      daysOverdue: json['days_overdue'] as int?,
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
      id: json['id'] as int,
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
