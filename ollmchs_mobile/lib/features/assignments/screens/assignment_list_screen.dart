import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/assignments_bloc.dart';
import '../models/assignment_model.dart';
import '../../../core/widgets/skeleton.dart';

class AssignmentListScreen extends StatefulWidget {
  const AssignmentListScreen({super.key});

  @override
  State<AssignmentListScreen> createState() => _AssignmentListScreenState();
}

class _AssignmentListScreenState extends State<AssignmentListScreen> {
  @override
  void initState() {
    super.initState();
    context.read<AssignmentsBloc>().add(const LoadAssignments());
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Assignments')),
      body: BlocBuilder<AssignmentsBloc, AssignmentsState>(
        builder: (context, state) {
          if (state is AssignmentsLoading) {
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
          if (state is AssignmentsError) {
            return Center(child: Text(state.error));
          }
          if (state is AssignmentsLoaded) {
            if (state.assignments.isEmpty) {
              return Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.assignment_outlined,
                      size: 64,
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                    const SizedBox(height: 16),
                    Text('No assignments', style: theme.textTheme.titleMedium),
                    const SizedBox(height: 8),
                    Text(
                      'Check back when assignments are posted',
                      style: theme.textTheme.bodyMedium?.copyWith(
                        color: theme.colorScheme.onSurfaceVariant,
                      ),
                    ),
                  ],
                ),
              );
            }
            return RefreshIndicator(
              onRefresh: () async =>
                  context.read<AssignmentsBloc>().add(const LoadAssignments()),
              child: ListView.builder(
                padding: const EdgeInsets.all(12),
                itemCount: state.assignments.length,
                itemBuilder: (_, i) {
                  final assignment = state.assignments[i];
                  return Card(
                    child: ListTile(
                      leading: CircleAvatar(
                        backgroundColor: assignment.isOverdue
                            ? theme.colorScheme.errorContainer
                            : theme.colorScheme.primaryContainer,
                        child: Icon(
                          assignment.isSubmitted
                              ? Icons.check_circle
                              : Icons.assignment,
                          color: assignment.isOverdue
                              ? theme.colorScheme.error
                              : theme.colorScheme.primary,
                        ),
                      ),
                      title: Text(
                        assignment.title,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      subtitle: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (assignment.subject != null)
                            Text(
                              assignment.subject!,
                              style: theme.textTheme.bodySmall,
                            ),
                          Text(
                            'Due: ${DateFormat('MMM d, y').format(assignment.dueAt)}',
                            style: TextStyle(
                              color: assignment.isOverdue
                                  ? theme.colorScheme.error
                                  : null,
                              fontWeight: assignment.isOverdue
                                  ? FontWeight.bold
                                  : null,
                            ),
                          ),
                        ],
                      ),
                      trailing: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          if (assignment.canMarkComplete)
                            FilledButton.tonal(
                              onPressed: () => context
                                  .read<AssignmentsBloc>()
                                  .add(MarkComplete(assignment.id)),
                              style: FilledButton.styleFrom(
                                minimumSize: Size.zero,
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 4,
                                ),
                                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                              ),
                              child: const Text(
                                'Done',
                                style: TextStyle(fontSize: 11),
                              ),
                            )
                          else if (assignment.isSubmitted)
                            const Chip(
                              label: Text(
                                'Submitted',
                                style: TextStyle(fontSize: 10),
                              ),
                              visualDensity: VisualDensity.compact,
                            ),
                          if (assignment.isOverdue)
                            Chip(
                              label: Text(
                                'Overdue',
                                style: TextStyle(
                                  fontSize: 10,
                                  color: theme.colorScheme.error,
                                ),
                              ),
                              visualDensity: VisualDensity.compact,
                              backgroundColor: theme.colorScheme.errorContainer,
                            ),
                        ],
                      ),
                      isThreeLine: true,
                      onTap: () => context.pushNamed(
                        'assignment-detail',
                        pathParameters: {'id': '${assignment.id}'},
                      ),
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
