import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../features/auth/bloc/auth_bloc.dart';
import '../../../features/auth/bloc/auth_event.dart';
import '../../../features/auth/bloc/auth_state.dart';
import '../../../features/auth/models/user_model.dart';
import '../../../features/messaging/bloc/messaging_bloc.dart';
import '../../../features/books/bloc/books_bloc.dart';
import '../../../features/books/bloc/books_event.dart';
import '../helpers/permission_helper.dart';

class AppDrawer extends StatelessWidget {
  const AppDrawer({super.key});

  UserModel? _user(BuildContext context) {
    final state = context.read<AuthBloc>().state;
    if (state is Authenticated) return state.user;
    return null;
  }

  int _unreadCount(BuildContext context) {
    final state = context.watch<MessagingBloc>().state;
    if (state is MessagingLoaded) return state.unreadCount;
    return 0;
  }

  @override
  Widget build(BuildContext context) {
    final user = _user(context);
    final theme = Theme.of(context);
    final unread = _unreadCount(context);

    return Drawer(
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          // Profile Card Header
          UserAccountsDrawerHeader(
            accountName: Text(
              user?.name ?? 'Guest',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
            accountEmail: Text(user?.email ?? ''),
            currentAccountPicture: CircleAvatar(
              backgroundColor: theme.colorScheme.primaryContainer,
              backgroundImage:
                  user?.avatar != null ? NetworkImage(user!.avatar!) : null,
              child: user?.avatar == null
                  ? Text(
                      (user?.name.isNotEmpty == true)
                          ? user!.name[0].toUpperCase()
                          : '?',
                      style: TextStyle(
                        fontSize: 24,
                        color: theme.colorScheme.primary,
                      ),
                    )
                  : null,
            ),
            otherAccountsPictures: [
              if (user != null)
                Text(
                  user.roles?.isNotEmpty == true
                      ? user.roles!.first.toUpperCase()
                      : '',
                  style: const TextStyle(
                    fontSize: 10,
                    color: Colors.white70,
                  ),
                ),
            ],
            decoration: BoxDecoration(
              color: theme.colorScheme.primaryContainer,
            ),
          ),

          // Student info if available
          if (user?.member != null) ...[
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (user!.member!['admission_number'] != null)
                    Text(
                      'ID: ${user.member!['admission_number']}',
                      style: theme.textTheme.bodySmall,
                    ),
                  if (user.department != null)
                    Text(
                      'Dept: ${user.department}',
                      style: theme.textTheme.bodySmall,
                    ),
                  if (user.program != null)
                    Text(
                      'Program: ${user.program}',
                      style: theme.textTheme.bodySmall,
                    ),
                ],
              ),
            ),
            const Divider(),
          ],

          _DrawerItem(
            icon: Icons.dashboard_outlined,
            title: 'Dashboard',
            onTap: () {
              Navigator.pop(context);
              context.goNamed('dashboard');
            },
          ),
          _DrawerItem(
            icon: Icons.menu_book_outlined,
            title: 'My Library',
            onTap: () {
              Navigator.pop(context);
              context.goNamed('books');
              context.read<BooksBloc>().add(const LoadBooks());
            },
          ),
          _DrawerItem(
            icon: Icons.auto_stories_outlined,
            title: 'Digital Library',
            onTap: () {
              Navigator.pop(context);
              context.goNamed('digital-library');
            },
          ),
          if (user != null && PermissionHelper.canViewAssignments(user))
            _DrawerItem(
              icon: Icons.assignment_outlined,
              title: 'Reading Assignments',
              onTap: () {
                Navigator.pop(context);
                context.goNamed('assignments');
              },
            ),
          _DrawerItem(
            icon: Icons.recommend_outlined,
            title: 'Recommendations',
            onTap: () {
              Navigator.pop(context);
              context.goNamed('recommendations');
            },
          ),
          _DrawerItem(
            icon: Icons.bookmark_border,
            title: 'Bookmarks',
            onTap: () {
              Navigator.pop(context);
              context.goNamed('bookmarks');
            },
          ),
          _DrawerItem(
            icon: Icons.download_outlined,
            title: 'Downloads',
            onTap: () {
              Navigator.pop(context);
              context.goNamed('downloaded-assets');
            },
          ),
          _DrawerItem(
            icon: Icons.credit_card_outlined,
            title: 'Library Card',
            onTap: () {
              Navigator.pop(context);
              context.goNamed('library-card');
            },
          ),
          _DrawerItem(
            icon: Icons.bookmark_add_outlined,
            title: 'Reservations',
            onTap: () {
              Navigator.pop(context);
              context.goNamed('reservations');
            },
          ),
          _DrawerItem(
            icon: Icons.history,
            title: 'Loan History',
            onTap: () {
              Navigator.pop(context);
              context.goNamed('loan-history');
            },
          ),
          const Divider(),
          _DrawerItem(
            icon: Icons.mail_outlined,
            title: 'Messages',
            badge: unread > 0 ? unread : null,
            onTap: () {
              Navigator.pop(context);
              context.goNamed('messages');
            },
          ),
          _DrawerItem(
            icon: Icons.notifications_outlined,
            title: 'Notifications',
            onTap: () {
              Navigator.pop(context);
              context.goNamed('notifications');
            },
          ),
          _DrawerItem(
            icon: Icons.event_outlined,
            title: 'Events',
            onTap: () {
              Navigator.pop(context);
              context.goNamed('events');
            },
          ),
          _DrawerItem(
            icon: Icons.campaign_outlined,
            title: 'Announcements',
            onTap: () {
              Navigator.pop(context);
              context.goNamed('announcements');
            },
          ),
          const Divider(),
          _DrawerItem(
            icon: Icons.person_outlined,
            title: 'Profile',
            onTap: () {
              Navigator.pop(context);
              context.goNamed('profile');
            },
          ),
          _DrawerItem(
            icon: Icons.settings_outlined,
            title: 'Settings',
            onTap: () {
              Navigator.pop(context);
              context.goNamed('settings');
            },
          ),
          _DrawerItem(
            icon: Icons.help_outline,
            title: 'Help',
            onTap: () {
              Navigator.pop(context);
              context.goNamed('help');
            },
          ),
          _DrawerItem(
            icon: Icons.info_outline,
            title: 'About',
            onTap: () {
              Navigator.pop(context);
              context.goNamed('about');
            },
          ),
          const Divider(),
          _DrawerItem(
            icon: Icons.logout,
            title: 'Logout',
            color: theme.colorScheme.error,
            onTap: () {
              Navigator.pop(context);
              _showLogoutDialog(context);
            },
          ),
        ],
      ),
    );
  }

  void _showLogoutDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Logout'),
        content: const Text('Are you sure you want to logout?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () {
              Navigator.pop(ctx);
              context.read<AuthBloc>().add(const LogoutEvent());
            },
            child: const Text('Logout'),
          ),
        ],
      ),
    );
  }
}

class _DrawerItem extends StatelessWidget {
  final IconData icon;
  final String title;
  final VoidCallback onTap;
  final int? badge;
  final Color? color;

  const _DrawerItem({
    required this.icon,
    required this.title,
    required this.onTap,
    this.badge,
    this.color,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Icon(icon, color: color),
      title: Text(
        title,
        style: TextStyle(color: color, fontWeight: FontWeight.w500),
      ),
      trailing: badge != null
          ? Badge(
              label: Text('$badge'),
              backgroundColor: Theme.of(context).colorScheme.error,
            )
          : null,
      onTap: onTap,
    );
  }
}
