import 'dart:async';
import 'package:dio/dio.dart';
import '../constants/environment.dart';
import '../errors/exceptions.dart';
import '../storage/local_storage_service.dart';

class ApiClient {
  late final Dio _dio;
  final LocalStorageService _storageService;

  Completer<String?>? _refreshCompleter;
  bool _isRefreshing = false;

  ApiClient({required this._storageService}) {
    _dio = Dio(
      BaseOptions(
        baseUrl: Environment.apiBaseUrl,
        connectTimeout: Environment.requestTimeout,
        receiveTimeout: Environment.requestTimeout,
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ),
    );

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await _storageService.getToken();
          if (token != null) options.headers['Authorization'] = 'Bearer $token';
          handler.next(options);
        },
        onError: (error, handler) async {
          if (error.response?.statusCode == 401 &&
              error.requestOptions.path != '/v1/auth/refresh' &&
              error.requestOptions.extra['retried'] != true) {
            final newToken = await _attemptRefresh();
            if (newToken != null) {
              error.requestOptions.headers['Authorization'] =
                  'Bearer $newToken';
              error.requestOptions.extra['retried'] = true;
              try {
                final response = await _dio.fetch(error.requestOptions);
                handler.resolve(response);
                return;
              } on DioException catch (e) {
                handler.next(e);
                return;
              }
            }
            await _storageService.clearAll();
          }
          handler.next(error);
        },
      ),
    );
  }

  Future<String?> _attemptRefresh() async {
    if (_isRefreshing) {
      return _refreshCompleter!.future;
    }

    _isRefreshing = true;
    _refreshCompleter = Completer<String?>();

    try {
      final newToken = await _refreshToken();
      _refreshCompleter!.complete(newToken);
      return newToken;
    } catch (_) {
      _refreshCompleter!.complete(null);
      return null;
    } finally {
      _isRefreshing = false;
      _refreshCompleter = null;
    }
  }

  Future<String?> _refreshToken() async {
    try {
      final dio = Dio(
        BaseOptions(
          baseUrl: Environment.apiBaseUrl,
          connectTimeout: Environment.requestTimeout,
          receiveTimeout: Environment.requestTimeout,
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
        ),
      );

      final token = await _storageService.getToken();
      if (token == null) return null;

      final response = await dio.post(
        '/v1/auth/refresh',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );

      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      final newToken =
          data['token'] as String? ?? data['access_token'] as String? ?? '';
      final expiresIn = data['expires_in'] as int?;

      if (newToken.isEmpty) return null;

      await _storageService.saveToken(newToken);
      if (expiresIn != null) {
        await _storageService.saveTokenExpiry(expiresIn);
      }

      return newToken;
    } on DioException {
      return null;
    }
  }

  Future<Response> get(
    String path, {
    Map<String, dynamic>? queryParameters,
  }) async {
    try {
      return await _dio.get(path, queryParameters: queryParameters);
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<Response> post(String path, {dynamic data}) async {
    try {
      return await _dio.post(path, data: data);
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<Response> put(String path, {dynamic data}) async {
    try {
      return await _dio.put(path, data: data);
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<Response> delete(String path) async {
    try {
      return await _dio.delete(path);
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Exception _handleError(DioException e) {
    switch (e.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
      case DioExceptionType.connectionError:
        return NetworkException();
      case DioExceptionType.badResponse:
        final sc = e.response?.statusCode;
        final data = e.response?.data;
        final msg = data is Map
            ? (data['message'] as String? ?? 'Unknown error')
            : 'Unknown error';
        if (sc == 401) return UnauthorizedException(msg);
        if (sc == 422)
          return ValidationException(
            message: msg,
            errors: data is Map
                ? data['errors'] as Map<String, dynamic>?
                : null,
          );
        return ServerException(message: msg, statusCode: sc);
      default:
        return NetworkException('Unexpected error occurred');
    }
  }
}
