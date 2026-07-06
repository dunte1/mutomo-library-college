import '../../../core/network/api_client.dart';
import '../../../core/storage/local_storage_service.dart';
import '../models/user_model.dart';

class AuthRepository {
  final ApiClient _api;
  final LocalStorageService _storage;

  AuthRepository(this._api, this._storage);

  Future<Map<String, dynamic>> login(
    String email,
    String password,
    String? deviceName,
  ) async {
    final response = await _api.post(
      '/v1/auth/login',
      data: {
        'email': email,
        'password': password,
        'device_name': deviceName ?? 'Mobile App',
      },
    );

    final data =
        response.data['data'] as Map<String, dynamic>? ??
        response.data as Map<String, dynamic>;

    // 2FA required — return temp token for verification
    if (data['requires_two_factor'] == true) {
      return {
        'requires_two_factor': true,
        'temp_token': data['temp_token'] as String? ?? '',
        'user_id': data['user_id'] as int?,
      };
    }

    final token =
        data['token'] as String? ?? data['access_token'] as String? ?? '';
    final userData = data['user'] as Map<String, dynamic>? ?? data;
    final expiresIn = data['expires_in'] as int?;

    if (token.isNotEmpty) {
      await _storage.saveToken(token);
    }
    if (expiresIn != null) {
      await _storage.saveTokenExpiry(expiresIn);
    }
    if (userData.isNotEmpty) {
      await _storage.cacheUser(userData.toString());
    }

    return {'user': UserModel.fromJson(userData), 'token': token};
  }

  Future<Map<String, dynamic>> verifyTwoFactor({
    required int userId,
    required String code,
    required String tempToken,
  }) async {
    final response = await _api.post(
      '/v1/auth/2fa/verify',
      data: {'user_id': userId, 'code': code, 'device_name': 'Mobile App'},
    );

    final data =
        response.data['data'] as Map<String, dynamic>? ??
        response.data as Map<String, dynamic>;
    final token =
        data['token'] as String? ?? data['access_token'] as String? ?? '';
    final expiresIn = data['expires_in'] as int?;

    if (token.isNotEmpty) {
      await _storage.saveToken(token);
    }
    if (expiresIn != null) {
      await _storage.saveTokenExpiry(expiresIn);
    }

    return {'token': token};
  }

  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String phone,
    required String password,
    required String passwordConfirmation,
    required String role,
    String? admissionNumber,
    int? departmentId,
    int? programId,
  }) async {
    final response = await _api.post(
      '/v1/auth/register',
      data: {
        'name': name,
        'email': email,
        'phone': phone,
        'password': password,
        'password_confirmation': passwordConfirmation,
        'role': role,
        'admission_number': admissionNumber,
        'department_id': departmentId,
        'program_id': programId,
      },
    );

    final data =
        response.data['data'] as Map<String, dynamic>? ??
        response.data as Map<String, dynamic>;
    final token =
        data['token'] as String? ?? data['access_token'] as String? ?? '';
    final userData = data['user'] as Map<String, dynamic>? ?? data;
    final expiresIn = data['expires_in'] as int?;

    if (token.isNotEmpty) {
      await _storage.saveToken(token);
    }
    if (expiresIn != null) {
      await _storage.saveTokenExpiry(expiresIn);
    }

    return {'user': UserModel.fromJson(userData), 'token': token};
  }

  Future<Map<String, dynamic>> enableTwoFactor({
    required String password,
  }) async {
    final response = await _api.post(
      '/v1/auth/2fa/enable',
      data: {'password': password},
    );
    final data =
        response.data['data'] as Map<String, dynamic>? ??
        response.data as Map<String, dynamic>;
    return {
      'secret': data['secret'] as String? ?? '',
      'qr_code_url': data['qr_code_url'] as String? ?? '',
      'recovery_codes':
          (data['recovery_codes'] as List<dynamic>?)?.cast<String>() ?? [],
    };
  }

  Future<void> verifyTwoFactorSetup({required String code}) async {
    await _api.post('/v1/auth/2fa/verify-setup', data: {'code': code});
  }

  Future<void> disableTwoFactor({
    required String password,
    required String code,
  }) async {
    await _api.post(
      '/v1/auth/2fa/disable',
      data: {'password': password, 'code': code},
    );
  }

  Future<void> logout() async {
    try {
      await _api.post('/v1/auth/logout');
    } catch (_) {}
    await _storage.clearAll();
  }

  Future<String?> refresh() async {
    final response = await _api.post('/v1/auth/refresh');
    final data =
        response.data['data'] as Map<String, dynamic>? ??
        response.data as Map<String, dynamic>;
    final newToken =
        data['token'] as String? ?? data['access_token'] as String? ?? '';
    final expiresIn = data['expires_in'] as int?;

    if (newToken.isEmpty) return null;

    await _storage.saveToken(newToken);
    if (expiresIn != null) {
      await _storage.saveTokenExpiry(expiresIn);
    }

    return newToken;
  }

  Future<String?> getStoredToken() async {
    return await _storage.getToken();
  }

  Future<UserModel> getUser() async {
    final response = await _api.get('/v1/auth/user');
    final data =
        response.data['data'] as Map<String, dynamic>? ??
        response.data as Map<String, dynamic>;
    return UserModel.fromJson(data);
  }

  Future<void> forgotPassword(String email) async {
    await _api.post('/v1/auth/forgot-password', data: {'email': email});
  }

  Future<void> resetPassword({
    required String token,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    await _api.post(
      '/v1/auth/reset-password',
      data: {
        'token': token,
        'email': email,
        'password': password,
        'password_confirmation': passwordConfirmation,
      },
    );
  }
}
