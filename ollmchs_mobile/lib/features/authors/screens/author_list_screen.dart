import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../core/network/api_client.dart';
import '../../../core/widgets/empty_state.dart';
import '../models/author_model.dart';

class AuthorListScreen extends StatefulWidget {
  const AuthorListScreen({super.key});

  @override
  State<AuthorListScreen> createState() => _AuthorListScreenState();
}

class _AuthorListScreenState extends State<AuthorListScreen> {
  List<AuthorModel> _authors = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final api = context.read<ApiClient>();
      final response = await api.get('/v1/authors');
      final data = response.data['data'] as List<dynamic>? ?? [];
      setState(() {
        _authors = data
            .map((e) => AuthorModel.fromJson(e as Map<String, dynamic>))
            .toList();
        _loading = false;
        _error = null;
      });
    } catch (e) {
      setState(() {
        _loading = false;
        _error = e.toString();
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Authors')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
          ? Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    Icons.error_outline,
                    size: 48,
                    color: theme.colorScheme.error,
                  ),
                  const SizedBox(height: 16),
                  Text(_error!, textAlign: TextAlign.center),
                  const SizedBox(height: 16),
                  FilledButton.tonal(
                    onPressed: _load,
                    child: const Text('Retry'),
                  ),
                ],
              ),
            )
          : _authors.isEmpty
          ? const EmptyState(
              icon: Icons.people_outline,
              title: 'No authors found',
              subtitle: 'Authors will appear here once added',
            )
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView.separated(
                padding: const EdgeInsets.all(12),
                itemCount: _authors.length,
                separatorBuilder: (_, __) => const Divider(),
                itemBuilder: (_, i) {
                  final author = _authors[i];
                  return ListTile(
                    leading: CircleAvatar(
                      backgroundImage: author.photo != null
                          ? NetworkImage(author.photo!)
                          : null,
                      child: author.photo == null
                          ? Text(author.name[0].toUpperCase())
                          : null,
                    ),
                    title: Text(
                      author.name,
                      style: const TextStyle(fontWeight: FontWeight.w600),
                    ),
                    subtitle: Text(
                      '${author.booksCount} books${author.nationality != null ? ' • ${author.nationality}' : ''}',
                    ),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: () => context.pushNamed(
                      'author-detail',
                      pathParameters: {'id': '${author.id}'},
                      extra: author.name,
                    ),
                  );
                },
              ),
            ),
    );
  }
}
