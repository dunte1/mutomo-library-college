import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Local storage service for persisting auth tokens and user preferences.
///
/// - On native (Android/iOS): uses [FlutterSecureStorage] (encrypted keychain)
/// - On web: uses an in-memory fallback (browser localStorage not suitable for tokens)
///
/// All methods are async for uniform API across platforms.
class LocalStorageService {
  static const _tokenKey = 'auth_token';
  static const _refreshTokenKey = 'refresh_token';
  static const _tokenExpiryKey = 'token_expiry';
  static const _userKey = 'cached_user';
  static const _themeKey = 'theme_mode';
  static const _notificationsKey = 'notifications_enabled';
  static const _onboardingKey = 'onboarding_completed';
  static const _biometricEnabledKey = 'biometric_enabled';
  static const _savedEmailKey = 'saved_email';
  static const _savedPasswordKey = 'saved_password';

  final FlutterSecureStorage? _secure;
  // Web fallback: in-memory store (avoids [MissingPluginException] on web)
  final Map<String, String> _inMemory = {};

  LocalStorageService()
    : _secure = kIsWeb
          ? null
          : const FlutterSecureStorage(
              aOptions: AndroidOptions(encryptedSharedPreferences: true),
            );

  // ---- Auth tokens ----

  Future<void> saveToken(String token) => _write(_tokenKey, token);

  Future<String?> getToken() => _read(_tokenKey);

  Future<void> saveRefreshToken(String token) =>
      _write(_refreshTokenKey, token);

  Future<String?> getRefreshToken() => _read(_refreshTokenKey);

  // ---- Token expiry ----

  Future<void> saveTokenExpiry(int expiresInSeconds) {
    final expiry = DateTime.now().add(Duration(seconds: expiresInSeconds));
    return _write(_tokenExpiryKey, expiry.toIso8601String());
  }

  Future<DateTime?> getTokenExpiry() async {
    final raw = await _read(_tokenExpiryKey);
    if (raw == null) return null;
    return DateTime.tryParse(raw);
  }

  Future<bool> isTokenExpired() async {
    final expiry = await getTokenExpiry();
    if (expiry == null) return true;
    return DateTime.now().isAfter(expiry);
  }

  Future<bool> isTokenExpiringSoon({int withinSeconds = 600}) async {
    final expiry = await getTokenExpiry();
    if (expiry == null) return true;
    return DateTime.now().isAfter(
      expiry.subtract(Duration(seconds: withinSeconds)),
    );
  }

  // ---- Cached user data ----

  Future<void> cacheUser(String userJson) => _write(_userKey, userJson);

  Future<String?> getCachedUser() => _read(_userKey);

  // ---- User preferences ----

  Future<void> setThemeMode(String mode) => _write(_themeKey, mode);

  Future<String?> getThemeMode() => _read(_themeKey);

  Future<void> setNotificationsEnabled(bool enabled) =>
      _write(_notificationsKey, enabled.toString());

  Future<bool> getNotificationsEnabled() async {
    final val = await _read(_notificationsKey);
    return val != 'false';
  }

  // ---- Onboarding ----

  Future<void> setOnboardingSeen(bool seen) =>
      _write(_onboardingKey, seen.toString());

  Future<bool> getOnboardingSeen() async {
    final val = await _read(_onboardingKey);
    return val == 'true';
  }

  // ---- Biometric Login ----

  Future<void> setBiometricEnabled(bool enabled) =>
      _write(_biometricEnabledKey, enabled.toString());

  Future<bool> getBiometricEnabled() async {
    final val = await _read(_biometricEnabledKey);
    return val == 'true';
  }

  Future<void> saveBiometricCredentials(String email, String password) async {
    await Future.wait([
      _write(_savedEmailKey, email),
      _write(_savedPasswordKey, password),
    ]);
  }

  Future<Map<String, String?>> getBiometricCredentials() async {
    final results = await Future.wait([
      _read(_savedEmailKey),
      _read(_savedPasswordKey),
    ]);
    return {'email': results[0], 'password': results[1]};
  }

  Future<void> clearBiometricCredentials() async {
    await Future.wait([_delete(_savedEmailKey), _delete(_savedPasswordKey)]);
  }

  // ---- Clear all (logout) ----

  Future<void> clearAll() async {
    await Future.wait([
      _delete(_tokenKey),
      _delete(_refreshTokenKey),
      _delete(_tokenExpiryKey),
      _delete(_userKey),
    ]);
  }

  // ---- Internal helpers ----

  Future<void> _write(String key, String value) async {
    final s = _secure;
    if (s != null) {
      try {
        await s.write(key: key, value: value);
        return;
      } catch (_) {
        // Fall through to in-memory on error
      }
    }
    _inMemory[key] = value;
  }

  Future<String?> _read(String key) async {
    final s = _secure;
    if (s != null) {
      try {
        final val = await s.read(key: key);
        if (val != null) return val;
      } catch (_) {
        // Fall through to in-memory
      }
    }
    return _inMemory[key];
  }

  Future<void> _delete(String key) async {
    final s = _secure;
    if (s != null) {
      try {
        await s.delete(key: key);
        return;
      } catch (_) {
        // Fall through to in-memory
      }
    }
    _inMemory.remove(key);
  }
}
