import '../../../core/utils/type_parsers.dart';

class FineModel {
  final int id;
  final double amount;
  final double? amountPaid;
  final String reason;
  final String status;
  final DateTime assessedAt;
  final DateTime? paidAt;
  final String? bookTitle;
  final String? fineType;
  final String? paymentMethod;
  final String? mpesaReference;
  final String? receiptNumber;
  final int? borrowRecordId;
  final double? waivedAmount;

  FineModel({
    required this.id,
    required this.amount,
    this.amountPaid,
    required this.reason,
    required this.status,
    required this.assessedAt,
    this.paidAt,
    this.bookTitle,
    this.fineType,
    this.paymentMethod,
    this.mpesaReference,
    this.receiptNumber,
    this.borrowRecordId,
    this.waivedAmount,
  });

  bool get isPending => status == 'pending' || status == 'unpaid';
  bool get isPaid => status == 'paid' || status == 'settled';
  double get balance => amount - (amountPaid ?? 0);

  FineModel copyWith({
    int? id,
    double? amount,
    double? amountPaid,
    String? reason,
    String? status,
    DateTime? assessedAt,
    DateTime? paidAt,
    String? bookTitle,
    String? fineType,
    String? paymentMethod,
    String? mpesaReference,
    String? receiptNumber,
    int? borrowRecordId,
    double? waivedAmount,
  }) {
    return FineModel(
      id: id ?? this.id,
      amount: amount ?? this.amount,
      amountPaid: amountPaid ?? this.amountPaid,
      reason: reason ?? this.reason,
      status: status ?? this.status,
      assessedAt: assessedAt ?? this.assessedAt,
      paidAt: paidAt ?? this.paidAt,
      bookTitle: bookTitle ?? this.bookTitle,
      fineType: fineType ?? this.fineType,
      paymentMethod: paymentMethod ?? this.paymentMethod,
      mpesaReference: mpesaReference ?? this.mpesaReference,
      receiptNumber: receiptNumber ?? this.receiptNumber,
      borrowRecordId: borrowRecordId ?? this.borrowRecordId,
      waivedAmount: waivedAmount ?? this.waivedAmount,
    );
  }

  factory FineModel.fromJson(Map<String, dynamic> json) {
    final borrow = json['borrow_record'] as Map<String, dynamic>?;
    String? title;
    int? borrowId;
    if (borrow != null) {
      final book = borrow['book'] as Map<String, dynamic>?;
      final bookCopy = borrow['book_copy'] as Map<String, dynamic>?;
      title =
          book?['title'] as String? ?? bookCopy?['book']?['title'] as String?;
      borrowId = parseIntOrNull(borrow['id']);
    }
    title ??= json['book_title'] as String?;

    return FineModel(
      id: parseInt(json['id'], fieldName: 'id'),
      amount: parseDouble(json['amount'], fieldName: 'amount'),
      amountPaid: parseDoubleOrNull(json['paid_amount']),
      reason: json['reason'] as String? ?? '',
      status: json['status'] as String? ?? 'pending',
      assessedAt: DateTime.parse(json['assessed_at'] as String),
      paidAt: json['paid_at'] != null
          ? DateTime.tryParse(json['paid_at'] as String)
          : null,
      bookTitle: title,
      fineType: json['fine_type'] as String? ?? json['type'] as String?,
      paymentMethod: json['payment_method'] as String?,
      mpesaReference: json['mpesa_reference'] as String?,
      receiptNumber: json['receipt_number'] as String?,
      borrowRecordId: borrowId ?? parseIntOrNull(json['borrow_record_id']),
      waivedAmount: parseDoubleOrNull(json['waived_amount']),
    );
  }
}
