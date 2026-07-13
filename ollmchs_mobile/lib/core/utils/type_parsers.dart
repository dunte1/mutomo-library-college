int parseInt(dynamic value, {String? fieldName}) {
  if (value is int) return value;
  if (value is double) return value.toInt();
  if (value is String) {
    final trimmed = value.trim();
    if (trimmed.isEmpty) {
      throw FormatException(
        'Cannot parse empty string to int${fieldName != null ? " for field: $fieldName" : ""}',
      );
    }
    final parsed = int.tryParse(trimmed);
    if (parsed != null) return parsed;
    throw FormatException(
      'Cannot parse "$trimmed" to int${fieldName != null ? " for field: $fieldName" : ""}',
    );
  }
  if (value == null) {
    throw FormatException(
      'Expected int but got null${fieldName != null ? " for field: $fieldName" : ""}',
    );
  }
  throw FormatException(
    'Cannot parse ${value.runtimeType} value "$value" to int${fieldName != null ? " for field: $fieldName" : ""}',
  );
}

int? parseIntOrNull(dynamic value, {String? fieldName}) {
  if (value == null) return null;
  if (value is int) return value;
  if (value is double) return value.toInt();
  if (value is String) {
    final trimmed = value.trim();
    if (trimmed.isEmpty) return null;
    final parsed = int.tryParse(trimmed);
    if (parsed != null) return parsed;
    return null;
  }
  return null;
}

double parseDouble(dynamic value, {String? fieldName}) {
  if (value is double) return value;
  if (value is int) return value.toDouble();
  if (value is String) {
    final trimmed = value.trim();
    if (trimmed.isEmpty) {
      throw FormatException(
        'Cannot parse empty string to double${fieldName != null ? " for field: $fieldName" : ""}',
      );
    }
    final parsed = double.tryParse(trimmed);
    if (parsed != null) return parsed;
    throw FormatException(
      'Cannot parse "$trimmed" to double${fieldName != null ? " for field: $fieldName" : ""}',
    );
  }
  if (value == null) {
    throw FormatException(
      'Expected double but got null${fieldName != null ? " for field: $fieldName" : ""}',
    );
  }
  throw FormatException(
    'Cannot parse ${value.runtimeType} value "$value" to double${fieldName != null ? " for field: $fieldName" : ""}',
  );
}

double? parseDoubleOrNull(dynamic value, {String? fieldName}) {
  if (value == null) return null;
  if (value is double) return value;
  if (value is int) return value.toDouble();
  if (value is String) {
    final trimmed = value.trim();
    if (trimmed.isEmpty) return null;
    final parsed = double.tryParse(trimmed);
    if (parsed != null) return parsed;
    return null;
  }
  return null;
}
