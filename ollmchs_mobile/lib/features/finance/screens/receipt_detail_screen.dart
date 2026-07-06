import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import '../bloc/finance_bloc.dart';

class ReceiptDetailScreen extends StatefulWidget {
  final int paymentId;
  const ReceiptDetailScreen({super.key, required this.paymentId});

  @override
  State<ReceiptDetailScreen> createState() => _ReceiptDetailScreenState();
}

class _ReceiptDetailScreenState extends State<ReceiptDetailScreen> {
  @override
  void initState() {
    super.initState();
    context.read<FinanceBloc>().add(LoadReceiptDetail(widget.paymentId));
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Receipt')),
      body: BlocBuilder<FinanceBloc, FinanceState>(
        builder: (context, state) {
          if (state is FinanceLoading && state is! FinanceLoaded) {
            return const Center(child: CircularProgressIndicator());
          }
          if (state is FinanceError && state is! FinanceLoaded) {
            return Center(child: Text(state.error));
          }
          if (state is FinanceLoaded && state.selectedReceipt != null) {
            final payment = state.selectedReceipt!;
            return SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        children: [
                          Icon(
                            payment.isSuccessful
                                ? Icons.check_circle
                                : Icons.pending,
                            size: 64,
                            color: payment.isSuccessful
                                ? Colors.green
                                : Colors.orange,
                          ),
                          const SizedBox(height: 12),
                          Text(
                            payment.isSuccessful
                                ? 'Payment Successful'
                                : 'Payment Pending',
                            style: theme.textTheme.titleLarge?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 24),
                          Text(
                            '${payment.currency} ${NumberFormat('#,##0.00').format(payment.amount)}',
                            style: theme.textTheme.headlineLarge?.copyWith(
                              fontWeight: FontWeight.bold,
                              color: theme.colorScheme.primary,
                            ),
                          ),
                          const SizedBox(height: 24),
                          _receiptRow(
                            theme,
                            'Description',
                            payment.description,
                          ),
                          _receiptRow(
                            theme,
                            'Payment Method',
                            payment.paymentMethod,
                          ),
                          _receiptRow(
                            theme,
                            'Status',
                            payment.status.toUpperCase(),
                          ),
                          _receiptRow(
                            theme,
                            'Date',
                            DateFormat(
                              'MMMM d, y h:mm a',
                            ).format(payment.createdAt),
                          ),
                          if (payment.reference != null)
                            _receiptRow(theme, 'Reference', payment.reference!),
                        ],
                      ),
                    ),
                  ),
                  if (payment.receiptUrl != null) ...[
                    const SizedBox(height: 16),
                    SizedBox(
                      width: double.infinity,
                      child: FilledButton.icon(
                        onPressed: () async {
                          final uri = Uri.parse(payment.receiptUrl!);
                          if (await canLaunchUrl(uri)) await launchUrl(uri);
                        },
                        icon: const Icon(Icons.download),
                        label: const Text('Download Receipt'),
                      ),
                    ),
                  ],
                ],
              ),
            );
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }

  Widget _receiptRow(ThemeData theme, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: theme.textTheme.bodyMedium?.copyWith(
              color: theme.colorScheme.onSurfaceVariant,
            ),
          ),
          Flexible(
            child: Text(
              value,
              textAlign: TextAlign.end,
              style: const TextStyle(fontWeight: FontWeight.w500),
            ),
          ),
        ],
      ),
    );
  }
}
