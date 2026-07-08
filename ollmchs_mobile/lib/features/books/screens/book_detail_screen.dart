import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../bloc/books_bloc.dart';
import '../bloc/books_event.dart';
import '../bloc/books_state.dart';
import '../models/book_copy_model.dart';
import '../../reservations/bloc/reservations_bloc.dart';
import '../../reservations/bloc/reservations_event.dart';
import '../../reservations/bloc/reservations_state.dart';

class BookDetailScreen extends StatefulWidget {
  final int bookId;
  const BookDetailScreen({super.key, required this.bookId});

  @override
  State<BookDetailScreen> createState() => _BookDetailScreenState();
}

class _BookDetailScreenState extends State<BookDetailScreen> {
  @override
  void initState() {
    super.initState();
    context.read<BooksBloc>().add(LoadBookDetail(widget.bookId));
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return BlocListener<ReservationsBloc, ReservationsState>(
      listener: (context, state) {
        if (state is ReservationsLoaded && state.message != null) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(state.message!)),
          );
        } else if (state is ReservationsError) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(state.error),
              backgroundColor: Theme.of(context).colorScheme.error,
            ),
          );
        }
      },
      child: Scaffold(
        appBar: AppBar(title: const Text('Book Details')),
        body: BlocBuilder<BooksBloc, BooksState>(
          builder: (context, state) {
            if (state is BooksLoading && state is! BooksLoaded) {
              return const Center(child: CircularProgressIndicator());
            }
            if (state is BooksError) {
              return Center(child: Text(state.message));
            }
            if (state is BooksLoaded && state.selectedBook != null) {
              final book = state.selectedBook!;
              final isAvailable = book.availableCopies > 0;

              return SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      height: 300,
                      width: double.infinity,
                      color: theme.colorScheme.surfaceContainerHighest,
                      child: book.coverImage != null
                          ? CachedNetworkImage(
                              imageUrl: book.coverImage!,
                              fit: BoxFit.contain,
                              errorWidget: (_, __, ___) =>
                                  const Icon(Icons.book, size: 80),
                            )
                          : const Icon(Icons.book, size: 80),
                    ),
                    Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            book.title,
                            style: theme.textTheme.headlineSmall?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 8),
                          if (book.authors.isNotEmpty)
                            Text(
                              'by ${book.authors.join(", ")}',
                              style: theme.textTheme.bodyLarge?.copyWith(
                                color: theme.colorScheme.onSurfaceVariant,
                              ),
                            ),
                          const SizedBox(height: 8),
                          Wrap(
                            spacing: 16,
                            runSpacing: 8,
                            children: [
                              if (book.isbn != null)
                                _MetaChip(label: 'ISBN', value: book.isbn!),
                              if (book.language != null)
                                _MetaChip(
                                  label: 'Language',
                                  value: book.language!,
                                ),
                              if (book.publicationYear != null)
                                _MetaChip(
                                  label: 'Year',
                                  value: '${book.publicationYear}',
                                ),
                              if (book.pageCount != null)
                                _MetaChip(
                                  label: 'Pages',
                                  value: '${book.pageCount}',
                                ),
                            ],
                          ),
                          const SizedBox(height: 16),
                          Row(
                            children: [
                              Icon(
                                isAvailable
                                    ? Icons.check_circle
                                    : Icons.cancel,
                                color: isAvailable ? Colors.green : Colors.red,
                              ),
                              const SizedBox(width: 8),
                              Text(
                                isAvailable
                                    ? '${book.availableCopies} copies available'
                                    : 'Currently unavailable',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w600,
                                  color: isAvailable
                                      ? Colors.green
                                      : Colors.red,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),
                          if (book.description != null &&
                              book.description!.isNotEmpty) ...[
                            Text(
                              'Description',
                              style: theme.textTheme.titleMedium?.copyWith(
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              book.description!,
                              style: theme.textTheme.bodyMedium,
                            ),
                            const SizedBox(height: 16),
                          ],
                          if (book.location != null ||
                              book.deweyDecimal != null) ...[
                            Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: theme
                                    .colorScheme
                                    .surfaceContainerHighest,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Column(
                                crossAxisAlignment:
                                    CrossAxisAlignment.start,
                                children: [
                                  if (book.location != null)
                                    Text(
                                      'Shelf Location: ${book.location}',
                                      style: theme.textTheme.bodyMedium,
                                    ),
                                  if (book.deweyDecimal != null)
                                    Text(
                                      'Dewey Decimal: ${book.deweyDecimal}',
                                      style: theme.textTheme.bodyMedium,
                                    ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 16),
                          ],
                          if (book.copies.isNotEmpty)
                            _CopiesSection(
                              copies: book.copies,
                              theme: theme,
                            ),
                          Row(
                            children: [
                              if (isAvailable)
                                Expanded(
                                  child: FilledButton.icon(
                                    onPressed: () {
                                      context.read<ReservationsBloc>().add(
                                        CreateReservation(book.id),
                                      );
                                    },
                                    icon: const Icon(Icons.bookmark_add),
                                    label: const Text('Reserve'),
                                  ),
                                )
                              else
                                Expanded(
                                  child: FilledButton.tonalIcon(
                                    onPressed: null,
                                    icon: const Icon(Icons.bookmark_add),
                                    label: const Text('Unavailable'),
                                  ),
                                ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              );
            }
            return const SizedBox.shrink();
          },
        ),
      ),
    );
  }
}

class _CopiesSection extends StatelessWidget {
  final List<BookCopyModel> copies;
  final ThemeData theme;
  const _CopiesSection({required this.copies, required this.theme});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Copies (${copies.length})',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          ...copies.map(
            (copy) => Card(
              margin: const EdgeInsets.only(bottom: 6),
              child: Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 10,
                ),
                child: Row(
                  children: [
                    Icon(
                      copy.isAvailable ? Icons.check_circle : Icons.block,
                      size: 18,
                      color: copy.isAvailable
                          ? Colors.green
                          : Colors.orange,
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (copy.barcode != null)
                            Text(
                              copy.barcode!,
                              style: theme.textTheme.bodySmall?.copyWith(
                                fontWeight: FontWeight.w600,
                                fontFamily: 'monospace',
                              ),
                            ),
                          if (copy.shelfLocation != null)
                            Text(
                              'Shelf: ${copy.shelfLocation}',
                              style: theme.textTheme.bodySmall,
                            ),
                          if (copy.currentBorrower != null)
                            Text(
                              'Borrowed by: ${copy.currentBorrower}',
                              style: theme.textTheme.bodySmall?.copyWith(
                                color: Colors.orange,
                              ),
                            ),
                        ],
                      ),
                    ),
                    Chip(
                      label: Text(
                        copy.status,
                        style: const TextStyle(fontSize: 10),
                      ),
                      visualDensity: VisualDensity.compact,
                      backgroundColor: copy.isAvailable
                          ? Colors.green.withValues(alpha: 0.1)
                          : Colors.orange.withValues(alpha: 0.1),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _MetaChip extends StatelessWidget {
  final String label;
  final String value;
  const _MetaChip({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Chip(
      label: Text('$label: $value', style: const TextStyle(fontSize: 12)),
      visualDensity: VisualDensity.compact,
    );
  }
}
