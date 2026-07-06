import 'package:equatable/equatable.dart';

abstract class BooksEvent extends Equatable {
  const BooksEvent();
  @override
  List<Object?> get props => [];
}

class LoadBooks extends BooksEvent {
  final int page;
  final String? search;
  final String? category;
  const LoadBooks({this.page = 1, this.search, this.category});
  @override
  List<Object?> get props => [page, search, category];
}

class LoadBookDetail extends BooksEvent {
  final int bookId;
  const LoadBookDetail(this.bookId);
  @override
  List<Object?> get props => [bookId];
}

class LoadFeaturedBooks extends BooksEvent {
  const LoadFeaturedBooks();
}

class SearchBooks extends BooksEvent {
  final String query;
  final int page;
  const SearchBooks({required this.query, this.page = 1});
  @override
  List<Object?> get props => [query, page];
}

class LoadCategories extends BooksEvent {
  const LoadCategories();
}
