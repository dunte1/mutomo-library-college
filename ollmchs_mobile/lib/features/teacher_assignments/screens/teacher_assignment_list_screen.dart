import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/teacher_assignments_bloc.dart';
import '../models/teacher_assignment_model.dart';

class TeacherAssignmentListScreen extends StatefulWidget {
  const TeacherAssignmentListScreen({super.key});

  @override
  State<TeacherAssignmentListScreen> createState() =>
      _TeacherAssignmentListScreenState();
}

class _TeacherAssignmentListScreenState
    extends State<TeacherAssignmentListScreen> {
  String? _typeFilter;
  String? _statusFilter;
  final _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    context.read<TeacherAssignmentsBloc>().add(const LoadTeacherAssignments());
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _load() {
    context.read<TeacherAssignmentsBloc>().add(
      LoadTeacherAssignments(
        type: _typeFilter,
        status: _statusFilter,
        search: _searchController.text.isNotEmpty
            ? _searchController.text
            : null,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Assignments'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            tooltip: 'New Assignment',
            onPressed: () => context.pushNamed('teacher-assignment-form'),
          ),
        ],
      ),
      body: Column(
        children: [
          // Filters
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            child: Column(
              children: [
                TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Search by title or student...',
                    prefixIcon: const Icon(Icons.search, size: 20),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                    isDense: true,
                    contentPadding: const EdgeInsets.symmetric(vertical: 8),
                    suffixIcon: _searchController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear, size: 18),
                            onPressed: () {
                              _searchController.clear();
                              _load();
                            },
                          )
                        : null,
                  ),
                  onSubmitted: (_) => _load(),
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: DropdownButtonFormField<String?>(
                        initialValue: _typeFilter,
                        decoration: const InputDecoration(
                          labelText: 'Type',
                          isDense: true,
                          contentPadding: EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 8,
                          ),
                        ),
                        items: const [
                          DropdownMenuItem(
                            value: null,
                            child: Text('All Types'),
                          ),
                          DropdownMenuItem(
                            value: 'assignment',
                            child: Text('Assignments'),
                          ),
                          DropdownMenuItem(
                            value: 'recommendation',
                            child: Text('Recommendations'),
                          ),
                        ],
                        onChanged: (v) {
                          setState(() => _typeFilter = v);
                          _load();
                        },
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: DropdownButtonFormField<String?>(
                        initialValue: _statusFilter,
                        decoration: const InputDecoration(
                          labelText: 'Status',
                          isDense: true,
                          contentPadding: EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 8,
                          ),
                        ),
                        items: const [
                          DropdownMenuItem(
                            value: null,
                            child: Text('All Statuses'),
                          ),
                          DropdownMenuItem(
                            value: 'pending',
                            child: Text('Pending'),
                          ),
                          DropdownMenuItem(
                            value: 'in_progress',
                            child: Text('In Progress'),
                          ),
                          DropdownMenuItem(
                            value: 'completed',
                            child: Text('Completed'),
                          ),
                          DropdownMenuItem(
                            value: 'overdue',
                            child: Text('Overdue'),
                          ),
                        ],
                        onChanged: (v) {
                          setState(() => _statusFilter = v);
                          _load();
                        },
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          // List
          Expanded(
            child:
                BlocConsumer<TeacherAssignmentsBloc, TeacherAssignmentsState>(
                  listener: (context, state) {
                    if (state is TeacherAssignmentsLoaded &&
                        state.message != null) {
                      ScaffoldMessenger.of(
                        context,
                      ).showSnackBar(SnackBar(content: Text(state.message!)));
                    }
                    if (state is TeacherAssignmentsError) {
                      ScaffoldMessenger.of(
                        context,
                      ).showSnackBar(SnackBar(content: Text(state.error)));
                    }
                  },
                  builder: (context, state) {
                    if (state is TeacherAssignmentsLoading) {
                      return const Center(child: CircularProgressIndicator());
                    }
                    if (state is TeacherAssignmentsError &&
                        state is! TeacherAssignmentsLoaded) {
                      return Center(child: Text(state.error));
                    }
                    if (state is TeacherAssignmentsLoaded) {
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
                              Text(
                                'No assignments yet',
                                style: theme.textTheme.titleMedium,
                              ),
                              const SizedBox(height: 8),
                              FilledButton.tonal(
                                onPressed: () => context.pushNamed(
                                  'teacher-assignment-form',
                                ),
                                child: const Text('Create Assignment'),
                              ),
                            ],
                          ),
                        );
                      }
                      return RefreshIndicator(
                        onRefresh: () async => _load(),
                        child: ListView.builder(
                          padding: const EdgeInsets.all(12),
                          itemCount: state.assignments.length,
                          itemBuilder: (_, i) =>
                              _buildCard(state.assignments[i], theme),
                        ),
                      );
                    }
                    return const SizedBox.shrink();
                  },
                ),
          ),
        ],
      ),
    );
  }

  Widget _buildCard(TeacherAssignmentModel a, ThemeData theme) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Chip(
                  label: Text(
                    a.isAssignment ? 'Assignment' : 'Recommendation',
                    style: const TextStyle(fontSize: 10),
                  ),
                  visualDensity: VisualDensity.compact,
                  backgroundColor: a.isAssignment
                      ? theme.colorScheme.primaryContainer
                      : theme.colorScheme.tertiaryContainer,
                ),
                const SizedBox(width: 8),
                Chip(
                  label: Text(a.status, style: const TextStyle(fontSize: 10)),
                  visualDensity: VisualDensity.compact,
                ),
                const Spacer(),
                PopupMenuButton<String>(
                  onSelected: (v) {
                    if (v == 'edit') {
                      context.pushNamed('teacher-assignment-form', extra: a);
                    }
                    if (v == 'progress') {
                      context.pushNamed(
                        'teacher-assignment-progress',
                        pathParameters: {'id': '${a.id}'},
                      );
                    }
                    if (v == 'delete') _confirmDelete(a);
                  },
                  itemBuilder: (_) => [
                    const PopupMenuItem(value: 'edit', child: Text('Edit')),
                    const PopupMenuItem(
                      value: 'progress',
                      child: Text('Progress'),
                    ),
                    const PopupMenuItem(value: 'delete', child: Text('Delete')),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              a.title,
              style: theme.textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            if (a.description != null && a.description!.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text(
                a.description!,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: theme.textTheme.bodySmall,
              ),
            ],
            const SizedBox(height: 8),
            Row(
              children: [
                Icon(
                  Icons.person_outline,
                  size: 14,
                  color: theme.colorScheme.onSurfaceVariant,
                ),
                const SizedBox(width: 4),
                Text(
                  a.student?.name ??
                      a.program?.name ??
                      a.department?.name ??
                      'Group',
                  style: theme.textTheme.bodySmall?.copyWith(
                    color: theme.colorScheme.onSurfaceVariant,
                  ),
                ),
              ],
            ),
            if (a.dueAt != null) ...[
              const SizedBox(height: 4),
              Row(
                children: [
                  Icon(
                    Icons.calendar_today,
                    size: 14,
                    color: theme.colorScheme.onSurfaceVariant,
                  ),
                  const SizedBox(width: 4),
                  Text(
                    'Due: ${DateFormat('MMM d, y').format(DateTime.parse(a.dueAt!))}',
                    style: theme.textTheme.bodySmall?.copyWith(
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                  ),
                ],
              ),
            ],
            if (a.book != null || a.digitalAsset != null) ...[
              const SizedBox(height: 6),
              Wrap(
                spacing: 6,
                children: [
                  if (a.book != null)
                    Chip(
                      avatar: const Icon(Icons.menu_book, size: 14),
                      label: Text(
                        a.book!.title,
                        style: const TextStyle(fontSize: 10),
                      ),
                      visualDensity: VisualDensity.compact,
                    ),
                  if (a.digitalAsset != null)
                    Chip(
                      avatar: const Icon(Icons.insert_drive_file, size: 14),
                      label: Text(
                        a.digitalAsset!.title,
                        style: const TextStyle(fontSize: 10),
                      ),
                      visualDensity: VisualDensity.compact,
                    ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  Future<void> _confirmDelete(TeacherAssignmentModel a) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete Assignment'),
        content: Text('Delete "${a.title}"? This cannot be undone.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: FilledButton.styleFrom(
              backgroundColor: Theme.of(ctx).colorScheme.error,
            ),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
    if (confirmed == true) {
      if (!context.mounted) return;
      context.read<TeacherAssignmentsBloc>().add(DeleteTeacherAssignment(a.id));
    }
  }
}
