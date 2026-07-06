import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/network/api_client.dart';
import '../models/digital_asset_model.dart';

// Events
abstract class DigitalLibraryEvent extends Equatable {
  const DigitalLibraryEvent();
  @override
  List<Object?> get props => [];
}

class LoadDigitalAssets extends DigitalLibraryEvent {
  final int page;
  final String? category;
  const LoadDigitalAssets({this.page = 1, this.category});
  @override
  List<Object?> get props => [page, category];
}

class LoadReadingHistory extends DigitalLibraryEvent {
  final int page;
  const LoadReadingHistory({this.page = 1});
  @override
  List<Object?> get props => [page];
}

class UpdateReadingProgress extends DigitalLibraryEvent {
  final int assetId;
  final double progress;
  final String? lastPage;
  const UpdateReadingProgress({
    required this.assetId,
    required this.progress,
    this.lastPage,
  });
  @override
  List<Object?> get props => [assetId, progress, lastPage];
}

class LoadRecommendations extends DigitalLibraryEvent {
  const LoadRecommendations();
}

class LoadDigitalCategories extends DigitalLibraryEvent {
  const LoadDigitalCategories();
}

// States
abstract class DigitalLibraryState extends Equatable {
  const DigitalLibraryState();
  @override
  List<Object?> get props => [];
}

class DigitalLibraryInitial extends DigitalLibraryState {}

class DigitalLibraryLoading extends DigitalLibraryState {}

class DigitalLibraryLoaded extends DigitalLibraryState {
  final List<DigitalAssetModel> assets;
  final bool hasMoreAssets;
  final int currentPage;
  final String? selectedCategory;
  final List<ReadingHistoryModel> readingHistory;
  final bool hasMoreHistory;
  final List<RecommendationModel> recommendations;
  final List<String> categories;
  final String? message;

  const DigitalLibraryLoaded({
    this.assets = const [],
    this.hasMoreAssets = true,
    this.currentPage = 1,
    this.selectedCategory,
    this.readingHistory = const [],
    this.hasMoreHistory = true,
    this.recommendations = const [],
    this.categories = const [],
    this.message,
  });
  @override
  List<Object?> get props => [
    assets,
    hasMoreAssets,
    currentPage,
    selectedCategory,
    readingHistory,
    hasMoreHistory,
    recommendations,
    categories,
    message,
  ];
}

class DigitalLibraryError extends DigitalLibraryState {
  final String error;
  const DigitalLibraryError(this.error);
  @override
  List<Object?> get props => [error];
}

// Bloc
class DigitalLibraryBloc
    extends Bloc<DigitalLibraryEvent, DigitalLibraryState> {
  final ApiClient _api;

  DigitalLibraryBloc({required this._api}) : super(DigitalLibraryInitial()) {
    on<LoadDigitalAssets>(_onLoadAssets);
    on<LoadReadingHistory>(_onLoadHistory);
    on<UpdateReadingProgress>(_onUpdateProgress);
    on<LoadRecommendations>(_onLoadRecommendations);
    on<LoadDigitalCategories>(_onLoadCategories);
  }

  Future<void> _onLoadAssets(
    LoadDigitalAssets event,
    Emitter<DigitalLibraryState> emit,
  ) async {
    if (state is! DigitalLibraryLoaded || event.page == 1) {
      emit(DigitalLibraryLoading());
    }

    try {
      final params = <String, dynamic>{'page': event.page, 'per_page': 20};
      if (event.category != null) params['category'] = event.category;

      final response = await _api.get(
        '/v1/digital-assets',
        queryParameters: params,
      );
      final data = response.data;
      final list = data['data'] as List<dynamic>? ?? [];
      final meta = data['meta'] as Map<String, dynamic>? ?? data;

      final assets = list
          .map((e) => DigitalAssetModel.fromJson(e as Map<String, dynamic>))
          .toList();
      final current = state;
      final allAssets = (current is DigitalLibraryLoaded && event.page > 1)
          ? [...current.assets, ...assets]
          : assets;

      emit(
        DigitalLibraryLoaded(
          assets: allAssets,
          hasMoreAssets:
              (meta['current_page'] as int? ?? 1) <
              (meta['last_page'] as int? ?? 1),
          currentPage: event.page,
          selectedCategory: event.category,
          readingHistory: current is DigitalLibraryLoaded
              ? current.readingHistory
              : [],
          recommendations: current is DigitalLibraryLoaded
              ? current.recommendations
              : [],
          categories: current is DigitalLibraryLoaded ? current.categories : [],
        ),
      );
    } catch (e) {
      emit(DigitalLibraryError('Failed to load assets: ${e.toString()}'));
    }
  }

  Future<void> _onLoadHistory(
    LoadReadingHistory event,
    Emitter<DigitalLibraryState> emit,
  ) async {
    try {
      final response = await _api.get(
        '/v1/reading-history',
        queryParameters: {'page': event.page, 'per_page': 20},
      );
      final data = response.data;
      final list = data['data'] as List<dynamic>? ?? [];

      final history = list
          .map((e) => ReadingHistoryModel.fromJson(e as Map<String, dynamic>))
          .toList();
      final current = state;
      final allHistory = (current is DigitalLibraryLoaded && event.page > 1)
          ? [...current.readingHistory, ...history]
          : history;

      if (current is DigitalLibraryLoaded) {
        emit(
          DigitalLibraryLoaded(
            assets: current.assets,
            hasMoreAssets: current.hasMoreAssets,
            currentPage: current.currentPage,
            readingHistory: allHistory,
            recommendations: current.recommendations,
            categories: current.categories,
          ),
        );
      }
    } catch (_) {}
  }

  Future<void> _onUpdateProgress(
    UpdateReadingProgress event,
    Emitter<DigitalLibraryState> emit,
  ) async {
    try {
      await _api.put(
        '/v1/reading-history/${event.assetId}',
        data: {'progress': event.progress, 'last_page': event.lastPage},
      );
    } catch (_) {}
  }

  Future<void> _onLoadRecommendations(
    LoadRecommendations event,
    Emitter<DigitalLibraryState> emit,
  ) async {
    try {
      final response = await _api.get('/v1/recommendations');
      final data = response.data['data'] as List<dynamic>? ?? [];
      final recommendations = data
          .map((e) => RecommendationModel.fromJson(e as Map<String, dynamic>))
          .toList();

      final current = state;
      if (current is DigitalLibraryLoaded) {
        emit(
          DigitalLibraryLoaded(
            assets: current.assets,
            hasMoreAssets: current.hasMoreAssets,
            currentPage: current.currentPage,
            readingHistory: current.readingHistory,
            recommendations: recommendations,
            categories: current.categories,
          ),
        );
      }
    } catch (_) {}
  }

  Future<void> _onLoadCategories(
    LoadDigitalCategories event,
    Emitter<DigitalLibraryState> emit,
  ) async {
    try {
      final response = await _api.get('/v1/digital-categories');
      final data = response.data['data'] as List<dynamic>? ?? [];
      final categories = data
          .map((e) => e is Map ? e['name'] as String? ?? '' : e.toString())
          .where((n) => n.isNotEmpty)
          .toList();

      final current = state;
      if (current is DigitalLibraryLoaded) {
        emit(
          DigitalLibraryLoaded(
            assets: current.assets,
            hasMoreAssets: current.hasMoreAssets,
            currentPage: current.currentPage,
            readingHistory: current.readingHistory,
            recommendations: current.recommendations,
            categories: categories,
          ),
        );
      }
    } catch (_) {}
  }
}
