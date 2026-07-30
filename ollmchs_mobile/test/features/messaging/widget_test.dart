import 'package:bloc_test/bloc_test.dart';
import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/features/messaging/bloc/messaging_bloc.dart';
import 'package:ollmchs_library/features/messaging/models/message_model.dart';

class MockApiClient extends Mock implements ApiClient {}

Response<dynamic> _fakeResponse(Map<String, dynamic> data) {
  return Response<dynamic>(
    data: data,
    statusCode: 200,
    requestOptions: RequestOptions(path: ''),
  );
}

void main() {
  group('MessageModel', () {
    test('fromJson parses correctly', () {
      final json = {
        'id': 1,
        'subject': 'Test',
        'body': 'Hello',
        'sender': {'name': 'John', 'profile_photo_url': null},
        'created_at': '2026-07-09T12:00:00.000000Z',
        'is_read': false,
        'has_attachments': false,
        'priority': 'normal',
        'replies_count': 0,
      };
      final msg = MessageModel.fromJson(json);
      expect(msg.id, 1);
      expect(msg.subject, 'Test');
      expect(msg.body, 'Hello');
      expect(msg.senderName, 'John');
      expect(msg.isRead, false);
      expect(msg.isUrgent, false);
    });

    test('isUrgent true for high priority', () {
      final msg = MessageModel.fromJson({
        'id': 2,
        'subject': 'High',
        'body': 'High message',
        'sender': null,
        'created_at': '2026-07-09T12:00:00.000000Z',
        'priority': 'high',
      });
      expect(msg.isUrgent, true);
    });

    test('isUrgent true for urgent priority', () {
      final msg = MessageModel.fromJson({
        'id': 3,
        'subject': 'Urgent',
        'body': 'Urgent message',
        'sender': null,
        'created_at': '2026-07-09T12:00:00.000000Z',
        'priority': 'urgent',
      });
      expect(msg.isUrgent, true);
    });

    test('parses recipient names from recipients list', () {
      final msg = MessageModel.fromJson({
        'id': 4,
        'subject': 'Group',
        'body': 'Group message',
        'sender': null,
        'created_at': '2026-07-09T12:00:00.000000Z',
        'recipients': [
          {'recipient': {'name': 'Alice'}},
          {'recipient': {'name': 'Bob'}},
        ],
      });
      expect(msg.recipientNames, contains('Alice'));
      expect(msg.recipientNames, contains('Bob'));
    });

    test('hasAttachments from bool field', () {
      final msg = MessageModel.fromJson({
        'id': 5,
        'subject': 'With Attach',
        'body': 'Body',
        'sender': null,
        'created_at': '2026-07-09T12:00:00.000000Z',
        'has_attachments': true,
      });
      expect(msg.hasAttachments, true);
    });

    test('hasAttachments from attachments list', () {
      final msg = MessageModel.fromJson({
        'id': 6,
        'subject': 'With Attach',
        'body': 'Body',
        'sender': null,
        'created_at': '2026-07-09T12:00:00.000000Z',
        'attachments': [
          {'id': 1, 'file_name': 'doc.pdf'}
        ],
      });
      expect(msg.hasAttachments, true);
    });
  });

  group('MessagingBloc', () {
    late ApiClient apiClient;
    late MessagingBloc bloc;

    setUp(() {
      apiClient = MockApiClient();
      bloc = MessagingBloc(api: apiClient);
    });

    tearDown(() {
      bloc.close();
    });

    test('initial state is MessagingInitial', () {
      expect(bloc.state, isA<MessagingInitial>());
    });

    blocTest<MessagingBloc, MessagingState>(
      'emits loading then error on LoadInbox when API fails',
      build: () {
        when(() => apiClient.get(any(),
                queryParameters: any(named: 'queryParameters')))
            .thenThrow(Exception('API error'));
        return bloc;
      },
      act: (bloc) => bloc.add(const LoadInbox()),
      expect: () => [isA<MessagingLoading>(), isA<MessagingError>()],
    );

    blocTest<MessagingBloc, MessagingState>(
      'emits fetch-unread-count result',
      build: () {
        when(() => apiClient.get(
              '/v1/messages/inbox',
              queryParameters: any(named: 'queryParameters'),
            )).thenAnswer((_) async =>
            _fakeResponse({'data': [], 'meta': {'current_page': 1, 'last_page': 1}}));
        when(() => apiClient.get('/v1/messages/unread-count'))
            .thenAnswer((_) async => _fakeResponse({'unread_count': 3}));
        return bloc;
      },
      act: (bloc) => bloc.add(const LoadInbox()),
      expect: () => [isA<MessagingLoading>(), isA<MessagingLoaded>()],
      verify: (_) {
        final state = bloc.state;
        if (state is MessagingLoaded) {
          expect(state.unreadCount, 3);
        }
      },
    );

    blocTest<MessagingBloc, MessagingState>(
      'LoadSentMessages from initial state emits loading then loaded',
      build: () {
        when(() => apiClient.get(
              '/v1/messages/sent',
              queryParameters: any(named: 'queryParameters'),
            )).thenAnswer((_) async =>
            _fakeResponse({'data': [], 'meta': {'current_page': 1, 'last_page': 1}}));
        return bloc;
      },
      act: (bloc) => bloc.add(const LoadSentMessages()),
      expect: () => [isA<MessagingLoading>(), isA<MessagingLoaded>()],
    );

    blocTest<MessagingBloc, MessagingState>(
      'LoadSentMessages when in MessagingLoaded state updates sent',
      build: () {
        when(() => apiClient.get(
              '/v1/messages/sent',
              queryParameters: any(named: 'queryParameters'),
            )).thenAnswer((_) async =>
            _fakeResponse({'data': [], 'meta': {'current_page': 1, 'last_page': 1}}));
        return bloc;
      },
      seed: () => const MessagingLoaded(),
      act: (bloc) => bloc.add(const LoadSentMessages()),
      expect: () => [isA<MessagingLoading>(), isA<MessagingLoaded>()],
    );

    blocTest<MessagingBloc, MessagingState>(
      'LoadArchivedMessages from initial state emits loading then loaded',
      build: () {
        when(() => apiClient.get(
              '/v1/messages/archived',
              queryParameters: any(named: 'queryParameters'),
            )).thenAnswer((_) async =>
            _fakeResponse({'data': [], 'meta': {'current_page': 1, 'last_page': 1}}));
        return bloc;
      },
      act: (bloc) => bloc.add(const LoadArchivedMessages()),
      expect: () => [isA<MessagingLoading>(), isA<MessagingLoaded>()],
    );

    blocTest<MessagingBloc, MessagingState>(
      'LoadArchivedMessages when in MessagingLoaded state updates archived',
      build: () {
        when(() => apiClient.get(
              '/v1/messages/archived',
              queryParameters: any(named: 'queryParameters'),
            )).thenAnswer((_) async =>
            _fakeResponse({'data': [], 'meta': {'current_page': 1, 'last_page': 1}}));
        return bloc;
      },
      seed: () => const MessagingLoaded(),
      act: (bloc) => bloc.add(const LoadArchivedMessages()),
      expect: () => [isA<MessagingLoading>(), isA<MessagingLoaded>()],
    );

    blocTest<MessagingBloc, MessagingState>(
      'FetchUnreadCount updates unread count in loaded state',
      build: () {
        when(() => apiClient.get('/v1/messages/unread-count'))
            .thenAnswer((_) async => _fakeResponse({'unread_count': 5}));
        return bloc;
      },
      seed: () => const MessagingLoaded(unreadCount: 0),
      act: (bloc) => bloc.add(const FetchUnreadCount()),
      verify: (_) {
        final state = bloc.state;
        if (state is MessagingLoaded) {
          expect(state.unreadCount, 5);
        }
      },
    );

    test('MessagingLoaded default state has empty lists', () {
      const state = MessagingLoaded();
      expect(state.inbox, isEmpty);
      expect(state.sent, isEmpty);
      expect(state.archived, isEmpty);
      expect(state.unreadCount, 0);
      expect(state.hasMoreInbox, isTrue);
      expect(state.hasMoreSent, isTrue);
      expect(state.hasMoreArchived, isTrue);
      expect(state.isSearching, isFalse);
    });

    test('MessagingLoaded with messages preserves them', () {
      final msg = MessageModel.fromJson({
        'id': 1,
        'subject': 'Test',
        'body': 'Body',
        'sender': null,
        'created_at': '2026-07-09T12:00:00.000000Z',
      });
      final state = MessagingLoaded(
        inbox: [msg],
        sent: [msg],
        unreadCount: 1,
        hasMoreInbox: false,
      );
      expect(state.inbox.length, 1);
      expect(state.sent.length, 1);
      expect(state.unreadCount, 1);
      expect(state.hasMoreInbox, isFalse);
    });
  });
}
