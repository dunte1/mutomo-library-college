part of 'teacher_assignments_bloc.dart';

abstract class TeacherAssignmentsState extends Equatable {
  const TeacherAssignmentsState();
  @override
  List<Object?> get props => [];
}

class TeacherAssignmentsInitial extends TeacherAssignmentsState {}

class TeacherAssignmentsLoading extends TeacherAssignmentsState {}

class TeacherAssignmentsLoaded extends TeacherAssignmentsState {
  final List<TeacherAssignmentModel> assignments;
  final TeacherAssignmentModel? selectedAssignment;
  final List<StudentProgressModel> progressStudents;
  final Map<String, dynamic> progressStats;
  final List<StudentItem> availableStudents;
  final List<ProgramItem> availablePrograms;
  final List<DepartmentItem> availableDepartments;
  final List<BookInfo> availableBooks;
  final List<AssetInfo> availableAssets;
  final String? message;

  const TeacherAssignmentsLoaded({
    this.assignments = const [],
    this.selectedAssignment,
    this.progressStudents = const [],
    this.progressStats = const {},
    this.availableStudents = const [],
    this.availablePrograms = const [],
    this.availableDepartments = const [],
    this.availableBooks = const [],
    this.availableAssets = const [],
    this.message,
  });

  TeacherAssignmentsLoaded copyWith({
    List<TeacherAssignmentModel>? assignments,
    TeacherAssignmentModel? selectedAssignment,
    List<StudentProgressModel>? progressStudents,
    Map<String, dynamic>? progressStats,
    List<StudentItem>? availableStudents,
    List<ProgramItem>? availablePrograms,
    List<DepartmentItem>? availableDepartments,
    List<BookInfo>? availableBooks,
    List<AssetInfo>? availableAssets,
    String? message,
    bool clearMessage = false,
  }) {
    return TeacherAssignmentsLoaded(
      assignments: assignments ?? this.assignments,
      selectedAssignment: selectedAssignment ?? this.selectedAssignment,
      progressStudents: progressStudents ?? this.progressStudents,
      progressStats: progressStats ?? this.progressStats,
      availableStudents: availableStudents ?? this.availableStudents,
      availablePrograms: availablePrograms ?? this.availablePrograms,
      availableDepartments: availableDepartments ?? this.availableDepartments,
      availableBooks: availableBooks ?? this.availableBooks,
      availableAssets: availableAssets ?? this.availableAssets,
      message: clearMessage ? null : (message ?? this.message),
    );
  }

  @override
  List<Object?> get props => [
    assignments,
    selectedAssignment,
    progressStudents,
    message ?? '',
  ];
}

class TeacherAssignmentsError extends TeacherAssignmentsState {
  final String error;
  const TeacherAssignmentsError(this.error);
  @override
  List<Object?> get props => [error];
}
