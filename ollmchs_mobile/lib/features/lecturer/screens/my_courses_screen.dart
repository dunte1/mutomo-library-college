import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../core/network/api_client.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/skeleton.dart';

class MyCoursesScreen extends StatefulWidget {
  const MyCoursesScreen({super.key});

  @override
  State<MyCoursesScreen> createState() => _MyCoursesScreenState();
}

class _MyCoursesScreenState extends State<MyCoursesScreen> {
  List<Map<String, dynamic>> _courses = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadCourses();
  }

  Future<void> _loadCourses() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final api = context.read<ApiClient>();
      final response = await api.get('/v1/programs');
      final data = response.data['data'] as List<dynamic>? ?? [];
      if (mounted) {
        setState(() {
          _courses = data.cast<Map<String, dynamic>>();
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = 'Failed to load courses: $e';
          _loading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('My Courses')),
      body: _loading
          ? const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [
                  SkeletonCard(height: 80),
                  SkeletonCard(height: 80),
                  SkeletonCard(height: 80),
                ],
              ),
            )
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.error_outline,
                          size: 64, color: theme.colorScheme.error),
                      const SizedBox(height: 16),
                      Text(_error!, textAlign: TextAlign.center),
                      const SizedBox(height: 16),
                      FilledButton.tonal(
                        onPressed: _loadCourses,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : _courses.isEmpty
                  ? const EmptyState(
                      icon: Icons.school_outlined,
                      title: 'No courses assigned',
                      subtitle:
                          'Courses assigned to you will appear here.',
                    )
                  : RefreshIndicator(
                      onRefresh: _loadCourses,
                      child: ListView.builder(
                        padding: const EdgeInsets.all(12),
                        itemCount: _courses.length,
                        itemBuilder: (_, i) {
                          final c = _courses[i];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 8),
                            child: ListTile(
                              leading: CircleAvatar(
                                child: Text(
                                  (c['name'] as String? ?? '?')[0].toUpperCase(),
                                ),
                              ),
                              title: Text(
                                c['name'] as String? ?? '',
                                style: const TextStyle(fontWeight: FontWeight.bold),
                              ),
                              subtitle: Text(
                                [
                                  if (c['code'] != null) 'Code: ${c['code']}',
                                  if (c['department_id'] != null)
                                    'Dept #${c['department_id']}',
                                ].join(' | '),
                              ),
                              trailing: const Icon(Icons.chevron_right),
                              onTap: () {
                                context.pushNamed(
                                  'teacher-assignment-form',
                                );
                              },
                            ),
                          );
                        },
                      ),
                    ),
    );
  }
}
