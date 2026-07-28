import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import '../../teacher_assignments/bloc/teacher_assignments_bloc.dart';
import '../../teacher_assignments/models/teacher_assignment_model.dart';
import '../../../core/widgets/empty_state.dart';

class AssignmentAnalyticsScreen extends StatefulWidget {
  const AssignmentAnalyticsScreen({super.key});

  @override
  State<AssignmentAnalyticsScreen> createState() =>
      _AssignmentAnalyticsScreenState();
}

class _AssignmentAnalyticsScreenState extends State<AssignmentAnalyticsScreen> {
  @override
  void initState() {
    super.initState();
    context.read<TeacherAssignmentsBloc>().add(const LoadTeacherAssignments());
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Assignment Analytics')),
      body: BlocBuilder<TeacherAssignmentsBloc, TeacherAssignmentsState>(
        builder: (context, state) {
          if (state is TeacherAssignmentsLoading) {
            return const Center(child: CircularProgressIndicator());
          }
          if (state is TeacherAssignmentsLoaded) {
            final assignments = state.assignments;
            if (assignments.isEmpty) {
              return const EmptyState(
                icon: Icons.analytics_outlined,
                title: 'No analytics available',
                subtitle: 'Create assignments to see analytics data.',
              );
            }

            final total = assignments.length;
            final completed =
                assignments.where((a) => a.status == 'completed').length;
            final pending =
                assignments.where((a) => a.status == 'pending').length;
            final inProgress =
                assignments.where((a) => a.status == 'in_progress').length;
            final overdue =
                assignments.where((a) => a.status == 'overdue').length;
            final completionRate =
                total > 0 ? (completed / total * 100).toStringAsFixed(0) : '0';

            return RefreshIndicator(
              onRefresh: () async {
                context
                    .read<TeacherAssignmentsBloc>()
                    .add(const LoadTeacherAssignments());
              },
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // Summary cards
                  Row(
                    children: [
                      Expanded(
                        child: _StatCard(
                          label: 'Total',
                          value: '$total',
                          icon: Icons.assignment,
                          color: theme.colorScheme.primary,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: _StatCard(
                          label: 'Completed',
                          value: '$completed',
                          icon: Icons.check_circle,
                          color: Colors.green,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: _StatCard(
                          label: 'Rate',
                          value: '$completionRate%',
                          icon: Icons.pie_chart,
                          color: theme.colorScheme.tertiary,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: _StatCard(
                          label: 'Pending',
                          value: '$pending',
                          icon: Icons.pending,
                          color: Colors.orange,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: _StatCard(
                          label: 'In Progress',
                          value: '$inProgress',
                          icon: Icons.autorenew,
                          color: Colors.blue,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: _StatCard(
                          label: 'Overdue',
                          value: '$overdue',
                          icon: Icons.warning,
                          color: theme.colorScheme.error,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),
                  Text(
                    'Assignment Breakdown',
                    style: theme.textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 12),
                  // Status distribution bar
                  if (total > 0) ...[
                    _StatusBar(
                      segments: [
                        _StatusBarSegment(
                            completed / total, Colors.green, 'Completed'),
                        _StatusBarSegment(
                            inProgress / total, Colors.blue, 'In Progress'),
                        _StatusBarSegment(
                            pending / total, Colors.orange, 'Pending'),
                        _StatusBarSegment(overdue / total,
                            theme.colorScheme.error, 'Overdue'),
                      ],
                    ),
                    const SizedBox(height: 24),
                  ],
                  Text(
                    'Recent Assignments',
                    style: theme.textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  ...assignments.take(10).map((a) => _AssignmentRow(a)),
                ],
              ),
            );
          }
          if (state is TeacherAssignmentsError) {
            return Center(child: Text(state.error));
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;
  final Color color;

  const _StatCard({
    required this.label,
    required this.value,
    required this.icon,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          children: [
            Icon(icon, color: color, size: 28),
            const SizedBox(height: 4),
            Text(
              value,
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: color,
                  ),
            ),
            Text(
              label,
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: Theme.of(context).colorScheme.onSurfaceVariant,
                  ),
            ),
          ],
        ),
      ),
    );
  }
}

class _StatusBarSegment {
  final double fraction;
  final Color color;
  final String label;
  const _StatusBarSegment(this.fraction, this.color, this.label);
}

class _StatusBar extends StatelessWidget {
  final List<_StatusBarSegment> segments;
  const _StatusBar({required this.segments});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(4),
          child: SizedBox(
            height: 24,
            child: Row(
              children: segments
                  .where((s) => s.fraction > 0)
                  .map((s) => Expanded(
                        flex: (s.fraction * 1000).toInt(),
                        child: Container(color: s.color),
                      ))
                  .toList(),
            ),
          ),
        ),
        const SizedBox(height: 8),
        Wrap(
          spacing: 12,
          runSpacing: 4,
          children: segments
              .where((s) => s.fraction > 0)
              .map((s) => Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 10,
                        height: 10,
                        decoration: BoxDecoration(
                          color: s.color,
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 4),
                      Text(s.label,
                          style: Theme.of(context).textTheme.bodySmall),
                    ],
                  ))
              .toList(),
        ),
      ],
    );
  }
}

class _AssignmentRow extends StatelessWidget {
  final TeacherAssignmentModel a;
  const _AssignmentRow(this.a);

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final statusColor = {
      'completed': Colors.green,
      'in_progress': Colors.blue,
      'pending': Colors.orange,
      'overdue': theme.colorScheme.error,
    }[a.status] ?? theme.colorScheme.onSurfaceVariant;

    return Card(
      margin: const EdgeInsets.only(bottom: 6),
      child: ListTile(
        dense: true,
        leading: CircleAvatar(
          backgroundColor: statusColor.withValues(alpha: 0.15),
          child: Icon(
            a.isAssignment ? Icons.assignment : Icons.recommend,
            size: 18,
            color: statusColor,
          ),
        ),
        title: Text(
          a.title,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: const TextStyle(fontWeight: FontWeight.w500),
        ),
        subtitle: Text(
          [
            a.status.toUpperCase(),
            if (a.dueAt != null)
              ' | Due: ${DateFormat('MMM d').format(DateTime.parse(a.dueAt!))}',
          ].join(''),
          style: theme.textTheme.bodySmall,
        ),
        trailing: Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
          decoration: BoxDecoration(
            color: statusColor.withValues(alpha: 0.15),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Text(
            a.status,
            style: TextStyle(
              fontSize: 10,
              color: statusColor,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
      ),
    );
  }
}
