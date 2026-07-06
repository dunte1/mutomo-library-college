import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../bloc/subscriptions_bloc.dart';

class SubscriptionCheckoutScreen extends StatefulWidget {
  final int planId;
  final String planName;
  final String planPrice;
  final String? planCurrency;
  final String? planDuration;
  final int? planDurationDays;
  const SubscriptionCheckoutScreen({
    super.key,
    required this.planId,
    required this.planName,
    required this.planPrice,
    this.planCurrency,
    this.planDuration,
    this.planDurationDays,
  });

  @override
  State<SubscriptionCheckoutScreen> createState() =>
      _SubscriptionCheckoutScreenState();
}

class _SubscriptionCheckoutScreenState
    extends State<SubscriptionCheckoutScreen> {
  String _paymentMethod = 'mpesa';
  final _phoneController = TextEditingController();
  bool _submitting = false;

  @override
  void dispose() {
    _phoneController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Checkout')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Plan Summary
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Plan Summary',
                    style: theme.textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              widget.planName,
                              style: theme.textTheme.titleLarge?.copyWith(
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            if (widget.planDuration != null)
                              Text(
                                widget.planDuration!,
                                style: theme.textTheme.bodySmall?.copyWith(
                                  color: theme.colorScheme.onSurfaceVariant,
                                ),
                              ),
                          ],
                        ),
                      ),
                      Text(
                        '${widget.planCurrency ?? 'KES'} ${widget.planPrice}',
                        style: theme.textTheme.headlineSmall?.copyWith(
                          fontWeight: FontWeight.bold,
                          color: theme.colorScheme.primary,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 24),

          // Payment Method
          Text(
            'Payment Method',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _PaymentMethodCard(
                  icon: Icons.phone_android,
                  label: 'M-Pesa',
                  selected: _paymentMethod == 'mpesa',
                  onTap: () => setState(() => _paymentMethod = 'mpesa'),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _PaymentMethodCard(
                  icon: Icons.credit_card,
                  label: 'Card',
                  selected: _paymentMethod == 'stripe',
                  onTap: () => setState(() => _paymentMethod = 'stripe'),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),

          // M-Pesa: phone input
          if (_paymentMethod == 'mpesa') ...[
            TextField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(
                labelText: 'M-Pesa Phone Number',
                hintText: 'e.g. 0712345678',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.phone),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'You will receive an STK push prompt on your phone to complete payment.',
              style: theme.textTheme.bodySmall?.copyWith(
                color: theme.colorScheme.onSurfaceVariant,
              ),
            ),
          ],

          // Stripe: info
          if (_paymentMethod == 'stripe')
            Card(
              color: theme.colorScheme.tertiaryContainer,
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Row(
                  children: [
                    Icon(
                      Icons.info_outline,
                      color: theme.colorScheme.onTertiaryContainer,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'You will be redirected to Stripe\'s secure checkout page to complete payment with your card.',
                        style: theme.textTheme.bodySmall?.copyWith(
                          color: theme.colorScheme.onTertiaryContainer,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

          const SizedBox(height: 32),

          // Submit
          SizedBox(
            width: double.infinity,
            child: FilledButton(
              onPressed: _submitting ? null : _onCheckout,
              child: _submitting
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : Text(
                      'Pay ${widget.planCurrency ?? 'KES'} ${widget.planPrice}',
                    ),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _onCheckout() async {
    if (_paymentMethod == 'mpesa' && _phoneController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter your M-Pesa phone number')),
      );
      return;
    }
    setState(() => _submitting = true);
    context.read<SubscriptionsBloc>().add(
      SubscribeToPlan(
        planId: widget.planId,
        paymentMethod: _paymentMethod,
        phone: _paymentMethod == 'mpesa' ? _phoneController.text.trim() : null,
      ),
    );
  }
}

class _PaymentMethodCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _PaymentMethodCard({
    required this.icon,
    required this.label,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 20),
        decoration: BoxDecoration(
          border: Border.all(
            color: selected ? theme.colorScheme.primary : theme.dividerColor,
            width: selected ? 2 : 1,
          ),
          borderRadius: BorderRadius.circular(12),
          color: selected
              ? theme.colorScheme.primaryContainer.withValues(alpha: 0.3)
              : null,
        ),
        child: Column(
          children: [
            Icon(
              icon,
              color: selected
                  ? theme.colorScheme.primary
                  : theme.colorScheme.onSurfaceVariant,
              size: 32,
            ),
            const SizedBox(height: 8),
            Text(
              label,
              style: TextStyle(
                fontWeight: selected ? FontWeight.bold : FontWeight.normal,
                color: selected ? theme.colorScheme.primary : null,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
