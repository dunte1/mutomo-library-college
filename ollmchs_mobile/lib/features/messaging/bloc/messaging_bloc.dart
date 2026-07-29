import 'package:dio/dio.dart';
import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/errors/error_mapper.dart';
import '../../../core/network/api_client.dart';
import '../../../core/utils/type_parsers.dart';
import '../models/message_model.dart';

// Events
abstract class MessagingEvent extends Equatable {
  const MessagingEvent();
  @override
  List<Object?> get props => [];
}

class LoadInbox extends MessagingEvent {
  final int page;
  const LoadInbox({this.page = 1});
  @override
  List<Object?> get props => [page];
}

class LoadSentMessages extends MessagingEvent {
  final int page;
  const LoadSentMessages({this.page = 1});
  @override
  List<Object?> get props => [page];
}

class LoadArchivedMessages extends MessagingEvent {
  final int page;
  const LoadArchivedMessages({this.page = 1});
  @override
  List<Object?> get props => [page];
}

class LoadMessageDetail extends MessagingEvent {
  final int messageId;
  const LoadMessageDetail(this.messageId);
  @override
  List<Object?> get props => [messageId];
}

class SendMessage extends MessagingEvent {
  final String subject;
  final String body;
  final List<int>? recipientIds;
  final String? departmentId;
  final String? priority;
  final String? type;
  final List<MultipartFile>? attachments;
  const SendMessage({
    required this.subject,
    required this.body,
    this.recipientIds,
    this.departmentId,
    this.priority,
    this.type,
    this.attachments,
  });
  @override
  List<Object?> get props => [
    subject,
    body,
    recipientIds ?? [],
    departmentId ?? '',
    priority ?? '',
    type ?? '',
  ];
}

class ReplyToMessage extends MessagingEvent {
  final int messageId;
  final String body;
  final bool replyAll;
  const ReplyToMessage({required this.messageId, required this.body, this.replyAll = false});
  @override
  List<Object?> get props => [messageId, body, replyAll];
}

class ForwardMessage extends MessagingEvent {
  final int messageId;
  final List<int> recipientIds;
  const ForwardMessage({required this.messageId, required this.recipientIds});
  @override
  List<Object?> get props => [messageId, ...recipientIds];
}

class SearchMessages extends MessagingEvent {
  final String query;
  final String scope;
  const SearchMessages({required this.query, this.scope = 'inbox'});
  @override
  List<Object?> get props => [query, scope];
}

class ArchiveMessage extends MessagingEvent {
  final int messageId;
  const ArchiveMessage(this.messageId);
  @override
  List<Object?> get props => [messageId];
}

class UnarchiveMessage extends MessagingEvent {
  final int messageId;
  const UnarchiveMessage(this.messageId);
  @override
  List<Object?> get props => [messageId];
}

class MarkAsRead extends MessagingEvent {
  final int messageId;
  const MarkAsRead(this.messageId);
  @override
  List<Object?> get props => [messageId];
}

class MarkAsUnread extends MessagingEvent {
  final int messageId;
  const MarkAsUnread(this.messageId);
  @override
  List<Object?> get props => [messageId];
}

class DeleteMessage extends MessagingEvent {
  final int messageId;
  const DeleteMessage(this.messageId);
  @override
  List<Object?> get props => [messageId];
}

class FetchUnreadCount extends MessagingEvent {
  const FetchUnreadCount();
  @override
  List<Object?> get props => [];
}

class LoadTemplates extends MessagingEvent {
  const LoadTemplates();
  @override
  List<Object?> get props => [];
}

class SaveTemplate extends MessagingEvent {
  final String name;
  final String subject;
  final String body;
  final String? priority;
  const SaveTemplate({
    required this.name,
    required this.subject,
    required this.body,
    this.priority,
  });
  @override
  List<Object?> get props => [name, subject, body, priority ?? ''];
}

class DeleteTemplate extends MessagingEvent {
  final int templateId;
  const DeleteTemplate(this.templateId);
  @override
  List<Object?> get props => [templateId];
}

class ApplyTemplate extends MessagingEvent {
  final int templateId;
  const ApplyTemplate(this.templateId);
  @override
  List<Object?> get props => [templateId];
}

// States
abstract class MessagingState extends Equatable {
  const MessagingState();
  @override
  List<Object?> get props => [];
}

class MessagingInitial extends MessagingState {}

class MessagingLoading extends MessagingState {}

class MessagingLoaded extends MessagingState {
  final List<MessageModel> inbox;
  final List<MessageModel> sent;
  final List<MessageModel> archived;
  final List<MessageModel> searchResults;
  final MessageModel? selectedMessage;
  final bool hasMoreInbox;
  final bool hasMoreSent;
  final bool hasMoreArchived;
  final bool isSearching;
  final String? message;
  final int unreadCount;
  final List<Map<String, dynamic>> templates;
  final Map<String, dynamic>? selectedTemplate;

