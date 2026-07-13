import '../../../core/network/api_client.dart';
import '../../../core/utils/type_parsers.dart';
import '../models/book_model.dart';
import '../../../core/storage/hive_cache_service.dart';

class PaginatedResult<T> {
  final List<T> items;
  final bool hasMore;
  final int total;

  PaginatedResult({
    required this.items,
    required this.hasMore,
    required this.total,
  });
}

class BooksRepository {
  final ApiClient _api;
  final HiveCacheService _cache;

  BooksRepository(this._api, {HiveCacheService? cache})
      : _cache = cache ?? HiveCacheService();

  Future<PaginatedResult<BookModel>> getBooks({
    int page = 1,
    String? search,
    String? category,
  }) async {
    final params = <String, dynamic>{'page': page, 'per_page': 20};
    if (search != null && search.isNotEmpty) params['search'] = search;
    if (category != null) params['category'] = category;

    try {
      final response = await _api.get('/v1/books', queryParameters: params);
      final data = response.data;
      final rawList =
          (data['data'] as List<dynamic>?)?.cast<Map<String, dynamic>>() ?? [];
      try {
        await _cache.cacheBooks(rawList);
      } catch (_) {
        // Cache failure should not block the response
      }
      return _parsePaginated(data);
    } catch (e) {
      if (page == 1) {
        try {
          final cached = _cache.getCachedBooks();
          if (cached != null) {
            return PaginatedResult(
              items: cached
                  .map((e) => BookModel.fromJson(e))
                  .toList(),
              hasMore: false,
              total: cached.length,
            );
          }
        } catch (_) {
          // Cache read failure — fall through to rethrow original error
        }
      }
      rethrow;
    }
  }

  Future<BookModel> getBookDetail(int id) async {
    try {
      final response = await _api.get('/v1/books/$id');
      final book =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      try {
        await _cache.put('book_detail_$id', book,
            ttl: const Duration(hours: 2));
      } catch (_) {
        // Cache failure should not block the response
      }
      return BookModel.fromJson(book);
    } catch (e) {
      final cached = _cache.get<Map<String, dynamic>>('book_detail_$id');
      if (cached != null) return BookModel.fromJson(cached);
      rethrow;
    }
  }

  Future<List<BookModel>> getFeaturedBooks() async {
    try {
      final response = await _api.get(
        '/v1/books',
        queryParameters: {'featured': true, 'per_page': 10},
      );
      final data = response.data;
      final rawList =
          (data['data'] as List<dynamic>?)?.cast<Map<String, dynamic>>() ?? [];
      try {
        await _cache.cacheBooks(rawList);
      } catch (_) {
        // Cache failure should not block the response
      }
      return rawList
          .map((e) => BookModel.fromJson(e))
          .toList();
    } catch (e) {
      final cached = _cache.getCachedBooks();
      if (cached != null) {
        return cached
            .map((e) => BookModel.fromJson(e))
            .toList();
      }
      rethrow;
    }
  }

  Future<PaginatedResult<BookModel>> searchBooks({
    required String query,
    int page = 1,
  }) async {
    final response = await _api.get(
      '/v1/books/search',
      queryParameters: {'q': query, 'page': page},
    );
    return _parsePaginated(response.data);
  }

  Future<List<String>> getCategories() async {
    try {
      final response = await _api.get('/v1/categories');
      final data = response.data['data'] as List<dynamic>? ?? [];
      return data
          .map((e) => e is Map ? e['name'] as String? ?? '' : e.toString())
          .where((n) => n.isNotEmpty)
          .toList();
    } catch (_) {
      return [];
    }
  }

  PaginatedResult<BookModel> _parsePaginated(dynamic data) {
    final list = data['data'] as List<dynamic>? ?? [];
    final meta = data['meta'] as Map<String, dynamic>? ?? data;
    final total = parseIntOrNull(meta['total']) ?? list.length;
    final lastPage = parseIntOrNull(meta['last_page']) ?? 1;
    final currentPage = parseIntOrNull(meta['current_page']) ?? 1;

    return PaginatedResult(
      items: list
          .map((e) => BookModel.fromJson(e as Map<String, dynamic>))
          .toList(),
      hasMore: currentPage < lastPage,
      total: total,
    );
  }

}
