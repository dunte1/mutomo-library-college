import 'package:flutter_test/flutter_test.dart';
import 'package:ollmchs_library/features/books/models/book_model.dart';
import 'package:ollmchs_library/features/books/models/book_copy_model.dart';

void main() {
  group('BookModel', () {
    test('fromJson creates valid BookModel with all fields', () {
      final json = {
        'id': 1,
        'title': 'Test Book',
        'isbn': '1234567890',
        'isbn13': '9781234567890',
        'description': 'A test book description',
        'publication_year': 2024,
        'page_count': 200,
        'cover_image': 'http://example.com/cover.jpg',
        'language': 'English',
        'publisher': 'Test Publisher',
        'category': 'Fiction',
        'category_id': 5,
        'authors': ['Author One', 'Author Two'],
        'total_copies': 5,
        'available_copies': 3,
        'is_featured': true,
        'location': 'Main Library',
        'dewey_decimal': '123.45 TES',
        'created_at': '2024-01-15T10:00:00Z',
        'copies': [
          {
            'id': 1,
            'barcode': 'BC001',
            'status': 'available',
          },
          {
            'id': 2,
            'barcode': 'BC002',
            'status': 'borrowed',
          },
        ],
      };

      final book = BookModel.fromJson(json);
      expect(book.id, equals(1));
      expect(book.title, equals('Test Book'));
      expect(book.isbn, equals('1234567890'));
      expect(book.isbn13, equals('9781234567890'));
      expect(book.description, equals('A test book description'));
      expect(book.publicationYear, equals(2024));
      expect(book.pageCount, equals(200));
      expect(book.coverImage, equals('http://example.com/cover.jpg'));
      expect(book.language, equals('English'));
      expect(book.publisher, equals('Test Publisher'));
      expect(book.category, equals('Fiction'));
      expect(book.categoryId, equals(5));
      expect(book.authors, contains('Author One'));
      expect(book.totalCopies, equals(5));
      expect(book.availableCopies, equals(3));
      expect(book.isFeatured, isTrue);
      expect(book.location, equals('Main Library'));
      expect(book.deweyDecimal, equals('123.45 TES'));
      expect(book.createdAt, isNotNull);
      expect(book.createdAt!.year, equals(2024));
      expect(book.copies.length, equals(2));
      expect(book.copies[0].barcode, equals('BC001'));
      expect(book.copies[1].barcode, equals('BC002'));
    });

    test('fromJson handles minimal fields', () {
      final json = {
        'id': 1,
        'title': 'Minimal Book',
      };
      final book = BookModel.fromJson(json);
      expect(book.id, equals(1));
      expect(book.title, equals('Minimal Book'));
      expect(book.isbn, isNull);
      expect(book.isbn13, isNull);
      expect(book.description, isNull);
      expect(book.authors, isEmpty);
      expect(book.totalCopies, equals(0));
      expect(book.isFeatured, isFalse);
      expect(book.copies, isEmpty);
    });

    test('fromJson handles publisher and category as maps', () {
      final json = {
        'id': 1,
        'title': 'Map Book',
        'publisher': {'id': 1, 'name': 'Map Publisher'},
        'category': {'id': 3, 'name': 'Science'},
        'authors': ['Single Author'],
      };

      final book = BookModel.fromJson(json);
      expect(book.publisher, equals('Map Publisher'));
      expect(book.category, equals('Science'));
      expect(book.categoryId, equals(3));
    });

    test('fromJson handles authors as maps', () {
      final json = {
        'id': 1,
        'title': 'Author Map Book',
        'authors': [
          {'id': 1, 'name': 'John Doe'},
          {'id': 2, 'name': 'Jane Smith'},
        ],
      };

      final book = BookModel.fromJson(json);
      expect(book.authors, contains('John Doe'));
      expect(book.authors, contains('Jane Smith'));
    });

    test('toJson roundtrip preserves fields', () {
      final json = {
        'id': 1,
        'title': 'Roundtrip Book',
        'isbn': null,
        'isbn13': null,
        'description': null,
        'publication_year': null,
        'page_count': null,
        'cover_image': null,
        'language': null,
        'publisher': null,
        'category': null,
        'category_id': null,
        'authors': [],
        'total_copies': 0,
        'available_copies': 0,
        'is_featured': false,
        'location': null,
        'dewey_decimal': null,
        'created_at': null,
        'copies': [],
      };
      final book = BookModel.fromJson(json);
      final out = book.toJson();
      expect(out['id'], equals(1));
      expect(out['title'], equals('Roundtrip Book'));
      expect(out['authors'], isEmpty);
      expect(out['total_copies'], equals(0));
      expect(out['is_featured'], isFalse);
      expect(out['copies'], isEmpty);
    });

    test('toJson includes nested copies', () {
      final book = BookModel(
        id: 1,
        title: 'With Copies',
        copies: [
          BookCopyModel(id: 1, barcode: 'BC001', status: 'available'),
        ],
      );
      final json = book.toJson();
      expect(json['copies'], isA<List>());
      expect((json['copies'] as List).length, equals(1));
      expect((json['copies'] as List).first['barcode'], equals('BC001'));
    });

    test('full JSON roundtrip', () {
      final json = {
        'id': 10,
        'title': 'Roundtrip Full',
        'isbn': '0987654321',
        'isbn13': '9780987654321',
        'description': 'Full roundtrip test',
        'publication_year': 2023,
        'page_count': 350,
        'cover_image': 'http://example.com/cover2.jpg',
        'language': 'French',
        'publisher': 'Another Publisher',
        'category': 'Non-Fiction',
        'category_id': 2,
        'authors': ['Author A', 'Author B'],
        'total_copies': 10,
        'available_copies': 7,
        'is_featured': true,
        'location': 'Reference Section',
        'dewey_decimal': '987.65 ROU',
        'created_at': '2023-06-01T00:00:00.000Z',
        'copies': [
          {'id': 10, 'barcode': 'BC010', 'status': 'available'},
        ],
      };

      final book = BookModel.fromJson(json);
      final out = book.toJson();

      expect(out['id'], equals(10));
      expect(out['title'], equals('Roundtrip Full'));
      expect(out['isbn'], equals('0987654321'));
      expect(out['publication_year'], equals(2023));
      expect(out['is_featured'], isTrue);
      expect((out['copies'] as List).length, equals(1));
    });
  });
}
