import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/loans_bloc.dart';
import '../bloc/loans_event.dart';
import '../bloc/loans_state.dart';
import '../models/loan_model.dart';
import '../../reservations/bloc/reservations_bloc.dart';
import '../../reservations/bloc/reservations_event.dart';
import '../../reservations/bloc/reservations_state.dart';
import '../../reservations/models/reservation_model.dart';
import '../../../core/network/api_client.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/skeleton.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/bottom_nav_shell.dart';

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
    _tabController = TabController(length: 3, vsync: this);
    context.read<LoansBloc>().add(const LoadActiveLoans());
    context.read<LoansBloc>().add(const LoadLoanHistory());
    context.read<ReservationsBloc>().add(const LoadReservations());

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
        leading: context.isCompact
            ? IconButton(
                icon: const Icon(Icons.menu),
                onPressed: () => shellScaffoldKey.currentState?.openDrawer(),
              )
            : null,
        title: const Text('My Loans'),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'Active'),
            Tab(text: 'Reservations'),
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
              _buildReservationsTab(theme),
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
      return const EmptyState(
        icon: Icons.assignment_outlined,
        title: 'No active loans',
        subtitle: "You don't have any books checked out",
      );
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
                  if (loan.status == 'active')
                    FilledButton.tonal(
                      onPressed: () async {
                        try {
                          final api = context.read<ApiClient>();
                          await api.post('/v1/loans/return', data: {'loan_id': loan.id});
                          if (context.mounted) {
                            context.read<LoansBloc>().add(const LoadActiveLoans());
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Book returned successfully')),
                            );
                          }
                        } catch (e) {
                          if (context.mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text('Return failed: $e'),
                                backgroundColor: Theme.of(context).colorScheme.error,
                              ),
                            );
                          }
                        }
                      },
                      child: const Text('Return', style: TextStyle(fontSize: 12)),
                    ),
                  if (loan.canRenew) ...[
                    const SizedBox(width: 4),
                    FilledButton.tonal(
                      onPressed: () =>
                          context.read<LoansBloc>().add(RenewLoan(loan.id)),
                      child: const Text('Renew'),
                    ),
                  ],
                  const SizedBox(width: 4),
                  IconButton(
                    icon: const Icon(Icons.info_outline, size: 20),
                    tooltip: 'Loan details',
                    onPressed: () => context.pushNamed(
                      'loan-detail',
                      pathParameters: {'id': '${loan.id}'},
                    ),
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

  Widget _buildReservationsTab(ThemeData theme) {
    return BlocBuilder<ReservationsBloc, ReservationsState>(
      builder: (context, state) {
        if (state is ReservationsLoading) {
          return const Padding(
            padding: EdgeInsets.all(16),
            child: Column(
              children: [
                SkeletonCard(height: 100),
                SkeletonCard(height: 100),
              ],
            ),
          );
        }
        if (state is ReservationsLoaded) {
          if (state.reservations.isEmpty) {
            return const EmptyState(
              icon: Icons.bookmark_border,
              title: 'No reservations',
              subtitle: "You haven't reserved any books yet",
            );
          }
          return RefreshIndicator(
            onRefresh: () async =>
                context.read<ReservationsBloc>().add(const LoadReservations()),
            child: ListView.builder(
              padding: const EdgeInsets.all(12),
              itemCount: state.reservations.length,
              itemBuilder: (_, i) {
                final reservation = state.reservations[i];
                return Card(
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: reservation.isActive
                          ? Colors.green.withValues(alpha: 0.1)
                          : Colors.orange.withValues(alpha: 0.1),
                      child: Icon(
                        reservation.isActive ? Icons.bookmark : Icons.bookmark_border,
                        color: reservation.isActive ? Colors.green : Colors.orange,
                      ),
                    ),
                    title: Text(
                      reservation.bookTitle ?? 'Reserved Book',
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    subtitle: Text(
                      'Reserved: ${DateFormat('MMM d').format(reservation.reservedAt)}'
                      '${reservation.expiresAt != null ? ' | Expires: ${DateFormat('MMM d').format(reservation.expiresAt!)}' : ''}',
                    ),
                    trailing: Chip(
                      label: Text(reservation.status, style: const TextStyle(fontSize: 10)),
                      visualDensity: VisualDensity.compact,
                    ),
                  ),
                );
              },
            ),
          );
        }
        return const EmptyState(
          icon: Icons.bookmark_border,
          title: 'No reservations',
          subtitle: "You haven't reserved any books yet",
        );
      },
    );
  }

  Widget _buildHistoryTab(LoansState state, ThemeData theme) {
    final history = state is LoansLoaded ? state.history : <LoanHistoryModel>[];
    final hasMore = state is LoansLoaded && state.hasMoreHistory;
    if (history.isEmpty && state is! LoansLoading) {
      return const EmptyState(
        icon: Icons.history,
        title: 'No loan history',
        subtitle: "You haven't borrowed any books yet",
      );
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
