import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import '../../../core/network/api_client.dart';
import '../bloc/reports_bloc.dart';

class LoanHistoryScreen extends StatelessWidget {
  const LoanHistoryScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (context) => ReportsBloc(api: context.read<ApiClient>())
        ..add(const LoadLoanHistory()),
      child: const LoanHistoryView(),
    );
  }
}

class LoanHistoryView extends StatelessWidget {
  const LoanHistoryView({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Loan History')),
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
                    onPressed: () => context.read<ReportsBloc>().add(const LoadLoanHistory()),
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }
          if (state is LoanHistoryLoaded) {
            if (state.loans.isEmpty) {
              return Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.book_outlined, size: 64, color: theme.colorScheme.onSurfaceVariant),
                    const SizedBox(height: 16),
                    Text('No loan history yet', style: theme.textTheme.titleMedium),
                    const SizedBox(height: 8),
                    Text(
                      'Your borrowing history will appear here',
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
              itemCount: state.loans.length + (state.hasMore ? 1 : 0),
              itemBuilder: (context, index) {
                if (index == state.loans.length) {
                  return Center(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: FilledButton(
                        onPressed: () => context.read<ReportsBloc>().add(
                          LoadLoanHistory(page: (index ~/ 20) + 2),
                        ),
                        child: const Text('Load More'),
                      ),
                    ),
                  );
                }
                final loan = state.loans[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: ListTile(
                    leading: loan.bookCover != null
                        ? ClipRRect(
                            borderRadius: BorderRadius.circular(4),
                            child: Image.network(
                              loan.bookCover!,
                              width: 40,
                              height: 60,
                              fit: BoxFit.cover,
                              errorBuilder: (_, __, ___) => Container(
                                width: 40,
                                height: 60,
                                color: theme.colorScheme.surfaceContainerHighest,
                                child: const Icon(Icons.book),
                              ),
                            ),
                          )
                        : Container(
                            width: 40,
                            height: 60,
                            decoration: BoxDecoration(
                              color: theme.colorScheme.surfaceContainerHighest,
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: const Icon(Icons.book),
                          ),
                    title: Text(
                      loan.bookTitle,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (loan.authorName != null)
                          Text(loan.authorName!, style: theme.textTheme.bodySmall),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            _buildStatusChip(theme, loan.status),
                            const Spacer(),
                            if (loan.borrowedAt != null)
                              Text(
                                DateFormat('MMM d, yyyy').format(loan.borrowedAt!),
                                style: theme.textTheme.bodySmall,
                              ),
                          ],
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

  Widget _buildStatusChip(ThemeData theme, String status) {
    Color color;
    String label;
    switch (status) {
      case 'active':
        color = Colors.green;
        label = 'Active';
        break;
      case 'overdue':
        color = Colors.red;
        label = 'Overdue';
        break;
      case 'returned':
        color = Colors.blue;
        label = 'Returned';
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
