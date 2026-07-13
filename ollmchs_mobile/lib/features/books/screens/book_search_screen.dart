import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../bloc/books_bloc.dart';
import '../bloc/books_event.dart';
import '../bloc/books_state.dart';
import '../../../core/widgets/empty_state.dart';

class BookSearchScreen extends StatefulWidget {
  const BookSearchScreen({super.key});

  @override
  State<BookSearchScreen> createState() => _BookSearchScreenState();
}

class _BookSearchScreenState extends State<BookSearchScreen> {
  final _searchController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _search(String query) {
    if (query.trim().length >= 2) {
      context.read<BooksBloc>().add(SearchBooks(query: query.trim()));
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: TextField(
          controller: _searchController,
          autofocus: true,
          decoration: const InputDecoration(
            hintText: 'Search books...',
            border: InputBorder.none,
          ),
          textInputAction: TextInputAction.search,
          onSubmitted: _search,
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.search),
            onPressed: () => _search(_searchController.text),
          ),
        ],
      ),
      body: BlocBuilder<BooksBloc, BooksState>(
        builder: (context, state) {
          if (state is BooksLoading) {
            return const Center(child: CircularProgressIndicator());
          }
          if (state is BooksError) {
            return Center(child: Text(state.message));
          }
          if (state is BooksLoaded && state.searchQuery != null) {
            if (state.books.isEmpty) {
              return const EmptyState(
                icon: Icons.search_off,
                title: 'No results found',
                subtitle: 'Try adjusting your search terms',
              );
            }
            return ListView.separated(
              padding: const EdgeInsets.all(12),
              itemCount: state.books.length,
              separatorBuilder: (_, __) => const Divider(),
              itemBuilder: (_, i) {
                final book = state.books[i];
                return ListTile(
                  leading: Container(
                    width: 40,
                    height: 56,
                    color: theme.colorScheme.surfaceContainerHighest,
                    child: book.coverImage != null
                        ? CachedNetworkImage(
                            imageUrl: book.coverImage!,
                            fit: BoxFit.cover,
                            placeholder: (_, __) => Container(color: Colors.grey[200]),
                            errorWidget: (_, __, ___) => Icon(Icons.broken_image, color: Colors.grey),
                          )
                        : const Icon(Icons.book),
                  ),
                  title: Text(
                    book.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  subtitle: Text(
                    book.authors.isNotEmpty ? book.authors.first : 'Unknown',
                  ),
                  trailing: Text(
                    '${book.availableCopies} available',
                    style: TextStyle(
                      color: book.availableCopies > 0
                          ? Colors.green
                          : Colors.red,
                    ),
                  ),
                  onTap: () => context.goNamed(
                    'book-detail',
                    pathParameters: {'id': '${book.id}'},
                  ),
                );
              },
            );
          }
          return const Center(child: Text('Search for books'));
        },
      ),
    );
  }
}
