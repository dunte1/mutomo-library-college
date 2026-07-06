import 'package:equatable/equatable.dart';

abstract class ReservationsEvent extends Equatable {
  const ReservationsEvent();
  @override
  List<Object?> get props => [];
}

class LoadReservations extends ReservationsEvent {
  const LoadReservations();
}

class CreateReservation extends ReservationsEvent {
  final int bookId;
  const CreateReservation(this.bookId);
  @override
  List<Object?> get props => [bookId];
}

class CancelReservation extends ReservationsEvent {
  final int reservationId;
  const CancelReservation(this.reservationId);
  @override
  List<Object?> get props => [reservationId];
}
