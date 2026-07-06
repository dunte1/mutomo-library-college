class ServerException implements Exception {
  final String message;
  final int? statusCode;
  final Map<String, dynamic>? errors;
  ServerException({required this.message, this.statusCode, this.errors});
  @override
  String toString() => 'ServerException: $message (status: $statusCode)';
}

class NetworkException implements Exception {
  final String message;
  NetworkException([this.message = 'No internet connection']);
  @override
  String toString() => 'NetworkException: $message';
}

class UnauthorizedException implements Exception {
  final String message;
  UnauthorizedException([
    this.message = 'Session expired. Please login again.',
  ]);
  @override
  String toString() => 'UnauthorizedException: $message';
}

class ValidationException implements Exception {
  final String message;
  final Map<String, dynamic>? errors;
  ValidationException({required this.message, this.errors});

  @override
  String toString() {
    if (errors != null && errors!.isNotEmpty) {
      final firstError = errors!.values.first;
      if (firstError is List && firstError.isNotEmpty) {
        return firstError.first.toString();
      }
      return firstError.toString();
    }
    return message;
  }
}
