import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../core/network/api_client.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/skeleton.dart';
import '../../books/models/book_model.dart';
import '../models/publisher_model.dart';

class PublisherDetailScreen extends StatefulWidget {
  final int publisherId;
  final String? publisherName;

  const PublisherDetailScreen({
    super.key,
    required this.publisherId,
    this.publisherName,
  });

  @override
  State<PublisherDetailScreen> createState() => _PublisherDetailScreenState();
}

class _PublisherDetailScreenState extends State<PublisherDetailScreen> {
  PublisherModel? _publisher;
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
      final response = await api.get('/v1/publishers/${widget.publisherId}');
      final data = response.data['data'] as Map<String, dynamic>;
      setState(() {
        _publisher = PublisherModel.fromJson(data);
        _loading = false;
      });
      // Load books by this publisher
      try {
        final booksResp = await api.get(
          '/v1/books',
          queryParameters: {'publisher_id': widget.publisherId},
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
        title: Text(_publisher?.name ?? widget.publisherName ?? 'Publisher'),
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
                  // Publisher header
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        width: 64,
                        height: 64,
                        decoration: BoxDecoration(
                          color: theme.colorScheme.primaryContainer,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(
                          Icons.business,
                          color: theme.colorScheme.primary,
                          size: 32,
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              _publisher?.name ?? '',
                              style: theme.textTheme.headlineSmall?.copyWith(
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '${_publisher?.booksCount ?? 0} books',
                              style: theme.textTheme.bodySmall?.copyWith(
                                color: theme.colorScheme.primary,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  if (_publisher?.description != null &&
                      _publisher!.description!.isNotEmpty) ...[
                    const SizedBox(height: 16),
                    Text(
                      'About',
                      style: theme.textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(_publisher!.description!),
                  ],
                  // Contact info
                  if (_publisher?.website != null ||
                      _publisher?.email != null ||
                      _publisher?.phone != null) ...[
                    const SizedBox(height: 16),
                    Text(
                      'Contact',
                      style: theme.textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    if (_publisher?.website != null)
                      _infoRow(Icons.language, _publisher!.website!),
                    if (_publisher?.email != null)
                      _infoRow(Icons.email_outlined, _publisher!.email!),
                    if (_publisher?.phone != null)
                      _infoRow(Icons.phone_outlined, _publisher!.phone!),
                  ],
                  if (_publisher?.address != null &&
                      _publisher!.address!.isNotEmpty) ...[
                    const SizedBox(height: 12),
                    _infoRow(Icons.location_on_outlined, _publisher!.address!),
                  ],
                  const SizedBox(height: 24),
                  Text(
                    'Books by ${_publisher?.name ?? 'this publisher'}',
                    style: theme.textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 12),
                  if (_books.isEmpty)
                    const EmptyState(
                      icon: Icons.menu_book_outlined,
                      title: 'No books found',
                      subtitle: 'No books from this publisher yet',
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

  Widget _infoRow(IconData icon, String text) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Icon(icon, size: 16, color: Theme.of(context).colorScheme.onSurfaceVariant),
          const SizedBox(width: 8),
          Expanded(child: Text(text, style: Theme.of(context).textTheme.bodyMedium)),
        ],
      ),
    );
  }
}
