import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/fines_bloc.dart';
import '../models/fine_model.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/skeleton.dart';

class FinesScreen extends StatefulWidget {
  const FinesScreen({super.key});

  @override
  State<FinesScreen> createState() => _FinesScreenState();
}

class _FinesScreenState extends State<FinesScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    context.read<FinesBloc>().add(const LoadFines());
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Widget _buildFineCard(FineModel fine, ThemeData theme) {
    return Card(
      child: ListTile(
        leading: Icon(
          fine.isPaid ? Icons.paid : Icons.pending_outlined,
          color: fine.isPaid ? Colors.green : Colors.orange,
        ),
        title: Text(fine.reason, maxLines: 1, overflow: TextOverflow.ellipsis),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(DateFormat('MMM d, y').format(fine.assessedAt)),
            if (fine.bookTitle != null)
              Text(
                fine.bookTitle!,
                style: theme.textTheme.bodySmall?.copyWith(
                  color: theme.colorScheme.onSurfaceVariant,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
          ],
        ),
        trailing: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Text(
              'KES ${fine.amount.toStringAsFixed(2)}',
              style: TextStyle(
                fontWeight: FontWeight.bold,
                color: fine.isPaid ? Colors.green : theme.colorScheme.error,
              ),
            ),
            const SizedBox(height: 4),
            if (fine.isPending)
              FilledButton.tonal(
                onPressed: () => context.pushNamed(
                  'fine-payment',
                  pathParameters: {'id': '${fine.id}'},
                ),
                style: FilledButton.styleFrom(
                  minimumSize: Size.zero,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                ),
                child: const Text('Pay', style: TextStyle(fontSize: 12)),
              )
            else
              Chip(
                label: Text(fine.status, style: const TextStyle(fontSize: 10)),
                visualDensity: VisualDensity.compact,
              ),
          ],
        ),
        isThreeLine: fine.bookTitle != null,
        onTap: () => context.pushNamed(
          'fine-detail',
          pathParameters: {'id': '${fine.id}'},
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Fines'),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'Outstanding'),
            Tab(text: 'Paid'),
          ],
        ),
      ),
      body: BlocConsumer<FinesBloc, FinesState>(
        listener: (context, state) {
          if (state is FinesLoaded && state.message != null) {
            ScaffoldMessenger.of(
              context,
            ).showSnackBar(SnackBar(content: Text(state.message!)));
          }
        },
        builder: (context, state) {
          if (state is FinesLoading && state is! FinesLoaded) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [
                  Skeleton(height: 80),
                  SizedBox(height: 16),
                  SkeletonCard(height: 72),
                  SkeletonCard(height: 72),
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
                  Text(state.error),
                  const SizedBox(height: 16),
                  FilledButton.tonal(
                    onPressed: () => context.read<FinesBloc>().add(const LoadFines()),
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }
          if (state is FinesLoaded) {
            final pending = state.fines.where((f) => f.isPending).toList();
            final paid = state.fines.where((f) => f.isPaid).toList();
            return TabBarView(
              controller: _tabController,
              children: [
                _buildOutstandingTab(pending, state.totalPending, theme),
                _buildPaidTab(paid, theme),
              ],
            );
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }

  Widget _buildOutstandingTab(List<FineModel> pending, double total, ThemeData theme) {
    if (pending.isEmpty) {
      return const EmptyState(
        icon: Icons.check_circle_outline,
        title: 'No outstanding fines',
        subtitle: "You're all caught up!",
      );
    }
    return RefreshIndicator(
      onRefresh: () async => context.read<FinesBloc>().add(const LoadFines()),
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            color: theme.colorScheme.errorContainer,
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                children: [
                  Text(
                    'Total Outstanding',
                    style: theme.textTheme.titleMedium?.copyWith(
                      color: theme.colorScheme.onErrorContainer,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'KES ${total.toStringAsFixed(2)}',
                    style: theme.textTheme.headlineLarge?.copyWith(
                      fontWeight: FontWeight.bold,
                      color: theme.colorScheme.onErrorContainer,
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          ...pending.map((fine) => _buildFineCard(fine, theme)),
          if (pending.isNotEmpty) ...[
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                onPressed: () {
                  for (final fine in pending) {
                    context.pushNamed(
                      'fine-payment',
                      pathParameters: {'id': '${fine.id}'},
                    );
                  }
                },
                icon: const Icon(Icons.payment),
                label: const Text('Pay All Outstanding'),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildPaidTab(List<FineModel> paid, ThemeData theme) {
    if (paid.isEmpty) {
      return const EmptyState(
        icon: Icons.history,
        title: 'No paid fines',
        subtitle: 'Payment history will appear here',
      );
    }
    return RefreshIndicator(
      onRefresh: () async => context.read<FinesBloc>().add(const LoadFines()),
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          ...paid.map((fine) => _buildFineCard(fine, theme)),
        ],
      ),
    );
  }
}
