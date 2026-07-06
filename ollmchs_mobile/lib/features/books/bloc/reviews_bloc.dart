import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/network/api_client.dart';
import '../models/review_model.dart';

// Events
abstract class ReviewsEvent extends Equatable {
  const ReviewsEvent();
  @override
  List<Object?> get props => [];
}

class LoadReviews extends ReviewsEvent {
  final int bookId;
  const LoadReviews(this.bookId);
  @override
  List<Object?> get props => [bookId];
}

class SubmitReview extends ReviewsEvent {
  final int bookId;
  final int rating;
  final String? review;
  const SubmitReview({required this.bookId, required this.rating, this.review});
  @override
  List<Object?> get props => [bookId, rating, review ?? ''];
}

class LoadMyReviews extends ReviewsEvent {
  const LoadMyReviews();
}

// States
abstract class ReviewsState extends Equatable {
  const ReviewsState();
  @override
  List<Object?> get props => [];
}

class ReviewsInitial extends ReviewsState {}

class ReviewsLoading extends ReviewsState {}

class ReviewsLoaded extends ReviewsState {
  final List<ReviewModel> reviews;
  final ReviewStats stats;
  final String? message;

  const ReviewsLoaded({
    this.reviews = const [],
    this.stats = const ReviewStats(),
    this.message,
  });
  @override
  List<Object?> get props => [reviews, stats, message];
}

class ReviewsError extends ReviewsState {
  final String error;
  const ReviewsError(this.error);
  @override
  List<Object?> get props => [error];
}

// Bloc
class ReviewsBloc extends Bloc<ReviewsEvent, ReviewsState> {
  final ApiClient _api;

  ReviewsBloc({required ApiClient api}) : _api = api, super(ReviewsInitial()) {
    on<LoadReviews>(_onLoadReviews);
    on<SubmitReview>(_onSubmitReview);
    on<LoadMyReviews>(_onLoadMyReviews);
  }

  Future<void> _onLoadReviews(
    LoadReviews event,
    Emitter<ReviewsState> emit,
  ) async {
    emit(ReviewsLoading());
    try {
      final response = await _api.get('/v1/books/${event.bookId}/reviews');
      final data = response.data['data'] as List<dynamic>? ?? [];
      final meta = response.data['meta'] as Map<String, dynamic>? ?? {};
      final reviews = data
          .map((e) => ReviewModel.fromJson(e as Map<String, dynamic>))
          .toList();
      final stats = ReviewStats.fromJson(meta);
      emit(ReviewsLoaded(reviews: reviews, stats: stats));
    } catch (e) {
      emit(ReviewsError('Failed to load reviews: ${e.toString()}'));
    }
  }

  Future<void> _onSubmitReview(
    SubmitReview event,
    Emitter<ReviewsState> emit,
  ) async {
    try {
      await _api.post(
        '/v1/books/reviews',
        data: {
          'book_id': event.bookId,
          'rating': event.rating,
          if (event.review != null) 'review': event.review,
        },
      );
      add(LoadReviews(event.bookId));
      emit(ReviewsLoaded(message: 'Review submitted for approval'));
    } catch (e) {
      emit(ReviewsError('Failed to submit review: ${e.toString()}'));
    }
  }

  Future<void> _onLoadMyReviews(
    LoadMyReviews event,
    Emitter<ReviewsState> emit,
  ) async {
    emit(ReviewsLoading());
    try {
      final response = await _api.get('/v1/my-reviews');
      final data = response.data['data'] as List<dynamic>? ?? [];
      final reviews = data
          .map((e) => ReviewModel.fromJson(e as Map<String, dynamic>))
          .toList();
      emit(ReviewsLoaded(reviews: reviews));
    } catch (e) {
      emit(ReviewsError('Failed to load reviews: ${e.toString()}'));
    }
  }
}
