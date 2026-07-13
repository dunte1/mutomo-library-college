import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../core/network/api_client.dart';
import '../bloc/reports_bloc.dart';

class ReportsScreen extends StatelessWidget {
  const ReportsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (context) => ReportsBloc(api: context.read<ApiClient>())
        ..add(const LoadReadingSummary()),
      child: const ReportsView(),
    );
  }
}

class ReportsView extends StatelessWidget {
  const ReportsView({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('My Reports')),
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
                  Text(state.message, textAlign: TextAlign.center),
                  const SizedBox(height: 16),
                  FilledButton(
                    onPressed: () => context.read<ReportsBloc>().add(const LoadReadingSummary()),
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }
          if (state is ReadingSummaryLoaded) {
            return _buildSummary(context, state.summary, theme);
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }

  Widget _buildSummary(BuildContext context, dynamic summary, ThemeData theme) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Text(
          'Reading Overview',
          style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 12),
        _buildStatGrid(context, summary, theme),
        const SizedBox(height: 24),
        Text(
          'Quick Links',
          style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 12),
        Card(
          child: Column(
            children: [
              ListTile(
                leading: const Icon(Icons.book_outlined),
                title: const Text('Loan History'),
                subtitle: const Text('View all your borrowing history'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => context.push('/reports/loan-history'),
              ),
              const Divider(height: 1),
              ListTile(
                leading: const Icon(Icons.attach_money),
                title: const Text('Fine History'),
                subtitle: const Text('View all fines and payments'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => context.push('/reports/fine-history'),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildStatGrid(BuildContext context, dynamic summary, ThemeData theme) {
    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 12,
      crossAxisSpacing: 12,
      childAspectRatio: 1.5,
      children: [
        _buildStatCard(
          theme,
          icon: Icons.menu_book,
          label: 'Total Borrowed',
          value: '${summary.totalBorrowed}',
          color: Colors.blue,
        ),
        _buildStatCard(
          theme,
          icon: Icons.bookmark,
          label: 'Active Loans',
          value: '${summary.activeLoans}',
          color: Colors.green,
        ),
        _buildStatCard(
          theme,
          icon: Icons.check_circle,
          label: 'Completed',
          value: '${summary.completedLoans}',
          color: Colors.teal,
        ),
        _buildStatCard(
          theme,
          icon: Icons.warning,
          label: 'Overdue',
          value: '${summary.overdueCount}',
          color: Colors.orange,
        ),
        _buildStatCard(
          theme,
          icon: Icons.attach_money,
          label: 'Total Fines',
          value: 'KES ${summary.totalFines.toStringAsFixed(2)}',
          color: Colors.red,
        ),
        _buildStatCard(
          theme,
          icon: Icons.pending,
          label: 'Pending Fines',
          value: 'KES ${summary.pendingFines.toStringAsFixed(2)}',
          color: Colors.amber,
        ),
        _buildStatCard(
          theme,
          icon: Icons.auto_stories,
          label: 'Digital Reads',
          value: '${summary.digitalReadCount}',
          color: Colors.purple,
        ),
        _buildStatCard(
          theme,
          icon: Icons.download,
          label: 'Downloads',
          value: '${summary.digitalAssetsDownloaded}',
          color: Colors.indigo,
        ),
      ],
    );
  }

  Widget _buildStatCard(
    ThemeData theme, {
    required IconData icon,
    required String label,
    required String value,
    required Color color,
  }) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: color, size: 24),
            const SizedBox(height: 4),
            Text(
              value,
              style: theme.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
              textAlign: TextAlign.center,
            ),
            Text(
              label,
              style: theme.textTheme.bodySmall?.copyWith(
                color: theme.colorScheme.onSurfaceVariant,
              ),
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }
}
