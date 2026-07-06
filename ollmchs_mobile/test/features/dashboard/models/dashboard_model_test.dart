import 'package:flutter_test/flutter_test.dart';
import 'package:ollmchs_library/features/dashboard/models/dashboard_model.dart';

void main() {
  group('DashboardModel', () {
    test('fromJson with nested stats map', () {
      final json = {
        'stats': {
          'total_books': 100,
          'active_loans': 15,
          'overdue_loans': 3,
          'total_fines': 250.50,
          'pending_fines': 5,
          'available_books': 80,
          'digital_assets': 20,
          'active_reservations': 4,
          'unread_notifications': 7,
        },
        'unread_messages': 2,
      };

      final model = DashboardModel.fromJson(json);
      expect(model.totalBooks, equals(100));
      expect(model.activeLoans, equals(15));
      expect(model.overdueLoans, equals(3));
      expect(model.totalFines, equals(250.50));
      expect(model.pendingFines, equals(5));
      expect(model.availableBooks, equals(80));
      expect(model.digitalAssets, equals(20));
      expect(model.activeReservations, equals(4));
      expect(model.unreadNotifications, equals(7));
      expect(model.unreadMessages, equals(2));
    });

    test('fromJson with flat json (no stats wrapper)', () {
      final json = {
        'total_books': 50,
        'active_loans': 10,
        'overdue_loans': 1,
        'total_fines': 75.0,
        'pending_fines': 2,
        'available_books': 40,
        'digital_assets': 5,
        'active_reservations': 0,
        'unread_notifications': 3,
        'unread_messages': 0,
      };

      final model = DashboardModel.fromJson(json);
      expect(model.totalBooks, equals(50));
      expect(model.activeLoans, equals(10));
      expect(model.overdueLoans, equals(1));
      expect(model.totalFines, equals(75.0));
    });

    test('fromJson handles alternate field names', () {
      final json = {
        'stats': {
          'active_borrows': 8,
          'overdue_borrows': 2,
          'pending_fines_total': 100.0,
          'pending_reservations': 3,
        },
      };

      final model = DashboardModel.fromJson(json);
      expect(model.activeLoans, equals(8));
      expect(model.overdueLoans, equals(2));
      expect(model.totalFines, equals(100.0));
      expect(model.activeReservations, equals(3));
    });

    test('fromJson with empty json uses defaults', () {
      final model = DashboardModel.fromJson({});
      expect(model.totalBooks, equals(0));
      expect(model.activeLoans, equals(0));
      expect(model.overdueLoans, equals(0));
      expect(model.totalFines, equals(0));
      expect(model.pendingFines, equals(0));
      expect(model.availableBooks, equals(0));
      expect(model.digitalAssets, equals(0));
      expect(model.activeReservations, equals(0));
      expect(model.unreadNotifications, equals(0));
      expect(model.unreadMessages, equals(0));
      expect(model.recentLoans, isEmpty);
      expect(model.dueSoonBooks, isEmpty);
      expect(model.featuredBooks, isEmpty);
    });

    test('fromJson parses recent loans, due soon, and featured books', () {
      final json = {
        'stats': {
          'total_books': 100,
        },
        'recent_loans': [
          {
            'id': 1,
            'book': {'title': 'Loan Book 1', 'cover_image': 'http://cover.jpg'},
            'due_at': '2024-07-15T00:00:00Z',
            'status': 'active',
          },
        ],
        'due_soon': [
          {
            'id': 2,
            'title': 'Due Soon Book',
            'description': 'Due in 2 days',
            'cover_image': 'http://due.jpg',
          },
        ],
        'featured_books': [
          {
            'id': 3,
            'title': 'Featured Book',
            'description': 'A featured book',
            'thumbnail_url': 'http://thumb.jpg',
          },
        ],
      };

      final model = DashboardModel.fromJson(json);
      expect(model.recentLoans.length, equals(1));
      expect(model.recentLoans[0].bookTitle, equals('Loan Book 1'));
      expect(model.recentLoans[0].bookCover, equals('http://cover.jpg'));
      expect(model.recentLoans[0].status, equals('active'));

      expect(model.dueSoonBooks.length, equals(1));
      expect(model.dueSoonBooks[0].title, equals('Due Soon Book'));
      expect(model.dueSoonBooks[0].description, equals('Due in 2 days'));
      expect(model.dueSoonBooks[0].imageUrl, equals('http://due.jpg'));

      expect(model.featuredBooks.length, equals(1));
      expect(model.featuredBooks[0].title, equals('Featured Book'));
      expect(model.featuredBooks[0].imageUrl, equals('http://thumb.jpg'));
    });
  });

  group('DashboardModel - constructor defaults', () {
    test('creates with default values', () {
      final model = DashboardModel();
      expect(model.totalBooks, equals(0));
      expect(model.activeLoans, equals(0));
      expect(model.recentLoans, isEmpty);
      expect(model.dueSoonBooks, isEmpty);
      expect(model.featuredBooks, isEmpty);
    });

    test('creates with provided values', () {
      final model = DashboardModel(totalBooks: 42, activeLoans: 7);
      expect(model.totalBooks, equals(42));
      expect(model.activeLoans, equals(7));
    });
  });

  group('DashboardLoan', () {
    test('fromJson with nested book map', () {
      final json = {
        'id': 10,
        'book': {
          'title': 'Nested Book',
          'cover_image': 'http://nested.jpg',
        },
        'due_at': '2024-08-01T00:00:00Z',
        'status': 'active',
      };

      final loan = DashboardLoan.fromJson(json);
      expect(loan.id, equals(10));
      expect(loan.bookTitle, equals('Nested Book'));
      expect(loan.bookCover, equals('http://nested.jpg'));
      expect(loan.dueAt.year, equals(2024));
      expect(loan.status, equals('active'));
    });

    test('fromJson with nested book_copy->book map', () {
      final json = {
        'id': 11,
        'book_copy': {
          'book': {
            'title': 'Copy Book',
            'cover_image': 'http://copy.jpg',
          },
        },
        'due_at': '2024-09-01T00:00:00Z',
        'status': 'overdue',
      };

      final loan = DashboardLoan.fromJson(json);
      expect(loan.id, equals(11));
      expect(loan.bookTitle, equals('Copy Book'));
      expect(loan.bookCover, equals('http://copy.jpg'));
      expect(loan.status, equals('overdue'));
    });

    test('fromJson with no book data uses empty string', () {
      final json = {
        'id': 12,
        'due_at': '2024-10-01T00:00:00Z',
      };

      final loan = DashboardLoan.fromJson(json);
      expect(loan.bookTitle, equals(''));
      expect(loan.bookCover, isNull);
    });

    test('fromJson defaults status to active', () {
      final json = {
        'id': 13,
        'due_at': '2024-11-01T00:00:00Z',
      };

      final loan = DashboardLoan.fromJson(json);
      expect(loan.status, equals('active'));
    });
  });

  group('DashboardItem', () {
    test('fromJson with cover_image', () {
      final json = {
        'id': 20,
        'title': 'Item with Cover',
        'description': 'Has cover image',
        'cover_image': 'http://cover.jpg',
      };

      final item = DashboardItem.fromJson(json);
      expect(item.id, equals(20));
      expect(item.title, equals('Item with Cover'));
      expect(item.description, equals('Has cover image'));
      expect(item.imageUrl, equals('http://cover.jpg'));
    });

    test('fromJson with thumbnail_url fallback', () {
      final json = {
        'id': 21,
        'title': 'Item with Thumbnail',
        'thumbnail_url': 'http://thumb.jpg',
      };

      final item = DashboardItem.fromJson(json);
      expect(item.imageUrl, equals('http://thumb.jpg'));
    });

    test('fromJson with no image', () {
      final json = {
        'id': 22,
        'title': 'No Image Item',
      };

      final item = DashboardItem.fromJson(json);
      expect(item.imageUrl, isNull);
    });

    test('fromJson defaults title to empty string', () {
      final json = {'id': 23};
      final item = DashboardItem.fromJson(json);
      expect(item.title, equals(''));
    });
  });
}
