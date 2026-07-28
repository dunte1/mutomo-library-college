import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import '../bloc/loans_bloc.dart';
import '../bloc/loans_event.dart';
import '../bloc/loans_state.dart';
import '../../../core/widgets/skeleton.dart';

class LoanDetailScreen extends StatefulWidget {
  final int loanId;
  const LoanDetailScreen({super.key, required this.loanId});

  @override
  State<LoanDetailScreen> createState() => _LoanDetailScreenState();
}

class _LoanDetailScreenState extends State<LoanDetailScreen> {
  @override
  void initState() {
    super.initState();
    context.read<LoansBloc>().add(LoadLoanDetail(widget.loanId));
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Loan Details')),
      body: BlocBuilder<LoansBloc, LoansState>(
        builder: (context, state) {
          if (state is LoansLoading) {
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
          if (state is LoansError) {
            return Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.error_outline, size: 48, color: theme.colorScheme.error),
                  const SizedBox(height: 8),
                  Text(state.error, textAlign: TextAlign.center),
                ],
              ),
            );
          }
          if (state is LoansLoaded && state.selectedLoan != null) {
            final loan = state.selectedLoan!;
            return SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Book cover + title
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (loan.bookCover != null)
                        ClipRRect(
                          borderRadius: BorderRadius.circular(12),
                          child: CachedNetworkImage(
                            imageUrl: loan.bookCover!,
                            width: 80,
                            height: 120,
                            fit: BoxFit.cover,
                            errorWidget: (_, __, ___) => Container(
                              width: 80,
                              height: 120,
                              color: theme.colorScheme.surfaceContainerHighest,
                              child: const Icon(Icons.book),
                            ),
                          ),
                        )
                      else
                        Container(
                          width: 80,
                          height: 120,
                          decoration: BoxDecoration(
                            color: theme.colorScheme.primaryContainer,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Icon(Icons.book, color: theme.colorScheme.primary),
                        ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              loan.bookTitle,
                              style: theme.textTheme.titleLarge?.copyWith(
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            if (loan.author != null) ...[
                              const SizedBox(height: 4),
                              Text(
                                loan.author!,
                                style: theme.textTheme.bodyMedium?.copyWith(
                                  color: theme.colorScheme.onSurfaceVariant,
                                ),
                              ),
                            ],
                            const SizedBox(height: 8),
                            _statusChip(theme, loan.status),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),

                  // Status card
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        children: [
                          if (loan.isOverdue && loan.daysOverdue != null)
                            Container(
                              width: double.infinity,
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: theme.colorScheme.errorContainer,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Row(
                                children: [
                                  Icon(Icons.warning_amber, color: theme.colorScheme.error),
                                  const SizedBox(width: 8),
                                  Text(
                                    '${loan.daysOverdue} day(s) overdue',
                                    style: TextStyle(
                                      color: theme.colorScheme.error,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          if (loan.isOverdue) const SizedBox(height: 12),
                          _detailRow(theme, 'Status', loan.status.toUpperCase()),
                          _detailRow(theme, 'Borrowed', DateFormat('MMM d, y').format(loan.borrowedAt)),
                          _detailRow(theme, 'Due Date', DateFormat('MMM d, y').format(loan.dueAt)),
                          if (loan.returnedAt != null)
                            _detailRow(theme, 'Returned', DateFormat('MMM d, y').format(loan.returnedAt!)),
                          if (loan.renewedAt != null)
                            _detailRow(theme, 'Last Renewed', DateFormat('MMM d, y').format(loan.renewedAt!)),
                          _detailRow(theme, 'Renewals Used', '${loan.renewalCount} of ${loan.maxRenewals}'),
                          if (loan.daysRemaining != null && loan.daysRemaining! > 0)
                            _detailRow(theme, 'Days Remaining', '${loan.daysRemaining}'),
                          if (loan.barcode != null)
                            _detailRow(theme, 'Copy Barcode', loan.barcode!),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Late fee preview for active loans
                  if (loan.status == 'active' || loan.status == 'borrowed')
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Icon(Icons.info_outline, color: theme.colorScheme.primary),
                                const SizedBox(width: 8),
                                Text(
                                  'Late Fee Warning',
                                  style: theme.textTheme.titleSmall?.copyWith(
                                    fontWeight: FontWeight.bold,
                                    color: theme.colorScheme.primary,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'If not returned by ${DateFormat('MMM d, y').format(loan.dueAt)}: Late fee KES 50/day',
                              style: theme.textTheme.bodyMedium,
                            ),
                          ],
                        ),
                      ),
                    ),
                  const SizedBox(height: 16),

                  // Renew button
                  if (loan.canRenew)
                    SizedBox(
                      width: double.infinity,
                      child: FilledButton.icon(
                        onPressed: () {
                          context.read<LoansBloc>().add(RenewLoan(loan.id));
                        },
                        icon: const Icon(Icons.autorenew),
                        label: const Text('Renew Loan'),
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

  Widget _statusChip(ThemeData theme, String status) {
    final Color color;
    switch (status) {
      case 'active':
      case 'borrowed':
        color = Colors.green;
      case 'overdue':
        color = Colors.red;
      case 'returned':
        color = Colors.blue;
      default:
        color = Colors.grey;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Text(
        status.toUpperCase(),
        style: TextStyle(color: color, fontSize: 12, fontWeight: FontWeight.bold),
      ),
    );
  }

  Widget _detailRow(ThemeData theme, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: theme.textTheme.bodyMedium?.copyWith(
            color: theme.colorScheme.onSurfaceVariant,
          )),
          Flexible(
            child: Text(
              value,
              style: theme.textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600),
              textAlign: TextAlign.end,
            ),
          ),
        ],
      ),
    );
  }
}
