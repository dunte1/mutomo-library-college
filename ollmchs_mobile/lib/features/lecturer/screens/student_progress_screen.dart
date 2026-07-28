import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../widgets/student_progress_card.dart';
import '../../teacher_assignments/bloc/teacher_assignments_bloc.dart';
import '../../../core/widgets/empty_state.dart';

class StudentProgressScreen extends StatefulWidget {
  const StudentProgressScreen({super.key});

  @override
  State<StudentProgressScreen> createState() => _StudentProgressScreenState();
}

class _StudentProgressScreenState extends State<StudentProgressScreen> {
  final _searchController = TextEditingController();
  String? _statusFilter;

  @override
  void initState() {
    super.initState();
    _loadStudents();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _loadStudents() {
    context.read<TeacherAssignmentsBloc>().add(const LoadStudents());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Student Progress')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              children: [
                TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Search students...',
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
                              _loadStudents();
                            },
                          )
                        : null,
                  ),
                  onSubmitted: (_) => _loadStudents(),
                ),
                const SizedBox(height: 8),
                DropdownButtonFormField<String?>(
                  initialValue: _statusFilter,
                  decoration: const InputDecoration(
                    labelText: 'Progress',
                    isDense: true,
                    contentPadding:
                        EdgeInsets.symmetric(horizontal: 8, vertical: 8),
                  ),
                  items: const [
                    DropdownMenuItem(value: null, child: Text('All')),
                    DropdownMenuItem(value: 'excellent', child: Text('Excellent')),
                    DropdownMenuItem(value: 'good', child: Text('Good')),
                    DropdownMenuItem(value: 'needs_improvement', child: Text('Needs Improvement')),
                    DropdownMenuItem(value: 'at_risk', child: Text('At Risk')),
                  ],
                  onChanged: (v) {
                    setState(() => _statusFilter = v);
                  },
                ),
              ],
            ),
          ),
          Expanded(
            child: BlocBuilder<TeacherAssignmentsBloc, TeacherAssignmentsState>(
              builder: (context, state) {
                if (state is TeacherAssignmentsLoading) {
                  return const Center(child: CircularProgressIndicator());
                }
                if (state is TeacherAssignmentsLoaded) {
                  final students = state.availableStudents;
                  if (students.isEmpty) {
                    return const EmptyState(
                      icon: Icons.people_outline,
                      title: 'No students found',
                      subtitle: 'Students enrolled in your programs will appear here.',
                    );
                  }
                  return RefreshIndicator(
                    onRefresh: () async => _loadStudents(),
                    child: ListView.builder(
                      padding: const EdgeInsets.all(12),
                      itemCount: students.length,
                      itemBuilder: (_, i) {
                        final s = students[i];
                        return StudentProgressCard(
                          name: s.name,
                          email: s.email,
                        );
                      },
                    ),
                  );
                }
                if (state is TeacherAssignmentsError) {
                  return Center(child: Text(state.error));
                }
                return const SizedBox.shrink();
              },
            ),
          ),
        ],
      ),
    );
  }
}
