import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/communication_bloc.dart';
import '../models/announcement_model.dart';
import '../../../core/widgets/skeleton.dart';

class AnnouncementListScreen extends StatefulWidget {
  const AnnouncementListScreen({super.key});

  @override
  State<AnnouncementListScreen> createState() => _AnnouncementListScreenState();
}

class _AnnouncementListScreenState extends State<AnnouncementListScreen> {
  @override
  void initState() {
    super.initState();
    context.read<CommunicationBloc>().add(const LoadAnnouncements());
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Announcements')),
      body: BlocBuilder<CommunicationBloc, CommunicationState>(
        builder: (context, state) {
          if (state is CommunicationLoading && state is! CommunicationLoaded) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [
                  SkeletonCard(height: 100),
                  SkeletonCard(height: 100),
                  SkeletonCard(height: 100),
                ],
              ),
            );
          }
          if (state is CommunicationError && state is! CommunicationLoaded) {
            return Center(child: Text(state.error));
          }
          final announcements = state is CommunicationLoaded
              ? state.announcements
              : <AnnouncementModel>[];
          if (announcements.isEmpty) {
            return Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    Icons.campaign_outlined,
                    size: 64,
                    color: theme.colorScheme.onSurfaceVariant,
                  ),
                  const SizedBox(height: 16),
                  Text('No announcements', style: theme.textTheme.titleMedium),
                ],
              ),
            );
          }
          return RefreshIndicator(
            onRefresh: () async => context.read<CommunicationBloc>().add(
              const LoadAnnouncements(),
            ),
            child: ListView.builder(
              padding: const EdgeInsets.all(12),
              itemCount: announcements.length,
              itemBuilder: (_, i) {
                final a = announcements[i];
                return Card(
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: a.isPinned
                          ? theme.colorScheme.primaryContainer
                          : theme.colorScheme.surfaceContainerHighest,
                      child: Icon(
                        a.isPinned ? Icons.push_pin : Icons.campaign,
                        color: a.isPinned ? theme.colorScheme.primary : null,
                      ),
                    ),
                    title: Text(
                      a.title,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontWeight: a.isPinned
                            ? FontWeight.bold
                            : FontWeight.normal,
                      ),
                    ),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (a.category != null)
                          Text(a.category!, style: theme.textTheme.bodySmall),
                        Text(
                          DateFormat('MMM d, y').format(a.createdAt),
                          style: theme.textTheme.bodySmall,
                        ),
                      ],
                    ),
                    trailing: a.isPinned
                        ? const Icon(Icons.push_pin, size: 16)
                        : const Icon(Icons.chevron_right),
                    isThreeLine: true,
                    onTap: () => context.pushNamed(
                      'announcement-detail',
                      pathParameters: {'id': '${a.id}'},
                    ),
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
