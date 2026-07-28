import '../../../core/utils/type_parsers.dart';

class NotificationModel {
  final int id;
  final String title;
  final String body;
  final String? type;
  final int? referenceId;
  final bool isRead;
  final DateTime createdAt;
  final String? iconUrl;
  final String? actionUrl;
  final int? entityId;

  NotificationModel({
    required this.id,
    required this.title,
    required this.body,
    this.type,
    this.referenceId,
    this.isRead = false,
    required this.createdAt,
    this.iconUrl,
    this.actionUrl,
    this.entityId,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as Map<String, dynamic>? ?? json;

    return NotificationModel(
      id: parseInt(json['id'], fieldName: 'id'),
      title: data['title'] as String? ?? json['title'] as String? ?? '',
      body:
          data['body'] as String? ??
          json['body'] as String? ??
          json['message'] as String? ??
          '',
      type: data['type'] as String? ?? json['type'] as String?,
      referenceId: parseIntOrNull(data['reference_id']) ?? parseIntOrNull(json['reference_id']),
      isRead: json['read_at'] != null || json['is_read'] == true,
      createdAt: DateTime.parse(json['created_at'] as String),
      iconUrl: json['icon_url'] as String?,
      actionUrl: data['action_url'] as String? ?? json['action_url'] as String?,
      entityId: parseIntOrNull(data['entity_id']) ?? parseIntOrNull(json['entity_id']),
    );
  }
}
