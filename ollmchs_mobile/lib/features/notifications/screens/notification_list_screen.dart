import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import '../bloc/notifications_bloc.dart';
import '../../../core/widgets/skeleton.dart';

class NotificationListScreen extends StatefulWidget {
  const NotificationListScreen({super.key});

  @override
  State<NotificationListScreen> createState() => _NotificationListScreenState();
}

class _NotificationListScreenState extends State<NotificationListScreen> {
  @override
  void initState() {
    super.initState();
    context.read<NotificationsBloc>().add(const LoadNotifications());
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        actions: [
          BlocBuilder<NotificationsBloc, NotificationsState>(
            builder: (context, state) {
              if (state is NotificationsLoaded && state.unreadCount > 0) {
                return TextButton(
                  onPressed: () => context.read<NotificationsBloc>().add(
                    const MarkAllNotificationsRead(),
                  ),
                  child: const Text('Mark All Read'),
                );
              }
              return const SizedBox.shrink();
            },
          ),
        ],
      ),
      body: BlocBuilder<NotificationsBloc, NotificationsState>(
        builder: (context, state) {
          if (state is NotificationsLoading && state is! NotificationsLoaded) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [
                  SkeletonCard(height: 72),
                  SkeletonCard(height: 72),
                  SkeletonCard(height: 72),
                  SkeletonCard(height: 72),
                ],
              ),
            );
          }
          if (state is NotificationsError) {
            return Center(child: Text(state.error));
          }
          if (state is NotificationsLoaded) {
            if (state.notifications.isEmpty) {
              return Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.notifications_none,
                      size: 64,
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'No notifications',
                      style: theme.textTheme.titleMedium,
                    ),
                  ],
                ),
              );
            }
            return RefreshIndicator(
              onRefresh: () async => context.read<NotificationsBloc>().add(
                const LoadNotifications(),
              ),
              child: ListView.builder(
                padding: const EdgeInsets.all(8),
                itemCount: state.notifications.length,
                itemBuilder: (_, i) {
                  final notification = state.notifications[i];
                  return Card(
                    color: notification.isRead
                        ? null
                        : theme.colorScheme.primaryContainer.withValues(
                            alpha: 0.2,
                          ),
                    child: ListTile(
                      leading: CircleAvatar(
                        backgroundColor: notification.isRead
                            ? Colors.grey.shade200
                            : theme.colorScheme.primaryContainer,
                        child: Icon(
                          _iconForType(notification.type),
                          color: notification.isRead
                              ? Colors.grey
                              : theme.colorScheme.onPrimaryContainer,
                          size: 20,
                        ),
                      ),
                      title: Text(
                        notification.title,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontWeight: notification.isRead
                              ? FontWeight.normal
                              : FontWeight.bold,
                        ),
                      ),
                      subtitle: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            notification.body,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                          Text(
                            DateFormat(
                              'MMM d, h:mm a',
                            ).format(notification.createdAt),
                            style: theme.textTheme.bodySmall?.copyWith(
                              color: theme.colorScheme.onSurfaceVariant,
                            ),
                          ),
                        ],
                      ),
                      trailing: notification.isRead
                          ? null
                          : IconButton(
                              icon: const Icon(
                                Icons.check_circle_outline,
                                size: 20,
                              ),
                              onPressed: () => context
                                  .read<NotificationsBloc>()
                                  .add(MarkNotificationRead(notification.id)),
                            ),
                      isThreeLine: true,
                    ),
                  );
                },
              ),
            );
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }

  IconData _iconForType(String? type) {
    switch (type) {
      case 'fine':
        return Icons.money_off;
      case 'overdue':
        return Icons.warning;
      case 'reservation':
        return Icons.bookmark;
      case 'message':
        return Icons.email;
      case 'due_date':
        return Icons.schedule;
      case 'system':
        return Icons.info;
      default:
        return Icons.notifications;
    }
  }
}
