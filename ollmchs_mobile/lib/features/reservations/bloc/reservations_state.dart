import 'package:equatable/equatable.dart';
import '../models/reservation_model.dart';

abstract class ReservationsState extends Equatable {
  const ReservationsState();
  @override
  List<Object?> get props => [];
}

class ReservationsInitial extends ReservationsState {}

class ReservationsLoading extends ReservationsState {}

class ReservationsLoaded extends ReservationsState {
  final List<ReservationModel> reservations;
  final String? message;

  const ReservationsLoaded({this.reservations = const [], this.message});
  @override
  List<Object?> get props => [reservations, message];
}

class ReservationsError extends ReservationsState {
  final String error;
  const ReservationsError(this.error);
  @override
  List<Object?> get props => [error];
}
