import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import '../../../core/network/api_client.dart';
import '../bloc/reports_bloc.dart';

class FineHistoryScreen extends StatelessWidget {
  const FineHistoryScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (context) => ReportsBloc(api: context.read<ApiClient>())
        ..add(const LoadFineHistory()),
      child: const FineHistoryView(),
    );
  }
}

class FineHistoryView extends StatelessWidget {
  const FineHistoryView({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Fine History')),
      body: BlocBuilder<ReportsBloc, ReportsState>(
        builder: (context, state) {
          if (state is ReportsLoading) {
            return const Center(child: CircularProgressIndicator());
          }
          if (state is ReportsError) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.error_outline, size: 48, color: theme.colorScheme.error),
                  const SizedBox(height: 16),
                  Text(state.message),
                  const SizedBox(height: 16),
                  FilledButton(
                    onPressed: () => context.read<ReportsBloc>().add(const LoadFineHistory()),
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }
          if (state is FineHistoryLoaded) {
            if (state.fines.isEmpty) {
              return Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.attach_money, size: 64, color: theme.colorScheme.onSurfaceVariant),
                    const SizedBox(height: 16),
                    Text('No fines yet', style: theme.textTheme.titleMedium),
                    const SizedBox(height: 8),
                    Text(
                      'Your fine history will appear here',
                      style: theme.textTheme.bodyMedium?.copyWith(
                        color: theme.colorScheme.onSurfaceVariant,
                      ),
                    ),
                  ],
                ),
              );
            }
            return ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: state.fines.length + (state.hasMore ? 1 : 0),
              itemBuilder: (context, index) {
                if (index == state.fines.length) {
                  return Center(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: FilledButton(
                        onPressed: () => context.read<ReportsBloc>().add(
                          LoadFineHistory(page: (index ~/ 20) + 2),
                        ),
                        child: const Text('Load More'),
                      ),
                    ),
                  );
                }
                final fine = state.fines[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: fine.status == 'paid'
                          ? Colors.green.withValues(alpha: 0.1)
                          : Colors.red.withValues(alpha: 0.1),
                      child: Icon(
                        fine.status == 'paid' ? Icons.check : Icons.attach_money,
                        color: fine.status == 'paid' ? Colors.green : Colors.red,
                      ),
                    ),
                    title: Text(
                      fine.bookTitle ?? 'Library Fine',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (fine.reason.isNotEmpty)
                          Text(fine.reason, style: theme.textTheme.bodySmall),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            _buildStatusChip(theme, fine.status),
                            const Spacer(),
                            if (fine.createdAt != null)
                              Text(
                                DateFormat('MMM d, yyyy').format(fine.createdAt!),
                                style: theme.textTheme.bodySmall,
                              ),
                          ],
                        ),
                      ],
                    ),
                    trailing: Text(
                      'KES ${fine.amount.toStringAsFixed(2)}',
                      style: theme.textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: fine.status == 'paid' ? Colors.green : Colors.red,
                      ),
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

  Widget _buildStatusChip(ThemeData theme, String status) {
    Color color;
    String label;
    switch (status) {
      case 'paid':
        color = Colors.green;
        label = 'Paid';
        break;
      case 'pending':
        color = Colors.orange;
        label = 'Pending';
        break;
      case 'waived':
        color = Colors.blue;
        label = 'Waived';
        break;
      default:
        color = Colors.grey;
        label = status;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        label,
        style: theme.textTheme.bodySmall?.copyWith(color: color, fontWeight: FontWeight.w500),
      ),
    );
  }
}