  const MessagingLoaded({
    this.inbox = const [],
    this.sent = const [],
    this.archived = const [],
    this.searchResults = const [],
    this.selectedMessage,
    this.hasMoreInbox = true,
    this.hasMoreSent = true,
    this.hasMoreArchived = true,
    this.isSearching = false,
    this.message,
    this.unreadCount = 0,
    this.templates = const [],
    this.selectedTemplate,
  });
  @override
  List<Object?> get props => [
    inbox,
    sent,
    archived,
    searchResults,
    selectedMessage,
    hasMoreInbox,
    hasMoreSent,
    hasMoreArchived,
    isSearching,
    message,
    unreadCount,
    templates,
    selectedTemplate,
  ];
}

class MessagingError extends MessagingState {
  final String error;
  const MessagingError(this.error);
  @override
  List<Object?> get props => [error];
}

// Bloc
class MessagingBloc extends Bloc<MessagingEvent, MessagingState> {
  final ApiClient _api;

  MessagingBloc({required this._api}) : super(MessagingInitial()) {
    on<LoadInbox>(_onLoadInbox);
    on<LoadSentMessages>(_onLoadSent);
    on<LoadArchivedMessages>(_onLoadArchived);
    on<LoadMessageDetail>(_onLoadDetail);
    on<SendMessage>(_onSend);
    on<ReplyToMessage>(_onReply);
    on<ForwardMessage>(_onForward);
    on<SearchMessages>(_onSearch);
    on<ArchiveMessage>(_onArchive);
    on<UnarchiveMessage>(_onUnarchive);
    on<MarkAsRead>(_onMarkRead);
    on<MarkAsUnread>(_onMarkUnread);
    on<DeleteMessage>(_onDelete);
    on<FetchUnreadCount>(_onFetchUnreadCount);
    on<LoadTemplates>(_onLoadTemplates);
    on<SaveTemplate>(_onSaveTemplate);
    on<DeleteTemplate>(_onDeleteTemplate);
    on<ApplyTemplate>(_onApplyTemplate);
  }

