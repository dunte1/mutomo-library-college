import '../../../core/utils/type_parsers.dart';

class CategoryModel {
  final int id;
  final String name;
  final String slug;
  final String? description;
  final int? parentId;
  final int booksCount;
  final int sortOrder;
  final List<CategoryModel> children;

  CategoryModel({
    required this.id,
    required this.name,
    required this.slug,
    this.description,
    this.parentId,
    this.booksCount = 0,
    this.sortOrder = 0,
    this.children = const [],
  });

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    final childrenJson = json['children'] as List<dynamic>?;
    return CategoryModel(
      id: parseInt(json['id'], fieldName: 'id'),
      name: json['name'] as String? ?? '',
      slug: json['slug'] as String? ?? '',
      description: json['description'] as String?,
      parentId: parseIntOrNull(json['parent_id']),
      booksCount: parseIntOrNull(json['books_count']) ?? 0,
      sortOrder: parseIntOrNull(json['sort_order']) ?? 0,
      children:
          childrenJson
              ?.map((e) => CategoryModel.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }
}
