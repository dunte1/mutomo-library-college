class EventModel {
  final int id;
  final String title;
  final String? description;
  final String? location;
  final DateTime startAt;
  final DateTime? endAt;
  final String? organizerName;
  final String? imageUrl;
  final String status;

  EventModel({
    required this.id,
    required this.title,
    this.description,
    this.location,
    required this.startAt,
    this.endAt,
    this.organizerName,
    this.imageUrl,
    this.status = 'upcoming',
  });

  bool get isUpcoming => startAt.isAfter(DateTime.now());
  bool get isOngoing =>
      startAt.isBefore(DateTime.now()) &&
      (endAt == null || endAt!.isAfter(DateTime.now()));
  bool get isPast => endAt != null && endAt!.isBefore(DateTime.now());

  factory EventModel.fromJson(Map<String, dynamic> json) {
    final organizer = json['organizer'] as Map<String, dynamic>?;
    return EventModel(
      id: json['id'] as int,
      title: json['title'] as String? ?? '',
      description: json['description'] as String?,
      location: json['location'] as String?,
      startAt: DateTime.parse(json['start_at'] as String),
      endAt: json['end_at'] != null
          ? DateTime.tryParse(json['end_at'] as String)
          : null,
      organizerName:
          organizer?['name'] as String? ?? json['organizer_name'] as String?,
      imageUrl: json['image_url'] as String?,
      status: json['status'] as String? ?? 'upcoming',
    );
  }
}
