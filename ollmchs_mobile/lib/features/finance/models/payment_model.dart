import '../../../core/utils/type_parsers.dart';

class PaymentModel {
  final int id;
  final double amount;
  final String currency;
  final String paymentMethod;
  final String status;
  final String description;
  final DateTime createdAt;
  final String? receiptUrl;
  final String? reference;

  PaymentModel({
    required this.id,
    required this.amount,
    this.currency = 'KES',
    required this.paymentMethod,
    required this.status,
    required this.description,
    required this.createdAt,
    this.receiptUrl,
    this.reference,
  });

  bool get isSuccessful => status == 'completed' || status == 'success';
  bool get isPending => status == 'pending';
  bool get isFailed => status == 'failed';

  factory PaymentModel.fromJson(Map<String, dynamic> json) {
    return PaymentModel(
      id: parseInt(json['id'], fieldName: 'id'),
      amount: (json['amount'] as num).toDouble(),
      currency: json['currency'] as String? ?? 'KES',
      paymentMethod: json['payment_method'] as String? ?? '',
      status: json['status'] as String? ?? 'pending',
      description: json['description'] as String? ?? '',
      createdAt: DateTime.parse(json['created_at'] as String),
      receiptUrl: json['receipt_url'] as String?,
      reference:
          json['reference'] as String? ??
          json['transaction_reference'] as String?,
    );
  }
}
