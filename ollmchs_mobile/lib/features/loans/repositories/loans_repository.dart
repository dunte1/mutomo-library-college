import '../../../core/network/api_client.dart';
import '../../../core/utils/type_parsers.dart';
import '../models/loan_model.dart';
import '../../books/repositories/books_repository.dart';
import '../../../core/storage/hive_cache_service.dart';

class LoansRepository {
  final ApiClient _api;
  final HiveCacheService _cache;

  LoansRepository(this._api, {HiveCacheService? cache})
      : _cache = cache ?? HiveCacheService();

  Future<PaginatedResult<LoanModel>> getActiveLoans({
    int page = 1,
  }) async {
    try {
      final response = await _api.get(
        '/v1/loans/active',
        queryParameters: {'page': page, 'per_page': 20},
      );
      final rawList = (response.data['data'] as List<dynamic>?)
              ?.cast<Map<String, dynamic>>() ??
          [];
      await _cache.cacheLoans(rawList);
      return _parseActiveLoans(response.data, rawList);
    } catch (e) {
      final cached = _cache.getCachedLoans();
      if (cached != null) {
        final items = cached.map((e) => LoanModel.fromJson(e)).toList();
        return PaginatedResult(
          items: items,
          hasMore: false,
          total: items.length,
        );
      }
      rethrow;
    }
  }

  PaginatedResult<LoanModel> _parseActiveLoans(
    dynamic data,
    List<Map<String, dynamic>> rawList,
  ) {
    final meta = data['meta'] as Map<String, dynamic>? ?? data;
    return PaginatedResult(
      items: rawList.map((e) => LoanModel.fromJson(e)).toList(),
      hasMore:
          (parseIntOrNull(meta['current_page']) ?? 1) <
          (parseIntOrNull(meta['last_page']) ?? 1),
      total: parseIntOrNull(meta['total']) ?? rawList.length,
    );
  }

  Future<PaginatedResult<LoanHistoryModel>> getLoanHistory({
    int page = 1,
  }) async {
    try {
      final response = await _api.get(
        '/v1/loans/history',
        queryParameters: {'page': page, 'per_page': 20},
      );
      final rawList = (response.data['data'] as List<dynamic>?)
              ?.cast<Map<String, dynamic>>() ??
          [];
      await _cache.put('loan_history_page_$page', rawList,
          ttl: const Duration(hours: 1));
      return _parseHistory(response.data);
    } catch (e) {
      final cached =
          _cache.get<List<dynamic>>('loan_history_page_$page');
      if (cached != null) {
        final items = cached
            .cast<Map<String, dynamic>>()
            .map((e) => LoanHistoryModel.fromJson(e))
            .toList();
        return PaginatedResult(
          items: items,
          hasMore: false,
          total: items.length,
        );
      }
      rethrow;
    }
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
          (parseIntOrNull(meta['current_page']) ?? 1) <
          (parseIntOrNull(meta['last_page']) ?? 1),
      total: parseIntOrNull(meta['total']) ?? list.length,
    );
  }

}
