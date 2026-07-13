import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/errors/error_mapper.dart';
import 'reservations_event.dart';
import 'reservations_state.dart';
import '../repositories/reservations_repository.dart';

class ReservationsBloc extends Bloc<ReservationsEvent, ReservationsState> {
  final ReservationsRepository _repository;

  ReservationsBloc({required this._repository}) : super(ReservationsInitial()) {
    on<LoadReservations>(_onLoadReservations);
    on<CreateReservation>(_onCreateReservation);
    on<CancelReservation>(_onCancelReservation);
  }

  Future<void> _onLoadReservations(
    LoadReservations event,
    Emitter<ReservationsState> emit,
  ) async {
    emit(ReservationsLoading());
    try {
      final reservations = await _repository.getReservations();
      emit(ReservationsLoaded(reservations: reservations));
    } catch (e) {
      emit(ReservationsError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onCreateReservation(
    CreateReservation event,
    Emitter<ReservationsState> emit,
  ) async {
    try {
      await _repository.createReservation(event.bookId);
      final reservations = await _repository.getReservations();
      emit(
        ReservationsLoaded(
          reservations: reservations,
          message: 'Book reserved successfully',
        ),
      );
    } catch (e) {
      emit(ReservationsError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onCancelReservation(
    CancelReservation event,
    Emitter<ReservationsState> emit,
  ) async {
    try {
      await _repository.cancelReservation(event.reservationId);
      final currentState = state;
      if (currentState is ReservationsLoaded) {
        final updated = currentState.reservations
            .where((r) => r.id != event.reservationId)
            .toList();
        emit(
          ReservationsLoaded(
            reservations: updated,
            message: 'Reservation cancelled',
          ),
        );
      }
    } catch (e) {
      emit(ReservationsError(ErrorMapper.map(e)));
    }
  }
}
