import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:bloc_test/bloc_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:dio/dio.dart';
import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/features/fines/bloc/fines_bloc.dart';
import 'package:ollmchs_library/features/fines/models/fine_model.dart';
import 'package:ollmchs_library/features/fines/models/payment_result.dart';
import 'package:ollmchs_library/features/fines/screens/fines_screen.dart';
import 'package:ollmchs_library/features/fines/screens/fine_detail_screen.dart';
import 'package:ollmchs_library/features/fines/screens/payment_screen.dart';
import 'package:ollmchs_library/features/fines/screens/payment_confirmation_screen.dart';

class MockApiClient extends Mock implements ApiClient {}

Response<dynamic> _fineListResponse() {
  return Response(
    data: {
      'data': [
        {
          'id': 1,
          'amount': 200.0,
          'reason': 'Overdue book',
          'status': 'pending',
          'assessed_at': '2026-07-20T10:00:00.000Z',
          'fine_type': 'overdue',
          'book_title': 'Anatomy Textbook',
        },
      ],
    },
    statusCode: 200,
    requestOptions: RequestOptions(path: ''),
  );
}

Response<dynamic> _fineDetailResponse() {
  return Response(
    data: {
      'data': {
        'id': 1,
        'amount': 200.0,
        'reason': 'Overdue book',
        'status': 'pending',
        'assessed_at': '2026-07-20T10:00:00.000Z',
        'fine_type': 'overdue',
        'book_title': 'Anatomy Textbook',
      },
    },
    statusCode: 200,
    requestOptions: RequestOptions(path: ''),
  );
}

