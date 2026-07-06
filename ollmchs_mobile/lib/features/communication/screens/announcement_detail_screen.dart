import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import '../bloc/communication_bloc.dart';

class AnnouncementDetailScreen extends StatefulWidget {
  final int announcementId;
  const AnnouncementDetailScreen({super.key, required this.announcementId});

  @override
  State<AnnouncementDetailScreen> createState() =>
      _AnnouncementDetailScreenState();
}

class _AnnouncementDetailScreenState extends State<AnnouncementDetailScreen> {
  @override
  void initState() {
    super.initState();
    context.read<CommunicationBloc>().add(
      LoadAnnouncementDetail(widget.announcementId),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Announcement')),
      body: BlocBuilder<CommunicationBloc, CommunicationState>(
        builder: (context, state) {
          if (state is CommunicationLoading && state is! CommunicationLoaded) {
            return const Center(child: CircularProgressIndicator());
          }
          if (state is CommunicationError && state is! CommunicationLoaded) {
            return Center(child: Text(state.error));
          }
          if (state is CommunicationLoaded &&
              state.selectedAnnouncement != null) {
            final a = state.selectedAnnouncement!;
            return SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (a.imageUrl != null)
                    ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: CachedNetworkImage(
                        imageUrl: a.imageUrl!,
                        width: double.infinity,
                        height: 200,
                        fit: BoxFit.cover,
                        errorWidget: (_, __, ___) => const SizedBox.shrink(),
                      ),
                    ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      if (a.isPinned)
                        Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: Chip(
                            label: const Text(
                              'Pinned',
                              style: TextStyle(fontSize: 10),
                            ),
                            visualDensity: VisualDensity.compact,
                          ),
                        ),
                      if (a.category != null)
                        Chip(
                          label: Text(
                            a.category!,
                            style: const TextStyle(fontSize: 10),
                          ),
                          visualDensity: VisualDensity.compact,
                        ),
                      const Spacer(),
                      Text(
                        DateFormat('MMM d, y').format(a.createdAt),
                        style: theme.textTheme.bodySmall,
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    a.title,
                    style: theme.textTheme.headlineSmall?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 16),
                  if (a.body != null)
                    Text(
                      a.body!,
                      style: theme.textTheme.bodyLarge?.copyWith(height: 1.6),
                    ),
                  const SizedBox(height: 12),
                  if (a.authorName != null)
                    Text(
                      '— ${a.authorName}',
                      style: theme.textTheme.bodyMedium?.copyWith(
                        fontStyle: FontStyle.italic,
                      ),
                    ),
                ],
              ),
            );
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }
}
