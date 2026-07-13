import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import '../bloc/notifications_bloc.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/skeleton.dart';

class NotificationListScreen extends StatefulWidget {
  const NotificationListScreen({super.key});

  @override
  State<NotificationListScreen> createState() => _NotificationListScreenState();
}

class _NotificationListScreenState extends State<NotificationListScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final ScrollController _scrollController = ScrollController();
  int _currentPage = 1;
  String? _selectedCategory;

  static const _categories = <String?, String>{
    null: 'All',
    'unread': 'Unread',
    'system': 'System',
    'library': 'Library',
    'finance': 'Finance',
    'events': 'Events',
    'assignments': 'Assignments',
    'message': 'Messages',
  };

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: _categories.length, vsync: this);
    _tabController.addListener(_onTabChanged);
    context.read<NotificationsBloc>().add(const LoadNotifications());
    _scrollController.addListener(_onScroll);
  }

  void _onTabChanged() {
    if (!_tabController.indexIsChanging) return;
    final keys = _categories.keys.toList();
    _selectedCategory = keys[_tabController.index];
    _currentPage = 1;
    _loadNotifications();
  }

  void _loadNotifications() {
    context.read<NotificationsBloc>().add(
      LoadNotifications(
        page: _currentPage,
        type: _selectedCategory,
      ),
    );
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent - 200) {
      final state = context.read<NotificationsBloc>().state;
      if (state is NotificationsLoaded && state.hasMore) {
        _currentPage++;
        _loadNotifications();
      }
    }
  }

  @override
  void dispose() {
    _tabController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          tabs: _categories.values.map((t) => Tab(text: t)).toList(),
        ),
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
                ],
              ),
            );
          }
          if (state is NotificationsError) {
            return Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(state.error),
                  const SizedBox(height: 16),
                  FilledButton.tonal(
                    onPressed: () {
                      _currentPage = 1;
                      _loadNotifications();
                    },
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }
          if (state is NotificationsLoaded) {
            final notifications = _selectedCategory == 'unread'
                ? state.notifications.where((n) => !n.isRead).toList()
                : _selectedCategory != null && _selectedCategory != 'unread'
                    ? state.notifications
                        .where((n) => n.type == _selectedCategory)
                        .toList()
                    : state.notifications;

            if (notifications.isEmpty) {
              return EmptyState(
                icon: _selectedCategory == 'unread'
                    ? Icons.mark_email_read_outlined
                    : Icons.notifications_none,
                title: _selectedCategory == 'unread'
                    ? 'All caught up!'
                    : 'No notifications',
                subtitle: _selectedCategory == 'unread'
                    ? 'You have no unread notifications'
                    : 'Notifications will appear here',
              );
            }
            return RefreshIndicator(
              onRefresh: () async {
                _currentPage = 1;
                _loadNotifications();
              },
              child: ListView.builder(
                controller: _scrollController,
                padding: const EdgeInsets.all(8),
                itemCount: notifications.length + (state.hasMore ? 1 : 0),
                itemBuilder: (_, i) {
                  if (i == notifications.length) {
                    return const Padding(
                      padding: EdgeInsets.symmetric(vertical: 16),
                      child: Center(child: CircularProgressIndicator()),
                    );
                  }
                  final notification = notifications[i];
                  return Card(
                    color: notification.isRead
                        ? null
                        : Theme.of(context).colorScheme.primaryContainer.withValues(alpha: 0.2),
                    child: ListTile(
                      leading: CircleAvatar(
                        backgroundColor: notification.isRead
                            ? Colors.grey.shade200
                            : Theme.of(context).colorScheme.primaryContainer,
                        child: Icon(
                          _iconForType(notification.type),
                          color: notification.isRead
                              ? Colors.grey
                              : Theme.of(context).colorScheme.onPrimaryContainer,
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
                            DateFormat('MMM d, h:mm a').format(notification.createdAt),
                            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                              color: Theme.of(context).colorScheme.onSurfaceVariant,
                            ),
                          ),
                        ],
                      ),
                      trailing: notification.isRead
                          ? null
                          : IconButton(
                              icon: const Icon(Icons.check_circle_outline, size: 20),
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
      case 'assignment':
        return Icons.assignment;
      case 'event':
        return Icons.event;
      case 'library':
        return Icons.menu_book;
      case 'finance':
        return Icons.account_balance_wallet;
      default:
        return Icons.notifications;
    }
  }
}
