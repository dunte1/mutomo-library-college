import 'dart:io';

import 'package:flutter_test/flutter_test.dart';
import 'package:hive_flutter/hive_flutter.dart';
import 'package:ollmchs_library/core/storage/hive_cache_service.dart';

void main() {
  late HiveCacheService service;
  late Directory tempDir;

  setUp(() async {
    tempDir = Directory.systemTemp.createTempSync('hive_test_');
    Hive.init(tempDir.path);
    await Hive.openBox('app_cache');
    service = HiveCacheService();
  });

  tearDown(() async {
    await Hive.deleteBoxFromDisk('app_cache');
    tempDir.deleteSync(recursive: true);
  });

  group('HiveCacheService', () {
    test('put and get roundtrip', () async {
      await service.put('test_key', {'name': 'test'});
      final result = service.get<Map>('test_key');
      expect(result, isNotNull);
      expect(result!['name'], equals('test'));
    });

    test('get returns null for missing key', () {
      expect(service.get<String>('nonexistent'), isNull);
    });

    test('cache returns null after TTL expiry', () async {
      await service.put('short_key', 'value', ttl: const Duration(milliseconds: 1));
      await Future.delayed(const Duration(milliseconds: 50));
      expect(service.get<String>('short_key'), isNull);
    });

    test('cacheBooks and getCachedBooks roundtrip', () async {
      final books = [{'id': 1, 'title': 'Book 1'}];
      await service.cacheBooks(books);
      final cached = service.getCachedBooks();
      expect(cached, isNotNull);
      expect(cached!.length, equals(1));
      expect(cached[0]['title'], equals('Book 1'));
    });

    test('cacheLoans and getCachedLoans roundtrip', () async {
      final loans = [{'id': 1, 'status': 'active'}];
      await service.cacheLoans(loans);
      final cached = service.getCachedLoans();
      expect(cached, isNotNull);
      expect(cached!.length, equals(1));
    });

    test('cacheReservations and getCachedReservations roundtrip', () async {
      final reservations = [{'id': 1, 'status': 'pending'}];
      await service.cacheReservations(reservations);
      final cached = service.getCachedReservations();
      expect(cached, isNotNull);
      expect(cached!.length, equals(1));
    });

    test('cacheFines and getCachedFines roundtrip', () async {
      final fines = [{'id': 1, 'amount': 50.0}];
      await service.cacheFines(fines);
      final cached = service.getCachedFines();
      expect(cached, isNotNull);
      expect(cached!.length, equals(1));
    });

    test('cacheDigitalAssets and getCachedDigitalAssets roundtrip', () async {
      final assets = [{'id': 1, 'name': 'Asset 1'}];
      await service.cacheDigitalAssets(assets);
      final cached = service.getCachedDigitalAssets();
      expect(cached, isNotNull);
      expect(cached!.length, equals(1));
    });

    test('cacheProfile and getCachedProfile roundtrip', () async {
      final profile = {'name': 'John', 'email': 'john@test.com'};
      await service.cacheProfile(profile);
      final cached = service.getCachedProfile();
      expect(cached, isNotNull);
      expect(cached!['name'], equals('John'));
    });

    test('remove clears single entry', () async {
      await service.put('key1', 'value1');
      await service.put('key2', 'value2');
      await service.remove('key1');
      expect(service.get<String>('key1'), isNull);
      expect(service.get<String>('key2'), isNotNull);
    });

    test('clear removes all entries', () async {
      await service.put('key1', 'value1');
      await service.put('key2', 'value2');
      await service.clear();
      expect(service.get<String>('key1'), isNull);
      expect(service.get<String>('key2'), isNull);
    });

    test('corrupted data returns null and deletes key', () async {
      final box = Hive.box('app_cache');
      await box.put('bad_key', 'not-json');
      expect(service.get<String>('bad_key'), isNull);
      expect(box.containsKey('bad_key'), isFalse);
    });

    test('default TTL is 1 hour', () async {
      await service.put('default_ttl_key', 'value');
      await Future.delayed(const Duration(milliseconds: 10));
      expect(service.get<String>('default_ttl_key'), isNotNull);
    });
  });
}
