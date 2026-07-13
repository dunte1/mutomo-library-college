import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../features/auth/bloc/auth_bloc.dart';
import '../../features/auth/bloc/auth_state.dart';
import '../../features/auth/models/user_model.dart';
import '../../features/messaging/bloc/messaging_bloc.dart';
import '../helpers/permission_helper.dart';
import '../utils/responsive.dart';
import 'app_drawer.dart';

final GlobalKey<ScaffoldState> shellScaffoldKey = GlobalKey<ScaffoldState>();

class _TabItem {
  final String label;
  final IconData icon;
  final IconData selectedIcon;
  final String route;
  final bool Function(UserModel) isAllowed;

  const _TabItem({
    required this.label,
    required this.icon,
    required this.selectedIcon,
    required this.route,
    required this.isAllowed,
  });
}

final _allTabs = [
  _TabItem(
    label: 'Dashboard',
    icon: Icons.dashboard_outlined,
    selectedIcon: Icons.dashboard,
    route: '/dashboard',
    isAllowed: PermissionHelper.canAccessDashboard,
  ),
  _TabItem(
    label: 'Books',
    icon: Icons.menu_book_outlined,
    selectedIcon: Icons.menu_book,
    route: '/books',
    isAllowed: (_) => true,
  ),
  _TabItem(
    label: 'Loans',
    icon: Icons.library_books_outlined,
    selectedIcon: Icons.library_books,
    route: '/loans',
    isAllowed: PermissionHelper.canBorrowBooks,
  ),
  _TabItem(
    label: 'Messages',
    icon: Icons.mail_outlined,
    selectedIcon: Icons.mail,
    route: '/messages',
    isAllowed: PermissionHelper.canViewMessages,
  ),
  _TabItem(
    label: 'Digital',
    icon: Icons.auto_stories_outlined,
    selectedIcon: Icons.auto_stories,
    route: '/digital-library',
    isAllowed: PermissionHelper.canAccessDigitalLibrary,
  ),
  _TabItem(
    label: 'Profile',
    icon: Icons.person_outlined,
    selectedIcon: Icons.person,
    route: '/profile',
    isAllowed: (_) => true,
  ),
];

class BottomNavShell extends StatelessWidget {
  final Widget child;
  const BottomNavShell({super.key, required this.child});

  List<_TabItem> _allowedTabs(BuildContext context) {
    final user = _currentUser(context);
    return _allTabs.where((t) => t.isAllowed(user)).toList();
  }

  UserModel _currentUser(BuildContext context) {
    final authState = context.watch<AuthBloc>().state;
    if (authState is Authenticated) return authState.user;
    return UserModel(id: 0, name: '', email: '');
  }

  int _currentIndex(BuildContext context, List<_TabItem> tabs) {
    final location = GoRouterState.of(context).uri.toString();
    for (var i = 0; i < tabs.length; i++) {
      if (location.startsWith(tabs[i].route)) return i;
    }
    return 0;
  }

  void _onTap(BuildContext context, List<_TabItem> tabs, int index) {
    if (index >= 0 && index < tabs.length) {
      context.go(tabs[index].route);
    }
  }

  int _unreadCount(BuildContext context) {
    final state = context.watch<MessagingBloc>().state;
    if (state is MessagingLoaded) return state.unreadCount;
    return 0;
  }

  @override
  Widget build(BuildContext context) {
    final tabs = _allowedTabs(context);
    final selectedIndex = _currentIndex(context, tabs);
    final unread = _unreadCount(context);

    if (context.isCompact) {
      return Scaffold(
        key: shellScaffoldKey,
        body: child,
        drawer: const AppDrawer(),
        bottomNavigationBar: NavigationBar(
          selectedIndex: selectedIndex,
          onDestinationSelected: (i) => _onTap(context, tabs, i),
          destinations: tabs
              .map(
                (t) => NavigationDestination(
                  icon: t.label == 'Messages' && unread > 0
                      ? Badge(
                          label: Text(unread.toString()),
                          child: Icon(t.icon),
                        )
                      : Icon(t.icon),
                  selectedIcon: t.label == 'Messages' && unread > 0
                      ? Badge(
                          label: Text(unread.toString()),
                          child: Icon(t.selectedIcon),
                        )
                      : Icon(t.selectedIcon),
                  label: t.label,
                ),
              )
              .toList(),
        ),
      );
    }

    return Scaffold(
      body: Row(
        children: [
          NavigationRail(
            selectedIndex: selectedIndex,
            onDestinationSelected: (i) => _onTap(context, tabs, i),
            leading: Padding(
              padding: const EdgeInsets.symmetric(vertical: 8),
              child: Icon(
                Icons.local_library,
                size: 32,
                color: Theme.of(context).colorScheme.primary,
              ),
            ),
            labelType: context.isExpanded
                ? null
                : NavigationRailLabelType.none,
            extended: context.isExpanded,
            minExtendedWidth: 200,
            destinations: tabs
                .map(
                  (t) => NavigationRailDestination(
                    icon: t.label == 'Messages' && unread > 0
                        ? Badge(
                            label: Text(unread.toString()),
                            child: Icon(t.icon),
                          )
                        : Icon(t.icon),
                    selectedIcon: t.label == 'Messages' && unread > 0
                        ? Badge(
                            label: Text(unread.toString()),
                            child: Icon(t.selectedIcon),
                          )
                        : Icon(t.selectedIcon),
                    label: Text(t.label),
                  ),
                )
                .toList(),
          ),
          const VerticalDivider(width: 1),
          Expanded(child: child),
        ],
      ),
    );
  }
}
