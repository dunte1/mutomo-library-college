import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/network/api_client.dart';

class NotificationPreferencesScreen extends StatefulWidget {
  const NotificationPreferencesScreen({super.key});

  @override
  State<NotificationPreferencesScreen> createState() =>
      _NotificationPreferencesScreenState();
}

class _NotificationPreferencesScreenState
    extends State<NotificationPreferencesScreen> {
  bool _dueDateReminders = true;
  bool _fineAlerts = true;
  bool _messageAlerts = true;
  bool _announcementAlerts = true;
  bool _eventReminders = true;
  bool _loading = false;
  bool _unsubscribing = false;
  List<Map<String, dynamic>> _subscriptions = [];
  bool _loadingSubscriptions = false;

  @override
  void initState() {
    super.initState();
    _load();
    _loadSubscriptions();
  }

  Future<void> _load() async {
    try {
      final api = context.read<ApiClient>();
      final response = await api.get('/v1/profile');
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      final prefs =
          data['notification_preferences'] as Map<String, dynamic>? ?? {};
      setState(() {
        _dueDateReminders = prefs['due_dates'] as bool? ?? true;
        _fineAlerts = prefs['fines'] as bool? ?? true;
        _messageAlerts = prefs['messages'] as bool? ?? true;
        _announcementAlerts = prefs['announcements'] as bool? ?? true;
        _eventReminders = prefs['events'] as bool? ?? true;
      });
    } catch (_) {}
  }

  Future<void> _loadSubscriptions() async {
    setState(() => _loadingSubscriptions = true);
    try {
      final api = context.read<ApiClient>();
      final response = await api.get('/v1/notifications/subscriptions');
      final list = (response.data['data'] as List<dynamic>? ?? [])
          .map((e) => e as Map<String, dynamic>)
          .toList();
      if (mounted) setState(() => _subscriptions = list);
    } catch (_) {}
    if (mounted) setState(() => _loadingSubscriptions = false);
  }

  Future<void> _unsubscribeAll() async {
    setState(() => _unsubscribing = true);
    try {
      final api = context.read<ApiClient>();
      await api.post('/v1/notifications/unsubscribe-all');
      if (mounted) {
        setState(() {
          _dueDateReminders = false;
          _fineAlerts = false;
          _messageAlerts = false;
          _announcementAlerts = false;
          _eventReminders = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('All notifications unsubscribed')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed: $e')),
        );
      }
    }
    if (mounted) setState(() => _unsubscribing = false);
  }

  Future<void> _toggleSubscription(Map<String, dynamic> sub, bool enabled) async {
    try {
      final api = context.read<ApiClient>();
      if (enabled) {
        await api.post('/v1/notifications/subscribe', data: {'topic': sub['topic'] ?? sub['name']});
      } else {
        await api.post('/v1/notifications/unsubscribe', data: {'topic': sub['topic'] ?? sub['name']});
      }
      _loadSubscriptions();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed: $e')),
        );
      }
    }
  }

  Future<void> _save() async {
    setState(() => _loading = true);
    try {
      final api = context.read<ApiClient>();
      await api.put(
        '/v1/profile',
        data: {
          'notification_preferences': {
            'due_dates': _dueDateReminders,
            'fines': _fineAlerts,
            'messages': _messageAlerts,
            'announcements': _announcementAlerts,
            'events': _eventReminders,
          },
        },
      );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Notification preferences saved')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Failed to save: $e')));
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        actions: [
          TextButton(
            onPressed: _loading ? null : _save,
            child: _loading
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Text('Save'),
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(
            'Notification Categories',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Card(
            child: Column(
              children: [
                SwitchListTile(
                  title: const Text('Due Date Reminders'),
                  subtitle: const Text('Books due for return'),
                  value: _dueDateReminders,
                  onChanged: (v) => setState(() => _dueDateReminders = v),
                ),
                const Divider(height: 1),
                SwitchListTile(
                  title: const Text('Fine Alerts'),
                  subtitle: const Text('Fines assessed or due'),
                  value: _fineAlerts,
                  onChanged: (v) => setState(() => _fineAlerts = v),
                ),
                const Divider(height: 1),
                SwitchListTile(
                  title: const Text('Message Alerts'),
                  subtitle: const Text('New library messages'),
                  value: _messageAlerts,
                  onChanged: (v) => setState(() => _messageAlerts = v),
                ),
                const Divider(height: 1),
                SwitchListTile(
                  title: const Text('Announcements'),
                  subtitle: const Text('Library announcements'),
                  value: _announcementAlerts,
                  onChanged: (v) => setState(() => _announcementAlerts = v),
                ),
                const Divider(height: 1),
                SwitchListTile(
                  title: const Text('Event Reminders'),
                  subtitle: const Text('Upcoming library events'),
                  value: _eventReminders,
                  onChanged: (v) => setState(() => _eventReminders = v),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Unsubscribe All
          Card(
            child: ListTile(
              leading: Icon(
                Icons.notifications_off_outlined,
                color: theme.colorScheme.error,
              ),
              title: Text(
                'Unsubscribe from All',
                style: TextStyle(color: theme.colorScheme.error),
              ),
              subtitle: const Text('Stop all push notifications'),
              trailing: _unsubscribing
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : null,
              onTap: _unsubscribing
                  ? null
                  : () async {
                      final confirmed = await showDialog<bool>(
                        context: context,
                        builder: (ctx) => AlertDialog(
                          title: const Text('Unsubscribe from All'),
                          content: const Text(
                            'You will stop receiving all push notifications. You can re-enable them later.',
                          ),
                          actions: [
                            TextButton(
                              onPressed: () => Navigator.pop(ctx, false),
                              child: const Text('Cancel'),
                            ),
                            FilledButton(
                              style: FilledButton.styleFrom(
                                backgroundColor: theme.colorScheme.error,
                              ),
                              onPressed: () => Navigator.pop(ctx, true),
                              child: const Text('Unsubscribe'),
                            ),
                          ],
                        ),
                      );
                      if (confirmed == true) _unsubscribeAll();
                    },
            ),
          ),
          const SizedBox(height: 24),

          // Push Notification Topics
          Text(
            'Push Notification Topics',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          if (_loadingSubscriptions)
            const Card(
              child: Padding(
                padding: EdgeInsets.all(16),
                child: Center(child: CircularProgressIndicator()),
              ),
            )
          else if (_subscriptions.isEmpty)
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Icon(Icons.info_outline, color: theme.colorScheme.primary),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'No subscription topics available.',
                        style: theme.textTheme.bodySmall,
                      ),
                    ),
                  ],
                ),
              ),
            )
          else
            Card(
              child: Column(
                children: _subscriptions.map((sub) {
                  final enabled = sub['subscribed'] as bool? ?? false;
                  return SwitchListTile(
                    title: Text(sub['name'] as String? ?? sub['topic'] as String? ?? 'Topic'),
                    subtitle: Text(sub['description'] as String? ?? ''),
                    value: enabled,
                    onChanged: (v) => _toggleSubscription(sub, v),
                  );
                }).toList(),
              ),
            ),
          const SizedBox(height: 24),

          Text(
            'Push Notifications',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  Icon(Icons.info_outline, color: theme.colorScheme.primary),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Push notification delivery depends on device permissions. Enable notifications in your device settings.',
                      style: theme.textTheme.bodySmall,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
