import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:share_plus/share_plus.dart';

class PaymentConfirmationScreen extends StatelessWidget {
  final String? receiptNumber;
  final String? mpesaReference;
  final double amount;
  final DateTime? paidAt;
  final String status;
  final String? paymentMethod;

  const PaymentConfirmationScreen({
    super.key,
    this.receiptNumber,
    this.mpesaReference,
    required this.amount,
    this.paidAt,
    this.status = 'confirmed',
    this.paymentMethod,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isConfirmed = status == 'confirmed' || status == 'completed';

    return Scaffold(
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            children: [
              const SizedBox(height: 32),
              // Status icon
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: isConfirmed
                      ? Colors.green.withValues(alpha: 0.1)
                      : theme.colorScheme.errorContainer,
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  isConfirmed ? Icons.check_circle : Icons.error,
                  size: 64,
                  color: isConfirmed ? Colors.green : theme.colorScheme.error,
                ),
              ),
              const SizedBox(height: 24),
              Text(
                isConfirmed ? 'Payment Successful' : 'Payment Failed',
                style: theme.textTheme.headlineSmall?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                isConfirmed
                    ? 'Your payment has been processed successfully.'
                    : 'There was an issue processing your payment.',
                textAlign: TextAlign.center,
                style: theme.textTheme.bodyMedium?.copyWith(
                  color: theme.colorScheme.onSurfaceVariant,
                ),
              ),
              const SizedBox(height: 32),

              // Receipt card
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    children: [
                      _receiptRow(theme, 'Amount', 'KES ${amount.toStringAsFixed(2)}'),
                      const Divider(),
                      if (receiptNumber != null)
                        _receiptRow(theme, 'Receipt #', receiptNumber!),
                      if (mpesaReference != null) ...[
                        const Divider(),
                        _receiptRow(theme, 'M-Pesa Ref', mpesaReference!),
                      ],
                      if (paymentMethod != null) ...[
                        const Divider(),
                        _receiptRow(theme, 'Method', _formatPaymentMethod(paymentMethod!)),
                      ],
                      if (paidAt != null) ...[
                        const Divider(),
                        _receiptRow(theme, 'Date', DateFormat('MMM d, y \'at\' h:mm a').format(paidAt!)),
                      ],
                      const Divider(),
                      _receiptRow(theme, 'Status', status.toUpperCase()),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 32),

              // Action buttons
              SizedBox(
                width: double.infinity,
                child: FilledButton(
                  onPressed: () => context.goNamed('fines'),
                  child: const Text('Done'),
                ),
              ),
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: () {
                    final text = StringBuffer();
                    text.writeln('OLLMCHS Library Payment Receipt');
                    text.writeln('--------------------------------');
                    text.writeln('Amount: KES ${amount.toStringAsFixed(2)}');
                    if (receiptNumber != null) text.writeln('Receipt: $receiptNumber');
                    if (mpesaReference != null) text.writeln('M-Pesa Ref: $mpesaReference');
                    if (paymentMethod != null) text.writeln('Method: ${_formatPaymentMethod(paymentMethod!)}');
                    if (paidAt != null) text.writeln('Date: ${DateFormat('MMM d, y \'at\' h:mm a').format(paidAt!)}');
                    text.writeln('Status: ${status.toUpperCase()}');
                    SharePlus.instance.share(ShareParams(text: text.toString(), subject: 'Payment Receipt'));
                  },
                  icon: const Icon(Icons.share),
                  label: const Text('Share Receipt'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _receiptRow(ThemeData theme, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(color: theme.colorScheme.onSurfaceVariant)),
          Flexible(
            child: Text(
              value,
              style: const TextStyle(fontWeight: FontWeight.w600),
              textAlign: TextAlign.end,
            ),
          ),
        ],
      ),
    );
  }

  String _formatPaymentMethod(String method) {
    switch (method) {
      case 'mpesa': return 'M-Pesa';
      case 'stripe': return 'Stripe';
      case 'cash': return 'Cash';
      default: return method;
    }
  }
}
