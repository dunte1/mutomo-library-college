import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/assignments_bloc.dart';
import '../../../core/widgets/skeleton.dart';

class AssignmentDetailScreen extends StatefulWidget {
  final int assignmentId;
  const AssignmentDetailScreen({super.key, required this.assignmentId});

  @override
  State<AssignmentDetailScreen> createState() => _AssignmentDetailScreenState();
}

class _AssignmentDetailScreenState extends State<AssignmentDetailScreen> {
  final _submissionController = TextEditingController();

  @override
  void initState() {
    super.initState();
    context.read<AssignmentsBloc>().add(
      LoadAssignmentDetail(widget.assignmentId),
    );
  }

  @override
  void dispose() {
    _submissionController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Assignment')),
      body: BlocConsumer<AssignmentsBloc, AssignmentsState>(
        listener: (context, state) {
          if (state is AssignmentsLoaded && state.message != null) {
            ScaffoldMessenger.of(
              context,
            ).showSnackBar(SnackBar(content: Text(state.message!)));
            if (state.message == 'Marked as complete') {
              context.pop();
            }
          }
        },
        builder: (context, state) {
          if (state is AssignmentsLoading && state is! AssignmentsLoaded) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: SkeletonCard(height: 200),
            );
          }
          if (state is AssignmentsError && state is! AssignmentsLoaded) {
            return Center(child: Text(state.error));
          }
          if (state is AssignmentsLoaded && state.selectedAssignment != null) {
            final assignment = state.selectedAssignment!;
            return SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            assignment.title,
                            style: theme.textTheme.headlineSmall?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 8),
                          if (assignment.subject != null)
                            Chip(
                              label: Text(assignment.subject!),
                              visualDensity: VisualDensity.compact,
                            ),
                          const SizedBox(height: 12),
                          if (assignment.description != null)
                            Text(
                              assignment.description!,
                              style: theme.textTheme.bodyLarge?.copyWith(
                                height: 1.5,
                              ),
                            ),
                          const SizedBox(height: 16),
                          Divider(color: theme.dividerColor),
                          _infoRow('Teacher', assignment.teacherName ?? 'N/A'),
                          _infoRow(
                            'Due Date',
                            DateFormat(
                              'MMM d, y h:mm a',
                            ).format(assignment.dueAt),
                          ),
                          _infoRow('Status', assignment.status.toUpperCase()),
                          if (assignment.score != null)
                            _infoRow('Score', '${assignment.score}'),
                          if (assignment.feedback != null)
                            _infoRow('Feedback', assignment.feedback!),
                        ],
                      ),
                    ),
                  ),
                  if (!assignment.isSubmitted && !assignment.isGraded)
                    Padding(
                      padding: const EdgeInsets.only(top: 16),
                      child: Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Submit Assignment',
                                style: theme.textTheme.titleMedium?.copyWith(
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 12),
                              TextField(
                                controller: _submissionController,
                                decoration: const InputDecoration(
                                  labelText: 'Your answer',
                                  border: OutlineInputBorder(),
                                  alignLabelWithHint: true,
                                ),
                                maxLines: 6,
                                minLines: 3,
                              ),
                              const SizedBox(height: 12),
                              SizedBox(
                                width: double.infinity,
                                child: FilledButton.icon(
                                  onPressed: () {
                                    if (_submissionController.text
                                        .trim()
                                        .isNotEmpty) {
                                      context.read<AssignmentsBloc>().add(
                                        SubmitAssignment(
                                          assignmentId: widget.assignmentId,
                                          submissionText: _submissionController
                                              .text
                                              .trim(),
                                        ),
                                      );
                                      _submissionController.clear();
                                    }
                                  },
                                  icon: const Icon(Icons.send),
                                  label: const Text('Submit'),
                                ),
                              ),
                              const SizedBox(height: 8),
                              SizedBox(
                                width: double.infinity,
                                child: OutlinedButton.icon(
                                  onPressed: () => context
                                      .read<AssignmentsBloc>()
                                      .add(MarkComplete(widget.assignmentId)),
                                  icon: const Icon(
                                    Icons.check_circle_outline,
                                    size: 18,
                                  ),
                                  label: const Text(
                                    'Mark as Complete (no text)',
                                  ),
                                ),
                              ),
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

  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(
              label,
              style: const TextStyle(fontWeight: FontWeight.w500),
            ),
          ),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }
}
