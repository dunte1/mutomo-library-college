import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import '../bloc/communication_bloc.dart';

class EventDetailScreen extends StatefulWidget {
  final int eventId;
  const EventDetailScreen({super.key, required this.eventId});

  @override
  State<EventDetailScreen> createState() => _EventDetailScreenState();
}

class _EventDetailScreenState extends State<EventDetailScreen> {
  @override
  void initState() {
    super.initState();
    context.read<CommunicationBloc>().add(LoadEventDetail(widget.eventId));
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Event')),
      body: BlocBuilder<CommunicationBloc, CommunicationState>(
        builder: (context, state) {
          if (state is CommunicationLoading && state is! CommunicationLoaded) {
            return const Center(child: CircularProgressIndicator());
          }
          if (state is CommunicationError && state is! CommunicationLoaded) {
            return Center(child: Text(state.error));
          }
          if (state is CommunicationLoaded && state.selectedEvent != null) {
            final e = state.selectedEvent!;
            return SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (e.imageUrl != null)
                    ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: CachedNetworkImage(
                        imageUrl: e.imageUrl!,
                        width: double.infinity,
                        height: 200,
                        fit: BoxFit.cover,
                        errorWidget: (_, __, ___) => const SizedBox.shrink(),
                      ),
                    ),
                  const SizedBox(height: 16),
                  Text(
                    e.title,
                    style: theme.textTheme.headlineSmall?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        children: [
                          _detailRow(
                            Icons.calendar_today,
                            'Date',
                            '${DateFormat('EEEE, MMMM d, y').format(e.startAt)}${e.endAt != null ? ' — ${DateFormat('MMMM d, y').format(e.endAt!)}' : ''}',
                          ),
                          const Divider(height: 16),
                          _detailRow(
                            Icons.access_time,
                            'Time',
                            '${DateFormat('h:mm a').format(e.startAt)}${e.endAt != null ? ' — ${DateFormat('h:mm a').format(e.endAt!)}' : ''}',
                          ),
                          if (e.location != null) ...[
                            const Divider(height: 16),
                            _detailRow(
                              Icons.location_on,
                              'Location',
                              e.location!,
                            ),
                          ],
                          if (e.organizerName != null) ...[
                            const Divider(height: 16),
                            _detailRow(
                              Icons.person,
                              'Organizer',
                              e.organizerName!,
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                  if (e.description != null && e.description!.isNotEmpty) ...[
                    const SizedBox(height: 16),
                    Text(
                      'About',
                      style: theme.textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      e.description!,
                      style: theme.textTheme.bodyLarge?.copyWith(height: 1.6),
                    ),
                  ],
                  if (e.location != null) ...[
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      child: FilledButton.icon(
                        onPressed: () async {
                          final uri = Uri.parse(
                            'https://www.google.com/maps/search/${Uri.encodeComponent(e.location!)}',
                          );
                          if (await canLaunchUrl(uri)) await launchUrl(uri);
                        },
                        icon: const Icon(Icons.map),
                        label: const Text('Open in Maps'),
                      ),
                    ),
                  ],
                ],
              ),
            );
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }

  Widget _detailRow(IconData icon, String label, String value) {
    final theme = Theme.of(context);
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 20, color: theme.colorScheme.primary),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: theme.textTheme.bodySmall?.copyWith(
                  color: theme.colorScheme.onSurfaceVariant,
                ),
              ),
              Text(value, style: theme.textTheme.bodyMedium),
            ],
          ),
        ),
      ],
    );
  }
}
