import 'package:flutter/foundation.dart';
import 'exceptions.dart';

/// Maps exceptions to user-friendly messages.
/// Raw exception details are logged internally; users see clean messages.
class ErrorMapper {
  ErrorMapper._();

  /// Convert any exception to a user-safe message.
  /// Logs the raw error in debug mode for developer visibility.
  static String map(dynamic error) {
    // Log raw error in debug mode only
    if (kDebugMode) {
      debugPrint('[ErrorMapper] Raw: $error');
    }

    if (error is NetworkException) {
      return 'No internet connection. Please check your network and try again.';
    }

    if (error is UnauthorizedException) {
      return 'Your session has expired. Please log in again.';
    }

    if (error is ValidationException) {
      return error.toString(); // Already user-friendly from toString()
    }

    if (error is ServerException) {
      return _mapServerException(error);
    }

    // Generic fallback — never expose raw exception text
    if (error.toString().contains('SocketException') ||
        error.toString().contains('Connection reset')) {
      return 'No internet connection. Please check your network and try again.';
    }

    if (error.toString().contains('timeout') ||
        error.toString().contains('Timeout')) {
      return 'Request timed out. Please try again.';
    }

    return 'Something went wrong. Please try again.';
  }

  static String _mapServerException(ServerException e) {
    switch (e.statusCode) {
      case 400:
        return 'Invalid request. Please check your input and try again.';
      case 401:
        return 'Your session has expired. Please log in again.';
      case 403:
        return 'You don\'t have permission to do that.';
      case 404:
        return 'The requested resource was not found.';
      case 422:
        // Validation errors — try to show the first field error
        if (e.errors != null && e.errors!.isNotEmpty) {
          final first = e.errors!.values.first;
          if (first is List && first.isNotEmpty) {
            return first.first.toString();
          }
          return first.toString();
        }
        return e.message.isNotEmpty ? e.message : 'Please check your input.';
      case 429:
        return 'Too many requests. Please wait a moment and try again.';
      case 500:
        return 'A server error occurred. Please try again later.';
      case 502:
      case 503:
        return 'The service is temporarily unavailable. Please try again later.';
      default:
        return e.message.isNotEmpty
            ? e.message
            : 'Something went wrong. Please try again.';
    }
  }
}
