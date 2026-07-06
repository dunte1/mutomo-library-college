import '../../../core/network/api_client.dart';
import '../models/reservation_model.dart';
import '../../../core/storage/hive_cache_service.dart';

class ReservationsRepository {
  final ApiClient _api;
  final HiveCacheService _cache;

  ReservationsRepository(this._api, {HiveCacheService? cache})
      : _cache = cache ?? HiveCacheService();

  Future<List<ReservationModel>> getReservations() async {
    try {
      final response = await _api.get('/v1/reservations');
      final rawList = (response.data['data'] as List<dynamic>?)
              ?.cast<Map<String, dynamic>>() ??
          [];
      await _cache.cacheReservations(rawList);
      return rawList
          .map((e) => ReservationModel.fromJson(e))
          .toList();
    } catch (e) {
      final cached = _cache.getCachedReservations();
      if (cached != null) {
        return cached
            .map((e) => ReservationModel.fromJson(e))
            .toList();
      }
      rethrow;
    }
  }

  Future<void> createReservation(int bookId) async {
    await _api.post('/v1/reservations', data: {'book_id': bookId});
    await _cache.remove('reservations_cache');
  }

  Future<void> cancelReservation(int id) async {
    await _api.delete('/v1/reservations/$id');
    await _cache.remove('reservations_cache');
  }

}
