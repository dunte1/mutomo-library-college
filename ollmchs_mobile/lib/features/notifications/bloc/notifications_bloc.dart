import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/network/api_client.dart';
import '../models/notification_model.dart';

// Events
abstract class NotificationEvent extends Equatable {
  const NotificationEvent();
  @override
  List<Object?> get props => [];
}

class LoadNotifications extends NotificationEvent {
  final int page;
  const LoadNotifications({this.page = 1});
  @override
  List<Object?> get props => [page];
}

class MarkNotificationRead extends NotificationEvent {
  final int notificationId;
  const MarkNotificationRead(this.notificationId);
  @override
  List<Object?> get props => [notificationId];
}

class MarkAllNotificationsRead extends NotificationEvent {
  const MarkAllNotificationsRead();
}

class LoadUnreadCount extends NotificationEvent {
  const LoadUnreadCount();
}

// States
abstract class NotificationsState extends Equatable {
  const NotificationsState();
  @override
  List<Object?> get props => [];
}

class NotificationsInitial extends NotificationsState {}

class NotificationsLoading extends NotificationsState {}

class NotificationsLoaded extends NotificationsState {
  final List<NotificationModel> notifications;
  final bool hasMore;
  final int unreadCount;
  final String? message;

  const NotificationsLoaded({
    this.notifications = const [],
    this.hasMore = true,
    this.unreadCount = 0,
    this.message,
  });
  @override
  List<Object?> get props => [notifications, hasMore, unreadCount, message];
}

class NotificationsError extends NotificationsState {
  final String error;
  const NotificationsError(this.error);
  @override
  List<Object?> get props => [error];
}

// Bloc
class NotificationsBloc extends Bloc<NotificationEvent, NotificationsState> {
  final ApiClient _api;

  NotificationsBloc({required this._api}) : super(NotificationsInitial()) {
    on<LoadNotifications>(_onLoad);
    on<MarkNotificationRead>(_onMarkRead);
    on<MarkAllNotificationsRead>(_onMarkAllRead);
    on<LoadUnreadCount>(_onLoadUnreadCount);
  }

  Future<void> _onLoad(
    LoadNotifications event,
    Emitter<NotificationsState> emit,
  ) async {
    if (state is! NotificationsLoaded || event.page == 1) {
      emit(NotificationsLoading());
    }

    try {
      final response = await _api.get(
        '/v1/notifications',
        queryParameters: {'page': event.page},
      );
      final data = response.data;
      final list = data['data'] as List<dynamic>? ?? [];
      final meta = data['meta'] as Map<String, dynamic>? ?? data;

      final notifications = list
          .map((e) => NotificationModel.fromJson(e as Map<String, dynamic>))
          .toList();
      final current = state;
      final all = (current is NotificationsLoaded && event.page > 1)
          ? [...current.notifications, ...notifications]
          : notifications;

      emit(
        NotificationsLoaded(
          notifications: all,
          hasMore:
              (meta['current_page'] as int? ?? 1) <
              (meta['last_page'] as int? ?? 1),
          unreadCount: current is NotificationsLoaded
              ? current.unreadCount
              : (data['unread_count'] as int? ?? 0),
        ),
      );
    } catch (e) {
      emit(NotificationsError('Failed to load notifications: ${e.toString()}'));
    }
  }

  Future<void> _onMarkRead(
    MarkNotificationRead event,
    Emitter<NotificationsState> emit,
  ) async {
    try {
      await _api.post('/v1/notifications/${event.notificationId}/read');
      final current = state;
      if (current is NotificationsLoaded) {
        final updated = current.notifications
            .map(
              (n) => n.id == event.notificationId
                  ? NotificationModel(
                      id: n.id,
                      title: n.title,
                      body: n.body,
                      type: n.type,
                      referenceId: n.referenceId,
                      isRead: true,
                      createdAt: n.createdAt,
                    )
                  : n,
            )
            .toList();
        emit(
          NotificationsLoaded(
            notifications: updated,
            hasMore: current.hasMore,
            unreadCount: current.unreadCount > 0 ? current.unreadCount - 1 : 0,
          ),
        );
      }
    } catch (_) {}
  }

  Future<void> _onMarkAllRead(
    MarkAllNotificationsRead event,
    Emitter<NotificationsState> emit,
  ) async {
    try {
      await _api.post('/v1/notifications/read-all');
      final current = state;
      if (current is NotificationsLoaded) {
        final updated = current.notifications
            .map(
              (n) => NotificationModel(
                id: n.id,
                title: n.title,
                body: n.body,
                type: n.type,
                referenceId: n.referenceId,
                isRead: true,
                createdAt: n.createdAt,
              ),
            )
            .toList();
        emit(
          NotificationsLoaded(
            notifications: updated,
            hasMore: current.hasMore,
            unreadCount: 0,
            message: 'All marked as read',
          ),
        );
      }
    } catch (_) {}
  }

  Future<void> _onLoadUnreadCount(
    LoadUnreadCount event,
    Emitter<NotificationsState> emit,
  ) async {
    try {
      final response = await _api.get('/v1/notifications/unread-count');
      final count = response.data['count'] as int? ?? 0;
      final current = state;
      if (current is NotificationsLoaded) {
        emit(
          NotificationsLoaded(
            notifications: current.notifications,
            hasMore: current.hasMore,
            unreadCount: count,
          ),
        );
      }
    } catch (_) {}
  }
}
