import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/notifications_bloc.dart';
import '../../../core/helpers/notification_route_helper.dart';

class NotificationDetailScreen extends StatelessWidget {
  final int notificationId;
  const NotificationDetailScreen({super.key, required this.notificationId});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Notification')),
      body: BlocBuilder<NotificationsBloc, NotificationsState>(
        builder: (context, state) {
          if (state is NotificationsLoaded) {
            final notif = state.notifications.where((n) => n.id == notificationId).firstOrNull;
            if (notif != null) {
              if (!notif.isRead) {
                context.read<NotificationsBloc>().add(MarkNotificationRead(notificationId));
              }
              return SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(
                          _iconForType(notif.type),
                          size: 32,
                          color: theme.colorScheme.primary,
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Text(
                            notif.title,
                            style: theme.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(
                      DateFormat("MMM d, y 'at' h:mm a").format(notif.createdAt),
                      style: theme.textTheme.bodySmall?.copyWith(color: theme.colorScheme.onSurfaceVariant),
                    ),
                    const SizedBox(height: 16),
                    const Divider(),
                    const SizedBox(height: 16),
                    Text(notif.body, style: theme.textTheme.bodyLarge),
                    const SizedBox(height: 24),
                    if (notif.actionUrl != null || notif.type != null)
                      SizedBox(
                        width: double.infinity,
                        child: FilledButton.icon(
                          onPressed: () {
                            final route = notif.actionUrl ?? NotificationRouteHelper.resolveRoute({
                              'type': notif.type,
                              'id': '${notif.entityId ?? notif.referenceId}',
                            });
                            if (route != null && context.mounted) context.push(route);
                          },
                          icon: const Icon(Icons.open_in_new),
                          label: const Text('View Related'),
                        ),
                      ),
                  ],
                ),
              );
            }
          }
          return const Center(child: Text('Notification not found'));
        },
      ),
    );
  }

  IconData _iconForType(String? type) {
    switch (type) {
      case 'overdue':
      case 'fine':
      case 'fine_assessed':
        return Icons.warning;
      case 'due_reminder':
      case 'due_date':
        return Icons.schedule;
      case 'reservation':
      case 'hold_available':
        return Icons.bookmark;
      case 'message':
        return Icons.mail;
      case 'assignment':
        return Icons.assignment;
      case 'event':
        return Icons.event;
      case 'announcement':
        return Icons.campaign;
      default:
        return Icons.notifications;
    }
  }
}
