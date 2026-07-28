import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mocktail/mocktail.dart';
import 'package:dio/dio.dart';
import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/features/scanner/screens/scanner_screen.dart';

class MockApiClient extends Mock implements ApiClient {}

void main() {
  late MockApiClient mockApi;

  setUp(() {
    mockApi = MockApiClient();
    when(() => mockApi.get(any(), queryParameters: any(named: 'queryParameters')))
        .thenAnswer((_) async => Response(
              data: {'data': {'status': 'active', 'full_name': 'John Student'}},
              statusCode: 200,
              requestOptions: RequestOptions(path: ''),
            ));
  });

  group('ScannerScreen', () {
    testWidgets('renders with title', (tester) async {
      await tester.pumpWidget(MaterialApp(
        home: RepositoryProvider<ApiClient>.value(
          value: mockApi,
          child: const ScannerScreen(),
        ),
      ));
      await tester.pump();
      expect(find.text('Scan QR / Barcode'), findsOneWidget);
    });

    testWidgets('shows scan instructions', (tester) async {
      await tester.pumpWidget(MaterialApp(
        home: RepositoryProvider<ApiClient>.value(
          value: mockApi,
          child: const ScannerScreen(),
        ),
      ));
      await tester.pump();
      expect(find.text('Point your camera at a QR code or barcode'), findsOneWidget);
    });

    testWidgets('has torch toggle button', (tester) async {
      await tester.pumpWidget(MaterialApp(
        home: RepositoryProvider<ApiClient>.value(
          value: mockApi,
          child: const ScannerScreen(),
        ),
      ));
      await tester.pump();
      expect(find.byIcon(Icons.flash_off), findsOneWidget);
    });
  });
}
