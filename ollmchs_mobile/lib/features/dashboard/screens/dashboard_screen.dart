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
    final cs = theme.colorScheme;
    final authState = context.read<AuthBloc>().state;
    final user = authState is Authenticated ? authState.user : UserModel(id: 0, name: '', email: '');
    final greeting = _greeting(user);
    return SafeArea(
      child: Column(
        children: [
          // Greeting header
          Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [cs.primary.withValues(alpha: 0.08), cs.primary.withValues(alpha: 0.02)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              border: Border(bottom: BorderSide(color: theme.dividerColor)),
            ),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
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
                          style: theme.textTheme.bodySmall?.copyWith(
                            color: cs.onSurface.withValues(alpha: 0.6),
                          ),
                        ),
                        Text(
                          user.name,
                          style: theme.textTheme.titleLarge?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    decoration: BoxDecoration(
                      color: cs.surfaceContainerHighest.withValues(alpha: 0.6),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: IconButton(
                      icon: const Icon(Icons.notifications_outlined),
                      onPressed: () => context.goNamed('notifications'),
                    ),
                  ),
                  const SizedBox(width: 4),
                  Container(
                    decoration: BoxDecoration(
                      color: cs.surfaceContainerHighest.withValues(alpha: 0.6),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: IconButton(
                      icon: const Icon(Icons.person_outline),
                      onPressed: () => context.goNamed('profile'),
                    ),
                  ),
                ],
              ),
            ),
          ),
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
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: cs.error.withValues(alpha: 0.1),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(Icons.error_outline, size: 40, color: cs.error),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      state.error,
                      textAlign: TextAlign.center,
                      style: theme.textTheme.bodyMedium?.copyWith(
                        color: cs.onSurface.withValues(alpha: 0.7),
                      ),
                    ),
                    const SizedBox(height: 20),
                    FilledButton.tonal(
                      style: FilledButton.styleFrom(
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                      ),
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
                      // Stat grid
                      GridView.count(
                        crossAxisCount: context.responsiveGridColumns(),
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        crossAxisSpacing: 10,
                        mainAxisSpacing: 10,
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

                      // Pending fines
                      if (dash.pendingFines > 0) ...[
                        const SizedBox(height: 12),
                        Card(
                          elevation: 0,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                            side: BorderSide(color: cs.error.withValues(alpha: 0.3)),
                          ),
                          clipBehavior: Clip.antiAlias,
                          child: InkWell(
                            onTap: () => context.goNamed('fines'),
                            child: Container(
                              decoration: BoxDecoration(
                                border: Border(left: BorderSide(color: cs.error, width: 3)),
                              ),
                              child: Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                                child: Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.all(8),
                                      decoration: BoxDecoration(
                                        color: cs.error.withValues(alpha: 0.1),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Icon(Icons.money_off, color: cs.error, size: 22),
                                    ),
                                    const SizedBox(width: 14),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            'KES ${dash.totalFines.toStringAsFixed(0)} in pending fines',
                                            style: theme.textTheme.bodyMedium?.copyWith(
                                              fontWeight: FontWeight.w600,
                                            ),
                                          ),
                                          Text(
                                            '${dash.pendingFines} unpaid fine(s)',
                                            style: theme.textTheme.bodySmall?.copyWith(
                                              color: cs.onSurface.withValues(alpha: 0.5),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    Icon(Icons.chevron_right, color: cs.onSurface.withValues(alpha: 0.4)),
                                  ],
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],

                      const SizedBox(height: 20),

                      // Quick Actions
                      _SectionHeader(title: 'Quick Actions'),
                      const SizedBox(height: 10),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          if (PermissionHelper.isStudentOrLecturer(user) ||
                              PermissionHelper.isStaff(user))
                            _ActionChip(
                              icon: Icons.search,
                              label: 'Search Books',
                              color: cs.primary,
                              onTap: () => context.goNamed('books-search'),
                            ),
                          _ActionChip(
                            icon: Icons.category,
                            label: 'Categories',
                            color: Colors.teal,
                            onTap: () => context.goNamed('categories'),
                          ),
                          _ActionChip(
                            icon: Icons.fiber_new,
                            label: 'New Arrivals',
                            color: Colors.pink,
                            onTap: () => context.goNamed('books-new-arrivals'),
                          ),
                          _ActionChip(
                            icon: Icons.person,
                            label: 'Authors',
                            color: Colors.indigo,
                            onTap: () => context.goNamed('authors'),
                          ),
                          _ActionChip(
                            icon: Icons.business,
                            label: 'Publishers',
                            color: Colors.brown,
                            onTap: () => context.goNamed('publishers'),
                          ),
                          if (PermissionHelper.canBorrowBooks(user))
                            _ActionChip(
                              icon: Icons.loop,
                              label: 'My Loans',
                              color: Colors.green,
                              onTap: () => context.goNamed('loans'),
                            ),
                          if (PermissionHelper.isStudent(user))
                            _ActionChip(
                              icon: Icons.bookmark,
                              label: 'Reservations',
                              color: Colors.orange,
                              onTap: () => context.goNamed('reservations'),
                            ),
                          if (PermissionHelper.isStudent(user))
                            _ActionChip(
                              icon: Icons.badge,
                              label: 'Library Card',
                              color: Colors.purple,
                              onTap: () => context.goNamed('library-card'),
                            ),
                          if (PermissionHelper.canAccessDigitalLibrary(user))
                            _ActionChip(
                              icon: Icons.devices,
                              label: 'Digital Library',
                              color: Colors.indigo,
                              onTap: () => context.goNamed('digital-library'),
                            ),
                          if (PermissionHelper.isStudent(user))
                            _ActionChip(
                              icon: Icons.card_membership,
                              label: 'Subscriptions',
                              color: Colors.amber,
                              onTap: () => context.goNamed('subscriptions'),
                            ),
                          if (PermissionHelper.isLecturer(user)) ...[
                            _ActionChip(
                              icon: Icons.assignment,
                              label: 'My Assignments',
                              color: Colors.cyan,
                              onTap: () => context.goNamed('assignments'),
                            ),
                            _ActionChip(
                              icon: Icons.create_new_folder,
                              label: 'Manage',
                              color: Colors.deepOrange,
                              onTap: () => context.goNamed('teacher-assignments'),
                            ),
                          ],
                          if (PermissionHelper.canViewMessages(user))
                            _ActionChip(
                              icon: Icons.email,
                              label: 'Messages',
                              color: Colors.blue,
                              onTap: () => context.goNamed('messages'),
                            ),
                        ],
                      ),

                      // Due Soon
                      if (dash.dueSoonBooks.isNotEmpty) ...[
                        const SizedBox(height: 24),
                        _SectionHeader(title: 'Due Soon'),
                        const SizedBox(height: 10),
                        SizedBox(
                          height: 130,
                          child: ListView.separated(
                            scrollDirection: Axis.horizontal,
                            itemCount: dash.dueSoonBooks.length,
                            separatorBuilder: (_, __) => const SizedBox(width: 10),
                            itemBuilder: (_, i) {
                              final item = dash.dueSoonBooks[i];
                              return _CarouselCard(
                                title: item.title,
                                subtitle: item.description,
                                color: Colors.red,
                              );
                            },
                          ),
                        ),
                      ],

                      // Featured
                      if (dash.featuredBooks.isNotEmpty) ...[
                        const SizedBox(height: 24),
                        _SectionHeader(title: 'Featured'),
                        const SizedBox(height: 10),
                        SizedBox(
                          height: 130,
                          child: ListView.separated(
                            scrollDirection: Axis.horizontal,
                            itemCount: dash.featuredBooks.length,
                            separatorBuilder: (_, __) => const SizedBox(width: 10),
                            itemBuilder: (_, i) {
                              final item = dash.featuredBooks[i];
                              return _CarouselCard(
                                title: item.title,
                                subtitle: item.description,
                                color: cs.primary,
                              );
                            },
                          ),
                        ),
                      ],

                      // Announcements
                      if (dash.recentAnnouncements.isNotEmpty) ...[
                        const SizedBox(height: 24),
                        _SectionHeader(title: 'Announcements'),
                        const SizedBox(height: 10),
                        ...dash.recentAnnouncements.take(3).map(
                          (a) => _ListItemCard(
                            icon: Icons.campaign,
                            iconColor: Colors.blue,
                            title: a.title,
                            subtitle: a.description,
                            onTap: () => context.goNamed('announcements'),
                          ),
                        ),
                      ],

                      // New Digital Assets
                      if (dash.recentDigitalAssets.isNotEmpty) ...[
                        const SizedBox(height: 24),
                        _SectionHeader(title: 'New Digital Assets'),
                        const SizedBox(height: 10),
                        SizedBox(
                          height: 130,
                          child: ListView.separated(
                            scrollDirection: Axis.horizontal,
                            itemCount: dash.recentDigitalAssets.length,
                            separatorBuilder: (_, __) => const SizedBox(width: 10),
                            itemBuilder: (_, i) {
                              final item = dash.recentDigitalAssets[i];
                              return _CarouselCard(
                                title: item.title,
                                icon: item.description == 'pdf'
                                    ? Icons.picture_as_pdf
                                    : Icons.insert_drive_file,
                                color: cs.primary,
                              );
                            },
                          ),
                        ),
                      ],

                      // Upcoming Events
                      if (dash.upcomingEvents.isNotEmpty) ...[
                        const SizedBox(height: 24),
                        _SectionHeader(title: 'Upcoming Events'),
                        const SizedBox(height: 10),
                        ...dash.upcomingEvents.take(3).map(
                          (e) => _ListItemCard(
                            icon: Icons.event,
                            iconColor: Colors.green,
                            title: e.title,
                            subtitle: e.description,
                            onTap: () => context.goNamed('events'),
                          ),
                        ),
                      ],

                      // Borrowing limit
                      const SizedBox(height: 24),
                      Card(
                        elevation: 0,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                          side: BorderSide(color: theme.dividerColor.withValues(alpha: 0.5)),
                        ),
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  gradient: LinearGradient(
                                    colors: [cs.primary.withValues(alpha: 0.2), cs.primary.withValues(alpha: 0.05)],
                                    begin: Alignment.topLeft,
                                    end: Alignment.bottomRight,
                                  ),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Icon(Icons.bookmark, color: cs.primary, size: 22),
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Borrowing: ${dash.activeLoans}/${dash.borrowLimit} slots used',
                                      style: theme.textTheme.bodyMedium?.copyWith(
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    ClipRRect(
                                      borderRadius: BorderRadius.circular(4),
                                      child: LinearProgressIndicator(
                                        value: dash.borrowLimit > 0
                                            ? dash.activeLoans / dash.borrowLimit
                                            : 0,
                                        minHeight: 6,
                                        backgroundColor: cs.surfaceContainerHighest,
                                        valueColor: AlwaysStoppedAnimation<Color>(
                                          dash.activeLoans / (dash.borrowLimit > 0 ? dash.borrowLimit : 1) > 0.8
                                              ? cs.error
                                              : cs.primary,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
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

class _SectionHeader extends StatelessWidget {
  final String title;
  const _SectionHeader({required this.title});

  @override
  Widget build(BuildContext context) {
    final cs = Theme.of(context).colorScheme;
    return Row(
      children: [
        Container(
          width: 3,
          height: 18,
          decoration: BoxDecoration(
            color: cs.primary,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 8),
        Text(
          title,
          style: Theme.of(context).textTheme.titleMedium?.copyWith(
            fontWeight: FontWeight.bold,
          ),
        ),
      ],
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
    final theme = Theme.of(context);
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(14),
        side: BorderSide(color: theme.dividerColor.withValues(alpha: 0.4)),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(width: 12),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  value,
                  style: theme.textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Text(
                  label,
                  style: theme.textTheme.bodySmall?.copyWith(
                    color: theme.colorScheme.onSurface.withValues(alpha: 0.6),
                  ),
                ),
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
  final Color color;
  final VoidCallback onTap;

  const _ActionChip({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final cs = Theme.of(context).colorScheme;
    return ActionChip(
      avatar: Icon(icon, size: 16, color: color),
      label: Text(label, style: TextStyle(fontSize: 13, color: cs.onSurface)),
      backgroundColor: color.withValues(alpha: 0.08),
      side: BorderSide(color: color.withValues(alpha: 0.2)),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      onPressed: onTap,
    );
  }
}

class _CarouselCard extends StatelessWidget {
  final String title;
  final String? subtitle;
  final Color color;
  final IconData? icon;

  const _CarouselCard({
    required this.title,
    this.subtitle,
    required this.color,
    this.icon,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(14),
        side: BorderSide(color: theme.dividerColor.withValues(alpha: 0.3)),
      ),
      child: SizedBox(
        width: 160,
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              if (icon != null) ...[
                Icon(icon, size: 32, color: color),
                const SizedBox(height: 8),
              ],
              Text(
                title,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                textAlign: TextAlign.center,
                style: theme.textTheme.bodyMedium?.copyWith(
                  fontWeight: FontWeight.w600,
                ),
              ),
              if (subtitle != null) ...[
                const SizedBox(height: 4),
                Text(
                  subtitle!,
                  style: theme.textTheme.bodySmall?.copyWith(
                    color: theme.colorScheme.onSurface.withValues(alpha: 0.5),
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  textAlign: TextAlign.center,
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _ListItemCard extends StatelessWidget {
  final IconData icon;
  final Color iconColor;
  final String title;
  final String? subtitle;
  final VoidCallback onTap;

  const _ListItemCard({
    required this.icon,
    required this.iconColor,
    required this.title,
    this.subtitle,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final cs = theme.colorScheme;
    return Card(
      elevation: 0,
      margin: const EdgeInsets.only(bottom: 8),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: theme.dividerColor.withValues(alpha: 0.3)),
      ),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: iconColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(icon, color: iconColor, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: theme.textTheme.bodyMedium?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    if (subtitle != null) ...[
                      const SizedBox(height: 2),
                      Text(
                        subtitle!,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: theme.textTheme.bodySmall?.copyWith(
                          color: cs.onSurface.withValues(alpha: 0.5),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              Icon(Icons.chevron_right, size: 18, color: cs.onSurface.withValues(alpha: 0.3)),
            ],
          ),
        ),
      ),
    );
  }
}
