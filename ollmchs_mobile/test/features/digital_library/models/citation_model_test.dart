import 'package:flutter_test/flutter_test.dart';
import 'package:ollmchs_library/features/digital_library/models/citation_model.dart';

void main() {
  group('CitationModel', () {
    test('fromJson with all fields', () {
      final json = {
        'digital_asset_id': 5,
        'style': 'APA',
        'citation_text': 'Author, A. (2024). Title. Publisher.',
        'generated_at': '2026-07-25T10:00:00.000Z',
      };
      final citation = CitationModel.fromJson(json);
      expect(citation.assetId, 5);
      expect(citation.style, 'APA');
      expect(citation.citationText, 'Author, A. (2024). Title. Publisher.');
      expect(citation.generatedAt, DateTime.parse('2026-07-25T10:00:00.000Z'));
    });

    test('fromJson with missing fields', () {
      final json = <String, dynamic>{};
      final citation = CitationModel.fromJson(json);
      expect(citation.style, 'APA');
      expect(citation.citationText, '');
    });

    test('fromJson with asset_id key', () {
      final json = {
        'asset_id': 10,
        'style': 'MLA',
        'citation_text': 'Citation text',
      };
      final citation = CitationModel.fromJson(json);
      expect(citation.assetId, 10);
    });
  });
}
