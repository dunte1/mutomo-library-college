class NotificationRouteHelper {
  NotificationRouteHelper._();

  static String? resolveRoute(Map<String, dynamic> data) {
    final type = data['type'] as String?;
    final id = data['id'] as String?;

    // If an explicit route is provided, use it
    final explicitRoute = data['route'] as String?;
    if (explicitRoute != null && explicitRoute.isNotEmpty) {
      return explicitRoute;
    }

    // Map notification type to route
    switch (type) {
      case 'fine':
      case 'fine_assessed':
        return id != null ? '/fines/$id' : '/fines';
      case 'overdue':
      case 'overdue_notice':
        return '/loans';
      case 'due_reminder':
      case 'due_date':
      case 'return_reminder':
        return '/loans';
      case 'reservation':
      case 'hold_available':
        return '/reservations';
      case 'message':
        return id != null ? '/messages/$id' : '/messages';
      case 'assignment':
      case 'new_assignment':
        return id != null ? '/assignments/$id' : '/assignments';
      case 'event':
        return id != null ? '/events/$id' : '/events';
      case 'announcement':
        return id != null ? '/announcements/$id' : '/announcements';
      case 'bulletin':
        return id != null ? '/bulletins/$id' : '/bulletins';
      case 'library':
      case 'digital_asset':
        return '/digital-library';
      case 'subscription':
      case 'subscription_expiring':
        return '/subscriptions/my';
      case 'system':
      default:
        return '/notifications';
    }
  }
}
