import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import '../bloc/loans_bloc.dart';
import '../bloc/loans_event.dart';
import '../models/loan_model.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/skeleton.dart';
import '../../../core/network/api_client.dart';

class OverdueLoansScreen extends StatefulWidget {
  const OverdueLoansScreen({super.key});

  @override
  State<OverdueLoansScreen> createState() => _OverdueLoansScreenState();
}

class _OverdueLoansScreenState extends State<OverdueLoansScreen> {
  List<LoanModel> _overdueLoans = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadOverdue();
  }

  Future<void> _loadOverdue() async {
    setState(() { _loading = true; _error = null; });
    try {
      final api = context.read<ApiClient>();
      final response = await api.get('/v1/loans/overdue');
      final list = (response.data['data'] as List<dynamic>? ?? [])
          .map((e) => LoanModel.fromJson(e as Map<String, dynamic>))
          .toList();
      if (mounted) setState(() { _overdueLoans = list; _loading = false; });
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Overdue Loans'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadOverdue,
          ),
        ],
      ),
      body: _loading
          ? const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [
                  SkeletonCard(height: 100),
                  SkeletonCard(height: 100),
                  SkeletonCard(height: 100),
                ],
              ),
            )
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.error_outline, size: 48, color: theme.colorScheme.error),
                      const SizedBox(height: 8),
                      Text(_error!, textAlign: TextAlign.center),
                      const SizedBox(height: 16),
                      FilledButton.tonal(onPressed: _loadOverdue, child: const Text('Retry')),
                    ],
                  ),
                )
              : _overdueLoans.isEmpty
                  ? const EmptyState(
                      icon: Icons.check_circle_outline,
                      title: 'No overdue loans',
                      subtitle: 'All your loans are on time',
                    )
                  : RefreshIndicator(
                      onRefresh: _loadOverdue,
                      child: ListView.builder(
                        padding: const EdgeInsets.all(12),
                        itemCount: _overdueLoans.length,
                        itemBuilder: (_, i) {
                          final loan = _overdueLoans[i];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 8),
                            child: ListTile(
                              leading: loan.bookCover != null
                                  ? ClipRRect(
                                      borderRadius: BorderRadius.circular(8),
                                      child: CachedNetworkImage(
                                        imageUrl: loan.bookCover!,
                                        width: 48,
                                        height: 64,
                                        fit: BoxFit.cover,
                                        errorWidget: (_, __, ___) => Icon(Icons.book, color: theme.colorScheme.primary),
                                      ),
                                    )
                                  : Container(
                                      width: 48,
                                      height: 64,
                                      decoration: BoxDecoration(
                                        color: theme.colorScheme.errorContainer,
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Icon(Icons.book, color: theme.colorScheme.error),
                                    ),
                              title: Text(loan.bookTitle, maxLines: 2, overflow: TextOverflow.ellipsis),
                              subtitle: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text('Due: ${DateFormat('MMM d, y').format(loan.dueAt)}'),
                                  if (loan.daysOverdue != null)
                                    Text(
                                      '${loan.daysOverdue} day(s) overdue',
                                      style: TextStyle(
                                        color: theme.colorScheme.error,
                                        fontWeight: FontWeight.bold,
                                        fontSize: 12,
                                      ),
                                    ),
                                ],
                              ),
                              trailing: loan.canRenew
                                  ? FilledButton.tonal(
                                      onPressed: () {
                                        context.read<LoansBloc>().add(RenewLoan(loan.id));
                                        _loadOverdue();
                                      },
                                      child: const Text('Renew'),
                                    )
                                  : null,
                              isThreeLine: true,
                            ),
                          );
                        },
                      ),
                    ),
    );
  }
}
