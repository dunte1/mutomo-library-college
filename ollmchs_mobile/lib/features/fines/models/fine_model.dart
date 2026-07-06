class FineModel {
  final int id;
  final double amount;
  final double? amountPaid;
  final String reason;
  final String status;
  final DateTime assessedAt;
  final DateTime? paidAt;
  final String? bookTitle;

  FineModel({
    required this.id,
    required this.amount,
    this.amountPaid,
    required this.reason,
    required this.status,
    required this.assessedAt,
    this.paidAt,
    this.bookTitle,
  });

  bool get isPending => status == 'pending' || status == 'unpaid';
  bool get isPaid => status == 'paid' || status == 'settled';

  factory FineModel.fromJson(Map<String, dynamic> json) {
    final borrow = json['borrow_record'] as Map<String, dynamic>?;
    String? title;
    if (borrow != null) {
      final book = borrow['book'] as Map<String, dynamic>?;
      final bookCopy = borrow['book_copy'] as Map<String, dynamic>?;
      title =
          book?['title'] as String? ?? bookCopy?['book']?['title'] as String?;
    }
    title ??= json['book_title'] as String?;

    return FineModel(
      id: json['id'] as int,
      amount: (json['amount'] as num).toDouble(),
      amountPaid: json['paid_amount'] != null
          ? (json['paid_amount'] as num).toDouble()
          : null,
      reason: json['reason'] as String? ?? '',
      status: json['status'] as String? ?? 'pending',
      assessedAt: DateTime.parse(json['assessed_at'] as String),
      paidAt: json['paid_at'] != null
          ? DateTime.tryParse(json['paid_at'] as String)
          : null,
      bookTitle: title,
    );
  }
}
