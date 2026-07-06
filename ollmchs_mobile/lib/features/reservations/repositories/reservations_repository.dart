import '../../../core/network/api_client.dart';
import '../models/reservation_model.dart';

class ReservationsRepository {
  final ApiClient _api;

  ReservationsRepository(this._api);

  Future<List<ReservationModel>> getReservations() async {
    final response = await _api.get('/v1/reservations');
    final data = response.data['data'] as List<dynamic>? ?? [];
    return data
        .map((e) => ReservationModel.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<void> createReservation(int bookId) async {
    await _api.post('/v1/reservations', data: {'book_id': bookId});
  }

  Future<void> cancelReservation(int id) async {
    await _api.delete('/v1/reservations/$id');
  }
}
