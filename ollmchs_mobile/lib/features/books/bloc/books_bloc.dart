import 'package:flutter_bloc/flutter_bloc.dart';
import 'books_event.dart';
import 'books_state.dart';
import '../models/book_model.dart';
import '../repositories/books_repository.dart';

class BooksBloc extends Bloc<BooksEvent, BooksState> {
  final BooksRepository _repository;

  BooksBloc({required this._repository}) : super(BooksInitial()) {
    on<LoadBooks>(_onLoadBooks);
    on<LoadBookDetail>(_onLoadBookDetail);
    on<LoadFeaturedBooks>(_onLoadFeaturedBooks);
    on<SearchBooks>(_onSearchBooks);
    on<LoadCategories>(_onLoadCategories);
  }

  Future<void> _onLoadBooks(LoadBooks event, Emitter<BooksState> emit) async {
    if (state is BooksInitial || event.page == 1) {
      emit(BooksLoading());
    }

    try {
      final result = await _repository.getBooks(
        page: event.page,
        search: event.search,
        category: event.category,
      );

      final currentState = state;
      final existingBooks = currentState is BooksLoaded && event.page > 1
          ? currentState.books
          : <BookModel>[];

      emit(
        BooksLoaded(
          books: [...existingBooks, ...result.items],
          hasMore: result.hasMore,
          currentPage: event.page,
          searchQuery: event.search,
          selectedCategory: event.category,
          featuredBooks: currentState is BooksLoaded
              ? currentState.featuredBooks
              : [],
        ),
      );
    } catch (e) {
      emit(BooksError('Failed to load books: ${e.toString()}'));
    }
  }

  Future<void> _onLoadBookDetail(
    LoadBookDetail event,
    Emitter<BooksState> emit,
  ) async {
    if (state is! BooksLoaded) {
      emit(BooksLoading());
    }

    try {
      final book = await _repository.getBookDetail(event.bookId);
      final currentState = state;
      if (currentState is BooksLoaded) {
        emit(currentState.copyWith(selectedBook: book));
      } else {
        emit(BooksLoaded(selectedBook: book));
      }
    } catch (e) {
      emit(BooksError('Failed to load book detail: ${e.toString()}'));
    }
  }

  Future<void> _onLoadFeaturedBooks(
    LoadFeaturedBooks event,
    Emitter<BooksState> emit,
  ) async {
    try {
      final featured = await _repository.getFeaturedBooks();
      final currentState = state;
      if (currentState is BooksLoaded) {
        emit(currentState.copyWith(featuredBooks: featured));
      }
    } catch (_) {}
  }

  Future<void> _onSearchBooks(
    SearchBooks event,
    Emitter<BooksState> emit,
  ) async {
    emit(BooksLoading());
    try {
      final result = await _repository.searchBooks(
        query: event.query,
        page: event.page,
      );
      emit(
        BooksLoaded(
          books: result.items,
          hasMore: result.hasMore,
          currentPage: event.page,
          searchQuery: event.query,
        ),
      );
    } catch (e) {
      emit(BooksError('Search failed: ${e.toString()}'));
    }
  }

  Future<void> _onLoadCategories(
    LoadCategories event,
    Emitter<BooksState> emit,
  ) async {
    // Categories are loaded as part of the books response or via a separate endpoint
    try {
      await _repository.getCategories();
      final currentState = state;
      if (currentState is BooksLoaded) {
        emit(currentState);
      }
    } catch (_) {}
  }
}
