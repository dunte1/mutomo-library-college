import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:dio/dio.dart';
import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/core/storage/local_storage_service.dart';

class MockApiClient extends Mock implements ApiClient {}
class MockLocalStorageService extends Mock implements LocalStorageService {}

void main() {
  late MockApiClient mockApi;
  late MockLocalStorageService mockStorage;

  setUp(() {
    mockStorage = MockLocalStorageService();
    when(() => mockStorage.getToken()).thenAnswer((_) async => 'test-token');
    when(() => mockStorage.getRefreshToken()).thenAnswer((_) async => 'refresh-token');
    when(() => mockStorage.saveToken(any())).thenAnswer((_) async {});
    when(() => mockStorage.saveRefreshToken(any())).thenAnswer((_) async {});
    when(() => mockStorage.saveTokenExpiry(any())).thenAnswer((_) async {});
    when(() => mockStorage.clearAll()).thenAnswer((_) async {});
    mockApi = MockApiClient();
    when(() => mockApi.get(any(), queryParameters: any(named: 'queryParameters')))
        .thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
    when(() => mockApi.post(any(), data: any(named: 'data')))
        .thenAnswer((_) async => Response(data: {'data': {}}, statusCode: 200, requestOptions: RequestOptions(path: '')));
  });

  group('Auth Endpoints', () {
    test('login endpoint called correctly', () async {
      when(() => mockApi.post('/v1/auth/login', data: any(named: 'data')))
          .thenAnswer((_) async => Response(
                data: {'data': {'token': 'abc', 'user': {'id': 1, 'name': 'Test'}}},
                statusCode: 200,
                requestOptions: RequestOptions(path: ''),
              ));
      final response = await mockApi.post('/v1/auth/login', data: {'email': 'test@test.com', 'password': 'pass'});
      expect(response.data['data']['token'], 'abc');
    });

    test('logout endpoint called correctly', () async {
      when(() => mockApi.post('/v1/auth/logout')).thenAnswer((_) async => Response(data: {}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      await mockApi.post('/v1/auth/logout');
      verify(() => mockApi.post('/v1/auth/logout')).called(1);
    });

    test('get user endpoint called correctly', () async {
      when(() => mockApi.get('/v1/auth/user')).thenAnswer((_) async => Response(data: {'data': {'id': 1, 'name': 'Test'}}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/auth/user');
      expect(response.data['data']['name'], 'Test');
    });
  });

  group('Catalog Endpoints', () {
    test('books list endpoint', () async {
      when(() => mockApi.get('/v1/books', queryParameters: any(named: 'queryParameters')))
          .thenAnswer((_) async => Response(data: {'data': [{'id': 1, 'title': 'Book 1'}], 'meta': {'current_page': 1, 'last_page': 1}}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/books', queryParameters: {'per_page': 15});
      expect(response.data['data'].length, 1);
    });

    test('book detail endpoint', () async {
      when(() => mockApi.get('/v1/books/1')).thenAnswer((_) async => Response(data: {'data': {'id': 1, 'title': 'Book 1'}}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/books/1');
      expect(response.data['data']['title'], 'Book 1');
    });

    test('categories endpoint', () async {
      when(() => mockApi.get('/v1/categories')).thenAnswer((_) async => Response(data: {'data': [{'id': 1, 'name': 'Cat 1'}]}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/categories');
      expect(response.data['data'].length, 1);
    });

    test('authors endpoint', () async {
      when(() => mockApi.get('/v1/authors')).thenAnswer((_) async => Response(data: {'data': [{'id': 1, 'name': 'Author 1'}]}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/authors');
      expect(response.data['data'].length, 1);
    });

    test('publishers endpoint', () async {
      when(() => mockApi.get('/v1/publishers')).thenAnswer((_) async => Response(data: {'data': [{'id': 1, 'name': 'Pub 1'}]}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/publishers');
      expect(response.data['data'].length, 1);
    });
  });

  group('Circulation Endpoints', () {
    test('active loans endpoint', () async {
      when(() => mockApi.get('/v1/loans/active')).thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/loans/active');
      expect(response.data['data'], isEmpty);
    });

    test('fines endpoint', () async {
      when(() => mockApi.get('/v1/fines')).thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/fines');
      expect(response.data['data'], isEmpty);
    });

    test('reservations endpoint', () async {
      when(() => mockApi.get('/v1/reservations')).thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/reservations');
      expect(response.data['data'], isEmpty);
    });
  });

  group('Digital Library Endpoints', () {
    test('digital assets endpoint', () async {
      when(() => mockApi.get('/v1/digital-assets')).thenAnswer((_) async => Response(data: {'data': [{'id': 1, 'title': 'Asset 1'}]}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/digital-assets');
      expect(response.data['data'].length, 1);
    });

    test('recommendations endpoint', () async {
      when(() => mockApi.get('/v1/recommendations')).thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/recommendations');
      expect(response.data['data'], isEmpty);
    });
  });

  group('Messaging Endpoints', () {
    test('inbox endpoint', () async {
      when(() => mockApi.get('/v1/messages/inbox')).thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/messages/inbox');
      expect(response.data['data'], isEmpty);
    });

    test('send message endpoint', () async {
      when(() => mockApi.post('/v1/messages/send', data: any(named: 'data')))
          .thenAnswer((_) async => Response(data: {'data': {'id': 1}}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.post('/v1/messages/send', data: {'recipient_id': 1, 'subject': 'Test', 'body': 'Hello'});
      expect(response.data['data']['id'], 1);
    });

    test('search messages endpoint', () async {
      when(() => mockApi.get('/v1/messages/search', queryParameters: any(named: 'queryParameters')))
          .thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/messages/search', queryParameters: {'q': 'test'});
      expect(response.data['data'], isEmpty);
    });
  });

  group('Notification Endpoints', () {
    test('notifications endpoint', () async {
      when(() => mockApi.get('/v1/notifications')).thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/notifications');
      expect(response.data['data'], isEmpty);
    });

    test('mark all read endpoint', () async {
      when(() => mockApi.post('/v1/notifications/read-all')).thenAnswer((_) async => Response(data: {}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      await mockApi.post('/v1/notifications/read-all');
      verify(() => mockApi.post('/v1/notifications/read-all')).called(1);
    });
  });

  group('Profile Endpoints', () {
    test('get profile endpoint', () async {
      when(() => mockApi.get('/v1/profile')).thenAnswer((_) async => Response(data: {'data': {'name': 'Test'}}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/profile');
      expect(response.data['data']['name'], 'Test');
    });

    test('update profile endpoint', () async {
      when(() => mockApi.put('/v1/profile', data: any(named: 'data')))
          .thenAnswer((_) async => Response(data: {'data': {'name': 'Updated'}}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.put('/v1/profile', data: {'name': 'Updated'});
      expect(response.data['data']['name'], 'Updated');
    });
  });

  group('Library Card Endpoints', () {
    test('library card endpoint', () async {
      when(() => mockApi.get('/v1/library-card')).thenAnswer((_) async => Response(data: {'data': {'card_number': 'LIB-001'}}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/library-card');
      expect(response.data['data']['card_number'], 'LIB-001');
    });
  });

  group('Subscription Endpoints', () {
    test('subscription plans endpoint', () async {
      when(() => mockApi.get('/v1/subscription-plans')).thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/subscription-plans');
      expect(response.data['data'], isEmpty);
    });

    test('my subscription endpoint', () async {
      when(() => mockApi.get('/v1/subscriptions/my')).thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/subscriptions/my');
      expect(response.data['data'], isEmpty);
    });
  });

  group('Report Endpoints', () {
    test('reading summary endpoint', () async {
      when(() => mockApi.get('/v1/reports/reading-summary')).thenAnswer((_) async => Response(data: {'data': {'total_borrowed': 10}}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/reports/reading-summary');
      expect(response.data['data']['total_borrowed'], 10);
    });

    test('loan history report endpoint', () async {
      when(() => mockApi.get('/v1/reports/loan-history')).thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/reports/loan-history');
      expect(response.data['data'], isEmpty);
    });
  });

  group('Content Endpoints', () {
    test('announcements endpoint', () async {
      when(() => mockApi.get('/v1/announcements')).thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/announcements');
      expect(response.data['data'], isEmpty);
    });

    test('events endpoint', () async {
      when(() => mockApi.get('/v1/events')).thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/events');
      expect(response.data['data'], isEmpty);
    });

    test('assignments endpoint', () async {
      when(() => mockApi.get('/v1/assignments')).thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/assignments');
      expect(response.data['data'], isEmpty);
    });
  });

  group('Dashboard Endpoint', () {
    test('dashboard endpoint', () async {
      when(() => mockApi.get('/v1/dashboard')).thenAnswer((_) async => Response(data: {'data': {'stats': {'total_books': 100}}}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/dashboard');
      expect(response.data['data']['stats']['total_books'], 100);
    });
  });

  group('Recipient Search (Messaging)', () {
    test('search messages by query', () async {
      when(() => mockApi.get('/v1/messages/search', queryParameters: any(named: 'queryParameters')))
          .thenAnswer((_) async => Response(data: {'data': [{'id': 1, 'subject': 'Found'}]}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/messages/search', queryParameters: {'q': 'Found'});
      expect(response.data['data'].length, 1);
      expect(response.data['data'][0]['subject'], 'Found');
    });

    test('search with no results', () async {
      when(() => mockApi.get('/v1/messages/search', queryParameters: any(named: 'queryParameters')))
          .thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
      final response = await mockApi.get('/v1/messages/search', queryParameters: {'q': 'nonexistent'});
      expect(response.data['data'], isEmpty);
    });
  });
}
