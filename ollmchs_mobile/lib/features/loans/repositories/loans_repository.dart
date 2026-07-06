import '../../../core/network/api_client.dart';
import '../models/loan_model.dart';
import '../../books/repositories/books_repository.dart';

class LoansRepository {
  final ApiClient _api;

  LoansRepository(this._api);

  Future<List<LoanModel>> getActiveLoans() async {
    final response = await _api.get('/v1/loans/active');
    final data = response.data['data'] as List<dynamic>? ?? [];
    return data
        .map((e) => LoanModel.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<PaginatedResult<LoanHistoryModel>> getLoanHistory({
    int page = 1,
  }) async {
    final response = await _api.get(
      '/v1/loans/history',
      queryParameters: {'page': page, 'per_page': 20},
    );
    return _parseHistory(response.data);
  }

  Future<LoanModel> getLoanDetail(int id) async {
    final response = await _api.get('/v1/loans/$id');
    final data =
        response.data['data'] as Map<String, dynamic>? ??
        response.data as Map<String, dynamic>;
    return LoanModel.fromJson(data);
  }

  Future<LoanModel> renewLoan(int id) async {
    final response = await _api.post('/v1/loans/$id/renew');
    final data =
        response.data['data'] as Map<String, dynamic>? ??
        response.data as Map<String, dynamic>;
    return LoanModel.fromJson(data);
  }

  PaginatedResult<LoanHistoryModel> _parseHistory(dynamic data) {
    final list = data['data'] as List<dynamic>? ?? [];
    final meta = data['meta'] as Map<String, dynamic>? ?? data;

    return PaginatedResult(
      items: list
          .map((e) => LoanHistoryModel.fromJson(e as Map<String, dynamic>))
          .toList(),
      hasMore:
          (meta['current_page'] as int? ?? 1) <
          (meta['last_page'] as int? ?? 1),
      total: meta['total'] as int? ?? list.length,
    );
  }
}
