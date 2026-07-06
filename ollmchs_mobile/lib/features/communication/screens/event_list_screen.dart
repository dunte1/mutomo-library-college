import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/communication_bloc.dart';
import '../models/event_model.dart';
import '../../../core/widgets/skeleton.dart';

class EventListScreen extends StatefulWidget {
  const EventListScreen({super.key});

  @override
  State<EventListScreen> createState() => _EventListScreenState();
}

class _EventListScreenState extends State<EventListScreen> {
  @override
  void initState() {
    super.initState();
    context.read<CommunicationBloc>().add(const LoadEvents());
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Events')),
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
          final events = state is CommunicationLoaded
              ? state.events
              : <EventModel>[];
          if (events.isEmpty) {
            return Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    Icons.event_outlined,
                    size: 64,
                    color: theme.colorScheme.onSurfaceVariant,
                  ),
                  const SizedBox(height: 16),
                  Text('No events', style: theme.textTheme.titleMedium),
                ],
              ),
            );
          }
          return RefreshIndicator(
            onRefresh: () async =>
                context.read<CommunicationBloc>().add(const LoadEvents()),
            child: ListView.builder(
              padding: const EdgeInsets.all(12),
              itemCount: events.length,
              itemBuilder: (_, i) {
                final e = events[i];
                return Card(
                  child: ListTile(
                    leading: Container(
                      width: 48,
                      height: 48,
                      decoration: BoxDecoration(
                        color: e.isPast
                            ? theme.colorScheme.surfaceContainerHighest
                            : theme.colorScheme.primaryContainer,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            DateFormat('d').format(e.startAt),
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                              color: e.isPast
                                  ? null
                                  : theme.colorScheme.primary,
                            ),
                          ),
                          Text(
                            DateFormat('MMM').format(e.startAt),
                            style: TextStyle(
                              fontSize: 10,
                              color: e.isPast
                                  ? null
                                  : theme.colorScheme.primary,
                            ),
                          ),
                        ],
                      ),
                    ),
                    title: Text(
                      e.title,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '${DateFormat('MMM d, y').format(e.startAt)}${e.endAt != null ? ' - ${DateFormat('MMM d, y').format(e.endAt!)}' : ''}',
                        ),
                        if (e.location != null)
                          Text(e.location!, style: theme.textTheme.bodySmall),
                      ],
                    ),
                    trailing: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        if (e.isOngoing)
                          Chip(
                            label: const Text(
                              'Ongoing',
                              style: TextStyle(
                                fontSize: 10,
                                color: Colors.green,
                              ),
                            ),
                            visualDensity: VisualDensity.compact,
                          ),
                        const SizedBox(width: 4),
                        const Icon(Icons.chevron_right),
                      ],
                    ),
                    isThreeLine: true,
                    onTap: () => context.pushNamed(
                      'event-detail',
                      pathParameters: {'id': '${e.id}'},
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
