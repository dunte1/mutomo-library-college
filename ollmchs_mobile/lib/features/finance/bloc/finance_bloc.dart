import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/network/api_client.dart';
import '../models/payment_model.dart';
import '../../fines/models/fine_model.dart';

// Events
abstract class FinanceEvent extends Equatable {
  const FinanceEvent();
  @override
  List<Object?> get props => [];
}

class LoadPaymentHistory extends FinanceEvent {
  final int page;
  const LoadPaymentHistory({this.page = 1});
  @override
  List<Object?> get props => [page];
}

class LoadReceiptDetail extends FinanceEvent {
  final int paymentId;
  const LoadReceiptDetail(this.paymentId);
  @override
  List<Object?> get props => [paymentId];
}

class PayFine extends FinanceEvent {
  final int fineId;
  const PayFine(this.fineId);
  @override
  List<Object?> get props => [fineId];
}

// States
abstract class FinanceState extends Equatable {
  const FinanceState();
  @override
  List<Object?> get props => [];
}

class FinanceInitial extends FinanceState {}

class FinanceLoading extends FinanceState {}

class FinanceLoaded extends FinanceState {
  final List<PaymentModel> payments;
  final PaymentModel? selectedReceipt;
  final List<FineModel> unpaidFines;
  final String? message;

  const FinanceLoaded({
    this.payments = const [],
    this.selectedReceipt,
    this.unpaidFines = const [],
    this.message,
  });
  @override
  List<Object?> get props => [payments, selectedReceipt, unpaidFines, message];
}

class FinanceError extends FinanceState {
  final String error;
  const FinanceError(this.error);
  @override
  List<Object?> get props => [error];
}

// Bloc
class FinanceBloc extends Bloc<FinanceEvent, FinanceState> {
  final ApiClient _api;

  FinanceBloc({required ApiClient api}) : _api = api, super(FinanceInitial()) {
    on<LoadPaymentHistory>(_onLoadPayments);
    on<LoadReceiptDetail>(_onLoadReceipt);
    on<PayFine>(_onPayFine);
  }

  Future<void> _onLoadPayments(
    LoadPaymentHistory event,
    Emitter<FinanceState> emit,
  ) async {
    emit(FinanceLoading());
    try {
      final response = await _api.get(
        '/v1/payments',
        queryParameters: {'page': event.page, 'per_page': 20},
      );
      final data = response.data['data'] as List<dynamic>? ?? [];
      final payments = data
          .map((e) => PaymentModel.fromJson(e as Map<String, dynamic>))
          .toList();

      List<FineModel> unpaidFines = [];
      try {
        final finesResp = await _api.get('/v1/fines');
        final finesData = finesResp.data['data'] as List<dynamic>? ?? [];
        unpaidFines = finesData
            .map((e) => FineModel.fromJson(e as Map<String, dynamic>))
            .where((f) => f.status == 'unpaid' || f.status == 'pending')
            .toList();
      } catch (_) {}

      emit(FinanceLoaded(payments: payments, unpaidFines: unpaidFines));
    } catch (e) {
      emit(FinanceError('Failed to load payments: ${e.toString()}'));
    }
  }

  Future<void> _onLoadReceipt(
    LoadReceiptDetail event,
    Emitter<FinanceState> emit,
  ) async {
    try {
      final response = await _api.get('/v1/payments/${event.paymentId}');
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      final payment = PaymentModel.fromJson(data);
      final current = state;
      if (current is FinanceLoaded) {
        emit(
          FinanceLoaded(
            payments: current.payments,
            selectedReceipt: payment,
            unpaidFines: current.unpaidFines,
          ),
        );
      }
    } catch (e) {
      emit(FinanceError('Failed to load receipt: ${e.toString()}'));
    }
  }

  Future<void> _onPayFine(PayFine event, Emitter<FinanceState> emit) async {
    try {
      await _api.post('/v1/fines/${event.fineId}/pay');
      add(const LoadPaymentHistory());
      emit(FinanceLoaded(message: 'Fine paid successfully'));
    } catch (e) {
      emit(FinanceError('Payment failed: ${e.toString()}'));
    }
  }
}
