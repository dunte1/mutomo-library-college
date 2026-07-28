import 'package:flutter_test/flutter_test.dart';
import 'package:go_router/go_router.dart';
import 'package:ollmchs_library/core/routing/app_router.dart';

/// All shell route base paths (the routes that should exist in GoRouter config).
const _expectedShellPaths = <String>{
  '/dashboard',
  '/categories',
  '/books',
  '/loans',
  '/reservations',
  '/fines',
  '/library-card',
  '/digital-library',
  '/messages',
  '/notifications',
  '/notifications/:id',
  '/profile',
  '/assignments',
  '/teacher-assignments',
  '/subscriptions',
  '/bulletins',
  '/announcements',
  '/events',
  '/authors',
  '/publishers',
  '/payments',
  '/reports',
  '/scanner',
  '/bookmarks',
  '/my-courses',
  '/student-progress',
  '/assignment-analytics',
  '/help',
  '/about',
};

const _expectedPreAuthPaths = <String>{
  '/splash',
  '/onboarding',
  '/login',
  '/register',
  '/forgot-password',
  '/two-factor',
  '/two-factor-setup',
  '/verify-email',
};

/// Recursively collect all GoRoute paths from a list of RouteBase.
Set<String> _collectPaths(List<RouteBase> routes) {
  final paths = <String>{};
  for (final route in routes) {
    if (route is GoRoute) {
      if (route.path != null) paths.add(route.path!);
      paths.addAll(_collectPaths(route.routes));
    } else if (route is ShellRoute) {
      paths.addAll(_collectPaths(route.routes));
    }
  }
  return paths;
}

Set<String> _collectTopLevelPaths() {
  final routes = AppRouter.router.configuration.routes;
  final all = _collectPaths(routes);
  return all.where((p) => p.startsWith('/')).toSet();
}

void main() {
  group('Router construction', () {
    test('AppRouter.router compiles and constructs without exception', () {
      expect(AppRouter.router, isA<GoRouter>());
    });
  });

  group('Pre-auth route paths exist', () {
    test('all expected pre-auth paths are present', () {
      final actual = _collectTopLevelPaths();
      final missing = _expectedPreAuthPaths.difference(actual);
      expect(missing, isEmpty, reason: 'Missing pre-auth routes: $missing');
    });
  });

  group('Shell route paths exist', () {
    test('all expected shell paths are present', () {
      final actual = _collectTopLevelPaths();
      final missing = _expectedShellPaths.difference(actual);
      expect(missing, isEmpty, reason: 'Missing shell routes: $missing');
    });
  });

  group('No stale/unexpected routes', () {
    test('every configured path is expected (detects stale routes)', () {
      final allExpected = _expectedShellPaths.union(_expectedPreAuthPaths);
      final actual = _collectTopLevelPaths();
      final unexpected = actual.difference(allExpected);
      expect(unexpected, isEmpty,
          reason: 'Unexpected routes (may need to be added to test): $unexpected');
    });
  });
}
