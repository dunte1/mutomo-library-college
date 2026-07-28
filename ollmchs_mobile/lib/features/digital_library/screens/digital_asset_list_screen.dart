import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../bloc/digital_library_bloc.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/skeleton.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/bottom_nav_shell.dart';

class DigitalAssetListScreen extends StatefulWidget {
  const DigitalAssetListScreen({super.key});

  @override
  State<DigitalAssetListScreen> createState() => _DigitalAssetListScreenState();
}

class _DigitalAssetListScreenState extends State<DigitalAssetListScreen> {
  String? _selectedCategory;

  @override
  void initState() {
    super.initState();
    final bloc = context.read<DigitalLibraryBloc>();
    bloc.add(const LoadDigitalAssets());
    bloc.add(const LoadRecommendations());
    bloc.add(const LoadDigitalCategories());
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        leading: context.isCompact
            ? IconButton(
                icon: const Icon(Icons.menu),
                onPressed: () => shellScaffoldKey.currentState?.openDrawer(),
              )
            : null,
        title: const Text('Digital Library'),
        actions: [
          IconButton(
            icon: const Icon(Icons.history),
            tooltip: 'Reading History',
            onPressed: () => context.pushNamed('reading-history'),
          ),
        ],
      ),
      body: BlocBuilder<DigitalLibraryBloc, DigitalLibraryState>(
        builder: (context, state) {
          if (state is DigitalLibraryLoading &&
              state is! DigitalLibraryLoaded) {
            return const SkeletonGrid(childAspectRatio: 0.8);
          }
          if (state is DigitalLibraryError) {
            return Center(child: Text(state.error));
          }
          if (state is DigitalLibraryLoaded) {
            if (state.assets.isEmpty && state.recommendations.isEmpty) {
              return const EmptyState(
                icon: Icons.library_books_outlined,
                title: 'No digital assets',
                subtitle: 'No digital resources available yet',
              );
            }
            return RefreshIndicator(
              onRefresh: () async {
                final bloc = context.read<DigitalLibraryBloc>();
                bloc.add(const LoadDigitalAssets());
                bloc.add(const LoadRecommendations());
              },
              child: CustomScrollView(
                slivers: [
                  SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(12, 12, 12, 0),
                      child: TextField(
                        decoration: InputDecoration(
                          hintText: 'Search digital assets...',
                          prefixIcon: const Icon(Icons.search),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16),
                          isDense: true,
                        ),
                        onSubmitted: (query) {
                          context.read<DigitalLibraryBloc>().add(
                            LoadDigitalAssets(
                              category: _selectedCategory,
                              search: query.trim().isEmpty ? null : query.trim(),
                            ),
                          );
                        },
                      ),
                    ),
                  ),
                  if (state.recommendations.isNotEmpty)
                    SliverToBoxAdapter(
                      child: _buildRecommendations(state, theme),
                    ),
                  if (state.categories.isNotEmpty)
                    SliverToBoxAdapter(
                      child: _buildCategoryChips(state, theme),
                    ),
                  SliverPadding(
                    padding: const EdgeInsets.all(12),
                    sliver: SliverGrid(
                      gridDelegate:
                          const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            childAspectRatio: 0.8,
                            crossAxisSpacing: 12,
                            mainAxisSpacing: 12,
                          ),
                      delegate: SliverChildBuilderDelegate((_, i) {
                        final asset = state.assets[i];
                        final icon = asset.isPdf
                            ? Icons.picture_as_pdf
                            : asset.isVideo
                            ? Icons.video_library
                            : asset.isAudio
                            ? Icons.audiotrack
                            : Icons.insert_drive_file;

                        return Card(
                          clipBehavior: Clip.antiAlias,
                          child: InkWell(
                            onTap: () => context.goNamed(
                              'digital-asset-reader',
                              pathParameters: {'id': '${asset.id}'},
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Expanded(
                                  child: Container(
                                    width: double.infinity,
                                    color: theme
                                        .colorScheme
                                        .surfaceContainerHighest,
                                    child: asset.thumbnailUrl != null
                                        ? CachedNetworkImage(
                                            imageUrl: asset.thumbnailUrl!,
                                            fit: BoxFit.cover,
                                            errorWidget: (_, __, ___) =>
                                                Icon(icon, size: 48),
                                          )
                                        : Icon(icon, size: 48),
                                  ),
                                ),
                                Padding(
                                  padding: const EdgeInsets.all(8),
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        asset.title,
                                        maxLines: 2,
                                        overflow: TextOverflow.ellipsis,
                                        style: const TextStyle(
                                          fontWeight: FontWeight.w600,
                                          fontSize: 13,
                                        ),
                                      ),
                                      const SizedBox(height: 4),
                                      if (asset.fileSize != null)
                                        Text(
                                          asset.fileSize!,
                                          style: TextStyle(
                                            fontSize: 11,
                                            color: theme
                                                .colorScheme
                                                .onSurfaceVariant,
                                          ),
                                        ),
                                      if (asset.fileType != null)
                                        Chip(
                                          label: Text(
                                            asset.fileType!,
                                            style: const TextStyle(
                                              fontSize: 10,
                                            ),
                                          ),
                                          visualDensity: VisualDensity.compact,
                                          padding: EdgeInsets.zero,
                                          materialTapTargetSize:
                                              MaterialTapTargetSize.shrinkWrap,
                                        ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      }, childCount: state.assets.length),
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

  Widget _buildRecommendations(DigitalLibraryLoaded state, ThemeData theme) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(12, 12, 12, 4),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.recommend, size: 20, color: theme.colorScheme.primary),
              const SizedBox(width: 8),
              Text(
                'Recommended for You',
                style: theme.textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          SizedBox(
            height: 140,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: state.recommendations.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (_, i) {
                final rec = state.recommendations[i];
                return SizedBox(
                  width: 120,
                  child: Card(
                    clipBehavior: Clip.antiAlias,
                    child: InkWell(
                      onTap: () {
                        if (rec.type == 'digital_asset') {
                          context.goNamed(
                            'digital-asset-reader',
                            pathParameters: {'id': '${rec.id}'},
                          );
                        }
                      },
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(
                            child: Container(
                              width: double.infinity,
                              color: theme.colorScheme.surfaceContainerHighest,
                              child: rec.coverImage != null
                                  ? CachedNetworkImage(
                                      imageUrl: rec.coverImage!,
                                      fit: BoxFit.cover,
                                      errorWidget: (_, __, ___) => const Icon(
                                        Icons.auto_stories,
                                        size: 32,
                                      ),
                                    )
                                  : const Icon(Icons.auto_stories, size: 32),
                            ),
                          ),
                          Padding(
                            padding: const EdgeInsets.all(6),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  rec.title,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                Text(
                                  rec.reason,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: TextStyle(
                                    fontSize: 9,
                                    color: theme.colorScheme.onSurfaceVariant,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCategoryChips(DigitalLibraryLoaded state, ThemeData theme) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(12, 8, 12, 4),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: [
            FilterChip(
              label: const Text('All'),
              selected: _selectedCategory == null,
              onSelected: (_) {
                setState(() => _selectedCategory = null);
                context.read<DigitalLibraryBloc>().add(
                  const LoadDigitalAssets(),
                );
              },
              visualDensity: VisualDensity.compact,
            ),
            const SizedBox(width: 8),
            ...state.categories.map(
              (cat) => Padding(
                padding: const EdgeInsets.only(right: 8),
                child: FilterChip(
                  label: Text(cat),
                  selected: _selectedCategory == cat,
                  onSelected: (selected) {
                    setState(() => _selectedCategory = selected ? cat : null);
                    context.read<DigitalLibraryBloc>().add(
                      LoadDigitalAssets(category: selected ? cat : null),
                    );
                  },
                  visualDensity: VisualDensity.compact,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
