import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/fines_bloc.dart';
import '../models/fine_model.dart';
import '../../../core/widgets/skeleton.dart';

class PaymentScreen extends StatefulWidget {
  final int fineId;
  const PaymentScreen({super.key, required this.fineId});

  @override
  State<PaymentScreen> createState() => _PaymentScreenState();
}

class _PaymentScreenState extends State<PaymentScreen> {
  String _paymentMethod = 'mpesa';
  final _phoneController = TextEditingController();
  bool _isProcessing = false;

  @override
  void initState() {
    super.initState();
    context.read<FinesBloc>().add(LoadFineDetail(widget.fineId));
  }

  @override
  void dispose() {
    _phoneController.dispose();
    super.dispose();
  }

  void _initiatePayment(FineModel fine) {
    if (_paymentMethod == 'mpesa' && _phoneController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter your M-Pesa phone number')),
      );
      return;
    }

    setState(() => _isProcessing = true);
    context.read<FinesBloc>().add(PayFineWithMethod(
      fineId: fine.id,
      paymentMethod: _paymentMethod,
      phoneNumber: _paymentMethod == 'mpesa' ? _phoneController.text.trim() : null,
    ));
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Payment')),
      body: BlocConsumer<FinesBloc, FinesState>(
        listener: (context, state) {
          if (state is FinesLoaded && state.lastPaymentResult != null) {
            final result = state.lastPaymentResult!;
            if (result.isConfirmed) {
              setState(() => _isProcessing = false);
              context.pushReplacementNamed(
                'payment-confirmation',
                extra: {
                  'receiptNumber': result.receiptNumber,
                  'mpesaReference': result.mpesaReference,
                  'amount': result.amount,
                  'paidAt': result.paidAt,
                  'status': result.status,
                  'paymentMethod': result.paymentMethod,
                },
              );
            } else if (result.isFailed) {
              setState(() => _isProcessing = false);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(result.message ?? 'Payment failed'),
                  backgroundColor: theme.colorScheme.error,
                ),
              );
            }
          }
          if (state is FinesError) {
            setState(() => _isProcessing = false);
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(state.error),
                backgroundColor: theme.colorScheme.error,
              ),
            );
          }
        },
        builder: (context, state) {
          if (state is FinesLoading && state is! FinesLoaded) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [
                  Skeleton(height: 120),
                  SizedBox(height: 16),
                  Skeleton(height: 200),
                ],
              ),
            );
          }
          if (state is FinesLoaded && state.selectedFine != null) {
            final fine = state.selectedFine!;
            return _buildPaymentForm(theme, fine);
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }

  Widget _buildPaymentForm(ThemeData theme, FineModel fine) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Fine summary
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    fine.reason,
                    style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
                  ),
                  if (fine.bookTitle != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      fine.bookTitle!,
                      style: theme.textTheme.bodyMedium?.copyWith(
                        color: theme.colorScheme.onSurfaceVariant,
                      ),
                    ),
                  ],
                  const SizedBox(height: 12),
                  Text(
                    'KES ${fine.amount.toStringAsFixed(2)}',
                    style: theme.textTheme.headlineSmall?.copyWith(
                      fontWeight: FontWeight.bold,
                      color: theme.colorScheme.primary,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Assessed: ${DateFormat('MMM d, y').format(fine.assessedAt)}',
                    style: theme.textTheme.bodySmall?.copyWith(
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 24),

          // Payment method
          Text(
            'Payment Method',
            style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          SegmentedButton<String>(
            segments: const [
              ButtonSegment(
                value: 'mpesa',
                label: Text('M-Pesa'),
                icon: Icon(Icons.phone_android),
              ),
              ButtonSegment(
                value: 'stripe',
                label: Text('Card'),
                icon: Icon(Icons.credit_card),
              ),
            ],
            selected: {_paymentMethod},
            onSelectionChanged: (selected) {
              setState(() => _paymentMethod = selected.first);
            },
          ),
          const SizedBox(height: 24),

          // M-Pesa phone input
          if (_paymentMethod == 'mpesa') ...[
            Text(
              'M-Pesa Phone Number',
              style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              decoration: InputDecoration(
                hintText: '+254 7XX XXX XXX',
                prefixIcon: const Icon(Icons.phone),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'You will receive an M-Pesa STK push prompt on this number.',
              style: theme.textTheme.bodySmall?.copyWith(
                color: theme.colorScheme.onSurfaceVariant,
              ),
            ),
          ],

          // Stripe placeholder
          if (_paymentMethod == 'stripe') ...[
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    Icon(Icons.credit_card, size: 48, color: theme.colorScheme.primary),
                    const SizedBox(height: 8),
                    Text(
                      'Card payment via Stripe',
                      style: theme.textTheme.titleMedium,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'You will be redirected to Stripe secure checkout.',
                      style: theme.textTheme.bodySmall?.copyWith(
                        color: theme.colorScheme.onSurfaceVariant,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              ),
            ),
          ],
          const SizedBox(height: 32),

          // Pay button
          SizedBox(
            width: double.infinity,
            child: FilledButton.icon(
              onPressed: _isProcessing ? null : () => _initiatePayment(fine),
              icon: _isProcessing
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                    )
                  : const Icon(Icons.payment),
              label: Text(_isProcessing ? 'Processing...' : 'Pay KES ${fine.amount.toStringAsFixed(2)}'),
            ),
          ),

          // Processing overlay
          if (_isProcessing) ...[
            const SizedBox(height: 24),
            Card(
              color: theme.colorScheme.secondaryContainer,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    const CircularProgressIndicator(),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            'Waiting for confirmation...',
                            style: theme.textTheme.titleSmall?.copyWith(fontWeight: FontWeight.bold),
                          ),
                          Text(
                            _paymentMethod == 'mpesa'
                                ? 'Check your phone for the M-Pesa prompt.'
                                : 'Redirecting to Stripe checkout...',
                            style: theme.textTheme.bodySmall,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
