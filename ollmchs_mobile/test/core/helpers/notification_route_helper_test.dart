import 'package:flutter_test/flutter_test.dart';
import 'package:ollmchs_library/core/helpers/notification_route_helper.dart';

void main() {
  group('NotificationRouteHelper', () {
    test('resolves fine notification to /fines/:id', () {
      final route = NotificationRouteHelper.resolveRoute({
        'type': 'fine',
        'id': '5',
      });
      expect(route, '/fines/5');
    });

    test('resolves fine_assessed to /fines/:id', () {
      final route = NotificationRouteHelper.resolveRoute({
        'type': 'fine_assessed',
        'id': '10',
      });
      expect(route, '/fines/10');
    });

    test('resolves overdue to /loans', () {
      final route = NotificationRouteHelper.resolveRoute({'type': 'overdue'});
      expect(route, '/loans');
    });

    test('resolves due_reminder to /loans', () {
      final route = NotificationRouteHelper.resolveRoute({'type': 'due_reminder'});
      expect(route, '/loans');
    });

    test('resolves reservation to /reservations', () {
      final route = NotificationRouteHelper.resolveRoute({'type': 'reservation'});
      expect(route, '/reservations');
    });

    test('resolves hold_available to /reservations', () {
      final route = NotificationRouteHelper.resolveRoute({'type': 'hold_available'});
      expect(route, '/reservations');
    });

    test('resolves message to /messages/:id', () {
      final route = NotificationRouteHelper.resolveRoute({
        'type': 'message',
        'id': '42',
      });
      expect(route, '/messages/42');
    });

    test('resolves assignment to /assignments/:id', () {
      final route = NotificationRouteHelper.resolveRoute({
        'type': 'assignment',
        'id': '7',
      });
      expect(route, '/assignments/7');
    });

    test('resolves event to /events/:id', () {
      final route = NotificationRouteHelper.resolveRoute({
        'type': 'event',
        'id': '3',
      });
      expect(route, '/events/3');
    });

    test('resolves announcement to /announcements/:id', () {
      final route = NotificationRouteHelper.resolveRoute({
        'type': 'announcement',
        'id': '1',
      });
      expect(route, '/announcements/1');
    });

    test('resolves system to /notifications', () {
      final route = NotificationRouteHelper.resolveRoute({'type': 'system'});
      expect(route, '/notifications');
    });

    test('resolves unknown type to /notifications', () {
      final route = NotificationRouteHelper.resolveRoute({'type': 'unknown'});
      expect(route, '/notifications');
    });

    test('uses explicit route when provided', () {
      final route = NotificationRouteHelper.resolveRoute({
        'route': '/custom/route',
        'type': 'fine',
      });
      expect(route, '/custom/route');
    });

    test('resolves fine without id to /fines', () {
      final route = NotificationRouteHelper.resolveRoute({'type': 'fine'});
      expect(route, '/fines');
    });

    test('resolves message without id to /messages', () {
      final route = NotificationRouteHelper.resolveRoute({'type': 'message'});
      expect(route, '/messages');
    });
  });
}
