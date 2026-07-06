import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/finance_bloc.dart';
import '../../../core/widgets/skeleton.dart';

class PaymentHistoryScreen extends StatefulWidget {
  const PaymentHistoryScreen({super.key});

  @override
  State<PaymentHistoryScreen> createState() => _PaymentHistoryScreenState();
}

class _PaymentHistoryScreenState extends State<PaymentHistoryScreen> {
  @override
  void initState() {
    super.initState();
    context.read<FinanceBloc>().add(const LoadPaymentHistory());
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Payments & Fines')),
      body: BlocConsumer<FinanceBloc, FinanceState>(
        listener: (context, state) {
          if (state is FinanceLoaded && state.message != null) {
            ScaffoldMessenger.of(
              context,
            ).showSnackBar(SnackBar(content: Text(state.message!)));
          }
        },
        builder: (context, state) {
          if (state is FinanceLoading && state is! FinanceLoaded) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [
                  SkeletonCard(height: 80),
                  SkeletonCard(height: 80),
                  SkeletonCard(height: 80),
                ],
              ),
            );
          }
          if (state is FinanceError && state is! FinanceLoaded) {
            return Center(child: Text(state.error));
          }
          if (state is FinanceLoaded) {
            return RefreshIndicator(
              onRefresh: () async =>
                  context.read<FinanceBloc>().add(const LoadPaymentHistory()),
              child: ListView(
                padding: const EdgeInsets.all(12),
                children: [
                  // Unpaid fines section
                  if (state.unpaidFines.isNotEmpty) ...[
                    Text(
                      'Unpaid Fines',
                      style: theme.textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    ...state.unpaidFines.map(
                      (fine) => Card(
                        color: theme.colorScheme.errorContainer.withValues(
                          alpha: 0.3,
                        ),
                        child: ListTile(
                          leading: CircleAvatar(
                            backgroundColor: theme.colorScheme.errorContainer,
                            child: Icon(
                              Icons.warning,
                              color: theme.colorScheme.error,
                            ),
                          ),
                          title: Text(
                            fine.reason,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          subtitle: Text(
                            'KES ${NumberFormat('#,##0').format(fine.amount.toInt())}',
                          ),
                          trailing: FilledButton.tonal(
                            onPressed: () => context.read<FinanceBloc>().add(
                              PayFine(fine.id),
                            ),
                            child: const Text('Pay'),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],
                  // Payment history
                  Text(
                    'Payment History',
                    style: theme.textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  if (state.payments.isEmpty)
                    Center(
                      child: Padding(
                        padding: const EdgeInsets.all(32),
                        child: Column(
                          children: [
                            Icon(
                              Icons.receipt_long_outlined,
                              size: 48,
                              color: theme.colorScheme.onSurfaceVariant,
                            ),
                            const SizedBox(height: 12),
                            Text(
                              'No payment history',
                              style: theme.textTheme.bodyLarge,
                            ),
                          ],
                        ),
                      ),
                    ),
                  ...state.payments.map(
                    (payment) => Card(
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: payment.isSuccessful
                              ? Colors.green.withValues(alpha: 0.1)
                              : Colors.orange.withValues(alpha: 0.1),
                          child: Icon(
                            payment.isSuccessful
                                ? Icons.check_circle
                                : Icons.pending,
                            color: payment.isSuccessful
                                ? Colors.green
                                : Colors.orange,
                          ),
                        ),
                        title: Text(
                          payment.description,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '${payment.currency} ${NumberFormat('#,##0').format(payment.amount.toInt())} — ${payment.paymentMethod}',
                            ),
                            Text(
                              DateFormat('MMM d, y').format(payment.createdAt),
                              style: theme.textTheme.bodySmall,
                            ),
                          ],
                        ),
                        trailing: Chip(
                          label: Text(
                            payment.status,
                            style: const TextStyle(fontSize: 10),
                          ),
                          visualDensity: VisualDensity.compact,
                        ),
                        isThreeLine: true,
                        onTap: payment.receiptUrl != null
                            ? () => context.pushNamed(
                                'receipt-detail',
                                pathParameters: {'id': '${payment.id}'},
                              )
                            : null,
                      ),
                    ),
                  ),
                ],
              ),
            );
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }
}
