import 'package:flutter_test/flutter_test.dart';
import 'package:ollmchs_library/core/utils/type_parsers.dart';

void main() {
  group('parseInt', () {
    test('parses int value', () {
      expect(parseInt(42, fieldName: 'test'), equals(42));
    });

    test('parses string integer', () {
      expect(parseInt('42', fieldName: 'test'), equals(42));
    });

    test('parses double value as int', () {
      expect(parseInt(42.0, fieldName: 'test'), equals(42));
    });

    test('throws on malformed string', () {
      expect(
        () => parseInt('abc', fieldName: 'id'),
        throwsA(isA<FormatException>()),
      );
    });

    test('throws on null input', () {
      expect(
        () => parseInt(null, fieldName: 'id'),
        throwsA(isA<FormatException>()),
      );
    });
  });

  group('parseIntOrNull', () {
    test('parses int value', () {
      expect(parseIntOrNull(42), equals(42));
    });

    test('parses string integer', () {
      expect(parseIntOrNull('42'), equals(42));
    });

    test('returns null for null input', () {
      expect(parseIntOrNull(null), isNull);
    });

    test('returns null for malformed string', () {
      expect(parseIntOrNull('abc'), isNull);
    });
  });

  group('parseDouble', () {
    test('parses double value', () {
      expect(parseDouble(3.14, fieldName: 'test'), closeTo(3.14, 0.001));
    });

    test('parses int value as double', () {
      expect(parseDouble(42, fieldName: 'test'), equals(42.0));
    });

    test('parses string double', () {
      expect(parseDouble('3.14', fieldName: 'test'), closeTo(3.14, 0.001));
    });

    test('throws on malformed string', () {
      expect(
        () => parseDouble('abc', fieldName: 'amount'),
        throwsA(isA<FormatException>()),
      );
    });

    test('throws on null input', () {
      expect(
        () => parseDouble(null, fieldName: 'amount'),
        throwsA(isA<FormatException>()),
      );
    });
  });

  group('parseDoubleOrNull', () {
    test('parses double value', () {
      expect(parseDoubleOrNull(3.14), closeTo(3.14, 0.001));
    });

    test('returns null for null input', () {
      expect(parseDoubleOrNull(null), isNull);
    });

    test('returns null for malformed string', () {
      expect(parseDoubleOrNull('abc'), isNull);
    });
  });
}
