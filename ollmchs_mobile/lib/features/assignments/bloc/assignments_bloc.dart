import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/network/api_client.dart';
import '../models/assignment_model.dart';

// Events
abstract class AssignmentsEvent extends Equatable {
  const AssignmentsEvent();
  @override
  List<Object?> get props => [];
}

class LoadAssignments extends AssignmentsEvent {
  final int page;
  const LoadAssignments({this.page = 1});
  @override
  List<Object?> get props => [page];
}

class LoadAssignmentDetail extends AssignmentsEvent {
  final int assignmentId;
  const LoadAssignmentDetail(this.assignmentId);
  @override
  List<Object?> get props => [assignmentId];
}

class SubmitAssignment extends AssignmentsEvent {
  final int assignmentId;
  final String? submissionText;
  final String? attachmentPath;
  const SubmitAssignment({
    required this.assignmentId,
    this.submissionText,
    this.attachmentPath,
  });
  @override
  List<Object?> get props => [
    assignmentId,
    submissionText ?? '',
    attachmentPath ?? '',
  ];
}

class MarkComplete extends AssignmentsEvent {
  final int assignmentId;
  const MarkComplete(this.assignmentId);
  @override
  List<Object?> get props => [assignmentId];
}

// States
abstract class AssignmentsState extends Equatable {
  const AssignmentsState();
  @override
  List<Object?> get props => [];
}

class AssignmentsInitial extends AssignmentsState {}

class AssignmentsLoading extends AssignmentsState {}

class AssignmentsLoaded extends AssignmentsState {
  final List<AssignmentModel> assignments;
  final AssignmentModel? selectedAssignment;
  final String? message;

  const AssignmentsLoaded({
    this.assignments = const [],
    this.selectedAssignment,
    this.message,
  });
  @override
  List<Object?> get props => [assignments, selectedAssignment, message];
}

class AssignmentsError extends AssignmentsState {
  final String error;
  const AssignmentsError(this.error);
  @override
  List<Object?> get props => [error];
}

// Bloc
class AssignmentsBloc extends Bloc<AssignmentsEvent, AssignmentsState> {
  final ApiClient _api;

  AssignmentsBloc({required ApiClient api})
    : _api = api,
      super(AssignmentsInitial()) {
    on<LoadAssignments>(_onLoad);
    on<LoadAssignmentDetail>(_onLoadDetail);
    on<SubmitAssignment>(_onSubmit);
    on<MarkComplete>(_onMarkComplete);
  }

  Future<void> _onLoad(
    LoadAssignments event,
    Emitter<AssignmentsState> emit,
  ) async {
    emit(AssignmentsLoading());
    try {
      final response = await _api.get(
        '/v1/assignments',
        queryParameters: {'page': event.page, 'per_page': 20},
      );
      final data = response.data['data'] as List<dynamic>? ?? [];
      final assignments = data
          .map((e) => AssignmentModel.fromJson(e as Map<String, dynamic>))
          .toList();
      emit(AssignmentsLoaded(assignments: assignments));
    } catch (e) {
      emit(AssignmentsError('Failed to load assignments: ${e.toString()}'));
    }
  }

  Future<void> _onLoadDetail(
    LoadAssignmentDetail event,
    Emitter<AssignmentsState> emit,
  ) async {
    try {
      final response = await _api.get('/v1/assignments/${event.assignmentId}');
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      final assignment = AssignmentModel.fromJson(data);
      final current = state;
      if (current is AssignmentsLoaded) {
        emit(
          AssignmentsLoaded(
            assignments: current.assignments,
            selectedAssignment: assignment,
          ),
        );
      }
    } catch (e) {
      emit(AssignmentsError('Failed to load assignment: ${e.toString()}'));
    }
  }

  Future<void> _onSubmit(
    SubmitAssignment event,
    Emitter<AssignmentsState> emit,
  ) async {
    try {
      await _api.post(
        '/v1/assignments/${event.assignmentId}/submit',
        data: {
          if (event.submissionText != null)
            'submission_text': event.submissionText,
        },
      );
      add(LoadAssignmentDetail(event.assignmentId));
      final current = state;
      if (current is AssignmentsLoaded) {
        emit(
          AssignmentsLoaded(
            assignments: current.assignments,
            selectedAssignment: current.selectedAssignment,
            message: 'Assignment submitted',
          ),
        );
      }
    } catch (e) {
      emit(AssignmentsError('Submission failed: ${e.toString()}'));
    }
  }

  Future<void> _onMarkComplete(
    MarkComplete event,
    Emitter<AssignmentsState> emit,
  ) async {
    try {
      await _api.post('/v1/assignments/${event.assignmentId}/submit', data: {});
      add(const LoadAssignments());
      add(LoadAssignmentDetail(event.assignmentId));
      final current = state;
      if (current is AssignmentsLoaded) {
        emit(
          AssignmentsLoaded(
            assignments: current.assignments,
            selectedAssignment: current.selectedAssignment,
            message: 'Marked as complete',
          ),
        );
      }
    } catch (e) {
      emit(AssignmentsError('Failed to mark complete: ${e.toString()}'));
    }
  }
}
