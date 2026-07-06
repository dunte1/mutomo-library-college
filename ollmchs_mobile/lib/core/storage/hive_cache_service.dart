import 'dart:convert';
import 'package:hive_flutter/hive_flutter.dart';

class HiveCacheService {
  static const String _boxName = 'app_cache';
  static const Duration _defaultTtl = Duration(hours: 1);

  static Future<void> init() async {
    await Hive.initFlutter();
    await Hive.openBox(_boxName);
  }

  Box get _box => Hive.box(_boxName);

  // ---- Generic cache with TTL ----

  Future<void> put(String key, dynamic value, {Duration? ttl}) async {
    final expiry = DateTime.now()
        .add(ttl ?? _defaultTtl)
        .millisecondsSinceEpoch;
    await _box.put(key, jsonEncode({'data': value, 'expiry': expiry}));
  }

  T? get<T>(String key) {
    final raw = _box.get(key);
    if (raw == null) return null;
    try {
      final entry = jsonDecode(raw) as Map<String, dynamic>;
      final expiry = entry['expiry'] as int;
      if (DateTime.now().millisecondsSinceEpoch > expiry) {
        _box.delete(key);
        return null;
      }
      return entry['data'] as T;
    } catch (_) {
      _box.delete(key);
      return null;
    }
  }

  Future<void> remove(String key) => _box.delete(key);

  Future<void> clear() => _box.clear();

  // ---- Convenience helpers ----

  Future<void> cacheBooks(List<Map<String, dynamic>> books) =>
      put('books_cache', books, ttl: const Duration(hours: 2));

  List<Map<String, dynamic>>? getCachedBooks() =>
      get<List<dynamic>>('books_cache')?.cast<Map<String, dynamic>>();

  Future<void> cacheLoans(List<Map<String, dynamic>> loans) =>
      put('loans_cache', loans);

  List<Map<String, dynamic>>? getCachedLoans() =>
      get<List<dynamic>>('loans_cache')?.cast<Map<String, dynamic>>();

  Future<void> cacheReservations(List<Map<String, dynamic>> reservations) =>
      put('reservations_cache', reservations);

  List<Map<String, dynamic>>? getCachedReservations() =>
      get<List<dynamic>>('reservations_cache')?.cast<Map<String, dynamic>>();

  Future<void> cacheFines(List<Map<String, dynamic>> fines) =>
      put('fines_cache', fines);

  List<Map<String, dynamic>>? getCachedFines() =>
      get<List<dynamic>>('fines_cache')?.cast<Map<String, dynamic>>();

  Future<void> cacheDigitalAssets(List<Map<String, dynamic>> assets) =>
      put('digital_assets_cache', assets);

  List<Map<String, dynamic>>? getCachedDigitalAssets() =>
      get<List<dynamic>>('digital_assets_cache')?.cast<Map<String, dynamic>>();

  Future<void> cacheProfile(Map<String, dynamic> profile) =>
      put('profile_cache', profile, ttl: const Duration(minutes: 30));

  Map<String, dynamic>? getCachedProfile() =>
      get<Map<String, dynamic>>('profile_cache');
}
