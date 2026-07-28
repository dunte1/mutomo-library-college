import '../../../core/utils/type_parsers.dart';

class PaymentResult {
  final int paymentId;
  final String? receiptNumber;
  final String? mpesaReference;
  final double amount;
  final String status;
  final DateTime paidAt;
  final String? message;
  final String? paymentMethod;

  PaymentResult({
    required this.paymentId,
    this.receiptNumber,
    this.mpesaReference,
    required this.amount,
    required this.status,
    required this.paidAt,
    this.message,
    this.paymentMethod,
  });

  bool get isConfirmed => status == 'confirmed' || status == 'completed';
  bool get isPending => status == 'pending' || status == 'processing';
  bool get isFailed => status == 'failed' || status == 'error';

  factory PaymentResult.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as Map<String, dynamic>? ?? json;
    return PaymentResult(
      paymentId: parseInt(data['payment_id'] ?? data['id'], fieldName: 'payment_id'),
      receiptNumber: data['receipt_number'] as String?,
      mpesaReference: data['mpesa_reference'] as String? ?? data['mpesa_receipt'] as String?,
      amount: parseDouble(data['amount'], fieldName: 'amount'),
      status: data['status'] as String? ?? 'pending',
      paidAt: DateTime.parse(data['paid_at'] as String? ?? data['created_at'] as String? ?? DateTime.now().toIso8601String()),
      message: data['message'] as String?,
      paymentMethod: data['payment_method'] as String?,
    );
  }
}
