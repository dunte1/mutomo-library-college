import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import '../bloc/teacher_assignments_bloc.dart';

class TeacherAssignmentProgressScreen extends StatefulWidget {
  final int assignmentId;
  const TeacherAssignmentProgressScreen({
    super.key,
    required this.assignmentId,
  });

  @override
  State<TeacherAssignmentProgressScreen> createState() =>
      _TeacherAssignmentProgressScreenState();
}

class _TeacherAssignmentProgressScreenState
    extends State<TeacherAssignmentProgressScreen> {
  @override
  void initState() {
    super.initState();
    context.read<TeacherAssignmentsBloc>().add(
      LoadAssignmentProgress(widget.assignmentId),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Assignment Progress')),
      body: BlocBuilder<TeacherAssignmentsBloc, TeacherAssignmentsState>(
        builder: (context, state) {
          if (state is TeacherAssignmentsLoading) {
            return const Center(child: CircularProgressIndicator());
          }
          if (state is TeacherAssignmentsError &&
              state is! TeacherAssignmentsLoaded) {
            return Center(child: Text(state.error));
          }
          if (state is TeacherAssignmentsLoaded) {
            final students = state.progressStudents;
            final stats = state.progressStats;

            if (students.isEmpty) {
              return const Center(child: Text('No progress data available'));
            }

            return RefreshIndicator(
              onRefresh: () async => context.read<TeacherAssignmentsBloc>().add(
                LoadAssignmentProgress(widget.assignmentId),
              ),
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // Stats cards
                  Row(
                    children: [
                      _StatsCard(
                        label: 'Total',
                        value: '${stats['total'] ?? students.length}',
                        color: Colors.blue,
                        theme: theme,
                      ),
                      const SizedBox(width: 8),
                      _StatsCard(
                        label: 'Viewed',
                        value: '${stats['viewed'] ?? 0}',
                        color: Colors.orange,
                        theme: theme,
                      ),
                      const SizedBox(width: 8),
                      _StatsCard(
                        label: 'Completed',
                        value: '${stats['completed'] ?? 0}',
                        color: Colors.green,
                        theme: theme,
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  Text(
                    'Student Progress',
                    style: theme.textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),

                  ...students.map(
                    (s) => Card(
                      margin: const EdgeInsets.only(bottom: 8),
                      child: Padding(
                        padding: const EdgeInsets.all(12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    s.studentName,
                                    style: theme.textTheme.titleSmall?.copyWith(
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ),
                                Chip(
                                  label: Text(
                                    s.status,
                                    style: const TextStyle(fontSize: 10),
                                  ),
                                  visualDensity: VisualDensity.compact,
                                  backgroundColor: s.isCompleted
                                      ? Colors.green.withValues(alpha: 0.1)
                                      : s.hasViewed
                                      ? Colors.orange.withValues(alpha: 0.1)
                                      : null,
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Row(
                              children: [
                                Icon(
                                  Icons.visibility,
                                  size: 14,
                                  color: s.hasViewed
                                      ? Colors.green
                                      : theme.colorScheme.onSurfaceVariant,
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  s.hasViewed
                                      ? 'Viewed ${_formatDate(s.viewedAt!)}'
                                      : 'Not viewed',
                                  style: theme.textTheme.bodySmall?.copyWith(
                                    color: s.hasViewed
                                        ? Colors.green
                                        : theme.colorScheme.onSurfaceVariant,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 4),
                            Row(
                              children: [
                                Icon(
                                  Icons.check_circle,
                                  size: 14,
                                  color: s.isCompleted
                                      ? Colors.green
                                      : theme.colorScheme.onSurfaceVariant,
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  s.isCompleted
                                      ? 'Completed ${_formatDate(s.completedAt!)}'
                                      : 'Not completed',
                                  style: theme.textTheme.bodySmall?.copyWith(
                                    color: s.isCompleted
                                        ? Colors.green
                                        : theme.colorScheme.onSurfaceVariant,
                                  ),
                                ),
                              ],
                            ),
                            if (s.score != null) ...[
                              const SizedBox(height: 4),
                              Row(
                                children: [
                                  Icon(
                                    Icons.grade,
                                    size: 14,
                                    color: theme.colorScheme.primary,
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    'Score: ${s.score}',
                                    style: theme.textTheme.bodySmall,
                                  ),
                                ],
                              ),
                            ],
                            if (s.feedback != null &&
                                s.feedback!.isNotEmpty) ...[
                              const SizedBox(height: 4),
                              Text(
                                'Feedback: ${s.feedback}',
                                style: theme.textTheme.bodySmall?.copyWith(
                                  fontStyle: FontStyle.italic,
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
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

  String _formatDate(String iso) {
    try {
      return DateFormat('MMM d, y').format(DateTime.parse(iso));
    } catch (_) {
      return iso;
    }
  }
}

class _StatsCard extends StatelessWidget {
  final String label;
  final String value;
  final Color color;
  final ThemeData theme;

  const _StatsCard({
    required this.label,
    required this.value,
    required this.color,
    required this.theme,
  });

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Card(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 16),
          child: Column(
            children: [
              Text(
                value,
                style: theme.textTheme.headlineMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: color,
                ),
              ),
              Text(label, style: theme.textTheme.bodySmall),
            ],
          ),
        ),
      ),
    );
  }
}
