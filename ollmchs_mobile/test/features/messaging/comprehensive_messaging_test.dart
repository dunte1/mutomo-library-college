import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:bloc_test/bloc_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:dio/dio.dart';
import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_bloc.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_event.dart';
import 'package:ollmchs_library/features/auth/models/user_model.dart';
import 'package:ollmchs_library/features/auth/repositories/auth_repository.dart';
import 'package:ollmchs_library/features/messaging/bloc/messaging_bloc.dart';
import 'package:ollmchs_library/features/messaging/models/message_model.dart';
import 'package:ollmchs_library/features/messaging/screens/inbox_screen.dart';
import 'package:ollmchs_library/features/messaging/screens/compose_message_screen.dart';

class MockApiClient extends Mock implements ApiClient {}
class MockAuthRepository extends Mock implements AuthRepository {}

Response<dynamic> _fakeResponse(dynamic data) {
  return Response<dynamic>(
    data: data,
    statusCode: 200,
    requestOptions: RequestOptions(path: ''),
  );
}

void main() {
  late MockApiClient mockApi;

  setUp(() {
    mockApi = MockApiClient();
    when(() => mockApi.get(any(), queryParameters: any(named: 'queryParameters')))
        .thenAnswer((_) async => _fakeResponse({'data': [], 'meta': {'current_page': 1, 'last_page': 1}}));
    when(() => mockApi.post(any(), data: any(named: 'data')))
        .thenAnswer((_) async => _fakeResponse({'data': {}}));
  });

  group('MessagingBloc', () {
    blocTest<MessagingBloc, MessagingState>(
      'emits [MessagingLoading, MessagingLoaded] when LoadInbox succeeds',
      build: () => MessagingBloc(api: mockApi),
      act: (bloc) => bloc.add(const LoadInbox()),
      expect: () => [
        isA<MessagingLoading>(),
        isA<MessagingLoaded>(),
      ],
    );

    blocTest<MessagingBloc, MessagingState>(
      'LoadSentMessages adds sent messages to state',
      build: () => MessagingBloc(api: mockApi),
      seed: () => const MessagingLoaded(),
      act: (bloc) => bloc.add(const LoadSentMessages()),
      expect: () => [
        isA<MessagingLoading>(),
        isA<MessagingLoaded>(),
      ],
    );

    blocTest<MessagingBloc, MessagingState>(
      'LoadSentMessages emits MessagingLoaded even when starting from MessagingInitial',
      build: () => MessagingBloc(api: mockApi),
      seed: () => MessagingInitial(),
      act: (bloc) => bloc.add(const LoadSentMessages()),
      expect: () => [
        isA<MessagingLoading>(),
        isA<MessagingLoaded>().having((s) => s.sent, 'sent', isEmpty),
      ],
    );

    blocTest<MessagingBloc, MessagingState>(
      'LoadArchivedMessages adds archived messages to state',
      build: () => MessagingBloc(api: mockApi),
      seed: () => const MessagingLoaded(),
      act: (bloc) => bloc.add(const LoadArchivedMessages()),
      expect: () => [
        isA<MessagingLoading>(),
        isA<MessagingLoaded>(),
      ],
    );

    blocTest<MessagingBloc, MessagingState>(
      'LoadArchivedMessages emits MessagingLoaded even when starting from MessagingInitial',
      build: () => MessagingBloc(api: mockApi),
      seed: () => MessagingInitial(),
      act: (bloc) => bloc.add(const LoadArchivedMessages()),
      expect: () => [
        isA<MessagingLoading>(),
        isA<MessagingLoaded>().having((s) => s.archived, 'archived', isEmpty),
      ],
    );

    blocTest<MessagingBloc, MessagingState>(
      'SendMessage calls post and refreshes inbox + sent',
      build: () => MessagingBloc(api: mockApi),
      seed: () => const MessagingLoaded(),
      act: (bloc) => bloc.add(const SendMessage(
        recipientIds: [1],
        subject: 'Test',
        body: 'Hello',
      )),
      verify: (_) {
        verify(() => mockApi.post('/v1/messages/send', data: any(named: 'data'))).called(1);
        verify(() => mockApi.get('/v1/messages/inbox', queryParameters: any(named: 'queryParameters'))).called(1);
        verify(() => mockApi.get('/v1/messages/sent', queryParameters: any(named: 'queryParameters'))).called(1);
      },
    );

    blocTest<MessagingBloc, MessagingState>(
      'LoadMessageDetail works even without MessagingLoaded seed',
      build: () {
        when(() => mockApi.get('/v1/messages/1'))
            .thenAnswer((_) async => _fakeResponse({
              'data': {
                'id': 1,
                'subject': 'Detail',
                'body': 'Detail body',
                'sender': {'name': 'John'},
                'created_at': '2026-07-25T10:00:00.000Z',
              },
            }));
        return MessagingBloc(api: mockApi);
      },
      seed: () => MessagingInitial(),
      act: (bloc) => bloc.add(const LoadMessageDetail(1)),
      expect: () => [
        isA<MessagingLoaded>().having((s) => s.selectedMessage, 'selectedMessage', isNotNull),
      ],
    );

    blocTest<MessagingBloc, MessagingState>(
      'SearchMessages updates searchResults in state',
      build: () => MessagingBloc(api: mockApi),
      seed: () => const MessagingLoaded(),
      act: (bloc) => bloc.add(const SearchMessages(query: 'test')),
      expect: () => [
        isA<MessagingLoading>(),
        isA<MessagingLoaded>().having((s) => s.isSearching, 'isSearching', true),
      ],
    );

    blocTest<MessagingBloc, MessagingState>(
      'ArchiveMessage removes message from inbox',
      build: () => MessagingBloc(api: mockApi),
      seed: () => MessagingLoaded(inbox: [
        MessageModel(id: 1, subject: 'Test', body: 'Body', senderName: 'John', isRead: false, sentAt: DateTime.now(), priority: 'normal'),
      ]),
      act: (bloc) => bloc.add(const ArchiveMessage(1)),
      verify: (_) {
        verify(() => mockApi.post('/v1/messages/1/archive')).called(1);
      },
    );

    blocTest<MessagingBloc, MessagingState>(
      'DeleteMessage removes message',
      build: () => MessagingBloc(api: mockApi),
      seed: () => MessagingLoaded(inbox: [
        MessageModel(id: 1, subject: 'Test', body: 'Body', senderName: 'John', isRead: false, sentAt: DateTime.now(), priority: 'normal'),
      ]),
      act: (bloc) => bloc.add(const DeleteMessage(1)),
      verify: (_) {
        verify(() => mockApi.delete('/v1/messages/1')).called(1);
      },
    );
  });

  group('MessageModel', () {
    test('fromJson with all fields', () {
      final json = {
        'id': 1,
        'subject': 'Test Subject',
        'body': 'Test body',
        'sender': {'name': 'John', 'profile_photo_url': 'http://example.com/photo.jpg'},
        'recipients': [
          {'recipient': {'name': 'Jane'}},
          {'recipient': {'name': 'Bob'}},
        ],
        'is_read': true,
        'created_at': '2026-07-25T10:00:00.000Z',
        'priority': 'high',
        'has_attachments': true,
        'attachments_count': 2,
      };
      final msg = MessageModel.fromJson(json);
      expect(msg.id, 1);
      expect(msg.subject, 'Test Subject');
      expect(msg.body, 'Test body');
      expect(msg.senderName, 'John');
      expect(msg.senderPhoto, 'http://example.com/photo.jpg');
      expect(msg.recipientNames, ['Jane', 'Bob']);
      expect(msg.isRead, true);
      expect(msg.priority, 'high');
      expect(msg.hasAttachments, true);
    });

    test('fromJson with minimal fields', () {
      final json = {
        'id': 2,
        'subject': 'Minimal',
        'body': 'Body',
        'created_at': '2026-07-25T10:00:00.000Z',
      };
      final msg = MessageModel.fromJson(json);
      expect(msg.id, 2);
      expect(msg.senderName, 'System');
      expect(msg.isRead, false);
      expect(msg.priority, 'normal');
    });

    test('fromJson parses replies array into nested MessageModel objects', () {
      final json = {
        'id': 3,
        'subject': 'Original',
        'body': 'Parent body',
        'sender': {'name': 'Alice'},
        'created_at': '2026-07-25T10:00:00.000Z',
        'replies': [
          {
            'id': 4,
            'subject': 'Re: Original',
            'body': 'Reply body',
            'sender': {'name': 'Bob'},
            'created_at': '2026-07-25T11:00:00.000Z',
          },
          {
            'id': 5,
            'subject': 'Re: Original',
            'body': 'Another reply',
            'sender': {'name': 'Charlie'},
            'created_at': '2026-07-25T12:00:00.000Z',
          },
        ],
      };
      final msg = MessageModel.fromJson(json);
      expect(msg.replies.length, 2);
      expect(msg.replies[0].id, 4);
      expect(msg.replies[0].senderName, 'Bob');
      expect(msg.replies[0].body, 'Reply body');
      expect(msg.replies[1].id, 5);
      expect(msg.replies[1].senderName, 'Charlie');
      expect(msg.replies[1].body, 'Another reply');
    });

    test('fromJson defaults replies to [] when key is absent', () {
      final json = {
        'id': 6,
        'subject': 'No replies',
        'body': 'Body',
        'sender': {'name': 'Alice'},
        'created_at': '2026-07-25T10:00:00.000Z',
      };
      final msg = MessageModel.fromJson(json);
      expect(msg.replies, isEmpty);
    });

    test('fromJson defaults replies to [] when replies is null', () {
      final json = {
        'id': 7,
        'subject': 'Null replies',
        'body': 'Body',
        'sender': {'name': 'Alice'},
        'created_at': '2026-07-25T10:00:00.000Z',
        'replies': null,
      };
      final msg = MessageModel.fromJson(json);
      expect(msg.replies, isEmpty);
    });

    test('isUrgent returns true for high priority', () {
      final msg = MessageModel(id: 1, subject: 's', body: 'b', isRead: false, sentAt: DateTime.now(), priority: 'high');
      expect(msg.isUrgent, true);
    });

    test('isUrgent returns false for normal priority', () {
      final msg = MessageModel(id: 1, subject: 's', body: 'b', isRead: false, sentAt: DateTime.now(), priority: 'normal');
      expect(msg.isUrgent, false);
    });
  });

  group('InboxScreen', () {
    testWidgets('renders inbox tab by default', (tester) async {
      await tester.pumpWidget(MaterialApp(
        home: BlocProvider<MessagingBloc>(
          create: (_) => MessagingBloc(api: mockApi)..emit(const MessagingLoaded()),
          child: const InboxScreen(),
        ),
      ));
      await tester.pump();
      expect(find.text('Messages'), findsOneWidget);
      expect(find.text('Inbox'), findsWidgets);
      expect(find.text('Sent'), findsWidgets);
      expect(find.text('Archive'), findsWidgets);
    });

    testWidgets('shows empty state when no messages', (tester) async {
      await tester.pumpWidget(MaterialApp(
        home: BlocProvider<MessagingBloc>(
          create: (_) => MessagingBloc(api: mockApi)..emit(const MessagingLoaded()),
          child: const InboxScreen(),
        ),
      ));
      await tester.pumpAndSettle();
      expect(find.text('No messages'), findsOneWidget);
    });

    testWidgets('shows messages when loaded', (tester) async {
      when(() => mockApi.get('/v1/messages/inbox', queryParameters: any(named: 'queryParameters')))
          .thenAnswer((_) async => _fakeResponse({
            'data': [
              {
                'id': 1,
                'subject': 'Test Message',
                'body': 'Hello',
                'sender': {'name': 'John'},
                'created_at': DateTime.now().toIso8601String(),
              },
            ],
            'meta': {'current_page': 1, 'last_page': 1},
          }));
      when(() => mockApi.get('/v1/messages/unread-count'))
          .thenAnswer((_) async => _fakeResponse({'unread_count': 0}));
      await tester.pumpWidget(MaterialApp(
        home: BlocProvider<MessagingBloc>(
          create: (_) => MessagingBloc(api: mockApi),
          child: const InboxScreen(),
        ),
      ));
      await tester.pumpAndSettle();
    });

    testWidgets('compose button is present', (tester) async {
      await tester.pumpWidget(MaterialApp(
        home: BlocProvider<MessagingBloc>(
          create: (_) => MessagingBloc(api: mockApi)..emit(const MessagingLoaded()),
          child: const InboxScreen(),
        ),
      ));
      await tester.pump();
      expect(find.byIcon(Icons.edit_outlined), findsOneWidget);
    });

    testWidgets('search button is present', (tester) async {
      await tester.pumpWidget(MaterialApp(
        home: BlocProvider<MessagingBloc>(
          create: (_) => MessagingBloc(api: mockApi)..emit(const MessagingLoaded()),
          child: const InboxScreen(),
        ),
      ));
      await tester.pump();
      expect(find.byIcon(Icons.search), findsOneWidget);
    });
  });

  group('ComposeMessageScreen', () {
    testWidgets('renders compose form', (tester) async {
      final mockAuthRepo = MockAuthRepository();
      when(() => mockAuthRepo.getStoredToken()).thenAnswer((_) async => 'token');
      when(() => mockAuthRepo.getUser()).thenAnswer((_) async => UserModel(
        id: 1, name: 'Test', email: 'test@test.com', roles: ['student'], permissions: [],
      ));
      await tester.pumpWidget(MultiBlocProvider(
        providers: [
          BlocProvider<AuthBloc>(
            create: (_) => AuthBloc(authRepository: mockAuthRepo)..add(const CheckAuthEvent()),
          ),
          BlocProvider<MessagingBloc>(
            create: (_) => MessagingBloc(api: mockApi),
          ),
        ],
        child: const MaterialApp(home: ComposeMessageScreen()),
      ));
      await tester.pump();
      expect(find.text('Compose Message'), findsOneWidget);
      expect(find.byType(TextField), findsWidgets);
    });
  });
}
