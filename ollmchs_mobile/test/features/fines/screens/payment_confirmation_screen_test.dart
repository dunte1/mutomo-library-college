import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:ollmchs_library/features/fines/screens/payment_confirmation_screen.dart';

void main() {
  Widget buildScreen({
    String? receiptNumber,
    String? mpesaReference,
    double amount = 200.0,
    String status = 'confirmed',
  }) {
    return MaterialApp(
      home: PaymentConfirmationScreen(
        receiptNumber: receiptNumber,
        mpesaReference: mpesaReference,
        amount: amount,
        paidAt: DateTime(2026, 7, 25, 14, 30),
        status: status,
        paymentMethod: 'mpesa',
      ),
    );
  }

  group('PaymentConfirmationScreen', () {
    testWidgets('shows success state for confirmed payment', (tester) async {
      await tester.pumpWidget(buildScreen(
        receiptNumber: 'RCT-001',
        mpesaReference: 'QHK7Y8Z5LP',
      ));
      await tester.pump();

      expect(find.text('Payment Successful'), findsOneWidget);
      expect(find.text('KES 200.00'), findsWidgets);
      expect(find.text('RCT-001'), findsOneWidget);
      expect(find.text('QHK7Y8Z5LP'), findsOneWidget);
      expect(find.byIcon(Icons.check_circle), findsOneWidget);
    });

    testWidgets('shows failure state for failed payment', (tester) async {
      await tester.pumpWidget(buildScreen(status: 'failed'));
      await tester.pump();

      expect(find.text('Payment Failed'), findsOneWidget);
      expect(find.byIcon(Icons.error), findsOneWidget);
    });

    testWidgets('shows Done button', (tester) async {
      await tester.pumpWidget(buildScreen());
      await tester.pump();

      expect(find.text('Done'), findsOneWidget);
    });

    testWidgets('shows Share Receipt button', (tester) async {
      await tester.pumpWidget(buildScreen());
      await tester.pump();

      expect(find.text('Share Receipt'), findsOneWidget);
    });
  });
}
