import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/errors/error_mapper.dart';
import 'loans_event.dart';
import 'loans_state.dart';
import '../models/loan_model.dart';
import '../repositories/loans_repository.dart';

class LoansBloc extends Bloc<LoansEvent, LoansState> {
  final LoansRepository _repository;

  LoansBloc({required this._repository}) : super(LoansInitial()) {
    on<LoadActiveLoans>(_onLoadActiveLoans);
    on<LoadLoanHistory>(_onLoadLoanHistory);
    on<LoadLoanDetail>(_onLoadLoanDetail);
    on<RenewLoan>(_onRenewLoan);
  }

  Future<void> _onLoadActiveLoans(
    LoadActiveLoans event,
    Emitter<LoansState> emit,
  ) async {
    if (state is! LoansLoaded || event.page == 1) {
      emit(LoansLoading());
    }

    try {
      final result = await _repository.getActiveLoans(page: event.page);
      final currentState = state;
      final existingLoans = currentState is LoansLoaded && event.page > 1
          ? currentState.activeLoans
          : <LoanModel>[];

      emit(
        LoansLoaded(
          activeLoans: [...existingLoans, ...result.items],
          history: currentState is LoansLoaded
              ? currentState.history
              : [],
          hasMoreActiveLoans: result.hasMore,
          activeLoansPage: event.page,
          hasMoreHistory: currentState is LoansLoaded
              ? currentState.hasMoreHistory
              : true,
          historyPage: currentState is LoansLoaded
              ? currentState.historyPage
              : 1,
        ),
      );
    } catch (e) {
      emit(LoansError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onLoadLoanHistory(
    LoadLoanHistory event,
    Emitter<LoansState> emit,
  ) async {
    if (state is! LoansLoaded) {
      emit(LoansLoading());
    }

    try {
      final result = await _repository.getLoanHistory(page: event.page);
      final currentState = state;
      final existingHistory = currentState is LoansLoaded && event.page > 1
          ? currentState.history
          : <LoanHistoryModel>[];

      emit(
        LoansLoaded(
          activeLoans: currentState is LoansLoaded
              ? currentState.activeLoans
              : [],
          history: [...existingHistory, ...result.items],
          hasMoreActiveLoans: currentState is LoansLoaded
              ? currentState.hasMoreActiveLoans
              : true,
          activeLoansPage: currentState is LoansLoaded
              ? currentState.activeLoansPage
              : 1,
          hasMoreHistory: result.hasMore,
          historyPage: event.page,
        ),
      );
    } catch (e) {
      emit(LoansError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onLoadLoanDetail(
    LoadLoanDetail event,
    Emitter<LoansState> emit,
  ) async {
    try {
      final loan = await _repository.getLoanDetail(event.loanId);
      final currentState = state;
      if (currentState is LoansLoaded) {
        emit(currentState.copyWith(selectedLoan: loan));
      }
    } catch (e) {
      emit(LoansError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onRenewLoan(RenewLoan event, Emitter<LoansState> emit) async {
    try {
      final renewed = await _repository.renewLoan(event.loanId);
      final currentState = state;
      if (currentState is LoansLoaded) {
        final updated = currentState.activeLoans
            .map((l) => l.id == event.loanId ? renewed : l)
            .toList();
        emit(
          currentState.copyWith(
            activeLoans: updated,
            selectedLoan: renewed,
            message: 'Loan renewed successfully',
          ),
        );
      }
    } catch (e) {
      emit(LoansError(ErrorMapper.map(e)));
    }
  }
}
