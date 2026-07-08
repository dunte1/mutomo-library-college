import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import '../bloc/loans_bloc.dart';
import '../bloc/loans_event.dart';
import '../bloc/loans_state.dart';
import '../models/loan_model.dart';
import '../../../core/widgets/skeleton.dart';

class ActiveLoansScreen extends StatefulWidget {
  const ActiveLoansScreen({super.key});

  @override
  State<ActiveLoansScreen> createState() => _ActiveLoansScreenState();
}

class _ActiveLoansScreenState extends State<ActiveLoansScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final ScrollController _activeScrollController = ScrollController();
  final ScrollController _historyScrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    context.read<LoansBloc>().add(const LoadActiveLoans());
    context.read<LoansBloc>().add(const LoadLoanHistory());

    _activeScrollController.addListener(_onActiveScroll);
    _historyScrollController.addListener(_onHistoryScroll);
  }

  void _onActiveScroll() {
    if (_activeScrollController.position.pixels >=
        _activeScrollController.position.maxScrollExtent - 200) {
      final state = context.read<LoansBloc>().state;
      if (state is LoansLoaded && state.hasMoreActiveLoans) {
        context
            .read<LoansBloc>()
            .add(LoadActiveLoans(page: state.activeLoansPage + 1));
      }
    }
  }

  void _onHistoryScroll() {
    if (_historyScrollController.position.pixels >=
        _historyScrollController.position.maxScrollExtent - 200) {
      final state = context.read<LoansBloc>().state;
      if (state is LoansLoaded && state.hasMoreHistory) {
        context
            .read<LoansBloc>()
            .add(LoadLoanHistory(page: state.historyPage + 1));
      }
    }
  }

  @override
  void dispose() {
    _tabController.dispose();
    _activeScrollController.dispose();
    _historyScrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Loans'),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'Active'),
            Tab(text: 'History'),
          ],
        ),
      ),
      body: BlocConsumer<LoansBloc, LoansState>(
        listener: (context, state) {
          if (state is LoansLoaded && state.message != null) {
            ScaffoldMessenger.of(
              context,
            ).showSnackBar(SnackBar(content: Text(state.message!)));
          }
        },
        builder: (context, state) {
          if (state is LoansLoading && state is! LoansLoaded) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [
                  SkeletonCard(height: 100),
                  SkeletonCard(height: 100),
                  SkeletonCard(height: 100),
                ],
              ),
            );
          }
          if (state is LoansError) {
            return Center(child: Text(state.error));
          }

          return TabBarView(
            controller: _tabController,
            children: [
              _buildActiveTab(state, theme),
              _buildHistoryTab(state, theme),
            ],
          );
        },
      ),
    );
  }

  Widget _buildActiveTab(LoansState state, ThemeData theme) {
    final loans = state is LoansLoaded ? state.activeLoans : <LoanModel>[];
    final hasMore = state is LoansLoaded && state.hasMoreActiveLoans;
    if (loans.isEmpty && state is! LoansLoading) {
      return const Center(child: Text('No active loans'));
    }
    return RefreshIndicator(
      onRefresh: () async =>
          context.read<LoansBloc>().add(const LoadActiveLoans()),
      child: ListView.builder(
        controller: _activeScrollController,
        padding: const EdgeInsets.all(12),
        itemCount: loans.length + (hasMore ? 1 : 0),
        itemBuilder: (_, i) {
          if (i == loans.length) {
            return const Padding(
              padding: EdgeInsets.symmetric(vertical: 16),
              child: Center(child: CircularProgressIndicator()),
            );
          }
          final loan = loans[i];
          final isOverdue = loan.isOverdue;
          return Card(
            child: ListTile(
              leading: Container(
                width: 40,
                height: 56,
                color: theme.colorScheme.surfaceContainerHighest,
                child: loan.bookCover != null
                    ? CachedNetworkImage(
                        imageUrl: loan.bookCover!,
                        fit: BoxFit.cover,
                        placeholder: (_, __) => Container(color: Colors.grey[200]),
                        errorWidget: (_, __, ___) => Icon(Icons.broken_image, color: Colors.grey),
                      )
                    : const Icon(Icons.book),
              ),
              title: Text(
                loan.bookTitle,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              subtitle: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Due: ${DateFormat('MMM d').format(loan.dueAt)}'),
                  if (isOverdue)
                    Text(
                      'OVERDUE!',
                      style: TextStyle(
                        color: theme.colorScheme.error,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  if (loan.renewalCount > 0)
                    Text(
                      'Renewed ${loan.renewalCount}x${loan.renewedAt != null ? ' (${DateFormat('MMM d').format(loan.renewedAt!)})' : ''}',
                      style: theme.textTheme.bodySmall?.copyWith(
                        color: theme.colorScheme.onSurfaceVariant,
                      ),
                    ),
                ],
              ),
              trailing: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (loan.canRenew)
                    FilledButton.tonal(
                      onPressed: () =>
                          context.read<LoansBloc>().add(RenewLoan(loan.id)),
                      child: const Text('Renew'),
                    ),
                  const SizedBox(width: 4),
                  IconButton(
                    icon: const Icon(Icons.info_outline, size: 20),
                    tooltip: 'Loan details',
                    onPressed: () => _showLoanDetail(loan, theme),
                  ),
                ],
              ),
              isThreeLine: loan.renewalCount > 0,
            ),
          );
        },
      ),
    );
  }

  void _showLoanDetail(LoanModel loan, ThemeData theme) {
    showModalBottomSheet(
      context: context,
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              loan.bookTitle,
              style: theme.textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            _detailRow(theme, 'Status', loan.status.toUpperCase()),
            _detailRow(
              theme,
              'Borrowed',
              DateFormat('MMM d, y').format(loan.borrowedAt),
            ),
            _detailRow(
              theme,
              'Due Date',
              DateFormat('MMM d, y').format(loan.dueAt),
            ),
            if (loan.returnedAt != null)
              _detailRow(
                theme,
                'Returned',
                DateFormat('MMM d, y').format(loan.returnedAt!),
              ),
            if (loan.renewalCount > 0)
              _detailRow(
                theme,
                'Renewals Used',
                '${loan.renewalCount} of ${loan.maxRenewals}',
              ),
            if (loan.renewedAt != null)
              _detailRow(
                theme,
                'Last Renewed',
                DateFormat('MMM d, y').format(loan.renewedAt!),
              ),
            if (loan.renewalCount > 0)
              Padding(
                padding: const EdgeInsets.only(top: 12),
                child: Text(
                  loan.renewalCount == 1
                      ? 'This loan was renewed once.'
                      : 'This loan was renewed ${loan.renewalCount} times.',
                  style: theme.textTheme.bodySmall?.copyWith(
                    color: theme.colorScheme.onSurfaceVariant,
                  ),
                ),
              ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: FilledButton(
                onPressed: () {
                  Navigator.pop(ctx);
                  context.read<LoansBloc>().add(RenewLoan(loan.id));
                },
                child: const Text('Renew Loan'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _detailRow(ThemeData theme, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: theme.textTheme.bodyMedium?.copyWith(
              color: theme.colorScheme.onSurfaceVariant,
            ),
          ),
          Text(
            value,
            style: theme.textTheme.bodyMedium?.copyWith(
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHistoryTab(LoansState state, ThemeData theme) {
    final history = state is LoansLoaded ? state.history : <LoanHistoryModel>[];
    final hasMore = state is LoansLoaded && state.hasMoreHistory;
    if (history.isEmpty && state is! LoansLoading) {
      return const Center(child: Text('No borrowing history'));
    }
    return RefreshIndicator(
      onRefresh: () async =>
          context.read<LoansBloc>().add(const LoadLoanHistory()),
      child: ListView.builder(
        controller: _historyScrollController,
        padding: const EdgeInsets.all(12),
        itemCount: history.length + (hasMore ? 1 : 0),
        itemBuilder: (_, i) {
          if (i == history.length) {
            return const Padding(
              padding: EdgeInsets.symmetric(vertical: 16),
              child: Center(child: CircularProgressIndicator()),
            );
          }
          final loan = history[i];
          return Card(
            child: ListTile(
              leading: Container(
                width: 40,
                height: 56,
                color: theme.colorScheme.surfaceContainerHighest,
                child: loan.bookCover != null
                    ? CachedNetworkImage(
                        imageUrl: loan.bookCover!,
                        fit: BoxFit.cover,
                        placeholder: (_, __) => Container(color: Colors.grey[200]),
                        errorWidget: (_, __, ___) => Icon(Icons.broken_image, color: Colors.grey),
                      )
                    : const Icon(Icons.book),
              ),
              title: Text(
                loan.bookTitle,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              subtitle: Text(
                'Borrowed: ${DateFormat('MMM d').format(loan.borrowedAt)}'
                '${loan.returnedAt != null ? ' | Returned: ${DateFormat('MMM d').format(loan.returnedAt!)}' : ''}',
              ),
              trailing: Chip(
                label: Text(loan.status, style: const TextStyle(fontSize: 11)),
                visualDensity: VisualDensity.compact,
              ),
            ),
          );
        },
      ),
    );
  }
}
