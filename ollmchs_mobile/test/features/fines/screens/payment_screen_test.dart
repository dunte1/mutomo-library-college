import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mocktail/mocktail.dart';
import 'package:dio/dio.dart';
import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/features/fines/bloc/fines_bloc.dart';
import 'package:ollmchs_library/features/fines/screens/payment_screen.dart';

class MockApiClient extends Mock implements ApiClient {}

void main() {
  late MockApiClient mockApi;

  setUp(() {
    mockApi = MockApiClient();
    when(() => mockApi.get(any(), queryParameters: any(named: 'queryParameters')))
        .thenAnswer((_) async => Response(
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
            ));
  });

  Widget buildScreen() {
    return MaterialApp(
      home: BlocProvider<FinesBloc>(
        create: (_) => FinesBloc(api: mockApi),
        child: const PaymentScreen(fineId: 1),
      ),
    );
  }

  group('PaymentScreen', () {
    testWidgets('renders without crashing', (tester) async {
      await tester.pumpWidget(buildScreen());
      await tester.pumpAndSettle();
      expect(find.byType(PaymentScreen), findsOneWidget);
    });

    testWidgets('shows payment method selector', (tester) async {
      await tester.pumpWidget(buildScreen());
      await tester.pumpAndSettle();

      expect(find.text('M-Pesa'), findsOneWidget);
      expect(find.text('Card'), findsOneWidget);
      expect(find.byType(SegmentedButton<String>), findsOneWidget);
    });

    testWidgets('shows pay button', (tester) async {
      await tester.pumpWidget(buildScreen());
      await tester.pumpAndSettle();

      expect(find.textContaining('Pay KES'), findsOneWidget);
    });
  });
}
