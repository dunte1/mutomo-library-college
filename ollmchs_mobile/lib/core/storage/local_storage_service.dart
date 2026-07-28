import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Local storage service for persisting auth tokens and user preferences.
///
/// - On native (Android/iOS): uses [FlutterSecureStorage] (encrypted keychain)
/// - On web: uses an in-memory fallback
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
  static const _pinEnabledKey = 'pin_enabled';
  static const _pinHashKey = 'pin_hash';
  static const _lastBackgroundKey = 'last_background_timestamp';
  static const _lastActivityKey = 'last_user_activity';
  static const _autoDownloadsKey = 'auto_downloads';
  static const _offlineSyncKey = 'offline_sync';
  static const _downloadQualityKey = 'download_quality';

  final FlutterSecureStorage? _secure;
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

  // ---- PIN Lock ----

  Future<void> setPinEnabled(bool enabled) =>
      _write(_pinEnabledKey, enabled.toString());

  Future<bool> getPinEnabled() async {
    final val = await _read(_pinEnabledKey);
    return val == 'true';
  }

  Future<void> savePinHash(String hash) =>
      _write(_pinHashKey, hash);

  Future<String?> getPinHash() => _read(_pinHashKey);

  Future<void> clearPin() => _delete(_pinHashKey);

  // ---- Session tracking (app lock) ----

  Future<void> saveLastBackgroundTimestamp(DateTime timestamp) =>
      _write(_lastBackgroundKey, timestamp.toIso8601String());

  Future<DateTime?> getLastBackgroundTimestamp() async {
    final raw = await _read(_lastBackgroundKey);
    if (raw == null) return null;
    return DateTime.tryParse(raw);
  }

  Future<void> saveLastUserActivity(DateTime timestamp) =>
      _write(_lastActivityKey, timestamp.toIso8601String());

  Future<DateTime?> getLastUserActivity() async {
    final raw = await _read(_lastActivityKey);
    if (raw == null) return null;
    return DateTime.tryParse(raw);
  }

  // ---- Downloads & Storage preferences ----

  Future<void> setAutoDownloads(bool enabled) =>
      _write(_autoDownloadsKey, enabled.toString());

  Future<bool> getAutoDownloads() async {
    final val = await _read(_autoDownloadsKey);
    return val == 'true';
  }

  Future<void> setOfflineSync(bool enabled) =>
      _write(_offlineSyncKey, enabled.toString());

  Future<bool> getOfflineSync() async {
    final val = await _read(_offlineSyncKey);
    return val != 'false'; // default true
  }

  Future<void> setDownloadQuality(String quality) =>
      _write(_downloadQualityKey, quality);

  Future<String> getDownloadQuality() async {
    final val = await _read(_downloadQualityKey);
    return val ?? 'standard';
  }

  // ---- Clear all (logout) ----

  Future<void> clearAll() async {
    await Future.wait([
      _delete(_tokenKey),
      _delete(_refreshTokenKey),
      _delete(_tokenExpiryKey),
      _delete(_userKey),
      _delete(_lastBackgroundKey),
      _delete(_lastActivityKey),
      _delete(_pinHashKey),
    ]);
  }

  // ---- Internal helpers ----

  Future<void> _write(String key, String value) async {
    final s = _secure;
    if (s != null) {
      try {
        await s.write(key: key, value: value);
        return;
      } catch (_) {}
    }
    _inMemory[key] = value;
  }

  Future<String?> _read(String key) async {
    final s = _secure;
    if (s != null) {
      try {
        final val = await s.read(key: key);
        if (val != null) return val;
      } catch (_) {}
    }
    return _inMemory[key];
  }

  Future<void> _delete(String key) async {
    final s = _secure;
    if (s != null) {
      try {
        await s.delete(key: key);
        return;
      } catch (_) {}
    }
    _inMemory.remove(key);
  }
}
