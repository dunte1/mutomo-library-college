import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import '../bloc/fines_bloc.dart';
import '../models/fine_model.dart';
import '../../../core/widgets/skeleton.dart';

class FinesScreen extends StatefulWidget {
  const FinesScreen({super.key});

  @override
  State<FinesScreen> createState() => _FinesScreenState();
}

class _FinesScreenState extends State<FinesScreen> {
  int _payingId = -1;

  @override
  void initState() {
    super.initState();
    context.read<FinesBloc>().add(const LoadFines());
  }

  Widget _buildFineCard(FineModel fine, ThemeData theme) {
    return Card(
      child: ListTile(
        leading: Icon(
          fine.isPaid ? Icons.paid : Icons.pending_outlined,
          color: fine.isPaid ? Colors.green : Colors.orange,
        ),
        title: Text(fine.reason, maxLines: 1, overflow: TextOverflow.ellipsis),
        subtitle: Text(DateFormat('MMM d, y').format(fine.assessedAt)),
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
              _payingId == fine.id
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : FilledButton.tonal(
                      onPressed: () => _confirmPayFine(fine),
                      style: FilledButton.styleFrom(
                        minimumSize: Size.zero,
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 4,
                        ),
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
        isThreeLine: true,
      ),
    );
  }

  Future<void> _confirmPayFine(FineModel fine) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Pay Fine'),
        content: Text(
          'Pay KES ${fine.amount.toStringAsFixed(2)} for "${fine.reason}"?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Pay Now'),
          ),
        ],
      ),
    );
    if (confirmed == true && mounted) {
      setState(() => _payingId = fine.id);
      context.read<FinesBloc>().add(PayFine(fine.id));
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Fines')),
      body: BlocConsumer<FinesBloc, FinesState>(
        listener: (context, state) {
          if (state is FinesLoaded && state.message != null) {
            setState(() => _payingId = -1);
            ScaffoldMessenger.of(
              context,
            ).showSnackBar(SnackBar(content: Text(state.message!)));
          }
          if (state is FinesError) {
            setState(() => _payingId = -1);
            ScaffoldMessenger.of(
              context,
            ).showSnackBar(SnackBar(content: Text(state.error)));
          }
        },
        builder: (context, state) {
          if (state is FinesLoading) {
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
            return Center(child: Text(state.error));
          }
          if (state is FinesLoaded) {
            return RefreshIndicator(
              onRefresh: () async =>
                  context.read<FinesBloc>().add(const LoadFines()),
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // Total pending card
                  Card(
                    color: theme.colorScheme.errorContainer,
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        children: [
                          Text(
                            'Total Pending Fines',
                            style: theme.textTheme.titleMedium?.copyWith(
                              color: theme.colorScheme.onErrorContainer,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'KES ${state.totalPending.toStringAsFixed(2)}',
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

                  if (state.fines.isEmpty)
                    Center(
                      child: Padding(
                        padding: const EdgeInsets.only(top: 48),
                        child: Column(
                          children: [
                            Icon(
                              Icons.check_circle,
                              size: 64,
                              color: Colors.green,
                            ),
                            const SizedBox(height: 16),
                            Text(
                              'No fines',
                              style: theme.textTheme.titleMedium,
                            ),
                          ],
                        ),
                      ),
                    )
                  else ...[
                    Text(
                      'Fine History',
                      style: theme.textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    ...state.fines.map((fine) => _buildFineCard(fine, theme)),
                  ],
                ],
              ),
            );
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }
}
