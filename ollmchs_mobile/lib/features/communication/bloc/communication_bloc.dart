import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/errors/error_mapper.dart';
import '../../../core/network/api_client.dart';
import '../models/announcement_model.dart';
import '../models/event_model.dart';

// Events
abstract class CommunicationEvent extends Equatable {
  const CommunicationEvent();
  @override
  List<Object?> get props => [];
}

class LoadAnnouncements extends CommunicationEvent {
  final int page;
  const LoadAnnouncements({this.page = 1});
  @override
  List<Object?> get props => [page];
}

class LoadAnnouncementDetail extends CommunicationEvent {
  final int id;
  const LoadAnnouncementDetail(this.id);
  @override
  List<Object?> get props => [id];
}

class LoadEvents extends CommunicationEvent {
  final int page;
  const LoadEvents({this.page = 1});
  @override
  List<Object?> get props => [page];
}

class LoadEventDetail extends CommunicationEvent {
  final int id;
  const LoadEventDetail(this.id);
  @override
  List<Object?> get props => [id];
}

// States
abstract class CommunicationState extends Equatable {
  const CommunicationState();
  @override
  List<Object?> get props => [];
}

class CommunicationInitial extends CommunicationState {}

class CommunicationLoading extends CommunicationState {}

class CommunicationLoaded extends CommunicationState {
  final List<AnnouncementModel> announcements;
  final AnnouncementModel? selectedAnnouncement;
  final List<EventModel> events;
  final EventModel? selectedEvent;
  final String? message;

  const CommunicationLoaded({
    this.announcements = const [],
    this.selectedAnnouncement,
    this.events = const [],
    this.selectedEvent,
    this.message,
  });
  @override
  List<Object?> get props => [
    announcements,
    selectedAnnouncement,
    events,
    selectedEvent,
    message,
  ];
}

class CommunicationError extends CommunicationState {
  final String error;
  const CommunicationError(this.error);
  @override
  List<Object?> get props => [error];
}

// Bloc
class CommunicationBloc extends Bloc<CommunicationEvent, CommunicationState> {
  final ApiClient _api;

  CommunicationBloc({required ApiClient api})
    : _api = api,
      super(CommunicationInitial()) {
    on<LoadAnnouncements>(_onLoadAnnouncements);
    on<LoadAnnouncementDetail>(_onLoadAnnouncementDetail);
    on<LoadEvents>(_onLoadEvents);
    on<LoadEventDetail>(_onLoadEventDetail);
  }

  Future<void> _onLoadAnnouncements(
    LoadAnnouncements event,
    Emitter<CommunicationState> emit,
  ) async {
    if (state is! CommunicationLoaded) emit(CommunicationLoading());
    try {
      final response = await _api.get(
        '/v1/announcements',
        queryParameters: {'page': event.page, 'per_page': 20},
      );
      final data = response.data['data'] as List<dynamic>? ?? [];
      final announcements = data
          .map((e) => AnnouncementModel.fromJson(e as Map<String, dynamic>))
          .toList();
      final current = state;
      emit(
        CommunicationLoaded(
          announcements: announcements,
          events: current is CommunicationLoaded ? current.events : [],
        ),
      );
    } catch (e) {
      emit(CommunicationError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onLoadAnnouncementDetail(
    LoadAnnouncementDetail event,
    Emitter<CommunicationState> emit,
  ) async {
    try {
      final response = await _api.get('/v1/announcements/${event.id}');
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      final announcement = AnnouncementModel.fromJson(data);
      final current = state;
      if (current is CommunicationLoaded) {
        emit(
          CommunicationLoaded(
            announcements: current.announcements,
            selectedAnnouncement: announcement,
            events: current.events,
          ),
        );
      }
    } catch (e) {
      emit(CommunicationError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onLoadEvents(
    LoadEvents event,
    Emitter<CommunicationState> emit,
  ) async {
    if (state is! CommunicationLoaded) emit(CommunicationLoading());
    try {
      final response = await _api.get(
        '/v1/events',
        queryParameters: {'page': event.page, 'per_page': 20},
      );
      final data = response.data['data'] as List<dynamic>? ?? [];
      final events = data
          .map((e) => EventModel.fromJson(e as Map<String, dynamic>))
          .toList();
      final current = state;
      emit(
        CommunicationLoaded(
          announcements: current is CommunicationLoaded
              ? current.announcements
              : [],
          events: events,
        ),
      );
    } catch (e) {
      emit(CommunicationError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onLoadEventDetail(
    LoadEventDetail event,
    Emitter<CommunicationState> emit,
  ) async {
    try {
      final response = await _api.get('/v1/events/${event.id}');
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      final evt = EventModel.fromJson(data);
      final current = state;
      if (current is CommunicationLoaded) {
        emit(
          CommunicationLoaded(
            announcements: current.announcements,
            events: current.events,
            selectedEvent: evt,
          ),
        );
      }
    } catch (e) {
      emit(CommunicationError(ErrorMapper.map(e)));
    }
  }
}