  List<MessageModel> _parseList(dynamic data) {
    final list = data['data'] as List<dynamic>? ?? [];
    return list
        .map((e) => MessageModel.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<int> _fetchUnreadCount() async {
    try {
      final r = await _api.get('/v1/messages/unread-count');
      return parseIntOrNull(r.data['unread_count']) ?? 0;
    } catch (_) {
      return 0;
    }
  }

  Future<void> _onLoadInbox(
    LoadInbox event,
    Emitter<MessagingState> emit,
  ) async {
    final current = state;
    if (current is! MessagingLoaded || event.page == 1) {
      emit(MessagingLoading());
    }

    try {
      final response = await _api.get(
        '/v1/messages/inbox',
        queryParameters: {'page': event.page},
      );
      final messages = _parseList(response.data);
      final meta = response.data['meta'] as Map<String, dynamic>? ?? {};
      final all = (current is MessagingLoaded && event.page > 1)
          ? [...current.inbox, ...messages]
          : messages;
      final unread = await _fetchUnreadCount();

      emit(
        MessagingLoaded(
          inbox: all,
          sent: current is MessagingLoaded ? current.sent : [],
          archived: current is MessagingLoaded ? current.archived : [],
          unreadCount: unread,
          hasMoreInbox: meta['current_page'] < (meta['last_page'] ?? 1),
        ),
      );
    } catch (e) {
      emit(MessagingError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onLoadSent(
    LoadSentMessages event,
    Emitter<MessagingState> emit,
  ) async {
    final current = state;
    if (current is! MessagingLoaded || event.page == 1) {
      emit(MessagingLoading());
    }

    try {
      final response = await _api.get(
        '/v1/messages/sent',
        queryParameters: {'page': event.page},
      );
      final messages = _parseList(response.data);
      final meta = response.data['meta'] as Map<String, dynamic>? ?? {};
      final all = (current is MessagingLoaded && event.page > 1)
          ? [...current.sent, ...messages]
          : messages;

      emit(
        MessagingLoaded(
          inbox: current is MessagingLoaded ? current.inbox : [],
          sent: all,
          archived: current is MessagingLoaded ? current.archived : [],
          unreadCount: current is MessagingLoaded ? current.unreadCount : 0,
          hasMoreSent: meta['current_page'] < (meta['last_page'] ?? 1),
        ),
      );
    } catch (_) {}
  }

  Future<void> _onLoadArchived(
    LoadArchivedMessages event,
    Emitter<MessagingState> emit,
  ) async {
    final current = state;
    if (current is! MessagingLoaded || event.page == 1) {
      emit(MessagingLoading());
    }

    try {
      final response = await _api.get(
        '/v1/messages/archived',
        queryParameters: {'page': event.page},
      );
      final messages = _parseList(response.data);
      final meta = response.data['meta'] as Map<String, dynamic>? ?? {};
      final all = (current is MessagingLoaded && event.page > 1)
          ? [...current.archived, ...messages]
          : messages;

      emit(
        MessagingLoaded(
          inbox: current is MessagingLoaded ? current.inbox : [],
          sent: current is MessagingLoaded ? current.sent : [],
          archived: all,
          unreadCount: current is MessagingLoaded ? current.unreadCount : 0,
          hasMoreArchived: meta['current_page'] < (meta['last_page'] ?? 1),
        ),
      );
    } catch (_) {}
  }

  Future<void> _onLoadDetail(
    LoadMessageDetail event,
    Emitter<MessagingState> emit,
  ) async {
    try {
      final response = await _api.get('/v1/messages/${event.messageId}');
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      final message = MessageModel.fromJson(data);
      final unread = await _fetchUnreadCount();

      final current = state;
      emit(
        MessagingLoaded(
          inbox: current is MessagingLoaded ? current.inbox : [],
          sent: current is MessagingLoaded ? current.sent : [],
          archived: current is MessagingLoaded ? current.archived : [],
          selectedMessage: message,
          unreadCount: unread,
        ),
      );
    } catch (e) {
      emit(MessagingError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onSend(SendMessage event, Emitter<MessagingState> emit) async {
    try {
      if (event.attachments != null && event.attachments!.isNotEmpty) {
        final formData = FormData.fromMap({
          'subject': event.subject,
          'body': event.body,
          'type': event.type ?? 'direct',
          'priority': event.priority ?? 'normal',
          if (event.recipientIds != null && event.recipientIds!.isNotEmpty)
            'recipient_ids': event.recipientIds,
          if (event.departmentId != null) 'department_id': event.departmentId,
          ...event.attachments!
              .asMap()
              .map((i, f) => MapEntry('attachments[$i]', f)),
        });
        await _api.post('/v1/messages/send', data: formData);
      } else {
        await _api.post(
          '/v1/messages/send',
          data: {
            'subject': event.subject,
            'body': event.body,
            'type': event.type ?? 'direct',
            'priority': event.priority ?? 'normal',
            if (event.recipientIds != null && event.recipientIds!.isNotEmpty)
              'recipient_ids': event.recipientIds,
            if (event.departmentId != null) 'department_id': event.departmentId,
          },
        );
      }
      add(const LoadInbox());
      add(const LoadSentMessages());
    } catch (e) {
      emit(MessagingError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onReply(
    ReplyToMessage event,
    Emitter<MessagingState> emit,
  ) async {
    try {
      await _api.post(
        '/v1/messages/${event.messageId}/reply',
        data: {
          'body': event.body,
          if (event.replyAll) 'reply_all': true,
        },
      );
      add(LoadMessageDetail(event.messageId));
      final current = state;
      if (current is MessagingLoaded) {
        emit(
          MessagingLoaded(
            inbox: current.inbox,
            sent: current.sent,
            archived: current.archived,
            message: 'Reply sent',
            unreadCount: current.unreadCount,
          ),
        );
      }
    } catch (e) {
      emit(MessagingError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onForward(
    ForwardMessage event,
    Emitter<MessagingState> emit,
  ) async {
    try {
      await _api.post(
        '/v1/messages/${event.messageId}/forward',
        data: {'recipient_ids': event.recipientIds},
      );
      final current = state;
      if (current is MessagingLoaded) {
        emit(
          MessagingLoaded(
            inbox: current.inbox,
            sent: current.sent,
            archived: current.archived,
            message: 'Message forwarded',
            unreadCount: current.unreadCount,
          ),
        );
      }
    } catch (e) {
      emit(MessagingError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onSearch(
    SearchMessages event,
    Emitter<MessagingState> emit,
  ) async {
    if (event.query.isEmpty) {
      final current = state;
      if (current is MessagingLoaded) {
        emit(
          MessagingLoaded(
            inbox: current.inbox,
            sent: current.sent,
            archived: current.archived,
            searchResults: [],
            isSearching: false,
            unreadCount: current.unreadCount,
          ),
        );
      }
      return;
    }

    final current = state;
    emit(MessagingLoading());

    try {
      final response = await _api.get(
        '/v1/messages/search',
        queryParameters: {'q': event.query, 'scope': event.scope},
      );
      final results = _parseList(response.data);

      emit(
        MessagingLoaded(
          inbox: current is MessagingLoaded ? current.inbox : [],
          sent: current is MessagingLoaded ? current.sent : [],
          archived: current is MessagingLoaded ? current.archived : [],
          searchResults: results,
          isSearching: true,
          unreadCount: current is MessagingLoaded ? current.unreadCount : 0,
        ),
      );
    } catch (e) {
      emit(MessagingError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onArchive(
    ArchiveMessage event,
    Emitter<MessagingState> emit,
  ) async {
    try {
      await _api.post('/v1/messages/${event.messageId}/archive');
      add(const LoadInbox());
    } catch (_) {}
  }

  Future<void> _onUnarchive(
    UnarchiveMessage event,
    Emitter<MessagingState> emit,
  ) async {
    try {
      await _api.post('/v1/messages/${event.messageId}/unarchive');
      add(const LoadArchivedMessages());
    } catch (_) {}
  }

  Future<void> _onMarkRead(
    MarkAsRead event,
    Emitter<MessagingState> emit,
  ) async {
    try {
      await _api.get('/v1/messages/${event.messageId}');
      final unread = await _fetchUnreadCount();
      final current = state;
      if (current is MessagingLoaded) {
        emit(
          MessagingLoaded(
            inbox: current.inbox,
            sent: current.sent,
            archived: current.archived,
            unreadCount: unread,
          ),
        );
      }
    } catch (_) {}
  }

  Future<void> _onMarkUnread(
    MarkAsUnread event,
    Emitter<MessagingState> emit,
  ) async {
    try {
      await _api.post('/v1/messages/${event.messageId}/mark-unread');
      final unread = await _fetchUnreadCount();
      final current = state;
      if (current is MessagingLoaded) {
        emit(
          MessagingLoaded(
            inbox: current.inbox,
            sent: current.sent,
            archived: current.archived,
            unreadCount: unread,
          ),
        );
      }
    } catch (_) {}
  }

  Future<void> _onDelete(
    DeleteMessage event,
    Emitter<MessagingState> emit,
  ) async {
    try {
      await _api.delete('/v1/messages/${event.messageId}');
      add(const LoadInbox());
    } catch (_) {}
  }

  Future<void> _onFetchUnreadCount(
    FetchUnreadCount event,
    Emitter<MessagingState> emit,
  ) async {
    final count = await _fetchUnreadCount();
    final current = state;
    if (current is MessagingLoaded) {
      emit(
        MessagingLoaded(
          inbox: current.inbox,
          sent: current.sent,
          archived: current.archived,
          unreadCount: count,
        ),
      );
    }
  }

  Future<void> _onLoadTemplates(
    LoadTemplates event,
    Emitter<MessagingState> emit,
  ) async {
    try {
      final r = await _api.get('/v1/messages/templates');
      final list = (r.data['data'] as List<dynamic>? ?? [])
          .map((e) => e as Map<String, dynamic>)
          .toList();
      final current = state;
      if (current is MessagingLoaded) {
        emit(MessagingLoaded(
          inbox: current.inbox,
          sent: current.sent,
          archived: current.archived,
          searchResults: current.searchResults,
          selectedMessage: current.selectedMessage,
          unreadCount: current.unreadCount,
          templates: list,
        ));
      }
    } catch (_) {}
  }

  Future<void> _onSaveTemplate(
    SaveTemplate event,
    Emitter<MessagingState> emit,
  ) async {
    try {
      await _api.post('/v1/messages/templates', data: {
        'name': event.name,
        'subject': event.subject,
        'body': event.body,
        if (event.priority != null) 'priority': event.priority,
      });
      add(const LoadTemplates());
    } catch (_) {}
  }

  Future<void> _onDeleteTemplate(
    DeleteTemplate event,
    Emitter<MessagingState> emit,
  ) async {
    try {
      await _api.delete('/v1/messages/templates/${event.templateId}');
      add(const LoadTemplates());
    } catch (_) {}
  }

  Future<void> _onApplyTemplate(
    ApplyTemplate event,
    Emitter<MessagingState> emit,
  ) async {
    try {
      final r = await _api.get('/v1/messages/templates/${event.templateId}');
      final data = r.data['data'] as Map<String, dynamic>? ?? r.data as Map<String, dynamic>;
      final current = state;
      if (current is MessagingLoaded) {
        emit(MessagingLoaded(
          inbox: current.inbox,
          sent: current.sent,
          archived: current.archived,
          searchResults: current.searchResults,
          selectedMessage: current.selectedMessage,
          unreadCount: current.unreadCount,
          templates: current.templates,
          selectedTemplate: data,
        ));
      }
    } catch (_) {}
  }
}
