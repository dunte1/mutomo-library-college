import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/errors/error_mapper.dart';
import '../../../core/network/api_client.dart';
import '../../../core/utils/type_parsers.dart';
import '../models/teacher_assignment_model.dart';

part 'teacher_assignments_event.dart';
part 'teacher_assignments_state.dart';

class TeacherAssignmentsBloc
    extends Bloc<TeacherAssignmentsEvent, TeacherAssignmentsState> {
  final ApiClient _api;

  TeacherAssignmentsBloc({required ApiClient api})
    : _api = api,
      super(TeacherAssignmentsInitial()) {
    on<LoadTeacherAssignments>(_onLoad);
    on<LoadTeacherAssignmentDetail>(_onLoadDetail);
    on<CreateTeacherAssignment>(_onCreate);
    on<UpdateTeacherAssignment>(_onUpdate);
    on<DeleteTeacherAssignment>(_onDelete);
    on<LoadAssignmentProgress>(_onLoadProgress);
    on<LoadStudents>(_onLoadStudents);
    on<LoadPrograms>(_onLoadPrograms);
    on<LoadDepartments>(_onLoadDepartments);
    on<LoadBooks>(_onLoadBooks);
    on<LoadDigitalAssets>(_onLoadDigitalAssets);
  }

  Future<void> _onLoad(
    LoadTeacherAssignments event,
    Emitter<TeacherAssignmentsState> emit,
  ) async {
    emit(TeacherAssignmentsLoading());
    try {
      final params = <String, dynamic>{'page': event.page, 'per_page': 20};
      if (event.type != null) params['type'] = event.type;
      if (event.status != null) params['status'] = event.status;
      if (event.search != null) params['search'] = event.search;
      final response = await _api.get(
        '/v1/teacher/assignments',
        queryParameters: params,
      );
      final data = response.data['data'] as List<dynamic>? ?? [];
      final assignments = data
          .map(
            (e) => TeacherAssignmentModel.fromJson(e as Map<String, dynamic>),
          )
          .toList();
      emit(TeacherAssignmentsLoaded(assignments: assignments));
    } catch (e) {
      emit(
        TeacherAssignmentsError(ErrorMapper.map(e)),
      );
    }
  }

  Future<void> _onLoadDetail(
    LoadTeacherAssignmentDetail event,
    Emitter<TeacherAssignmentsState> emit,
  ) async {
    try {
      final response = await _api.get('/v1/teacher/assignments/${event.id}');
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      final assignment = TeacherAssignmentModel.fromJson(data);
      final current = state;
      if (current is TeacherAssignmentsLoaded) {
        emit(current.copyWith(selectedAssignment: assignment));
      }
    } catch (e) {
      emit(
        TeacherAssignmentsError(ErrorMapper.map(e)),
      );
    }
  }

  Future<void> _onCreate(
    CreateTeacherAssignment event,
    Emitter<TeacherAssignmentsState> emit,
  ) async {
    try {
      await _api.post('/v1/teacher/assignments', data: event.toJson());
      add(const LoadTeacherAssignments());
      emit(
        TeacherAssignmentsLoaded(message: 'Assignment created successfully'),
      );
    } catch (e) {
      emit(TeacherAssignmentsError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onUpdate(
    UpdateTeacherAssignment event,
    Emitter<TeacherAssignmentsState> emit,
  ) async {
    try {
      await _api.put(
        '/v1/teacher/assignments/${event.id}',
        data: event.toJson(),
      );
      add(const LoadTeacherAssignments());
      emit(
        TeacherAssignmentsLoaded(message: 'Assignment updated successfully'),
      );
    } catch (e) {
      emit(TeacherAssignmentsError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onDelete(
    DeleteTeacherAssignment event,
    Emitter<TeacherAssignmentsState> emit,
  ) async {
    try {
      await _api.delete('/v1/teacher/assignments/${event.id}');
      add(const LoadTeacherAssignments());
      emit(TeacherAssignmentsLoaded(message: 'Assignment deleted'));
    } catch (e) {
      emit(TeacherAssignmentsError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onLoadProgress(
    LoadAssignmentProgress event,
    Emitter<TeacherAssignmentsState> emit,
  ) async {
    try {
      final response = await _api.get(
        '/v1/teacher/assignments/${event.assignmentId}/progress',
      );
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      final studentsData = data['students'] as List<dynamic>? ?? [];
      final stats = data['stats'] as Map<String, dynamic>? ?? {};
      final progress = studentsData
          .map((e) => StudentProgressModel.fromJson(e as Map<String, dynamic>))
          .toList();
      final current = state;
      if (current is TeacherAssignmentsLoaded) {
        emit(
          current.copyWith(
            progressStudents: progress,
            progressStats: stats.cast<String, dynamic>(),
          ),
        );
      }
    } catch (e) {
      emit(TeacherAssignmentsError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onLoadStudents(
    LoadStudents event,
    Emitter<TeacherAssignmentsState> emit,
  ) async {
    try {
      final params = <String, dynamic>{};
      if (event.programId != null) params['program_id'] = event.programId;
      if (event.departmentId != null) {
        params['department_id'] = event.departmentId;
      }
      final response = await _api.get('/v1/students', queryParameters: params);
      final data = response.data['data'] as List<dynamic>? ?? [];
      final students = data
          .map((e) => StudentItem.fromJson(e as Map<String, dynamic>))
          .toList();
      final current = state;
      if (current is TeacherAssignmentsLoaded) {
        emit(current.copyWith(availableStudents: students));
      }
    } catch (_) {}
  }

  Future<void> _onLoadPrograms(
    LoadPrograms event,
    Emitter<TeacherAssignmentsState> emit,
  ) async {
    try {
      final response = await _api.get('/v1/programs');
      final data = response.data['data'] as List<dynamic>? ?? [];
      final programs = data
          .map((e) => ProgramItem.fromJson(e as Map<String, dynamic>))
          .toList();
      final current = state;
      if (current is TeacherAssignmentsLoaded) {
        emit(current.copyWith(availablePrograms: programs));
      }
    } catch (_) {}
  }

  Future<void> _onLoadDepartments(
    LoadDepartments event,
    Emitter<TeacherAssignmentsState> emit,
  ) async {
    try {
      final response = await _api.get('/v1/departments');
      final data = response.data['data'] as List<dynamic>? ?? [];
      final departments = data
          .map((e) => DepartmentItem.fromJson(e as Map<String, dynamic>))
          .toList();
      final current = state;
      if (current is TeacherAssignmentsLoaded) {
        emit(current.copyWith(availableDepartments: departments));
      }
    } catch (_) {}
  }

  Future<void> _onLoadBooks(
    LoadBooks event,
    Emitter<TeacherAssignmentsState> emit,
  ) async {
    try {
      final response = await _api.get(
        '/v1/books',
        queryParameters: {'per_page': 100},
      );
      final data = response.data['data'] as List<dynamic>? ?? [];
      final books = data
          .map(
            (e) => BookInfo(
              id: parseInt(e['id'], fieldName: 'id'),
              title: e['title'] as String? ?? '',
            ),
          )
          .toList();
      final current = state;
      if (current is TeacherAssignmentsLoaded) {
        emit(current.copyWith(availableBooks: books));
      }
    } catch (_) {}
  }

  Future<void> _onLoadDigitalAssets(
    LoadDigitalAssets event,
    Emitter<TeacherAssignmentsState> emit,
  ) async {
    try {
      final response = await _api.get(
        '/v1/digital-assets',
        queryParameters: {'per_page': 100},
      );
      final data = response.data['data'] as List<dynamic>? ?? [];
      final assets = data
          .map(
            (e) => AssetInfo(
              id: parseInt(e['id'], fieldName: 'id'),
              title: e['title'] as String? ?? '',
            ),
          )
          .toList();
      final current = state;
      if (current is TeacherAssignmentsLoaded) {
        emit(current.copyWith(availableAssets: assets));
      }
    } catch (_) {}
  }
}
