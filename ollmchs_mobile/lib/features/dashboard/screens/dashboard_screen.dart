import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../auth/bloc/auth_bloc.dart';
import '../../auth/bloc/auth_state.dart';
import '../../auth/models/user_model.dart';
import '../bloc/dashboard_bloc.dart';
import '../../../core/helpers/permission_helper.dart';
import '../../../core/widgets/skeleton.dart';
import '../../../core/utils/responsive.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  @override
  void initState() {
    super.initState();
    context.read<DashboardBloc>().add(const LoadDashboard());
  }

  String _greeting(UserModel user) {
    final hour = DateTime.now().hour;
    final timeGreeting = hour < 12
        ? 'Good morning'
        : hour < 17
        ? 'Good afternoon'
        : 'Good evening';
    return user.name.isNotEmpty ? '$timeGreeting, ${user.name}' : timeGreeting;
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final authState = context.read<AuthBloc>().state;
    final user = authState is Authenticated ? authState.user : UserModel(id: 0, name: '', email: '');
    final greeting = _greeting(user);
    return SafeArea(
      child: Column(
        children: [
          // Custom AppBar area
          Material(
            color: theme.colorScheme.surface,
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 4),
              child: Row(
                children: [
                  Builder(
                    builder: (btnContext) => IconButton(
                      icon: const Icon(Icons.menu),
                      onPressed: () => Scaffold.of(btnContext).openDrawer(),
                    ),
                  ),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          greeting,
                          style: theme.textTheme.titleSmall?.copyWith(
                            fontWeight: FontWeight.normal,
                          ),
                        ),
                        Text(
                          user.name,
                          style: theme.textTheme.titleMedium?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.notifications_outlined),
                    onPressed: () => context.goNamed('notifications'),
                  ),
                  IconButton(
                    icon: const Icon(Icons.person_outline),
                    onPressed: () => context.goNamed('profile'),
                  ),
                ],
              ),
            ),
          ),
          Divider(height: 1, color: theme.dividerColor),
          // Body
          Expanded(
            child: ResponsiveCenter(
              child: BlocBuilder<DashboardBloc, DashboardState>(
                builder: (context, state) {
            if (state is DashboardLoading) {
              return Padding(
                padding: context.responsivePadding,
                child: Column(
                  children: [
                    const Row(
                      children: [
                        Expanded(child: Skeleton(height: 80)),
                        SizedBox(width: 8),
                        Expanded(child: Skeleton(height: 80)),
                      ],
                    ),
                    const SizedBox(height: 8),
                    const Row(
                      children: [
                        Expanded(child: Skeleton(height: 80)),
                        SizedBox(width: 8),
                        Expanded(child: Skeleton(height: 80)),
                      ],
                    ),
                    const SizedBox(height: 16),
                    const Skeleton(height: 60),
                    const SizedBox(height: 16),
                    const Skeleton(height: 20, width: 120),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      children: List.generate(
                        6,
                        (_) => const Skeleton(
                          width: 100,
                          height: 36,
                          borderRadius: 18,
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    const Skeleton(height: 20, width: 100),
                    const SizedBox(height: 8),
                    SizedBox(
                      height: 120,
                      child: ListView.separated(
                        scrollDirection: Axis.horizontal,
                        itemCount: 3,
                        separatorBuilder: (_, __) => const SizedBox(width: 8),
                        itemBuilder: (_, __) =>
                            const Skeleton(width: 150, height: 120),
                      ),
                    ),
                  ],
                ),
              );
            }
            if (state is DashboardError) {
              return Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.error_outline,
                      size: 48,
                      color: theme.colorScheme.error,
                    ),
                    const SizedBox(height: 8),
                    Text(state.error, textAlign: TextAlign.center),
                    const SizedBox(height: 16),
                    FilledButton.tonal(
                      onPressed: () => context.read<DashboardBloc>().add(
                        const LoadDashboard(),
                      ),
                      child: const Text('Retry'),
                    ),
                  ],
                ),
              );
            }
            if (state is DashboardLoaded) {
              final dash = state.dashboard;
              return RefreshIndicator(
                onRefresh: () async {
                  context.read<DashboardBloc>().add(const LoadDashboard());
                },
                child: SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: context.responsivePadding,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      GridView.count(
                        crossAxisCount: context.responsiveGridColumns(),
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        crossAxisSpacing: 8,
                        mainAxisSpacing: 8,
                        childAspectRatio: 2.5,
                        children: [
                          _StatCard(
                            icon: Icons.menu_book,
                            label: 'Books',
                            value: '${dash.totalBooks}',
                            color: Colors.blue,
                          ),
                          _StatCard(
                            icon: Icons.loop,
                            label: 'Active Loans',
                            value: '${dash.activeLoans}',
                            color: Colors.green,
                          ),
                          _StatCard(
                            icon: Icons.warning_amber,
                            label: 'Overdue',
                            value: '${dash.overdueLoans}',
                            color: Colors.red,
                          ),
                          _StatCard(
                            icon: Icons.devices,
                            label: 'Digital',
                            value: '${dash.digitalAssets}',
                            color: Colors.purple,
                          ),
                          if (dash.availableBooks > 0)
                            _StatCard(
                              icon: Icons.check_circle_outline,
                              label: 'Available',
                              value: '${dash.availableBooks}',
                              color: Colors.teal,
                            ),
                          if (dash.activeReservations > 0)
                            _StatCard(
                              icon: Icons.bookmark,
                              label: 'Reservations',
                              value: '${dash.activeReservations}',
                              color: Colors.orange,
                            ),
                          if (dash.unreadNotifications > 0)
                            _StatCard(
                              icon: Icons.notifications_active,
                              label: 'Alerts',
                              value: '${dash.unreadNotifications}',
                              color: Colors.amber,
                            ),
                          if (dash.unreadMessages > 0)
                            _StatCard(
                              icon: Icons.mail,
                              label: 'Messages',
                              value: '${dash.unreadMessages}',
                              color: Colors.indigo,
                            ),
                        ],
                      ),
                      if (dash.pendingFines > 0) ...[
                        const SizedBox(height: 8),
                        Card(
                          color: theme.colorScheme.errorContainer,
                          child: ListTile(
                            leading: const Icon(Icons.money_off),
                            title: Text(
                              'KES ${dash.totalFines.toStringAsFixed(0)} in pending fines',
                            ),
                            subtitle: Text('${dash.pendingFines} unpaid fine(s)'),
                            trailing: const Icon(Icons.chevron_right),
                            onTap: () => context.goNamed('fines'),
                          ),
                        ),
                      ],
                      const SizedBox(height: 16),

                      Text(
                        'Quick Actions',
                        style: theme.textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          if (PermissionHelper.isStudentOrLecturer(user) ||
                              PermissionHelper.isStaff(user))
                            _ActionChip(
                              icon: Icons.search,
                              label: 'Search Books',
                              onTap: () => context.goNamed('books-search'),
                            ),
                          _ActionChip(
                            icon: Icons.category,
                            label: 'Categories',
                            onTap: () => context.goNamed('categories'),
                          ),
                          _ActionChip(
                            icon: Icons.fiber_new,
                            label: 'New Arrivals',
                            onTap: () => context.goNamed('books-new-arrivals'),
                          ),
                          _ActionChip(
                            icon: Icons.person,
                            label: 'Authors',
                            onTap: () => context.goNamed('authors'),
                          ),
                          _ActionChip(
                            icon: Icons.business,
                            label: 'Publishers',
                            onTap: () => context.goNamed('publishers'),
                          ),
                          if (PermissionHelper.canBorrowBooks(user))
                            _ActionChip(
                              icon: Icons.loop,
                              label: 'My Loans',
                              onTap: () => context.goNamed('loans'),
                            ),
                          if (PermissionHelper.isStudent(user))
                            _ActionChip(
                              icon: Icons.bookmark,
                              label: 'Reservations',
                              onTap: () => context.goNamed('reservations'),
                            ),
                          if (PermissionHelper.isStudent(user))
                            _ActionChip(
                              icon: Icons.badge,
                              label: 'Library Card',
                              onTap: () => context.goNamed('library-card'),
                            ),
                          if (PermissionHelper.canAccessDigitalLibrary(user))
                            _ActionChip(
                              icon: Icons.devices,
                              label: 'Digital Library',
                              onTap: () => context.goNamed('digital-library'),
                            ),
                          if (PermissionHelper.isStudent(user))
                            _ActionChip(
                              icon: Icons.card_membership,
                              label: 'Subscriptions',
                              onTap: () => context.goNamed('subscriptions'),
                            ),
                          if (PermissionHelper.isLecturer(user)) ...[
                            _ActionChip(
                              icon: Icons.assignment,
                              label: 'My Assignments',
                              onTap: () => context.goNamed('assignments'),
                            ),
                            _ActionChip(
                              icon: Icons.create_new_folder,
                              label: 'Manage',
                              onTap: () => context.goNamed('teacher-assignments'),
                            ),
                          ],
                          if (PermissionHelper.canViewMessages(user))
                            _ActionChip(
                              icon: Icons.email,
                              label: 'Messages',
                              onTap: () => context.goNamed('messages'),
                            ),
                        ],
                      ),
                      const SizedBox(height: 16),

                      if (dash.dueSoonBooks.isNotEmpty) ...[
                        Text(
                          'Due Soon',
                          style: theme.textTheme.titleMedium?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        SizedBox(
                          height: 120,
                          child: ListView.separated(
                            scrollDirection: Axis.horizontal,
                            itemCount: dash.dueSoonBooks.length,
                            separatorBuilder: (_, __) => const SizedBox(width: 8),
                            itemBuilder: (_, i) {
                              final item = dash.dueSoonBooks[i];
                              return Card(
                                child: SizedBox(
                                  width: 150,
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        item.title,
                                        maxLines: 2,
                                        overflow: TextOverflow.ellipsis,
                                        textAlign: TextAlign.center,
                                      ),
                                      const SizedBox(height: 4),
                                      if (item.description != null)
                                        Text(
                                          item.description!,
                                          style: theme.textTheme.bodySmall,
                                          maxLines: 1,
                                        ),
                                    ],
                                  ),
                                ),
                              );
                            },
                          ),
                        ),
                      ],
                      if (dash.featuredBooks.isNotEmpty) ...[
                        const SizedBox(height: 16),
                        Text(
                          'Featured',
                          style: theme.textTheme.titleMedium?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        SizedBox(
                          height: 120,
                          child: ListView.separated(
                            scrollDirection: Axis.horizontal,
                            itemCount: dash.featuredBooks.length,
                            separatorBuilder: (_, __) => const SizedBox(width: 8),
                            itemBuilder: (_, i) {
                              final item = dash.featuredBooks[i];
                              return Card(
                                child: SizedBox(
                                  width: 150,
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        item.title,
                                        maxLines: 2,
                                        overflow: TextOverflow.ellipsis,
                                        textAlign: TextAlign.center,
                                      ),
                                      const SizedBox(height: 4),
                                      if (item.description != null)
                                        Text(
                                          item.description!,
                                          style: theme.textTheme.bodySmall,
                                          maxLines: 1,
                                        ),
                                    ],
                                  ),
                                ),
                              );
                            },
                          ),
                        ),
                      ],

                      // Announcements section
                      if (dash.recentAnnouncements.isNotEmpty) ...[
                        const SizedBox(height: 16),
                        Text(
                          'Announcements',
                          style: theme.textTheme.titleMedium?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        ...dash.recentAnnouncements.take(3).map(
                          (a) => Card(
                            child: ListTile(
                              leading: const Icon(Icons.campaign, color: Colors.blue),
                              title: Text(a.title, maxLines: 1, overflow: TextOverflow.ellipsis),
                              subtitle: a.description != null
                                  ? Text(a.description!, maxLines: 1, overflow: TextOverflow.ellipsis)
                                  : null,
                              trailing: const Icon(Icons.chevron_right),
                              onTap: () => context.goNamed('announcements'),
                            ),
                          ),
                        ),
                      ],

                      // Recent Digital Assets section
                      if (dash.recentDigitalAssets.isNotEmpty) ...[
                        const SizedBox(height: 16),
                        Text(
                          'New Digital Assets',
                          style: theme.textTheme.titleMedium?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        SizedBox(
                          height: 120,
                          child: ListView.separated(
                            scrollDirection: Axis.horizontal,
                            itemCount: dash.recentDigitalAssets.length,
                            separatorBuilder: (_, __) => const SizedBox(width: 8),
                            itemBuilder: (_, i) {
                              final item = dash.recentDigitalAssets[i];
                              return Card(
                                child: SizedBox(
                                  width: 150,
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(
                                        item.description == 'pdf'
                                            ? Icons.picture_as_pdf
                                            : Icons.insert_drive_file,
                                        size: 32,
                                        color: theme.colorScheme.primary,
                                      ),
                                      const SizedBox(height: 8),
                                      Text(
                                        item.title,
                                        maxLines: 2,
                                        overflow: TextOverflow.ellipsis,
                                        textAlign: TextAlign.center,
                                        style: const TextStyle(fontSize: 12),
                                      ),
                                    ],
                                  ),
                                ),
                              );
                            },
                          ),
                        ),
                      ],

                      // Upcoming Events section
                      if (dash.upcomingEvents.isNotEmpty) ...[
                        const SizedBox(height: 16),
                        Text(
                          'Upcoming Events',
                          style: theme.textTheme.titleMedium?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        ...dash.upcomingEvents.take(3).map(
                          (e) => Card(
                            child: ListTile(
                              leading: const Icon(Icons.event, color: Colors.green),
                              title: Text(e.title, maxLines: 1, overflow: TextOverflow.ellipsis),
                              subtitle: e.description != null
                                  ? Text(e.description!, maxLines: 1, overflow: TextOverflow.ellipsis)
                                  : null,
                              trailing: const Icon(Icons.chevron_right),
                              onTap: () => context.goNamed('events'),
                            ),
                          ),
                        ),
                      ],

                      // Borrowing limit indicator
                      const SizedBox(height: 16),
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(12),
                          child: Row(
                            children: [
                              Icon(Icons.bookmark, color: theme.colorScheme.primary),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Borrowing: ${dash.activeLoans}/${dash.borrowLimit} slots used',
                                      style: theme.textTheme.bodyMedium?.copyWith(
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    LinearProgressIndicator(
                                      value: dash.borrowLimit > 0
                                          ? dash.activeLoans / dash.borrowLimit
                                          : 0,
                                      backgroundColor: theme.colorScheme.surfaceContainerHighest,
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }
            return const SizedBox.shrink();
          },
        ),
      ),
          ),
        ],
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final Color color;

  const _StatCard({
    required this.icon,
    required this.label,
    required this.value,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(icon, color: color),
            ),
            const SizedBox(width: 12),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  value,
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Text(label, style: Theme.of(context).textTheme.bodySmall),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _ActionChip extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  const _ActionChip({
    required this.icon,
    required this.label,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return ActionChip(
      avatar: Icon(icon, size: 18),
      label: Text(label),
      onPressed: onTap,
    );
  }
}
