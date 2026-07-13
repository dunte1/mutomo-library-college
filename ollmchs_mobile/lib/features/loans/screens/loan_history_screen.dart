import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import '../bloc/loans_bloc.dart';
import '../bloc/loans_event.dart';
import '../bloc/loans_state.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/skeleton.dart';

class LoanHistoryScreen extends StatefulWidget {
  const LoanHistoryScreen({super.key});

  @override
  State<LoanHistoryScreen> createState() => _LoanHistoryScreenState();
}

class _LoanHistoryScreenState extends State<LoanHistoryScreen> {
  final _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    context.read<LoansBloc>().add(const LoadLoanHistory());
    _scrollController.addListener(_onScroll);
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent - 200) {
      final state = context.read<LoansBloc>().state;
      if (state is LoansLoaded && state.hasMoreHistory) {
        context.read<LoansBloc>().add(
          LoadLoanHistory(page: state.historyPage + 1),
        );
      }
    }
  }

  String _formatDate(DateTime date) => DateFormat('dd MMM yyyy').format(date);

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Loan History')),
      body: BlocBuilder<LoansBloc, LoansState>(
        builder: (context, state) {
          if (state is LoansLoading && state is! LoansLoaded) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [
                  SkeletonCard(height: 72),
                  SkeletonCard(height: 72),
                  SkeletonCard(height: 72),
                ],
              ),
            );
          }
          if (state is LoansError && state is! LoansLoaded) {
            return Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    Icons.error_outline,
                    size: 48,
                    color: theme.colorScheme.error,
                  ),
                  const SizedBox(height: 8),
                  Text(state.error, textAlign: TextAlign.center),
                  const SizedBox(height: 16),
                  FilledButton.tonal(
                    onPressed: () =>
                        context.read<LoansBloc>().add(const LoadLoanHistory()),
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }
          if (state is LoansLoaded) {
            final history = state.history;
            if (history.isEmpty) {
              return const EmptyState(
                icon: Icons.history,
                title: 'No loan history',
                subtitle: "You haven't borrowed any books yet",
              );
            }
            return RefreshIndicator(
              onRefresh: () async {
                context.read<LoansBloc>().add(const LoadLoanHistory());
              },
              child: ListView.builder(
                controller: _scrollController,
                padding: const EdgeInsets.all(16),
                itemCount: history.length + (state.hasMoreHistory ? 1 : 0),
                itemBuilder: (_, i) {
                  if (i >= history.length) {
                    return const Center(
                      child: Padding(
                        padding: EdgeInsets.all(16),
                        child: CircularProgressIndicator(),
                      ),
                    );
                  }
                  final loan = history[i];
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
                                errorWidget: (_, __, ___) => Icon(
                                  Icons.book,
                                  size: 32,
                                  color: theme.colorScheme.primary,
                                ),
                              ),
                            )
                          : Container(
                              width: 48,
                              height: 64,
                              decoration: BoxDecoration(
                                color: theme.colorScheme.primaryContainer,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Icon(
                                Icons.book,
                                color: theme.colorScheme.primary,
                              ),
                            ),
                      title: Text(
                        loan.bookTitle,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      subtitle: Text(
                        'Borrowed: ${_formatDate(loan.borrowedAt)}\nReturned: ${loan.returnedAt != null ? _formatDate(loan.returnedAt!) : "—"}',
                        style: theme.textTheme.bodySmall,
                      ),
                      trailing: Text(
                        loan.status,
                        style: theme.textTheme.labelMedium?.copyWith(
                          color: theme.colorScheme.primary,
                        ),
                      ),
                      isThreeLine: true,
                    ),
                  );
                },
              ),
            );
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }
}
