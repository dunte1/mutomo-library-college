class AssignmentModel {
  final int id;
  final String title;
  final String? description;
  final String? attachmentUrl;
  final DateTime dueAt;
  final String status;
  final String? subject;
  final String? teacherName;
  final DateTime? submittedAt;
  final String? feedback;
  final int? score;

  AssignmentModel({
    required this.id,
    required this.title,
    this.description,
    this.attachmentUrl,
    required this.dueAt,
    this.status = 'pending',
    this.subject,
    this.teacherName,
    this.submittedAt,
    this.feedback,
    this.score,
  });

  bool get isPending => status == 'pending';
  bool get isInProgress => status == 'in_progress';
  bool get isOverdue => dueAt.isBefore(DateTime.now()) && status == 'pending';
  bool get isSubmitted => status == 'submitted';
  bool get isGraded => status == 'graded';
  bool get canMarkComplete => isPending || isInProgress;

  factory AssignmentModel.fromJson(Map<String, dynamic> json) {
    final teacher = json['teacher'] as Map<String, dynamic>?;
    return AssignmentModel(
      id: json['id'] as int,
      title: json['title'] as String? ?? '',
      description: json['description'] as String?,
      attachmentUrl: json['attachment_url'] as String?,
      dueAt: DateTime.parse(json['due_at'] as String),
      status: json['status'] as String? ?? 'pending',
      subject: json['subject'] as String?,
      teacherName:
          teacher?['name'] as String? ?? json['teacher_name'] as String?,
      submittedAt: json['submitted_at'] != null
          ? DateTime.tryParse(json['submitted_at'] as String)
          : null,
      feedback: json['feedback'] as String?,
      score: json['score'] as int?,
    );
  }
}
