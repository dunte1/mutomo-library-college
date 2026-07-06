import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/network/api_client.dart';
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

class LoadMessageDetail extends MessagingEvent {
  final int messageId;
  const LoadMessageDetail(this.messageId);
  @override
  List<Object?> get props => [messageId];
}

class SendMessage extends MessagingEvent {
  final String subject;
  final String body;
  final int? recipientId;
  final String? departmentId;
  final String? priority;
  const SendMessage({
    required this.subject,
    required this.body,
    this.recipientId,
    this.departmentId,
    this.priority,
  });
  @override
  List<Object?> get props => [
    subject,
    body,
    recipientId,
    departmentId,
    priority ?? '',
  ];
}

class ReplyToMessage extends MessagingEvent {
  final int messageId;
  final String body;
  const ReplyToMessage({required this.messageId, required this.body});
  @override
  List<Object?> get props => [messageId, body];
}

class DeleteMessage extends MessagingEvent {
  final int messageId;
  const DeleteMessage(this.messageId);
  @override
  List<Object?> get props => [messageId];
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
  final MessageModel? selectedMessage;
  final bool hasMoreInbox;
  final bool hasMoreSent;
  final String? message;
  final int unreadCount;

  const MessagingLoaded({
    this.inbox = const [],
    this.sent = const [],
    this.selectedMessage,
    this.hasMoreInbox = true,
    this.hasMoreSent = true,
    this.message,
    this.unreadCount = 0,
  });
  @override
  List<Object?> get props => [
    inbox,
    sent,
    selectedMessage,
    hasMoreInbox,
    hasMoreSent,
    message,
    unreadCount,
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
    on<LoadMessageDetail>(_onLoadDetail);
    on<SendMessage>(_onSend);
    on<ReplyToMessage>(_onReply);
    on<DeleteMessage>(_onDelete);
  }

  Future<void> _onLoadInbox(
    LoadInbox event,
    Emitter<MessagingState> emit,
  ) async {
    if (state is! MessagingLoaded || event.page == 1) {
      emit(MessagingLoading());
    }

    try {
      final response = await _api.get(
        '/v1/messages/inbox',
        queryParameters: {'page': event.page},
      );
      final data = response.data;
      final list = data['data'] as List<dynamic>? ?? [];
      final messages = list
          .map((e) => MessageModel.fromJson(e as Map<String, dynamic>))
          .toList();

      final current = state;
      final all = (current is MessagingLoaded && event.page > 1)
          ? [...current.inbox, ...messages]
          : messages;

      final unread = await _api
          .get('/v1/notifications/unread-count')
          .then((r) => r.data['unread_count'] as int? ?? 0)
          .catchError((_) => 0);

      emit(
        MessagingLoaded(
          inbox: all,
          sent: current is MessagingLoaded ? current.sent : [],
          unreadCount: unread,
        ),
      );
    } catch (e) {
      emit(MessagingError('Failed to load inbox: ${e.toString()}'));
    }
  }

  Future<void> _onLoadSent(
    LoadSentMessages event,
    Emitter<MessagingState> emit,
  ) async {
    try {
      final response = await _api.get(
        '/v1/messages/sent',
        queryParameters: {'page': event.page},
      );
      final data = response.data;
      final list = data['data'] as List<dynamic>? ?? [];
      final messages = list
          .map((e) => MessageModel.fromJson(e as Map<String, dynamic>))
          .toList();

      final current = state;
      final all = (current is MessagingLoaded && event.page > 1)
          ? [...current.sent, ...messages]
          : messages;

      if (current is MessagingLoaded) {
        emit(
          MessagingLoaded(
            inbox: current.inbox,
            sent: all,
            unreadCount: current.unreadCount,
          ),
        );
      }
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

      final current = state;
      if (current is MessagingLoaded) {
        emit(
          MessagingLoaded(
            inbox: current.inbox,
            sent: current.sent,
            selectedMessage: message,
            unreadCount: current.unreadCount,
          ),
        );
      }
    } catch (e) {
      emit(MessagingError('Failed to load message: ${e.toString()}'));
    }
  }

  Future<void> _onSend(SendMessage event, Emitter<MessagingState> emit) async {
    try {
      await _api.post(
        '/v1/messages/send',
        data: {
          'subject': event.subject,
          'body': event.body,
          if (event.recipientId != null) 'recipient_id': event.recipientId,
          if (event.departmentId != null) 'department_id': event.departmentId,
          if (event.priority != null) 'priority': event.priority,
        },
      );
      add(const LoadInbox());
      final current = state;
      if (current is MessagingLoaded) {
        emit(
          MessagingLoaded(
            inbox: current.inbox,
            sent: current.sent,
            message: 'Message sent',
            unreadCount: current.unreadCount,
          ),
        );
      }
    } catch (e) {
      emit(MessagingError('Failed to send: ${e.toString()}'));
    }
  }

  Future<void> _onReply(
    ReplyToMessage event,
    Emitter<MessagingState> emit,
  ) async {
    try {
      await _api.post(
        '/v1/messages/${event.messageId}/reply',
        data: {'body': event.body},
      );
      add(LoadMessageDetail(event.messageId));
      final current = state;
      if (current is MessagingLoaded) {
        emit(
          MessagingLoaded(
            inbox: current.inbox,
            sent: current.sent,
            message: 'Reply sent',
            unreadCount: current.unreadCount,
          ),
        );
      }
    } catch (e) {
      emit(MessagingError('Failed to reply: ${e.toString()}'));
    }
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
}
