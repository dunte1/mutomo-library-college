import 'dart:async';
import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/errors/error_mapper.dart';
import '../../../core/network/api_client.dart';
import '../models/fine_model.dart';
import '../models/payment_result.dart';

// Events
abstract class FinesEvent extends Equatable {
  const FinesEvent();
  @override
  List<Object?> get props => [];
}

class LoadFines extends FinesEvent {
  const LoadFines();
}

class LoadFineDetail extends FinesEvent {
  final int fineId;
  const LoadFineDetail(this.fineId);
  @override
  List<Object?> get props => [fineId];
}

class PayFine extends FinesEvent {
  final int fineId;
  const PayFine(this.fineId);
  @override
  List<Object?> get props => [fineId];
}

class PayFineWithMethod extends FinesEvent {
  final int fineId;
  final String paymentMethod;
  final String? phoneNumber;
  const PayFineWithMethod({
    required this.fineId,
    required this.paymentMethod,
    this.phoneNumber,
  });
  @override
  List<Object?> get props => [fineId, paymentMethod, phoneNumber ?? ''];
}

class PollPaymentStatus extends FinesEvent {
  final int fineId;
  const PollPaymentStatus(this.fineId);
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
  final FineModel? selectedFine;
  final PaymentResult? lastPaymentResult;

  const FinesLoaded({
    this.fines = const [],
    this.totalPending = 0,
    this.message,
    this.selectedFine,
    this.lastPaymentResult,
  });
  @override
  List<Object?> get props =>
      [fines, totalPending, message, selectedFine, lastPaymentResult];
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
    on<LoadFineDetail>(_onLoadFineDetail);
    on<PayFine>(_onPayFine);
    on<PayFineWithMethod>(_onPayFineWithMethod);
    on<PollPaymentStatus>(_onPollPaymentStatus);
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

      final current = state;
      emit(FinesLoaded(
        fines: fines,
        totalPending: total,
        selectedFine: current is FinesLoaded ? current.selectedFine : null,
        lastPaymentResult: current is FinesLoaded ? current.lastPaymentResult : null,
      ));
    } catch (e) {
      emit(FinesError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onLoadFineDetail(
      LoadFineDetail event, Emitter<FinesState> emit) async {
    final current = state;
    if (current is! FinesLoaded) {
      emit(FinesLoading());
    }
    try {
      final response = await _api.get('/v1/fines/${event.fineId}');
      final data = response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      final fine = FineModel.fromJson(data);
      final s = state;
      if (s is FinesLoaded) {
        emit(FinesLoaded(
          fines: s.fines,
          totalPending: s.totalPending,
          selectedFine: fine,
          lastPaymentResult: s.lastPaymentResult,
        ));
      } else {
        emit(FinesLoaded(fines: [], totalPending: 0, selectedFine: fine));
      }
    } catch (e) {
      emit(FinesError(ErrorMapper.map(e)));
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
      emit(FinesError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onPayFineWithMethod(
      PayFineWithMethod event, Emitter<FinesState> emit) async {
    try {
      final response = await _api.post(
        '/v1/fines/${event.fineId}/pay',
        data: {
          'payment_method': event.paymentMethod,
          if (event.phoneNumber != null) 'phone_number': event.phoneNumber,
        },
      );
      final responseData = response.data['data'] as Map<String, dynamic>?;
      PaymentResult? result;
      if (responseData != null) {
        result = PaymentResult.fromJson(responseData);
      }
      final current = state;
      emit(FinesLoaded(
        fines: current is FinesLoaded ? current.fines : [],
        totalPending: current is FinesLoaded ? current.totalPending : 0,
        message: 'Payment initiated',
        lastPaymentResult: result,
      ));
      // Start polling if payment is pending
      if (result != null && result.isPending) {
        add(PollPaymentStatus(event.fineId));
      }
    } catch (e) {
      emit(FinesError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onPollPaymentStatus(
      PollPaymentStatus event, Emitter<FinesState> emit) async {
    try {
      final response = await _api.get('/v1/fines/${event.fineId}');
      final data = response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      final fine = FineModel.fromJson(data);
      final current = state;
      if (current is FinesLoaded) {
        final result = current.lastPaymentResult;
        if (result != null && result.isPending) {
          // Re-poll after 3 seconds
          await Future.delayed(const Duration(seconds: 3));
          if (!isClosed) {
            add(PollPaymentStatus(event.fineId));
          }
        } else {
          emit(FinesLoaded(
            fines: current.fines,
            totalPending: current.totalPending,
            selectedFine: fine,
            lastPaymentResult: current.lastPaymentResult,
          ));
        }
      }
    } catch (_) {
      // Silent failure for polling
    }
  }
}
