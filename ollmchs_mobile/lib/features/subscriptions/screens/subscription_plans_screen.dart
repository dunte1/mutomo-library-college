import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/subscriptions_bloc.dart';
import '../../../core/widgets/skeleton.dart';

class SubscriptionPlansScreen extends StatefulWidget {
  const SubscriptionPlansScreen({super.key});

  @override
  State<SubscriptionPlansScreen> createState() =>
      _SubscriptionPlansScreenState();
}

class _SubscriptionPlansScreenState extends State<SubscriptionPlansScreen> {
  @override
  void initState() {
    super.initState();
    context.read<SubscriptionsBloc>().add(const LoadSubscriptionPlans());
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Subscription Plans')),
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
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [
                  SkeletonCard(height: 160),
                  SkeletonCard(height: 160),
                  SkeletonCard(height: 160),
                ],
              ),
            );
          }
          if (state is SubscriptionsError) {
            return Center(child: Text(state.error));
          }
          if (state is SubscriptionsLoaded) {
            if (state.plans.isEmpty) {
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
                      'No plans available',
                      style: theme.textTheme.titleMedium,
                    ),
                  ],
                ),
              );
            }
            return ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: state.plans.length,
              itemBuilder: (_, i) {
                final plan = state.plans[i];
                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                plan.name,
                                style: theme.textTheme.titleLarge?.copyWith(
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                            if (plan.isPopular)
                              Chip(
                                label: const Text(
                                  'Popular',
                                  style: TextStyle(
                                    fontSize: 10,
                                    color: Colors.white,
                                  ),
                                ),
                                backgroundColor: theme.colorScheme.primary,
                                visualDensity: VisualDensity.compact,
                              ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(
                          '${plan.currency ?? 'KES'} ${NumberFormat('#,##0').format(plan.price)}',
                          style: theme.textTheme.headlineMedium?.copyWith(
                            fontWeight: FontWeight.bold,
                            color: theme.colorScheme.primary,
                          ),
                        ),
                        Text(
                          '/ ${plan.durationDays} days',
                          style: theme.textTheme.bodySmall,
                        ),
                        if (plan.description != null) ...[
                          const SizedBox(height: 8),
                          Text(
                            plan.description!,
                            style: theme.textTheme.bodyMedium,
                          ),
                        ],
                        const SizedBox(height: 12),
                        ...plan.features.map(
                          (f) => Padding(
                            padding: const EdgeInsets.symmetric(vertical: 2),
                            child: Row(
                              children: [
                                Icon(
                                  Icons.check_circle,
                                  size: 16,
                                  color: theme.colorScheme.primary,
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    f,
                                    style: theme.textTheme.bodySmall,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),
                        SizedBox(
                          width: double.infinity,
                          child: FilledButton(
                            onPressed: () => context.pushNamed(
                              'subscription-checkout',
                              extra: {
                                'planId': plan.id,
                                'planName': plan.name,
                                'planPrice': plan.price.toString(),
                                'planCurrency': plan.currency ?? 'KES',
                                'planDuration': '${plan.durationDays} days',
                                'planDurationDays': plan.durationDays,
                              },
                            ),
                            child: const Text('Subscribe'),
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            );
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }
}
