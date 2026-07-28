import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mocktail/mocktail.dart';
import 'package:dio/dio.dart';
import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/features/subscriptions/bloc/subscriptions_bloc.dart';
import 'package:ollmchs_library/features/subscriptions/screens/my_subscription_screen.dart';

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
                  'plan_id': 1,
                  'plan': {'id': 1, 'name': 'Student Monthly'},
                  'status': 'active',
                  'start_at': '2026-07-01T00:00:00.000Z',
                  'end_at': '2026-08-01T00:00:00.000Z',
                  'auto_renew': true,
                  'payment_method': 'mpesa',
                },
              },
              statusCode: 200,
              requestOptions: RequestOptions(path: ''),
            ));
  });

  Widget buildScreen() {
    return MaterialApp(
      home: BlocProvider<SubscriptionsBloc>(
        create: (_) => SubscriptionsBloc(api: mockApi),
        child: const MySubscriptionScreen(),
      ),
    );
  }

  group('MySubscriptionScreen', () {
    testWidgets('renders without crashing', (tester) async {
      await tester.pumpWidget(buildScreen());
      await tester.pumpAndSettle();
      // Screen should render without errors
      expect(find.byType(MySubscriptionScreen), findsOneWidget);
    });

    testWidgets('shows subscription details when loaded', (tester) async {
      await tester.pumpWidget(buildScreen());
      await tester.pumpAndSettle();

      // The screen loads subscription from API and should show details
      expect(find.text('Student Monthly'), findsOneWidget);
    });
  });
}
