import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/digital_library_bloc.dart';
import '../models/digital_asset_model.dart';
import '../../../core/widgets/skeleton.dart';

class ReadingHistoryScreen extends StatefulWidget {
  const ReadingHistoryScreen({super.key});

  @override
  State<ReadingHistoryScreen> createState() => _ReadingHistoryScreenState();
}

class _ReadingHistoryScreenState extends State<ReadingHistoryScreen> {
  @override
  void initState() {
    super.initState();
    context.read<DigitalLibraryBloc>().add(const LoadReadingHistory());
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Reading History')),
      body: BlocBuilder<DigitalLibraryBloc, DigitalLibraryState>(
        builder: (context, state) {
          final history = state is DigitalLibraryLoaded
              ? state.readingHistory
              : <ReadingHistoryModel>[];
          if (state is DigitalLibraryLoading && history.isEmpty) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [SkeletonCard(), SkeletonCard(), SkeletonCard()],
              ),
            );
          }
          if (history.isEmpty) {
            return Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    Icons.history,
                    size: 64,
                    color: theme.colorScheme.onSurfaceVariant,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'No reading history',
                    style: theme.textTheme.titleMedium,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Start reading digital assets to track progress',
                    style: theme.textTheme.bodyMedium?.copyWith(
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                  ),
                ],
              ),
            );
          }
          return RefreshIndicator(
            onRefresh: () async => context.read<DigitalLibraryBloc>().add(
              const LoadReadingHistory(),
            ),
            child: ListView.builder(
              padding: const EdgeInsets.all(12),
              itemCount: history.length,
              itemBuilder: (_, i) {
                final item = history[i];
                return Card(
                  child: ListTile(
                    leading: Container(
                      width: 40,
                      height: 56,
                      color: theme.colorScheme.surfaceContainerHighest,
                      child: item.assetCover != null
                          ? CachedNetworkImage(
                              imageUrl: item.assetCover!,
                              fit: BoxFit.cover,
                              errorWidget: (_, __, ___) =>
                                  const Icon(Icons.auto_stories),
                            )
                          : const Icon(Icons.auto_stories),
                    ),
                    title: Text(
                      item.assetTitle,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (item.progress != null)
                          Padding(
                            padding: const EdgeInsets.symmetric(vertical: 4),
                            child: LinearProgressIndicator(
                              value: item.progress! / 100,
                              minHeight: 4,
                              borderRadius: BorderRadius.circular(2),
                            ),
                          ),
                        Text(
                          '${item.progress?.toStringAsFixed(0) ?? '0'}% complete${item.lastReadAt != null ? ' • ${DateFormat('MMM d').format(item.lastReadAt!)}' : ''}',
                          style: theme.textTheme.bodySmall,
                        ),
                      ],
                    ),
                    trailing: const Icon(Icons.chevron_right),
                    isThreeLine: true,
                    onTap: () => context.pushNamed(
                      'digital-asset-reader',
                      pathParameters: {'id': '${item.assetId}'},
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
