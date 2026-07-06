import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../core/network/api_client.dart';
import '../models/book_model.dart';
import '../models/category_model.dart';

class NewArrivalsScreen extends StatefulWidget {
  const NewArrivalsScreen({super.key});

  @override
  State<NewArrivalsScreen> createState() => _NewArrivalsScreenState();
}

class _NewArrivalsScreenState extends State<NewArrivalsScreen> {
  late final ApiClient _api;
  int _period = 30;
  int? _categoryId;
  List<BookModel> _books = [];
  List<CategoryModel> _categories = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _api = context.read<ApiClient>();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final results = await Future.wait([
        _api.get(
          '/v1/books',
          queryParameters: {
            'days': _period,
            if (_categoryId != null) 'category_id': _categoryId,
            'per_page': 50,
          },
        ),
        _api.get('/v1/categories'),
      ]);
      final booksData = results[0].data['data'] as List<dynamic>? ?? [];
      final categoriesData = results[1].data['data'] as List<dynamic>? ?? [];
      setState(() {
        _books = booksData
            .map((e) => BookModel.fromJson(e as Map<String, dynamic>))
            .toList();
        _categories = categoriesData
            .map((e) => CategoryModel.fromJson(e as Map<String, dynamic>))
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
      appBar: AppBar(title: const Text('New Arrivals')),
      body: Column(
        children: [
          // Filters
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: theme.colorScheme.surface,
              border: Border(bottom: BorderSide(color: theme.dividerColor)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Time Period',
                  style: theme.textTheme.labelMedium?.copyWith(
                    color: theme.colorScheme.onSurfaceVariant,
                  ),
                ),
                const SizedBox(height: 8),
                Row(
                  children: [7, 30, 90]
                      .map(
                        (d) => Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: FilterChip(
                            label: Text('$d days'),
                            selected: _period == d,
                            onSelected: (_) {
                              setState(() => _period = d);
                              _load();
                            },
                          ),
                        ),
                      )
                      .toList(),
                ),
                const SizedBox(height: 8),
                DropdownButtonFormField<int?>(
                  value: _categoryId,
                  decoration: const InputDecoration(
                    labelText: 'Category',
                    border: OutlineInputBorder(),
                    contentPadding: EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 8,
                    ),
                    isDense: true,
                  ),
                  items: [
                    const DropdownMenuItem(
                      value: null,
                      child: Text('All Categories'),
                    ),
                    ..._categories.map(
                      (c) => DropdownMenuItem(value: c.id, child: Text(c.name)),
                    ),
                  ],
                  onChanged: (v) {
                    setState(() => _categoryId = v);
                    _load();
                  },
                ),
              ],
            ),
          ),
          // Results
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _error != null
                ? Center(child: Text(_error!))
                : _books.isEmpty
                ? Center(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          Icons.library_books_outlined,
                          size: 64,
                          color: theme.colorScheme.onSurfaceVariant,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No new arrivals found',
                          style: theme.textTheme.titleMedium,
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Try a different period or category',
                          style: theme.textTheme.bodySmall?.copyWith(
                            color: theme.colorScheme.onSurfaceVariant,
                          ),
                        ),
                      ],
                    ),
                  )
                : RefreshIndicator(
                    onRefresh: _load,
                    child: GridView.builder(
                      padding: const EdgeInsets.all(12),
                      gridDelegate:
                          const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            childAspectRatio: 0.7,
                            crossAxisSpacing: 12,
                            mainAxisSpacing: 12,
                          ),
                      itemCount: _books.length,
                      itemBuilder: (_, i) {
                        final book = _books[i];
                        return Card(
                          clipBehavior: Clip.antiAlias,
                          child: InkWell(
                            onTap: () => context.goNamed(
                              'book-detail',
                              pathParameters: {'id': '${book.id}'},
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Expanded(
                                  child: Container(
                                    width: double.infinity,
                                    color: theme
                                        .colorScheme
                                        .surfaceContainerHighest,
                                    child: book.coverImage != null
                                        ? Image.network(
                                            book.coverImage!,
                                            fit: BoxFit.cover,
                                            errorBuilder: (_, __, ___) =>
                                                const Icon(
                                                  Icons.book,
                                                  size: 48,
                                                ),
                                          )
                                        : const Icon(Icons.book, size: 48),
                                  ),
                                ),
                                Padding(
                                  padding: const EdgeInsets.all(8),
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        book.title,
                                        maxLines: 2,
                                        overflow: TextOverflow.ellipsis,
                                        style: const TextStyle(
                                          fontWeight: FontWeight.w600,
                                          fontSize: 13,
                                        ),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        book.authors.isNotEmpty
                                            ? book.authors.first
                                            : 'Unknown',
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                        style: TextStyle(
                                          fontSize: 11,
                                          color: theme
                                              .colorScheme
                                              .onSurfaceVariant,
                                        ),
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        '$book.availableCopies/$book.totalCopies available',
                                        style: TextStyle(
                                          fontSize: 11,
                                          color: book.availableCopies > 0
                                              ? Colors.green
                                              : Colors.red,
                                          fontWeight: FontWeight.w500,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  ),
          ),
        ],
      ),
    );
  }
}