void main() {
  late MockApiClient mockApi;

  setUp(() {
    mockApi = MockApiClient();
    when(() => mockApi.get(any(), queryParameters: any(named: 'queryParameters')))
        .thenAnswer((_) async => _fineDetailResponse());
    when(() => mockApi.get('/v1/fines'))
        .thenAnswer((_) async => _fineListResponse());
    when(() => mockApi.post(any(), data: any(named: 'data')))
        .thenAnswer((_) async => Response(data: {'data': {}}, statusCode: 200, requestOptions: RequestOptions(path: '')));
  });

  group('FineModel', () {
    test('fromJson parses all fields correctly', () {
      final json = {
        'id': 1, 'amount': 500.0, 'paid_amount': 200.0,
        'reason': 'Lost book', 'status': 'pending',
        'assessed_at': '2026-07-20T10:00:00.000Z',
        'paid_at': '2026-07-22T10:00:00.000Z',
        'book_title': 'Physiology Manual',
        'fine_type': 'lost', 'payment_method': 'mpesa',
        'mpesa_reference': 'REF123', 'receipt_number': 'RCT001',
        'borrow_record_id': 5, 'waived_amount': 50.0,
      };
      final fine = FineModel.fromJson(json);
      expect(fine.id, 1);
      expect(fine.amount, 500.0);
      expect(fine.amountPaid, 200.0);
      expect(fine.reason, 'Lost book');
      expect(fine.isPending, true);
      expect(fine.bookTitle, 'Physiology Manual');
      expect(fine.fineType, 'lost');
      expect(fine.paymentMethod, 'mpesa');
      expect(fine.mpesaReference, 'REF123');
      expect(fine.receiptNumber, 'RCT001');
      expect(fine.borrowRecordId, 5);
      expect(fine.waivedAmount, 50.0);
      expect(fine.balance, 300.0);
    });

    test('isPaid getter works for paid and settled', () {
      expect(FineModel(id: 1, amount: 100, reason: 'r', status: 'paid', assessedAt: DateTime.now()).isPaid, true);
      expect(FineModel(id: 1, amount: 100, reason: 'r', status: 'settled', assessedAt: DateTime.now()).isPaid, true);
      expect(FineModel(id: 1, amount: 100, reason: 'r', status: 'pending', assessedAt: DateTime.now()).isPaid, false);
    });

    test('copyWith works correctly', () {
      final original = FineModel(id: 1, amount: 100, reason: 'r', status: 'pending', assessedAt: DateTime.now());
      final copied = original.copyWith(status: 'paid', amountPaid: 100);
      expect(copied.status, 'paid');
      expect(copied.amountPaid, 100);
      expect(copied.amount, 100); // preserved
      expect(copied.reason, 'r'); // preserved
    });
  });

  group('PaymentResult', () {
    test('fromJson parses all fields', () {
      final json = {
        'data': {
          'payment_id': 1, 'receipt_number': 'RCT001',
          'mpesa_reference': 'REF123', 'amount': 200.0,
          'status': 'confirmed', 'paid_at': '2026-07-25T14:30:00.000Z',
          'message': 'Success', 'payment_method': 'mpesa',
        },
      };
      final result = PaymentResult.fromJson(json);
      expect(result.paymentId, 1);
      expect(result.receiptNumber, 'RCT001');
      expect(result.mpesaReference, 'REF123');
      expect(result.amount, 200.0);
      expect(result.isConfirmed, true);
      expect(result.isPending, false);
      expect(result.isFailed, false);
    });

    test('status getters work correctly', () {
      expect(PaymentResult(paymentId: 1, amount: 100, status: 'confirmed', paidAt: DateTime.now()).isConfirmed, true);
      expect(PaymentResult(paymentId: 1, amount: 100, status: 'completed', paidAt: DateTime.now()).isConfirmed, true);
      expect(PaymentResult(paymentId: 1, amount: 100, status: 'pending', paidAt: DateTime.now()).isPending, true);
      expect(PaymentResult(paymentId: 1, amount: 100, status: 'processing', paidAt: DateTime.now()).isPending, true);
      expect(PaymentResult(paymentId: 1, amount: 100, status: 'failed', paidAt: DateTime.now()).isFailed, true);
    });
  });

  group('FinesBloc', () {
    blocTest<FinesBloc, FinesState>(
      'LoadFines succeeds with correct data',
      build: () => FinesBloc(api: mockApi),
      act: (bloc) => bloc.add(const LoadFines()),
      expect: () => [
        isA<FinesLoading>(),
        isA<FinesLoaded>().having((s) => s.fines.length, 'fines count', 1),
      ],
    );

    blocTest<FinesBloc, FinesState>(
      'LoadFineDetail populates selectedFine',
      build: () => FinesBloc(api: mockApi),
      seed: () => const FinesLoaded(),
      act: (bloc) => bloc.add(const LoadFineDetail(1)),
      expect: () => [
        isA<FinesLoaded>().having((s) => s.selectedFine?.id, 'selected fine id', 1),
      ],
    );

    blocTest<FinesBloc, FinesState>(
      'PayFineWithMethod sends correct data',
      build: () => FinesBloc(api: mockApi),
      act: (bloc) => bloc.add(const PayFineWithMethod(
        fineId: 1, paymentMethod: 'mpesa', phoneNumber: '+254712345678',
      )),
      verify: (_) {
        verify(() => mockApi.post('/v1/fines/1/pay', data: {
          'payment_method': 'mpesa',
          'phone_number': '+254712345678',
        })).called(1);
      },
    );

    blocTest<FinesBloc, FinesState>(
      'LoadFines error emits FinesError',
      build: () {
        when(() => mockApi.get(any(), queryParameters: any(named: 'queryParameters')))
            .thenThrow(Exception('Network error'));
        return FinesBloc(api: mockApi);
      },
      act: (bloc) => bloc.add(const LoadFines()),
      expect: () => [
        isA<FinesLoading>(),
        isA<FinesError>(),
      ],
    );
  });

  group('FinesScreen', () {
    testWidgets('renders with tabs (Outstanding/Paid)', (tester) async {
      await tester.pumpWidget(MaterialApp(
        home: BlocProvider<FinesBloc>(
          create: (_) => FinesBloc(api: mockApi),
          child: const FinesScreen(),
        ),
      ));
      await tester.pump();
      expect(find.text('Outstanding'), findsOneWidget);
      expect(find.text('Paid'), findsOneWidget);
    });

    testWidgets('shows empty state when no fines', (tester) async {
      when(() => mockApi.get('/v1/fines', queryParameters: any(named: 'queryParameters')))
          .thenAnswer((_) async => Response(
            data: {'data': <dynamic>[]},
            statusCode: 200,
            requestOptions: RequestOptions(path: ''),
          ));
      await tester.pumpWidget(MaterialApp(
        home: BlocProvider<FinesBloc>(
          create: (_) => FinesBloc(api: mockApi),
          child: const FinesScreen(),
        ),
      ));
      await tester.pumpAndSettle();
      expect(find.text('No outstanding fines'), findsOneWidget);
    });

    testWidgets('shows pending fines', (tester) async {
      await tester.pumpWidget(MaterialApp(
        home: BlocProvider<FinesBloc>(
          create: (_) => FinesBloc(api: mockApi),
          child: const FinesScreen(),
        ),
      ));
      await tester.pumpAndSettle();
      expect(find.text('Overdue book'), findsOneWidget);
    });

    testWidgets('Pay All button visible when fines exist', (tester) async {
      await tester.pumpWidget(MaterialApp(
        home: BlocProvider<FinesBloc>(
          create: (_) => FinesBloc(api: mockApi),
          child: const FinesScreen(),
        ),
      ));
      await tester.pumpAndSettle();
      expect(find.text('Pay All Outstanding'), findsOneWidget);
    });
  });

  group('PaymentConfirmationScreen', () {
    testWidgets('shows success state', (tester) async {
      await tester.pumpWidget(MaterialApp(
        home: PaymentConfirmationScreen(
          receiptNumber: 'RCT001',
          mpesaReference: 'REF123',
          amount: 200.0,
          paidAt: DateTime(2026, 7, 25, 14, 30),
          status: 'confirmed',
          paymentMethod: 'mpesa',
        ),
      ));
      await tester.pump();
      expect(find.text('Payment Successful'), findsOneWidget);
      expect(find.text('RCT001'), findsOneWidget);
      expect(find.text('REF123'), findsOneWidget);
      expect(find.text('Done'), findsOneWidget);
      expect(find.text('Share Receipt'), findsOneWidget);
    });

    testWidgets('shows failure state', (tester) async {
      await tester.pumpWidget(MaterialApp(
        home: PaymentConfirmationScreen(
          amount: 200.0,
          status: 'failed',
        ),
      ));
      await tester.pump();
      expect(find.text('Payment Failed'), findsOneWidget);
    });
  });
}
