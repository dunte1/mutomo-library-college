import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/network/api_client.dart';
import '../models/report_model.dart';

// Events
abstract class ReportsEvent extends Equatable {
  const ReportsEvent();
  @override
  List<Object?> get props => [];
}

class LoadReadingSummary extends ReportsEvent {
  const LoadReadingSummary();
}

class LoadLoanHistory extends ReportsEvent {
  final int page;
  const LoadLoanHistory({this.page = 1});
  @override
  List<Object?> get props => [page];
}

class LoadFineHistory extends ReportsEvent {
  final int page;
  const LoadFineHistory({this.page = 1});
  @override
  List<Object?> get props => [page];
}

// States
abstract class ReportsState extends Equatable {
  const ReportsState();
  @override
  List<Object?> get props => [];
}

class ReportsInitial extends ReportsState {}

class ReportsLoading extends ReportsState {}

class ReadingSummaryLoaded extends ReportsState {
  final ReadingSummary summary;
  const ReadingSummaryLoaded(this.summary);
  @override
  List<Object?> get props => [summary];
}

class LoanHistoryLoaded extends ReportsState {
  final List<LoanReportItem> loans;
  final bool hasMore;
  const LoanHistoryLoaded({required this.loans, this.hasMore = true});
  @override
  List<Object?> get props => [loans, hasMore];
}

class FineHistoryLoaded extends ReportsState {
  final List<FineReportItem> fines;
  final bool hasMore;
  const FineHistoryLoaded({required this.fines, this.hasMore = true});
  @override
  List<Object?> get props => [fines, hasMore];
}

class ReportsError extends ReportsState {
  final String message;
  const ReportsError(this.message);
  @override
  List<Object?> get props => [message];
}

// Bloc
class ReportsBloc extends Bloc<ReportsEvent, ReportsState> {
  final ApiClient _api;

  ReportsBloc({required ApiClient api}) : _api = api, super(ReportsInitial()) {
    on<LoadReadingSummary>(_onLoadReadingSummary);
    on<LoadLoanHistory>(_onLoadLoanHistory);
    on<LoadFineHistory>(_onLoadFineHistory);
  }

  Future<void> _onLoadReadingSummary(
    LoadReadingSummary event,
    Emitter<ReportsState> emit,
  ) async {
    emit(ReportsLoading());
    try {
      final response = await _api.get('/v1/reports/reading-summary');
      final data = response.data['data'] as Map<String, dynamic>;
      final summary = ReadingSummary.fromJson(data);
      emit(ReadingSummaryLoaded(summary));
    } catch (e) {
      emit(ReportsError('Failed to load reading summary: $e'));
    }
  }

  Future<void> _onLoadLoanHistory(
    LoadLoanHistory event,
    Emitter<ReportsState> emit,
  ) async {
    try {
      final response = await _api.get(
        '/v1/reports/loan-history',
        queryParameters: {'page': event.page},
      );
      final list = (response.data['data'] as List<dynamic>? ?? [])
          .map((e) => LoanReportItem.fromJson(e as Map<String, dynamic>))
          .toList();
      final meta = response.data['meta'] as Map<String, dynamic>? ?? {};
      final hasMore = meta['current_page'] < (meta['last_page'] ?? 1);

      final current = state;
      final all = (current is LoanHistoryLoaded && event.page > 1)
          ? [...current.loans, ...list]
          : list;

      emit(LoanHistoryLoaded(loans: all, hasMore: hasMore));
    } catch (e) {
      emit(ReportsError('Failed to load loan history: $e'));
    }
  }

  Future<void> _onLoadFineHistory(
    LoadFineHistory event,
    Emitter<ReportsState> emit,
  ) async {
    try {
      final response = await _api.get(
        '/v1/reports/fine-history',
        queryParameters: {'page': event.page},
      );
      final list = (response.data['data'] as List<dynamic>? ?? [])
          .map((e) => FineReportItem.fromJson(e as Map<String, dynamic>))
          .toList();
      final meta = response.data['meta'] as Map<String, dynamic>? ?? {};
      final hasMore = meta['current_page'] < (meta['last_page'] ?? 1);

      final current = state;
      final all = (current is FineHistoryLoaded && event.page > 1)
          ? [...current.fines, ...list]
          : list;

      emit(FineHistoryLoaded(fines: all, hasMore: hasMore));
    } catch (e) {
      emit(ReportsError('Failed to load fine history: $e'));
    }
  }
}
