import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../core/network/api_client.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/skeleton.dart';
import '../../books/models/book_model.dart';
import '../models/author_model.dart';

class AuthorDetailScreen extends StatefulWidget {
  final int authorId;
  final String? authorName;

  const AuthorDetailScreen({
    super.key,
    required this.authorId,
    this.authorName,
  });

  @override
  State<AuthorDetailScreen> createState() => _AuthorDetailScreenState();
}

class _AuthorDetailScreenState extends State<AuthorDetailScreen> {
  AuthorModel? _author;
  List<BookModel> _books = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final api = context.read<ApiClient>();
      final response = await api.get('/v1/authors/${widget.authorId}');
      final data = response.data['data'] as Map<String, dynamic>;
      setState(() {
        _author = AuthorModel.fromJson(data);
        _loading = false;
      });
      // Load books by this author
      try {
        final booksResp = await api.get(
          '/v1/books',
          queryParameters: {'author_id': widget.authorId},
        );
        final booksData = booksResp.data['data'] as List<dynamic>? ?? [];
        setState(() {
          _books = booksData
              .map((e) => BookModel.fromJson(e as Map<String, dynamic>))
              .toList();
        });
      } catch (_) {}
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
      appBar: AppBar(
        title: Text(_author?.name ?? widget.authorName ?? 'Author'),
      ),
      body: _loading
          ? const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [
                  SkeletonCard(height: 120),
                  SizedBox(height: 16),
                  SkeletonCard(height: 200),
                ],
              ),
            )
          : _error != null
          ? Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.error_outline, size: 48, color: theme.colorScheme.error),
                  const SizedBox(height: 16),
                  Text(_error!, textAlign: TextAlign.center),
                  const SizedBox(height: 16),
                  FilledButton.tonal(onPressed: _load, child: const Text('Retry')),
                ],
              ),
            )
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // Author header
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      CircleAvatar(
                        radius: 40,
                        backgroundImage: _author?.photo != null
                            ? CachedNetworkImageProvider(_author!.photo!)
                            : null,
                        child: _author?.photo == null
                            ? Text(
                                _author?.name.isNotEmpty == true
                                    ? _author!.name[0].toUpperCase()
                                    : '?',
                                style: const TextStyle(fontSize: 32),
                              )
                            : null,
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              _author?.name ?? '',
                              style: theme.textTheme.headlineSmall?.copyWith(
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            if (_author?.nationality != null) ...[
                              const SizedBox(height: 4),
                              Text(
                                _author!.nationality!,
                                style: theme.textTheme.bodyMedium?.copyWith(
                                  color: theme.colorScheme.onSurfaceVariant,
                                ),
                              ),
                            ],
                            const SizedBox(height: 4),
                            Text(
                              '${_author?.booksCount ?? 0} books',
                              style: theme.textTheme.bodySmall?.copyWith(
                                color: theme.colorScheme.primary,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  if (_author?.biography != null &&
                      _author!.biography!.isNotEmpty) ...[
                    const SizedBox(height: 16),
                    Text(
                      'About',
                      style: theme.textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(_author!.biography!),
                  ],
                  const SizedBox(height: 24),
                  Text(
                    'Books by ${_author?.name ?? 'this author'}',
                    style: theme.textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 12),
                  if (_books.isEmpty)
                    const EmptyState(
                      icon: Icons.menu_book_outlined,
                      title: 'No books found',
                      subtitle: 'No books by this author yet',
                    )
                  else
                    ..._books.map(
                      (book) => Card(
                        margin: const EdgeInsets.only(bottom: 8),
                        child: ListTile(
                          leading: book.coverImage != null
                              ? ClipRRect(
                                  borderRadius: BorderRadius.circular(4),
                                  child: CachedNetworkImage(
                                    imageUrl: book.coverImage!,
                                    width: 40,
                                    height: 56,
                                    fit: BoxFit.cover,
                                    errorWidget: (_, __, ___) => Container(
                                      width: 40,
                                      height: 56,
                                      color: theme.colorScheme.surfaceContainerHighest,
                                      child: const Icon(Icons.menu_book, size: 20),
                                    ),
                                  ),
                                )
                              : CircleAvatar(
                                  child: Text(
                                    book.title.isNotEmpty
                                        ? book.title[0].toUpperCase()
                                        : '?',
                                  ),
                                ),
                          title: Text(
                            book.title,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                          subtitle: Text(
                            book.category ?? '',
                            style: theme.textTheme.bodySmall,
                          ),
                          trailing: const Icon(Icons.chevron_right),
                          onTap: () => context.pushNamed(
                            'book-detail',
                            pathParameters: {'id': '${book.id}'},
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
    );
  }
}
