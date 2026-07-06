import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/network/api_client.dart';
import '../models/dashboard_model.dart';

// Events
abstract class DashboardEvent extends Equatable {
  const DashboardEvent();
  @override
  List<Object?> get props => [];
}

class LoadDashboard extends DashboardEvent {
  const LoadDashboard();
}

// States
abstract class DashboardState extends Equatable {
  const DashboardState();
  @override
  List<Object?> get props => [];
}

class DashboardInitial extends DashboardState {}

class DashboardLoading extends DashboardState {}

class DashboardLoaded extends DashboardState {
  final DashboardModel dashboard;
  const DashboardLoaded({required this.dashboard});
  @override
  List<Object?> get props => [dashboard];
}

class DashboardError extends DashboardState {
  final String error;
  const DashboardError(this.error);
  @override
  List<Object?> get props => [error];
}

// Bloc
class DashboardBloc extends Bloc<DashboardEvent, DashboardState> {
  final ApiClient _api;

  DashboardBloc({required this._api}) : super(DashboardInitial()) {
    on<LoadDashboard>(_onLoad);
  }

  Future<void> _onLoad(
    LoadDashboard event,
    Emitter<DashboardState> emit,
  ) async {
    emit(DashboardLoading());
    try {
      final response = await _api.get('/v1/dashboard');
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      emit(DashboardLoaded(dashboard: DashboardModel.fromJson(data)));
    } catch (e) {
      emit(DashboardError('Failed to load dashboard: ${e.toString()}'));
    }
  }
}
