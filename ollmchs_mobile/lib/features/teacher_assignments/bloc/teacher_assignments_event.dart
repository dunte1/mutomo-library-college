part of 'teacher_assignments_bloc.dart';

abstract class TeacherAssignmentsEvent extends Equatable {
  const TeacherAssignmentsEvent();
  @override
  List<Object?> get props => [];
}

class LoadTeacherAssignments extends TeacherAssignmentsEvent {
  final int page;
  final String? type;
  final String? status;
  final String? search;
  const LoadTeacherAssignments({
    this.page = 1,
    this.type,
    this.status,
    this.search,
  });
  @override
  List<Object?> get props => [page, type ?? '', status ?? '', search ?? ''];
}

class LoadTeacherAssignmentDetail extends TeacherAssignmentsEvent {
  final int id;
  const LoadTeacherAssignmentDetail(this.id);
  @override
  List<Object?> get props => [id];
}

class CreateTeacherAssignment extends TeacherAssignmentsEvent {
  final String assignTo;
  final int? studentId;
  final int? programId;
  final int? departmentId;
  final String type;
  final String title;
  final String? description;
  final String? dueDate;
  final int? bookId;
  final int? digitalAssetId;
  final String? notes;

  const CreateTeacherAssignment({
    required this.assignTo,
    this.studentId,
    this.programId,
    this.departmentId,
    required this.type,
    required this.title,
    this.description,
    this.dueDate,
    this.bookId,
    this.digitalAssetId,
    this.notes,
  });

  Map<String, dynamic> toJson() => {
    'assign_to': assignTo,
    if (studentId != null) 'student_id': studentId,
    if (programId != null) 'program_id': programId,
    if (departmentId != null) 'department_id': departmentId,
    'type': type,
    'title': title,
    if (description != null) 'description': description,
    if (dueDate != null && type == 'assignment') 'due_date': dueDate,
    if (bookId != null) 'book_id': bookId,
    if (digitalAssetId != null) 'digital_asset_id': digitalAssetId,
    if (notes != null) 'notes': notes,
  };

  @override
  List<Object?> get props => [
    assignTo,
    studentId,
    programId,
    departmentId,
    type,
    title,
  ];
}

class UpdateTeacherAssignment extends TeacherAssignmentsEvent {
  final int id;
  final String? title;
  final String? description;
  final String? dueDate;
  final String? type;
  final int? bookId;
  final int? digitalAssetId;
  final String? notes;
  final String? status;

  const UpdateTeacherAssignment({
    required this.id,
    this.title,
    this.description,
    this.dueDate,
    this.type,
    this.bookId,
    this.digitalAssetId,
    this.notes,
    this.status,
  });

  Map<String, dynamic> toJson() => {
    if (title != null) 'title': title,
    if (description != null) 'description': description,
    if (dueDate != null) 'due_date': dueDate,
    if (type != null) 'type': type,
    if (bookId != null) 'book_id': bookId,
    if (digitalAssetId != null) 'digital_asset_id': digitalAssetId,
    if (notes != null) 'notes': notes,
    if (status != null) 'status': status,
  };

  @override
  List<Object?> get props => [id, title ?? '', dueDate ?? ''];
}

class DeleteTeacherAssignment extends TeacherAssignmentsEvent {
  final int id;
  const DeleteTeacherAssignment(this.id);
  @override
  List<Object?> get props => [id];
}

class LoadAssignmentProgress extends TeacherAssignmentsEvent {
  final int assignmentId;
  const LoadAssignmentProgress(this.assignmentId);
  @override
  List<Object?> get props => [assignmentId];
}

class LoadStudents extends TeacherAssignmentsEvent {
  final int? programId;
  final int? departmentId;
  const LoadStudents({this.programId, this.departmentId});
  @override
  List<Object?> get props => [programId, departmentId];
}

class LoadPrograms extends TeacherAssignmentsEvent {
  const LoadPrograms();
}

class LoadDepartments extends TeacherAssignmentsEvent {
  const LoadDepartments();
}

class LoadBooks extends TeacherAssignmentsEvent {
  const LoadBooks();
}

class LoadDigitalAssets extends TeacherAssignmentsEvent {
  const LoadDigitalAssets();
}
