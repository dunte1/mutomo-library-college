import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mocktail/mocktail.dart';
import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/features/scanner/screens/scanner_screen.dart';

class MockApiClient extends Mock implements ApiClient {}

void main() {
  late MockApiClient mockApi;

  setUp(() {
    mockApi = MockApiClient();
  });

  Widget buildScreen() {
    return MaterialApp(
      home: RepositoryProvider<ApiClient>.value(
        value: mockApi,
        child: const ScannerScreen(),
      ),
    );
  }

  group('ScannerScreen', () {
    testWidgets('renders scanner with instructions', (tester) async {
      await tester.pumpWidget(buildScreen());
      await tester.pump();
      expect(find.text('Point your camera at a QR code or barcode'), findsOneWidget);
    });

    testWidgets('has torch toggle button', (tester) async {
      await tester.pumpWidget(buildScreen());
      await tester.pump();
      expect(find.byIcon(Icons.flash_off), findsOneWidget);
    });

    testWidgets('has app bar with title', (tester) async {
      await tester.pumpWidget(buildScreen());
      await tester.pump();
      expect(find.text('Scan QR / Barcode'), findsOneWidget);
    });
  });
}
