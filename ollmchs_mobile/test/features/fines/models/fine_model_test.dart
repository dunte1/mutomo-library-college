import 'package:flutter_test/flutter_test.dart';
import 'package:ollmchs_library/features/fines/models/fine_model.dart';

void main() {
  group('FineModel', () {
    test('fromJson with all fields', () {
      final json = {
        'id': 1,
        'amount': 200.0,
        'paid_amount': 100.0,
        'reason': 'Overdue book',
        'status': 'pending',
        'assessed_at': '2026-07-20T10:00:00.000Z',
        'paid_at': '2026-07-22T14:30:00.000Z',
        'book_title': 'Anatomy Textbook',
        'fine_type': 'overdue',
        'payment_method': 'mpesa',
        'mpesa_reference': 'QHK7Y8Z5LP',
        'receipt_number': 'RCT-20260722-001',
        'borrow_record_id': 5,
      };
      final fine = FineModel.fromJson(json);
      expect(fine.id, 1);
      expect(fine.amount, 200.0);
      expect(fine.amountPaid, 100.0);
      expect(fine.reason, 'Overdue book');
      expect(fine.status, 'pending');
      expect(fine.bookTitle, 'Anatomy Textbook');
      expect(fine.fineType, 'overdue');
      expect(fine.paymentMethod, 'mpesa');
      expect(fine.mpesaReference, 'QHK7Y8Z5LP');
      expect(fine.receiptNumber, 'RCT-20260722-001');
      expect(fine.borrowRecordId, 5);
    });

    test('fromJson with missing optional fields', () {
      final json = {
        'id': 2,
        'amount': 500.0,
        'reason': 'Lost book',
        'status': 'paid',
        'assessed_at': '2026-07-20T10:00:00.000Z',
      };
      final fine = FineModel.fromJson(json);
      expect(fine.id, 2);
      expect(fine.amountPaid, isNull);
      expect(fine.paidAt, isNull);
      expect(fine.bookTitle, isNull);
      expect(fine.fineType, isNull);
      expect(fine.paymentMethod, isNull);
      expect(fine.borrowRecordId, isNull);
    });

    test('fromJson with nested borrow_record', () {
      final json = {
        'id': 3,
        'amount': 100.0,
        'reason': 'Damage',
        'status': 'pending',
        'assessed_at': '2026-07-20T10:00:00.000Z',
        'borrow_record': {
          'id': 10,
          'book': {'title': 'Physiology Manual'},
        },
      };
      final fine = FineModel.fromJson(json);
      expect(fine.bookTitle, 'Physiology Manual');
      expect(fine.borrowRecordId, 10);
    });

    test('isPending getter', () {
      final pending = FineModel(id: 1, amount: 100, reason: 'test', status: 'pending', assessedAt: DateTime.now());
      final unpaid = FineModel(id: 2, amount: 100, reason: 'test', status: 'unpaid', assessedAt: DateTime.now());
      final paid = FineModel(id: 3, amount: 100, reason: 'test', status: 'paid', assessedAt: DateTime.now());
      expect(pending.isPending, isTrue);
      expect(unpaid.isPending, isTrue);
      expect(paid.isPending, isFalse);
    });

    test('isPaid getter', () {
      final paid = FineModel(id: 1, amount: 100, reason: 'test', status: 'paid', assessedAt: DateTime.now());
      final settled = FineModel(id: 2, amount: 100, reason: 'test', status: 'settled', assessedAt: DateTime.now());
      final pending = FineModel(id: 3, amount: 100, reason: 'test', status: 'pending', assessedAt: DateTime.now());
      expect(paid.isPaid, isTrue);
      expect(settled.isPaid, isTrue);
      expect(pending.isPaid, isFalse);
    });

    test('balance computed property', () {
      final fine = FineModel(id: 1, amount: 200, amountPaid: 50, reason: 'test', status: 'pending', assessedAt: DateTime.now());
      expect(fine.balance, 150.0);
    });

    test('copyWith preserves unmodified fields', () {
      final original = FineModel(
        id: 1, amount: 200, reason: 'Overdue', status: 'pending',
        assessedAt: DateTime(2026, 7, 20), bookTitle: 'Test Book',
        fineType: 'overdue',
      );
      final copied = original.copyWith(status: 'paid');
      expect(copied.id, 1);
      expect(copied.amount, 200);
      expect(copied.reason, 'Overdue');
      expect(copied.status, 'paid');
      expect(copied.bookTitle, 'Test Book');
    });
  });
}
