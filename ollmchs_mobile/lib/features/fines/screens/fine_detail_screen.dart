import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/fines_bloc.dart';
import '../models/fine_model.dart';
import '../../../core/widgets/skeleton.dart';

class FineDetailScreen extends StatefulWidget {
  final int fineId;
  const FineDetailScreen({super.key, required this.fineId});

  @override
  State<FineDetailScreen> createState() => _FineDetailScreenState();
}

class _FineDetailScreenState extends State<FineDetailScreen> {
  @override
  void initState() {
    super.initState();
    context.read<FinesBloc>().add(LoadFineDetail(widget.fineId));
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Fine Details')),
      body: BlocBuilder<FinesBloc, FinesState>(
        builder: (context, state) {
          if (state is FinesLoading && state is! FinesLoaded) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [
                  Skeleton(height: 200),
                  SizedBox(height: 16),
                  Skeleton(height: 48),
                  SizedBox(height: 8),
                  Skeleton(height: 48),
                ],
              ),
            );
          }
          if (state is FinesError) {
            return Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.error_outline, size: 48, color: theme.colorScheme.error),
                  const SizedBox(height: 8),
                  Text(state.error, textAlign: TextAlign.center),
                  const SizedBox(height: 16),
                  FilledButton.tonal(
                    onPressed: () => context.read<FinesBloc>().add(LoadFineDetail(widget.fineId)),
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }
          if (state is FinesLoaded && state.selectedFine != null) {
            final fine = state.selectedFine!;
            return _buildDetail(theme, fine);
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }

  Widget _buildDetail(ThemeData theme, FineModel fine) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Status banner
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: fine.isPaid
                  ? Colors.green.withValues(alpha: 0.1)
                  : theme.colorScheme.errorContainer,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              children: [
                Icon(
                  fine.isPaid ? Icons.check_circle : Icons.warning,
                  size: 48,
                  color: fine.isPaid ? Colors.green : theme.colorScheme.error,
                ),
                const SizedBox(height: 8),
                Text(
                  'KES ${fine.amount.toStringAsFixed(2)}',
                  style: theme.textTheme.headlineMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: fine.isPaid ? Colors.green : theme.colorScheme.error,
                  ),
                ),
                const SizedBox(height: 4),
                Chip(
                  label: Text(fine.status.toUpperCase()),
                  backgroundColor: fine.isPaid
                      ? Colors.green.withValues(alpha: 0.1)
                      : theme.colorScheme.errorContainer,
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),

          // Fine details
          Text(
            'Fine Details',
            style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          Card(
            child: Column(
              children: [
                _detailRow(theme, 'Reason', fine.reason),
                if (fine.fineType != null) _detailRow(theme, 'Type', _formatFineType(fine.fineType!)),
                if (fine.bookTitle != null) _detailRow(theme, 'Book', fine.bookTitle!),
                _detailRow(theme, 'Assessed', DateFormat('MMM d, y').format(fine.assessedAt)),
                _detailRow(theme, 'Amount', 'KES ${fine.amount.toStringAsFixed(2)}'),
                if (fine.amountPaid != null && fine.amountPaid! > 0)
                  _detailRow(theme, 'Paid', 'KES ${fine.amountPaid!.toStringAsFixed(2)}'),
                if (fine.balance > 0 && fine.amountPaid != null && fine.amountPaid! > 0)
                  _detailRow(theme, 'Balance', 'KES ${fine.balance.toStringAsFixed(2)}'),
              ],
            ),
          ),

          // Payment details (if paid)
          if (fine.isPaid) ...[
            const SizedBox(height: 24),
            Text(
              'Payment Details',
              style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            Card(
              child: Column(
                children: [
                  if (fine.paidAt != null)
                    _detailRow(theme, 'Paid On', DateFormat('MMM d, y \'at\' h:mm a').format(fine.paidAt!)),
                  if (fine.paymentMethod != null) _detailRow(theme, 'Method', _formatPaymentMethod(fine.paymentMethod!)),
                  if (fine.mpesaReference != null) _detailRow(theme, 'M-Pesa Ref', fine.mpesaReference!),
                  if (fine.receiptNumber != null) _detailRow(theme, 'Receipt', fine.receiptNumber!),
                ],
              ),
            ),
          ],

          // Pay button (if pending)
          if (fine.isPending) ...[
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                onPressed: () => context.pushNamed(
                  'fine-payment',
                  pathParameters: {'id': '${fine.id}'},
                ),
                icon: const Icon(Icons.payment),
                label: Text('Pay KES ${fine.amount.toStringAsFixed(2)}'),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _detailRow(ThemeData theme, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(color: theme.colorScheme.onSurfaceVariant)),
          Flexible(
            child: Text(
              value,
              style: const TextStyle(fontWeight: FontWeight.w500),
              textAlign: TextAlign.end,
            ),
          ),
        ],
      ),
    );
  }

  String _formatFineType(String type) {
    switch (type) {
      case 'overdue': return 'Overdue';
      case 'lost': return 'Lost Book';
      case 'damage': return 'Damage';
      default: return type;
    }
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
