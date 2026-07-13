import '../../../core/utils/type_parsers.dart';

class PublisherModel {
  final int id;
  final String name;
  final String? description;
  final String? website;
  final String? email;
  final String? phone;
  final String? address;
  final int booksCount;

  PublisherModel({
    required this.id,
    required this.name,
    this.description,
    this.website,
    this.email,
    this.phone,
    this.address,
    this.booksCount = 0,
  });

  factory PublisherModel.fromJson(Map<String, dynamic> json) {
    return PublisherModel(
      id: parseInt(json['id'], fieldName: 'id'),
      name: json['name'] as String? ?? '',
      description: json['description'] as String?,
      website: json['website'] as String?,
      email: json['email'] as String?,
      phone: json['phone'] as String?,
      address: json['address'] as String?,
      booksCount: parseIntOrNull(json['books_count']) ?? 0,
    );
  }
}
