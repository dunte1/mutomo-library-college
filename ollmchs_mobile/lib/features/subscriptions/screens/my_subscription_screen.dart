import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import '../bloc/subscriptions_bloc.dart';

class MySubscriptionScreen extends StatefulWidget {
  const MySubscriptionScreen({super.key});

  @override
  State<MySubscriptionScreen> createState() => _MySubscriptionScreenState();
}

class _MySubscriptionScreenState extends State<MySubscriptionScreen> {
  @override
  void initState() {
    super.initState();
    context.read<SubscriptionsBloc>().add(const LoadMySubscription());
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('My Subscription')),
      body: BlocConsumer<SubscriptionsBloc, SubscriptionsState>(
        listener: (context, state) {
          if (state is SubscriptionsLoaded && state.message != null) {
            ScaffoldMessenger.of(
              context,
            ).showSnackBar(SnackBar(content: Text(state.message!)));
          }
        },
        builder: (context, state) {
          if (state is SubscriptionsLoading) {
            return const Center(child: CircularProgressIndicator());
          }
          if (state is SubscriptionsLoaded) {
            final sub = state.mySubscription;
            if (sub == null) {
              return Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.card_membership_outlined,
                      size: 64,
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'No active subscription',
                      style: theme.textTheme.titleMedium,
                    ),
                    const SizedBox(height: 8),
                    FilledButton(
                      onPressed: () => context.read<SubscriptionsBloc>().add(
                        const LoadSubscriptionPlans(),
                      ),
                      child: const Text('Browse Plans'),
                    ),
                  ],
                ),
              );
            }
            return Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        children: [
                          Icon(
                            Icons.verified,
                            size: 64,
                            color: sub.isActive ? Colors.green : Colors.orange,
                          ),
                          const SizedBox(height: 12),
                          Text(
                            sub.planName,
                            style: theme.textTheme.headlineSmall?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Chip(
                            label: Text(
                              sub.status.toUpperCase(),
                              style: const TextStyle(fontSize: 12),
                            ),
                            backgroundColor: sub.isActive
                                ? Colors.green.withValues(alpha: 0.1)
                                : Colors.orange.withValues(alpha: 0.1),
                          ),
                          const SizedBox(height: 16),
                          _infoRow(
                            'Started',
                            DateFormat('MMM d, y').format(sub.startAt),
                          ),
                          if (sub.endAt != null)
                            _infoRow(
                              'Expires',
                              DateFormat('MMM d, y').format(sub.endAt!),
                            ),
                          _infoRow('Auto-Renew', sub.autoRenew ? 'Yes' : 'No'),
                          if (sub.paymentMethod != null)
                            _infoRow('Payment', sub.paymentMethod!),
                          if (sub.isExpiringSoon)
                            Padding(
                              padding: const EdgeInsets.only(top: 12),
                              child: Text(
                                'Your subscription is expiring soon!',
                                style: TextStyle(
                                  color: theme.colorScheme.error,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                        ],
                      ),
                    ),
                  ),
                  const Spacer(),
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      onPressed: () => context.read<SubscriptionsBloc>().add(
                        CancelSubscription(sub.id),
                      ),
                      icon: const Icon(Icons.cancel_outlined),
                      label: const Text('Cancel Subscription'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: theme.colorScheme.error,
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

  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontWeight: FontWeight.w500)),
          Text(value),
        ],
      ),
    );
  }
}
