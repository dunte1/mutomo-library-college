import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../bloc/teacher_assignments_bloc.dart';
import '../models/teacher_assignment_model.dart';

class TeacherAssignmentFormScreen extends StatefulWidget {
  final TeacherAssignmentModel? editAssignment;
  const TeacherAssignmentFormScreen({super.key, this.editAssignment});

  @override
  State<TeacherAssignmentFormScreen> createState() =>
      _TeacherAssignmentFormScreenState();
}

class _TeacherAssignmentFormScreenState
    extends State<TeacherAssignmentFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _notesController = TextEditingController();

  String _assignTo = 'student';
  String _type = 'assignment';
  int? _studentId;
  int? _programId;
  int? _departmentId;
  int? _bookId;
  int? _digitalAssetId;
  DateTime? _dueDate;

  bool _editing = false;
  bool _loaded = false;

  @override
  void initState() {
    super.initState();
    if (widget.editAssignment != null) {
      _editing = true;
      final a = widget.editAssignment!;
      _titleController.text = a.title;
      _descriptionController.text = a.description ?? '';
      _notesController.text = a.notes ?? '';
      _type = a.type;
      _dueDate = a.dueAt != null ? DateTime.tryParse(a.dueAt!) : null;
      if (a.student != null) {
        _assignTo = 'student';
        _studentId = a.student!.id;
      } else if (a.program != null) {
        _assignTo = 'program';
        _programId = a.program!.id;
      } else if (a.department != null) {
        _assignTo = 'department';
        _departmentId = a.department!.id;
      }
      _bookId = a.book?.id;
      _digitalAssetId = a.digitalAsset?.id;
    }
    _loadReferences();
  }

  void _loadReferences() {
    final bloc = context.read<TeacherAssignmentsBloc>();
    bloc.add(const LoadPrograms());
    bloc.add(const LoadDepartments());
    bloc.add(const LoadBooks());
    bloc.add(const LoadDigitalAssets());
    bloc.add(const LoadStudents());
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: Text(_editing ? 'Edit Assignment' : 'New Assignment'),
      ),
      body: BlocConsumer<TeacherAssignmentsBloc, TeacherAssignmentsState>(
        listener: (context, state) {
          if (state is TeacherAssignmentsLoaded && state.message != null) {
            ScaffoldMessenger.of(
              context,
            ).showSnackBar(SnackBar(content: Text(state.message!)));
            context.pop();
          }
          if (state is TeacherAssignmentsError && _loaded) {
            ScaffoldMessenger.of(
              context,
            ).showSnackBar(SnackBar(content: Text(state.error)));
          }
          if (state is TeacherAssignmentsLoaded) _loaded = true;
        },
        builder: (context, state) {
          final loaded = state is TeacherAssignmentsLoaded;
          final students = loaded ? state.availableStudents : <StudentItem>[];
          final programs = loaded ? state.availablePrograms : <ProgramItem>[];
          final departments = loaded
              ? state.availableDepartments
              : <DepartmentItem>[];
          final books = loaded ? state.availableBooks : <BookInfo>[];
          final assets = loaded ? state.availableAssets : <AssetInfo>[];

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Assign To
                  if (!_editing) ...[
                    Text(
                      'Assign To',
                      style: theme.textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                          child: _RadioCard(
                            label: 'Student',
                            value: 'student',
                            group: _assignTo,
                            onTap: () => setState(() => _assignTo = 'student'),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: _RadioCard(
                            label: 'Program',
                            value: 'program',
                            group: _assignTo,
                            onTap: () => setState(() => _assignTo = 'program'),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: _RadioCard(
                            label: 'Department',
                            value: 'department',
                            group: _assignTo,
                            onTap: () =>
                                setState(() => _assignTo = 'department'),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),

                    if (_assignTo == 'student')
                      DropdownButtonFormField<int>(
                        initialValue: _studentId,
                        decoration: const InputDecoration(
                          labelText: 'Select Student',
                          border: OutlineInputBorder(),
                        ),
                        items: students
                            .map(
                              (s) => DropdownMenuItem(
                                value: s.id,
                                child: Text(
                                  '${s.name}${s.email != null ? ' (${s.email})' : ''}',
                                ),
                              ),
                            )
                            .toList(),
                        onChanged: (v) => setState(() => _studentId = v),
                        validator: (v) => _assignTo == 'student' && v == null
                            ? 'Required'
                            : null,
                      ),
                    if (_assignTo == 'program')
                      DropdownButtonFormField<int>(
                        initialValue: _programId,
                        decoration: const InputDecoration(
                          labelText: 'Select Program',
                          border: OutlineInputBorder(),
                        ),
                        items: programs
                            .map(
                              (p) => DropdownMenuItem(
                                value: p.id,
                                child: Text(p.name),
                              ),
                            )
                            .toList(),
                        onChanged: (v) {
                          setState(() => _programId = v);
                          context.read<TeacherAssignmentsBloc>().add(
                            LoadStudents(programId: v),
                          );
                        },
                        validator: (v) => _assignTo == 'program' && v == null
                            ? 'Required'
                            : null,
                      ),
                    if (_assignTo == 'department')
                      DropdownButtonFormField<int>(
                        initialValue: _departmentId,
                        decoration: const InputDecoration(
                          labelText: 'Select Department',
                          border: OutlineInputBorder(),
                        ),
                        items: departments
                            .map(
                              (d) => DropdownMenuItem(
                                value: d.id,
                                child: Text(d.name),
                              ),
                            )
                            .toList(),
                        onChanged: (v) {
                          setState(() => _departmentId = v);
                          context.read<TeacherAssignmentsBloc>().add(
                            LoadStudents(departmentId: v),
                          );
                        },
                        validator: (v) => _assignTo == 'department' && v == null
                            ? 'Required'
                            : null,
                      ),
                    const SizedBox(height: 16),
                  ],

                  // Type
                  Text(
                    'Type',
                    style: theme.textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Expanded(
                        child: _RadioCard(
                          label: 'Assignment',
                          value: 'assignment',
                          group: _type,
                          onTap: () => setState(() => _type = 'assignment'),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: _RadioCard(
                          label: 'Recommendation',
                          value: 'recommendation',
                          group: _type,
                          onTap: () => setState(() => _type = 'recommendation'),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Due Date (only for assignments)
                  if (_type == 'assignment') ...[
                    InkWell(
                      onTap: () async {
                        final date = await showDatePicker(
                          context: context,
                          initialDate:
                              _dueDate ??
                              DateTime.now().add(const Duration(days: 7)),
                          firstDate: DateTime.now(),
                          lastDate: DateTime.now().add(
                            const Duration(days: 365),
                          ),
                        );
                        if (date != null) setState(() => _dueDate = date);
                      },
                      child: InputDecorator(
                        decoration: const InputDecoration(
                          labelText: 'Due Date',
                          border: OutlineInputBorder(),
                        ),
                        child: Text(
                          _dueDate != null
                              ? '${_dueDate!.day}/${_dueDate!.month}/${_dueDate!.year}'
                              : 'Tap to select',
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],

                  // Title
                  TextFormField(
                    controller: _titleController,
                    decoration: const InputDecoration(
                      labelText: 'Title',
                      border: OutlineInputBorder(),
                    ),
                    validator: (v) =>
                        v == null || v.trim().isEmpty ? 'Required' : null,
                  ),
                  const SizedBox(height: 16),

                  // Description
                  TextFormField(
                    controller: _descriptionController,
                    decoration: const InputDecoration(
                      labelText: 'Description (optional)',
                      border: OutlineInputBorder(),
                      alignLabelWithHint: true,
                    ),
                    maxLines: 4,
                  ),
                  const SizedBox(height: 16),

                  // Book
                  DropdownButtonFormField<int?>(
                    initialValue: _bookId,
                    decoration: const InputDecoration(
                      labelText: 'Linked Book (optional)',
                      border: OutlineInputBorder(),
                    ),
                    items: [
                      const DropdownMenuItem(value: null, child: Text('None')),
                      ...books.map(
                        (b) => DropdownMenuItem(
                          value: b.id,
                          child: Text(
                            b.title,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ),
                    ],
                    onChanged: (v) => setState(() => _bookId = v),
                  ),
                  const SizedBox(height: 16),

                  // Digital Asset
                  DropdownButtonFormField<int?>(
                    initialValue: _digitalAssetId,
                    decoration: const InputDecoration(
                      labelText: 'Linked Digital Asset (optional)',
                      border: OutlineInputBorder(),
                    ),
                    items: [
                      const DropdownMenuItem(value: null, child: Text('None')),
                      ...assets.map(
                        (a) => DropdownMenuItem(
                          value: a.id,
                          child: Text(
                            a.title,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ),
                    ],
                    onChanged: (v) => setState(() => _digitalAssetId = v),
                  ),
                  const SizedBox(height: 16),

                  // Notes
                  TextFormField(
                    controller: _notesController,
                    decoration: const InputDecoration(
                      labelText: 'Private Notes (optional)',
                      border: OutlineInputBorder(),
                      alignLabelWithHint: true,
                    ),
                    maxLines: 2,
                  ),
                  const SizedBox(height: 24),

                  // Submit
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton(
                      onPressed: state is TeacherAssignmentsLoading
                          ? null
                          : _onSubmit,
                      child: state is TeacherAssignmentsLoading
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : Text(
                              _editing
                                  ? 'Update'
                                  : (_assignTo != 'student'
                                        ? 'Assign to Group'
                                        : 'Create'),
                            ),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  void _onSubmit() {
    if (!_formKey.currentState!.validate()) return;

    final bloc = context.read<TeacherAssignmentsBloc>();

    if (_editing) {
      bloc.add(
        UpdateTeacherAssignment(
          id: widget.editAssignment!.id,
          title: _titleController.text.trim(),
          description: _descriptionController.text.trim().isEmpty
              ? null
              : _descriptionController.text.trim(),
          dueDate: _dueDate?.toIso8601String(),
          type: _type,
          bookId: _bookId,
          digitalAssetId: _digitalAssetId,
          notes: _notesController.text.trim().isEmpty
              ? null
              : _notesController.text.trim(),
        ),
      );
    } else {
      bloc.add(
        CreateTeacherAssignment(
          assignTo: _assignTo,
          studentId: _studentId,
          programId: _programId,
          departmentId: _departmentId,
          type: _type,
          title: _titleController.text.trim(),
          description: _descriptionController.text.trim().isEmpty
              ? null
              : _descriptionController.text.trim(),
          dueDate: _type == 'assignment' ? _dueDate?.toIso8601String() : null,
          bookId: _bookId,
          digitalAssetId: _digitalAssetId,
          notes: _notesController.text.trim().isEmpty
              ? null
              : _notesController.text.trim(),
        ),
      );
    }
  }
}

class _RadioCard extends StatelessWidget {
  final String label;
  final String value;
  final String group;
  final VoidCallback onTap;

  const _RadioCard({
    required this.label,
    required this.value,
    required this.group,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final selected = value == group;
    final theme = Theme.of(context);
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          border: Border.all(
            color: selected ? theme.colorScheme.primary : theme.dividerColor,
            width: selected ? 2 : 1,
          ),
          borderRadius: BorderRadius.circular(8),
          color: selected
              ? theme.colorScheme.primaryContainer.withValues(alpha: 0.3)
              : null,
        ),
        child: Center(
          child: Text(
            label,
            style: TextStyle(
              fontWeight: selected ? FontWeight.bold : FontWeight.normal,
            ),
          ),
        ),
      ),
    );
  }
}
