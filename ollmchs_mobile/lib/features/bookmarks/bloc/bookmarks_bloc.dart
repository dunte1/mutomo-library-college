import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/errors/error_mapper.dart';
import '../../../core/network/api_client.dart';
import '../models/bookmark_model.dart';

abstract class BookmarksEvent extends Equatable {
  const BookmarksEvent();
  @override
  List<Object?> get props => [];
}

class LoadBookmarks extends BookmarksEvent {
  final String? type;
  const LoadBookmarks({this.type});
  @override
  List<Object?> get props => [type ?? ''];
}

class AddBookmark extends BookmarksEvent {
  final int? bookId;
  final int? authorId;
  const AddBookmark({this.bookId, this.authorId});
  @override
  List<Object?> get props => [bookId, authorId];
}

class RemoveBookmark extends BookmarksEvent {
  final int bookmarkId;
  const RemoveBookmark(this.bookmarkId);
  @override
  List<Object?> get props => [bookmarkId];
}

abstract class BookmarksState extends Equatable {
  const BookmarksState();
  @override
  List<Object?> get props => [];
}

class BookmarksInitial extends BookmarksState {}

class BookmarksLoading extends BookmarksState {}

class BookmarksLoaded extends BookmarksState {
  final List<BookmarkModel> bookmarks;
  final String? message;
  const BookmarksLoaded({this.bookmarks = const [], this.message});
  @override
  List<Object?> get props => [bookmarks, message];
}

class BookmarksError extends BookmarksState {
  final String error;
  const BookmarksError(this.error);
  @override
  List<Object?> get props => [error];
}

class BookmarksBloc extends Bloc<BookmarksEvent, BookmarksState> {
  final ApiClient _api;

  BookmarksBloc({required ApiClient api}) : _api = api, super(BookmarksInitial()) {
    on<LoadBookmarks>(_onLoad);
    on<AddBookmark>(_onAdd);
    on<RemoveBookmark>(_onRemove);
  }

  Future<void> _onLoad(LoadBookmarks event, Emitter<BookmarksState> emit) async {
    emit(BookmarksLoading());
    try {
      final params = <String, dynamic>{};
      if (event.type != null) params['type'] = event.type;
      final response = await _api.get('/v1/bookmarks', queryParameters: params);
      final data = response.data['data'] as List<dynamic>? ?? [];
      final bookmarks = data
          .map((e) => BookmarkModel.fromJson(e as Map<String, dynamic>))
          .toList();
      emit(BookmarksLoaded(bookmarks: bookmarks));
    } catch (e) {
      emit(BookmarksError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onAdd(AddBookmark event, Emitter<BookmarksState> emit) async {
    try {
      await _api.post('/v1/bookmarks', data: {
        if (event.bookId != null) 'book_id': event.bookId,
        if (event.authorId != null) 'author_id': event.authorId,
      });
      add(const LoadBookmarks());
    } catch (e) {
      emit(BookmarksError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onRemove(RemoveBookmark event, Emitter<BookmarksState> emit) async {
    try {
      await _api.delete('/v1/bookmarks/${event.bookmarkId}');
      final current = state;
      if (current is BookmarksLoaded) {
        final updated = current.bookmarks.where((b) => b.id != event.bookmarkId).toList();
        emit(BookmarksLoaded(bookmarks: updated, message: 'Bookmark removed'));
      }
    } catch (e) {
      emit(BookmarksError(ErrorMapper.map(e)));
    }
  }
}
