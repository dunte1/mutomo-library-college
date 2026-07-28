import 'package:flutter_test/flutter_test.dart';
import 'package:ollmchs_library/features/fines/models/payment_result.dart';

void main() {
  group('PaymentResult', () {
    test('fromJson with all fields', () {
      final json = {
        'data': {
          'payment_id': 1,
          'receipt_number': 'RCT-001',
          'mpesa_reference': 'QHK7Y8Z5LP',
          'amount': 200.0,
          'status': 'confirmed',
          'paid_at': '2026-07-25T14:30:00.000Z',
          'message': 'Payment successful',
          'payment_method': 'mpesa',
        },
      };
      final result = PaymentResult.fromJson(json);
      expect(result.paymentId, 1);
      expect(result.receiptNumber, 'RCT-001');
      expect(result.mpesaReference, 'QHK7Y8Z5LP');
      expect(result.amount, 200.0);
      expect(result.status, 'confirmed');
      expect(result.message, 'Payment successful');
      expect(result.paymentMethod, 'mpesa');
    });

    test('fromJson flat structure', () {
      final json = {
        'payment_id': 2,
        'amount': 100.0,
        'status': 'pending',
        'paid_at': '2026-07-25T14:30:00.000Z',
      };
      final result = PaymentResult.fromJson(json);
      expect(result.paymentId, 2);
      expect(result.amount, 100.0);
      expect(result.isPending, isTrue);
    });

    test('status getters', () {
      final confirmed = PaymentResult(paymentId: 1, amount: 100, status: 'confirmed', paidAt: DateTime.now());
      final pending = PaymentResult(paymentId: 2, amount: 100, status: 'pending', paidAt: DateTime.now());
      final failed = PaymentResult(paymentId: 3, amount: 100, status: 'failed', paidAt: DateTime.now());
      expect(confirmed.isConfirmed, isTrue);
      expect(pending.isPending, isTrue);
      expect(failed.isFailed, isTrue);
    });
  });
}
