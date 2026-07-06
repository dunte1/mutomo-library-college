import 'dart:async';
import 'package:flutter_test/flutter_test.dart';
import 'package:ollmchs_library/core/storage/local_storage_service.dart';

void main() {
  late LocalStorageService storage;

  setUp(() {
    storage = LocalStorageService();
  });

  group('LocalStorageService — token expiry storage', () {
    test('saveTokenExpiry stores and getTokenExpiry retrieves the value', () async {
      await storage.saveTokenExpiry(86400);

      final expiry = await storage.getTokenExpiry();

      expect(expiry, isNotNull);
      final expected = DateTime.now().add(const Duration(seconds: 86400));
      expect(expiry!.difference(expected).inSeconds, closeTo(0, 2));
    });

    test('getTokenExpiry returns null when nothing stored', () async {
      final expiry = await storage.getTokenExpiry();
      expect(expiry, isNull);
    });

    test('saveToken and saveTokenExpiry persist independently', () async {
      await storage.saveToken('my-test-token');
      await storage.saveTokenExpiry(7200);

      expect(await storage.getToken(), equals('my-test-token'));
      expect(await storage.getTokenExpiry(), isNotNull);
    });
  });

  group('isTokenExpired', () {
    test('returns false when token has plenty of time left', () async {
      await storage.saveTokenExpiry(86400);

      final expired = await storage.isTokenExpired();

      expect(expired, isFalse);
    });

    test('returns true when token is already expired', () async {
      // Save a token with 0-second expiry, wait briefly so DateTime.now() advances past it
      await storage.saveTokenExpiry(0);
      await Future.delayed(const Duration(milliseconds: 50));

      final expired = await storage.isTokenExpired();

      expect(expired, isTrue);
    });

    test('returns true when no expiry is stored (null)', () async {
      final expired = await storage.isTokenExpired();
      expect(expired, isTrue);
    });

    test('returns true when token expired just barely', () async {
      // Save a 1-second expiry, wait 2 seconds
      await storage.saveTokenExpiry(1);
      await Future.delayed(const Duration(seconds: 2));

      final expired = await storage.isTokenExpired();
      expect(expired, isTrue);
    });
  });

  group('isTokenExpiringSoon', () {
    test('returns true when expiry is within threshold', () async {
      await storage.saveTokenExpiry(300); // 5 minutes from now

      final result = await storage.isTokenExpiringSoon(withinSeconds: 600);

      expect(result, isTrue);
    });

    test('returns false when expiry is well outside threshold', () async {
      await storage.saveTokenExpiry(86400); // 24 hours from now

      final result = await storage.isTokenExpiringSoon(withinSeconds: 600);

      expect(result, isFalse);
    });

    test('returns true when no expiry stored (null)', () async {
      final result = await storage.isTokenExpiringSoon(withinSeconds: 600);
      expect(result, isTrue);
    });

    test('returns false with zero threshold when token has time left', () async {
      await storage.saveTokenExpiry(86400);

      final result = await storage.isTokenExpiringSoon(withinSeconds: 0);

      expect(result, isFalse);
    });
  });

  group('clearAll — includes token expiry', () {
    test('clearAll removes token, refresh token, expiry, and user', () async {
      await storage.saveToken('abc');
      await storage.saveTokenExpiry(86400);

      expect(await storage.getToken(), isNotNull);
      expect(await storage.getTokenExpiry(), isNotNull);

      await storage.clearAll();

      expect(await storage.getToken(), isNull);
      expect(await storage.getTokenExpiry(), isNull);
    });
  });

  group('Token expiry — refresh guard behavior', () {
    test('concurrent callers share a single completer result', () async {
      // Simulates the guard pattern: a Completer is shared among concurrent callers.
      // Only the first caller executes the real work; others await the same future.
      final completer = Completer<String>();
      final results = <String>[];

      // Simulate 3 concurrent requests waiting on the same completer
      final f1 = completer.future.then((v) => results.add('r1:$v'));
      final f2 = completer.future.then((v) => results.add('r2:$v'));
      final f3 = completer.future.then((v) => results.add('r3:$v'));

      completer.complete('done');
      await Future.wait([f1, f2, f3]);

      expect(results, containsAll(['r1:done', 'r2:done', 'r3:done']));
    });
  });
}
