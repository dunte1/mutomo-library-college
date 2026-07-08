import 'package:equatable/equatable.dart';

abstract class LoansEvent extends Equatable {
  const LoansEvent();
  @override
  List<Object?> get props => [];
}

class LoadActiveLoans extends LoansEvent {
  final int page;
  const LoadActiveLoans({this.page = 1});
  @override
  List<Object?> get props => [page];
}

class LoadLoanHistory extends LoansEvent {
  final int page;
  const LoadLoanHistory({this.page = 1});
  @override
  List<Object?> get props => [page];
}

class LoadLoanDetail extends LoansEvent {
  final int loanId;
  const LoadLoanDetail(this.loanId);
  @override
  List<Object?> get props => [loanId];
}

class RenewLoan extends LoansEvent {
  final int loanId;
  const RenewLoan(this.loanId);
  @override
  List<Object?> get props => [loanId];
}
