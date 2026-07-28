import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:bloc_test/bloc_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:dio/dio.dart';
import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/features/messaging/bloc/messaging_bloc.dart';
import 'package:ollmchs_library/features/messaging/models/message_model.dart';
import 'package:ollmchs_library/features/messaging/screens/inbox_screen.dart';
import 'package:ollmchs_library/features/messaging/screens/compose_message_screen.dart';
import 'package:ollmchs_library/features/messaging/screens/message_detail_screen.dart';

class MockApiClient extends Mock implements ApiClient {}

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
        isA<MessagingLoaded>(),
      ],
    );

    blocTest<MessagingBloc, MessagingState>(
      'LoadArchivedMessages adds archived messages to state',
      build: () => MessagingBloc(api: mockApi),
      seed: () => const MessagingLoaded(),
      act: (bloc) => bloc.add(const LoadArchivedMessages()),
      expect: () => [
        isA<MessagingLoaded>(),
      ],
    );

    blocTest<MessagingBloc, MessagingState>(
      'SendMessage emits success message',
      build: () => MessagingBloc(api: mockApi),
      seed: () => const MessagingLoaded(),
      act: (bloc) => bloc.add(const SendMessage(
        recipientIds: [1],
        subject: 'Test',
        body: 'Hello',
      )),
      expect: () => [
        isA<MessagingLoaded>().having((s) => s.message, 'message', 'Message sent'),
      ],
    );

    blocTest<MessagingBloc, MessagingState>(
      'SearchMessages updates searchResults in state',
      build: () => MessagingBloc(api: mockApi),
      seed: () => const MessagingLoaded(),
      act: (bloc) => bloc.add(const SearchMessages(query: 'test')),
      expect: () => [
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
        'sender_name': 'John',
        'sender_photo': 'http://example.com/photo.jpg',
        'recipient_names': ['Jane', 'Bob'],
        'is_read': true,
        'sent_at': '2026-07-25T10:00:00.000Z',
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
        'sent_at': '2026-07-25T10:00:00.000Z',
      };
      final msg = MessageModel.fromJson(json);
      expect(msg.id, 2);
      expect(msg.senderName, isNull);
      expect(msg.isRead, false);
      expect(msg.priority, 'normal');
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
      await tester.pumpWidget(MaterialApp(
        home: BlocProvider<MessagingBloc>(
          create: (_) => MessagingBloc(api: mockApi)..emit(MessagingLoaded(
            inbox: [
              MessageModel(id: 1, subject: 'Test Message', body: 'Hello', senderName: 'John', isRead: false, sentAt: DateTime.now(), priority: 'normal'),
            ],
          )),
          child: const InboxScreen(),
        ),
      ));
      await tester.pumpAndSettle();
      expect(find.text('Test Message'), findsOneWidget);
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
      await tester.pumpWidget(MaterialApp(
        home: BlocProvider<MessagingBloc>(
          create: (_) => MessagingBloc(api: mockApi),
          child: const ComposeMessageScreen(),
        ),
      ));
      await tester.pump();
      expect(find.text('Compose'), findsOneWidget);
      expect(find.byType(TextField), findsWidgets);
    });
  });
}
