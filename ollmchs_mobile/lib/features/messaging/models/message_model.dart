import '../../../core/utils/type_parsers.dart';

class MessageModel {
  final int id;
  final String subject;
  final String body;
  final String? senderName;
  final String? senderPhoto;
  final DateTime sentAt;
  final bool isRead;
  final bool hasAttachments;
  final String priority;
  final int? replyCount;
  final List<String>? recipientNames;

  MessageModel({
    required this.id,
    required this.subject,
    required this.body,
    this.senderName,
    this.senderPhoto,
    required this.sentAt,
    this.isRead = false,
    this.hasAttachments = false,
    this.priority = 'normal',
    this.replyCount,
    this.recipientNames,
  });

  bool get isUrgent => priority == 'urgent' || priority == 'high';

  factory MessageModel.fromJson(Map<String, dynamic> json) {
    final sender = json['sender'] as Map<String, dynamic>?;
    final recipients = json['recipients'] as List<dynamic>?;

    List<String>? names;
    if (recipients != null) {
      names = recipients
          .map((r) => r is Map<String, dynamic>
              ? r['recipient'] is Map<String, dynamic>
                  ? r['recipient']['name'] as String? ?? ''
                  : ''
              : '')
          .where((n) => n.isNotEmpty)
          .toList();
    }

    return MessageModel(
      id: parseInt(json['id'], fieldName: 'id'),
      subject: json['subject'] as String? ?? '(No Subject)',
      body: json['body'] as String? ?? '',
      senderName:
          sender?['name'] as String? ??
          json['sender_name'] as String? ??
          'System',
      senderPhoto: sender?['profile_photo_url'] as String?,
      sentAt: DateTime.parse(
        json['created_at'] as String? ?? json['sent_at'] as String,
      ),
      isRead: json['is_read'] as bool? ?? json['read_at'] != null,
      hasAttachments:
          json['has_attachments'] as bool? ??
          (json['attachments'] != null &&
              (json['attachments'] as List).isNotEmpty),
      priority: json['priority'] as String? ?? 'normal',
      replyCount: parseIntOrNull(json['replies_count']),
      recipientNames: names,
    );
  }
}
