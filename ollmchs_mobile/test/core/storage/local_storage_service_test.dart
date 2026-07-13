import 'package:flutter_test/flutter_test.dart';
import 'package:ollmchs_library/core/storage/local_storage_service.dart';

void main() {
  late LocalStorageService storage;

  setUp(() {
    storage = LocalStorageService();
  });

  group('LocalStorageService', () {
    group('Auth tokens', () {
      test('saveToken and getToken roundtrip', () async {
        await storage.saveToken('test-token-123');
        final token = await storage.getToken();
        expect(token, 'test-token-123');
      });

      test('getToken returns null when not set', () async {
        final token = await storage.getToken();
        expect(token, isNull);
      });

      test('saveRefreshToken and getRefreshToken roundtrip', () async {
        await storage.saveRefreshToken('refresh-456');
        final token = await storage.getRefreshToken();
        expect(token, 'refresh-456');
      });

      test('getRefreshToken returns null when not set', () async {
        final token = await storage.getRefreshToken();
        expect(token, isNull);
      });
    });

    group('Token expiry', () {
      test('saveTokenExpiry and getTokenExpiry roundtrip', () async {
        await storage.saveTokenExpiry(3600); // 1 hour
        final expiry = await storage.getTokenExpiry();
        expect(expiry, isNotNull);
        expect(expiry!.isAfter(DateTime.now()), true);
      });

      test('isTokenExpired returns true when no expiry set', () async {
        final expired = await storage.isTokenExpired();
        expect(expired, true);
      });

      test('isTokenExpired returns false when token is fresh', () async {
        await storage.saveTokenExpiry(3600);
        final expired = await storage.isTokenExpired();
        expect(expired, false);
      });

      test('isTokenExpiringSoon returns true when no expiry set', () async {
        final expiringSoon = await storage.isTokenExpiringSoon();
        expect(expiringSoon, true);
      });

      test('isTokenExpiringSoon returns false when far from expiry', () async {
        await storage.saveTokenExpiry(3600);
        final expiringSoon = await storage.isTokenExpiringSoon(withinSeconds: 600);
        expect(expiringSoon, false);
      });
    });

    group('Cached user', () {
      test('cacheUser and getCachedUser roundtrip', () async {
        await storage.cacheUser('{"id":1,"name":"Test"}');
        final user = await storage.getCachedUser();
        expect(user, '{"id":1,"name":"Test"}');
      });

      test('getCachedUser returns null when not set', () async {
        final user = await storage.getCachedUser();
        expect(user, isNull);
      });
    });

    group('User preferences', () {
      test('setThemeMode and getThemeMode roundtrip', () async {
        await storage.setThemeMode('dark');
        final mode = await storage.getThemeMode();
        expect(mode, 'dark');
      });

      test('getThemeMode returns null when not set', () async {
        final mode = await storage.getThemeMode();
        expect(mode, isNull);
      });

      test('setNotificationsEnabled and getNotificationsEnabled roundtrip', () async {
        await storage.setNotificationsEnabled(false);
        final enabled = await storage.getNotificationsEnabled();
        expect(enabled, false);
      });

      test('getNotificationsEnabled defaults to true', () async {
        final enabled = await storage.getNotificationsEnabled();
        expect(enabled, true);
      });
    });

    group('Onboarding', () {
      test('setOnboardingSeen and getOnboardingSeen roundtrip', () async {
        await storage.setOnboardingSeen(true);
        final seen = await storage.getOnboardingSeen();
        expect(seen, true);
      });

      test('getOnboardingSeen defaults to false', () async {
        final seen = await storage.getOnboardingSeen();
        expect(seen, false);
      });
    });

    group('Biometric Login', () {
      test('setBiometricEnabled and getBiometricEnabled roundtrip', () async {
        await storage.setBiometricEnabled(true);
        final enabled = await storage.getBiometricEnabled();
        expect(enabled, true);
      });

      test('getBiometricEnabled defaults to false', () async {
        final enabled = await storage.getBiometricEnabled();
        expect(enabled, false);
      });

      test('setBiometricEnabled false persists correctly', () async {
        await storage.setBiometricEnabled(true);
        await storage.setBiometricEnabled(false);
        final enabled = await storage.getBiometricEnabled();
        expect(enabled, false);
      });
    });

    group('PIN Lock', () {
      test('setPinEnabled and getPinEnabled roundtrip', () async {
        await storage.setPinEnabled(true);
        final enabled = await storage.getPinEnabled();
        expect(enabled, true);
      });

      test('getPinEnabled defaults to false', () async {
        final enabled = await storage.getPinEnabled();
        expect(enabled, false);
      });

      test('savePinHash and getPinHash roundtrip', () async {
        await storage.savePinHash('salt:hash123');
        final hash = await storage.getPinHash();
        expect(hash, 'salt:hash123');
      });

      test('getPinHash returns null when not set', () async {
        final hash = await storage.getPinHash();
        expect(hash, isNull);
      });

      test('clearPin removes stored hash', () async {
        await storage.savePinHash('salt:hash123');
        await storage.clearPin();
        final hash = await storage.getPinHash();
        expect(hash, isNull);
      });
    });

    group('Session tracking', () {
      test('saveLastBackgroundTimestamp and getLastBackgroundTimestamp roundtrip', () async {
        final now = DateTime.now();
        await storage.saveLastBackgroundTimestamp(now);
        final stored = await storage.getLastBackgroundTimestamp();
        expect(stored, isNotNull);
        expect(stored!.difference(now).inSeconds, 0);
      });

      test('getLastBackgroundTimestamp returns null when not set', () async {
        final ts = await storage.getLastBackgroundTimestamp();
        expect(ts, isNull);
      });

      test('saveLastUserActivity and getLastUserActivity roundtrip', () async {
        final now = DateTime.now();
        await storage.saveLastUserActivity(now);
        final stored = await storage.getLastUserActivity();
        expect(stored, isNotNull);
        expect(stored!.difference(now).inSeconds, 0);
      });

      test('getLastUserActivity returns null when not set', () async {
        final ts = await storage.getLastUserActivity();
        expect(ts, isNull);
      });
    });

    group('clearAll', () {
      test('clearAll removes auth tokens and user data', () async {
        await storage.saveToken('token');
        await storage.saveRefreshToken('refresh');
        await storage.cacheUser('user');
        await storage.savePinHash('pin');

        await storage.clearAll();

        expect(await storage.getToken(), isNull);
        expect(await storage.getRefreshToken(), isNull);
        expect(await storage.getCachedUser(), isNull);
        expect(await storage.getPinHash(), isNull);
      });

      test('clearAll preserves preferences', () async {
        await storage.setBiometricEnabled(true);
        await storage.setNotificationsEnabled(false);
        await storage.setThemeMode('dark');

        await storage.clearAll();

        expect(await storage.getBiometricEnabled(), true);
        expect(await storage.getNotificationsEnabled(), false);
        expect(await storage.getThemeMode(), 'dark');
      });
    });

    group('Overwrite behavior', () {
      test('saving same key overwrites previous value', () async {
        await storage.saveToken('old-token');
        await storage.saveToken('new-token');
        final token = await storage.getToken();
        expect(token, 'new-token');
      });

      test('different keys are independent', () async {
        await storage.saveToken('token-value');
        await storage.saveRefreshToken('refresh-value');
        expect(await storage.getToken(), 'token-value');
        expect(await storage.getRefreshToken(), 'refresh-value');
      });
    });
  });
}
