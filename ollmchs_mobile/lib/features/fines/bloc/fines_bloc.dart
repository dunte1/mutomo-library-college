import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/network/api_client.dart';
import '../models/fine_model.dart';

// Events
abstract class FinesEvent extends Equatable {
  const FinesEvent();
  @override
  List<Object?> get props => [];
}

class LoadFines extends FinesEvent {
  const LoadFines();
}

class PayFine extends FinesEvent {
  final int fineId;
  const PayFine(this.fineId);
  @override
  List<Object?> get props => [fineId];
}

// States
abstract class FinesState extends Equatable {
  const FinesState();
  @override
  List<Object?> get props => [];
}

class FinesInitial extends FinesState {}

class FinesLoading extends FinesState {}

class FinesLoaded extends FinesState {
  final List<FineModel> fines;
  final double totalPending;
  final String? message;

  const FinesLoaded({
    this.fines = const [],
    this.totalPending = 0,
    this.message,
  });
  @override
  List<Object?> get props => [fines, totalPending, message];
}

class FinesError extends FinesState {
  final String error;
  const FinesError(this.error);
  @override
  List<Object?> get props => [error];
}

// Bloc
class FinesBloc extends Bloc<FinesEvent, FinesState> {
  final ApiClient _api;

  FinesBloc({required this._api}) : super(FinesInitial()) {
    on<LoadFines>(_onLoadFines);
    on<PayFine>(_onPayFine);
  }

  Future<void> _onLoadFines(LoadFines event, Emitter<FinesState> emit) async {
    emit(FinesLoading());
    try {
      final response = await _api.get('/v1/fines');
      final data = response.data['data'] as List<dynamic>? ?? [];
      final fines = data
          .map((e) => FineModel.fromJson(e as Map<String, dynamic>))
          .toList();
      final meta = response.data['meta'] as Map<String, dynamic>?;
      final pendingTotal = fines
          .where((f) => f.isPending)
          .fold<double>(0.0, (sum, f) => sum + f.amount);
      final total = meta != null
          ? (meta['total_outstanding'] as num?)?.toDouble() ?? pendingTotal
          : pendingTotal;

      emit(FinesLoaded(fines: fines, totalPending: total));
    } catch (e) {
      emit(FinesError('Failed to load fines: ${e.toString()}'));
    }
  }

  Future<void> _onPayFine(PayFine event, Emitter<FinesState> emit) async {
    try {
      await _api.post('/v1/fines/${event.fineId}/pay');
      add(const LoadFines());
      final current = state;
      if (current is FinesLoaded) {
        emit(
          FinesLoaded(
            fines: current.fines,
            totalPending: current.totalPending,
            message: 'Payment initiated',
          ),
        );
      }
    } catch (e) {
      emit(FinesError('Payment failed: ${e.toString()}'));
    }
  }
}
