import '../../../core/network/api_client.dart';
import '../models/book_model.dart';

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

  BooksRepository(this._api);

  Future<PaginatedResult<BookModel>> getBooks({
    int page = 1,
    String? search,
    String? category,
  }) async {
    final params = <String, dynamic>{'page': page, 'per_page': 20};
    if (search != null && search.isNotEmpty) params['search'] = search;
    if (category != null) params['category'] = category;

    final response = await _api.get('/v1/books', queryParameters: params);
    final data = response.data;

    return _parsePaginated(data);
  }

  Future<BookModel> getBookDetail(int id) async {
    final response = await _api.get('/v1/books/$id');
    final book =
        response.data['data'] as Map<String, dynamic>? ??
        response.data as Map<String, dynamic>;
    return BookModel.fromJson(book);
  }

  Future<List<BookModel>> getFeaturedBooks() async {
    final response = await _api.get(
      '/v1/books',
      queryParameters: {'featured': true, 'per_page': 10},
    );
    final data = response.data;
    final list = data['data'] as List<dynamic>? ?? [];

    return list
        .map((e) => BookModel.fromJson(e as Map<String, dynamic>))
        .toList();
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
    final total = meta['total'] as int? ?? list.length;
    final lastPage = meta['last_page'] as int? ?? 1;
    final currentPage = meta['current_page'] as int? ?? 1;

    return PaginatedResult(
      items: list
          .map((e) => BookModel.fromJson(e as Map<String, dynamic>))
          .toList(),
      hasMore: currentPage < lastPage,
      total: total,
    );
  }
}
