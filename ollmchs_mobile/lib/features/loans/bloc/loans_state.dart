import 'package:equatable/equatable.dart';
import '../models/loan_model.dart';

abstract class LoansState extends Equatable {
  const LoansState();
  @override
  List<Object?> get props => [];
}

class LoansInitial extends LoansState {}

class LoansLoading extends LoansState {}

class LoansLoaded extends LoansState {
  final List<LoanModel> activeLoans;
  final List<LoanHistoryModel> history;
  final LoanModel? selectedLoan;
  final bool hasMoreHistory;
  final int historyPage;
  final String? message;

  const LoansLoaded({
    this.activeLoans = const [],
    this.history = const [],
    this.selectedLoan,
    this.hasMoreHistory = true,
    this.historyPage = 1,
    this.message,
  });

  LoansLoaded copyWith({
    List<LoanModel>? activeLoans,
    List<LoanHistoryModel>? history,
    LoanModel? selectedLoan,
    bool? hasMoreHistory,
    int? historyPage,
    String? message,
    bool clearMessage = false,
  }) {
    return LoansLoaded(
      activeLoans: activeLoans ?? this.activeLoans,
      history: history ?? this.history,
      selectedLoan: selectedLoan ?? this.selectedLoan,
      hasMoreHistory: hasMoreHistory ?? this.hasMoreHistory,
      historyPage: historyPage ?? this.historyPage,
      message: clearMessage ? null : (message ?? this.message),
    );
  }

  @override
  List<Object?> get props => [
    activeLoans,
    history,
    selectedLoan,
    hasMoreHistory,
    historyPage,
    message,
  ];
}

class LoansError extends LoansState {
  final String error;
  const LoansError(this.error);
  @override
  List<Object?> get props => [error];
}
