import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../bloc/books_bloc.dart';
import '../bloc/books_event.dart';
import '../bloc/books_state.dart';
import '../widgets/book_card.dart';
import '../../../core/widgets/skeleton.dart';

class BookListScreen extends StatefulWidget {
  const BookListScreen({super.key});

  @override
  State<BookListScreen> createState() => _BookListScreenState();
}

class _BookListScreenState extends State<BookListScreen> {
  final _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    context.read<BooksBloc>().add(const LoadBooks());
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent - 200) {
      final state = context.read<BooksBloc>().state;
      if (state is BooksLoaded && state.hasMore) {
        context.read<BooksBloc>().add(LoadBooks(page: state.currentPage + 1));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Books'),
        actions: [
          IconButton(
            icon: const Icon(Icons.search),
            onPressed: () => context.goNamed('books-search'),
          ),
        ],
      ),
      body: BlocBuilder<BooksBloc, BooksState>(
        builder: (context, state) {
          if (state is BooksLoading && state is! BooksLoaded) {
            return const SkeletonGrid();
          }
          if (state is BooksError) {
            return Center(child: Text(state.message));
          }
          if (state is BooksLoaded) {
            if (state.books.isEmpty) {
              return const Center(child: Text('No books found'));
            }
            return RefreshIndicator(
              onRefresh: () async =>
                  context.read<BooksBloc>().add(const LoadBooks()),
              child: GridView.builder(
                controller: _scrollController,
                padding: const EdgeInsets.all(12),
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  childAspectRatio: 0.7,
                  crossAxisSpacing: 12,
                  mainAxisSpacing: 12,
                ),
                itemCount: state.books.length + (state.hasMore ? 1 : 0),
                itemBuilder: (_, i) {
                  if (i >= state.books.length) {
                    return const Center(child: CircularProgressIndicator());
                  }
                  final book = state.books[i];
                  return BookCard(
                    title: book.title,
                    author: book.authors.isNotEmpty
                        ? book.authors.first
                        : 'Unknown',
                    coverUrl: book.coverImage,
                    available: book.availableCopies,
                    total: book.totalCopies,
                    onTap: () => context.goNamed(
                      'book-detail',
                      pathParameters: {'id': '${book.id}'},
                    ),
                  );
                },
              ),
            );
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }
}
