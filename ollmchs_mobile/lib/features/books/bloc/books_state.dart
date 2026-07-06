import 'package:equatable/equatable.dart';
import '../models/book_model.dart';

abstract class BooksState extends Equatable {
  const BooksState();
  @override
  List<Object?> get props => [];
}

class BooksInitial extends BooksState {}

class BooksLoading extends BooksState {}

class BooksLoaded extends BooksState {
  final List<BookModel> books;
  final bool hasMore;
  final int currentPage;
  final String? searchQuery;
  final String? selectedCategory;
  final List<BookModel> featuredBooks;
  final BookModel? selectedBook;

  const BooksLoaded({
    this.books = const [],
    this.hasMore = true,
    this.currentPage = 1,
    this.searchQuery,
    this.selectedCategory,
    this.featuredBooks = const [],
    this.selectedBook,
  });

  BooksLoaded copyWith({
    List<BookModel>? books,
    bool? hasMore,
    int? currentPage,
    String? searchQuery,
    String? selectedCategory,
    List<BookModel>? featuredBooks,
    BookModel? selectedBook,
    bool clearSelectedBook = false,
  }) {
    return BooksLoaded(
      books: books ?? this.books,
      hasMore: hasMore ?? this.hasMore,
      currentPage: currentPage ?? this.currentPage,
      searchQuery: searchQuery ?? this.searchQuery,
      selectedCategory: selectedCategory ?? this.selectedCategory,
      featuredBooks: featuredBooks ?? this.featuredBooks,
      selectedBook: clearSelectedBook
          ? null
          : (selectedBook ?? this.selectedBook),
    );
  }

  @override
  List<Object?> get props => [
    books,
    hasMore,
    currentPage,
    searchQuery,
    selectedCategory,
    featuredBooks,
    selectedBook,
  ];
}

class BooksError extends BooksState {
  final String message;
  const BooksError(this.message);
  @override
  List<Object?> get props => [message];
}
