import '../../../core/utils/type_parsers.dart';

class TeacherAssignmentModel {
  final int id;
  final String title;
  final String? description;
  final String type;
  final String status;
  final String? dueAt;
  final StudentInfo? student;
  final BookInfo? book;
  final AssetInfo? digitalAsset;
  final ProgramInfo? program;
  final DepartmentInfo? department;
  final String? notes;
  final String? createdAt;
  final String? updatedAt;

  TeacherAssignmentModel({
    required this.id,
    required this.title,
    this.description,
    required this.type,
    this.status = 'pending',
    this.dueAt,
    this.student,
    this.book,
    this.digitalAsset,
    this.program,
    this.department,
    this.notes,
    this.createdAt,
    this.updatedAt,
  });

  bool get isAssignment => type == 'assignment';
  bool get isRecommendation => type == 'recommendation';
  bool get isPending => status == 'pending';
  bool get isCompleted => status == 'completed';

  factory TeacherAssignmentModel.fromJson(Map<String, dynamic> json) {
    return TeacherAssignmentModel(
      id: parseInt(json['id'], fieldName: 'id'),
      title: json['title'] as String? ?? '',
      description: json['description'] as String?,
      type: json['type'] as String? ?? 'assignment',
      status: json['status'] as String? ?? 'pending',
      dueAt: json['due_at'] as String?,
      student: json['student'] != null
          ? StudentInfo.fromJson(json['student'] as Map<String, dynamic>)
          : null,
      book: json['book'] != null
          ? BookInfo.fromJson(json['book'] as Map<String, dynamic>)
          : null,
      digitalAsset: json['digital_asset'] != null
          ? AssetInfo.fromJson(json['digital_asset'] as Map<String, dynamic>)
          : null,
      program: json['program'] != null
          ? ProgramInfo.fromJson(json['program'] as Map<String, dynamic>)
          : null,
      department: json['department'] != null
          ? DepartmentInfo.fromJson(json['department'] as Map<String, dynamic>)
          : null,
      notes: json['notes'] as String?,
      createdAt: json['created_at'] as String?,
      updatedAt: json['updated_at'] as String?,
    );
  }
}

class StudentInfo {
  final int id;
  final String name;
  final String? email;
  StudentInfo({required this.id, required this.name, this.email});
  factory StudentInfo.fromJson(Map<String, dynamic> json) => StudentInfo(
    id: parseInt(json['id'], fieldName: 'id'),
    name: json['name'] as String? ?? '',
    email: json['email'] as String?,
  );
}

class BookInfo {
  final int id;
  final String title;
  BookInfo({required this.id, required this.title});
  factory BookInfo.fromJson(Map<String, dynamic> json) =>
      BookInfo(id: parseInt(json['id'], fieldName: 'id'), title: json['title'] as String? ?? '');
}

class AssetInfo {
  final int id;
  final String title;
  AssetInfo({required this.id, required this.title});
  factory AssetInfo.fromJson(Map<String, dynamic> json) =>
      AssetInfo(id: parseInt(json['id'], fieldName: 'id'), title: json['title'] as String? ?? '');
}

class ProgramInfo {
  final int id;
  final String name;
  ProgramInfo({required this.id, required this.name});
  factory ProgramInfo.fromJson(Map<String, dynamic> json) =>
      ProgramInfo(id: parseInt(json['id'], fieldName: 'id'), name: json['name'] as String? ?? '');
}

class DepartmentInfo {
  final int id;
  final String name;
  DepartmentInfo({required this.id, required this.name});
  factory DepartmentInfo.fromJson(Map<String, dynamic> json) => DepartmentInfo(
    id: parseInt(json['id'], fieldName: 'id'),
    name: json['name'] as String? ?? '',
  );
}

class StudentProgressModel {
  final int id;
  final int studentId;
  final String studentName;
  final String status;
  final String? viewedAt;
  final String? completedAt;
  final int? score;
  final String? feedback;

  StudentProgressModel({
    required this.id,
    required this.studentId,
    required this.studentName,
    this.status = 'pending',
    this.viewedAt,
    this.completedAt,
    this.score,
    this.feedback,
  });

  bool get hasViewed => viewedAt != null;
  bool get isCompleted => completedAt != null;

  factory StudentProgressModel.fromJson(Map<String, dynamic> json) {
    return StudentProgressModel(
      id: parseInt(json['id'], fieldName: 'id'),
      studentId: parseInt(json['student_id'], fieldName: 'student_id'),
      studentName: json['student_name'] as String? ?? '',
      status: json['status'] as String? ?? 'pending',
      viewedAt: json['viewed_at'] as String?,
      completedAt: json['completed_at'] as String?,
      score: parseIntOrNull(json['score']),
      feedback: json['feedback'] as String?,
    );
  }
}

class StudentItem {
  final int id;
  final String name;
  final String? email;
  StudentItem({required this.id, required this.name, this.email});
  factory StudentItem.fromJson(Map<String, dynamic> json) => StudentItem(
    id: parseInt(json['id'], fieldName: 'id'),
    name: json['name'] as String? ?? '',
    email: json['email'] as String?,
  );
}

class ProgramItem {
  final int id;
  final String name;
  final String? code;
  ProgramItem({required this.id, required this.name, this.code});
  factory ProgramItem.fromJson(Map<String, dynamic> json) => ProgramItem(
    id: parseInt(json['id'], fieldName: 'id'),
    name: json['name'] as String? ?? '',
    code: json['code'] as String?,
  );
}

class DepartmentItem {
  final int id;
  final String name;
  final String? code;
  DepartmentItem({required this.id, required this.name, this.code});
  factory DepartmentItem.fromJson(Map<String, dynamic> json) => DepartmentItem(
    id: parseInt(json['id'], fieldName: 'id'),
    name: json['name'] as String? ?? '',
    code: json['code'] as String?,
  );
}
