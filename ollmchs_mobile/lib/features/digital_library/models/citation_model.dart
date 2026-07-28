import '../../../core/utils/type_parsers.dart';

class CitationModel {
  final int assetId;
  final String style;
  final String citationText;
  final DateTime generatedAt;

  CitationModel({
    required this.assetId,
    required this.style,
    required this.citationText,
    required this.generatedAt,
  });

  factory CitationModel.fromJson(Map<String, dynamic> json) {
    return CitationModel(
      assetId: parseIntOrNull(json['digital_asset_id'] ?? json['asset_id']) ?? 0,
      style: json['style'] as String? ?? 'APA',
      citationText: json['citation_text'] as String? ?? '',
      generatedAt: json['generated_at'] != null
          ? DateTime.parse(json['generated_at'] as String)
          : DateTime.now(),
    );
  }
}
