import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../core/network/api_client.dart';
import '../../../core/widgets/empty_state.dart';
import '../models/recommendation_model.dart';

class RecommendationsScreen extends StatelessWidget {
  const RecommendationsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (context) => _RecommendationsCubit(context.read<ApiClient>())
        ..loadRecommendations(),
      child: const _RecommendationsView(),
    );
  }
}

class _RecommendationsCubit extends Cubit<RecommendationsState> {
  final ApiClient _api;

  _RecommendationsCubit(this._api) : super(RecommendationsLoading());

  Future<void> loadRecommendations() async {
    emit(RecommendationsLoading());
    try {
      final response = await _api.get('/v1/recommendations');
      final data = response.data['data'] as List<dynamic>? ?? [];
      final recommendations = data
          .map((e) => RecommendationItem.fromJson(e as Map<String, dynamic>))
          .toList();
      emit(RecommendationsLoaded(recommendations));
    } catch (e) {
      emit(RecommendationsError('Failed to load recommendations: $e'));
    }
  }
}

abstract class RecommendationsState {}
class RecommendationsLoading extends RecommendationsState {}
class RecommendationsLoaded extends RecommendationsState {
  final List<RecommendationItem> recommendations;
  RecommendationsLoaded(this.recommendations);
}
class RecommendationsError extends RecommendationsState {
  final String message;
  RecommendationsError(this.message);
}

class _RecommendationsView extends StatelessWidget {
  const _RecommendationsView();

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Recommended for You')),
      body: BlocBuilder<_RecommendationsCubit, RecommendationsState>(
        builder: (context, state) {
          if (state is RecommendationsLoading) {
            return const Center(child: CircularProgressIndicator());
          }
          if (state is RecommendationsError) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.error_outline, size: 48, color: theme.colorScheme.error),
                  const SizedBox(height: 16),
                  Text(state.message, textAlign: TextAlign.center),
                  const SizedBox(height: 16),
                  FilledButton(
                    onPressed: () => context.read<_RecommendationsCubit>().loadRecommendations(),
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }
          if (state is RecommendationsLoaded) {
            if (state.recommendations.isEmpty) {
              return const EmptyState(
                icon: Icons.recommend_outlined,
                title: 'No recommendations',
                subtitle: 'Recommendations will appear as you use the library',
              );
            }
            return RefreshIndicator(
              onRefresh: () => context.read<_RecommendationsCubit>().loadRecommendations(),
              child: ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: state.recommendations.length,
                itemBuilder: (context, index) {
                  final rec = state.recommendations[index];
                  return _buildRecommendationCard(context, rec, theme);
                },
              ),
            );
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }

  Widget _buildRecommendationCard(BuildContext context, RecommendationItem rec, ThemeData theme) {
    final item = rec.item;
    if (item == null) return const SizedBox.shrink();

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: () {
          _showRecommendationDetail(context, rec, theme);
        },
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: item.coverImage != null
                    ? Image.network(
                        item.coverImage!,
                        width: 60,
                        height: 80,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => _buildPlaceholder(theme, item.type),
                      )
                    : _buildPlaceholder(theme, item.type),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.title,
                      style: theme.textTheme.titleSmall?.copyWith(fontWeight: FontWeight.bold),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    if (item.authors != null && item.authors!.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(
                        item.authors!.map((a) => a['name']).join(', '),
                        style: theme.textTheme.bodySmall?.copyWith(
                          color: theme.colorScheme.onSurfaceVariant,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Icon(
                          item.type == 'book' ? Icons.book : Icons.auto_stories,
                          size: 16,
                          color: theme.colorScheme.primary,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          item.type == 'book' ? 'Book' : 'Digital Asset',
                          style: theme.textTheme.bodySmall?.copyWith(
                            color: theme.colorScheme.primary,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: theme.colorScheme.primaryContainer.withValues(alpha: 0.5),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        rec.reason,
                        style: theme.textTheme.bodySmall?.copyWith(
                          color: theme.colorScheme.onPrimaryContainer,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
              Icon(
                Icons.chevron_right,
                color: theme.colorScheme.onSurfaceVariant,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPlaceholder(ThemeData theme, String type) {
    return Container(
      width: 60,
      height: 80,
      decoration: BoxDecoration(
        color: theme.colorScheme.surfaceContainerHighest,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Icon(
        type == 'book' ? Icons.book : Icons.auto_stories,
        color: theme.colorScheme.onSurfaceVariant,
      ),
    );
  }

  void _showRecommendationDetail(BuildContext context, RecommendationItem rec, ThemeData theme) {
    final item = rec.item;
    if (item == null) return;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.5,
        minChildSize: 0.3,
        maxChildSize: 0.8,
        expand: false,
        builder: (ctx, scrollController) => SingleChildScrollView(
          controller: scrollController,
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: theme.colorScheme.onSurfaceVariant.withValues(alpha: 0.3),
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              Text(
                item.title,
                style: theme.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              if (item.authors != null && item.authors!.isNotEmpty)
                Text(
                  item.authors!.map((a) => a['name']).join(', '),
                  style: theme.textTheme.bodyMedium?.copyWith(
                    color: theme.colorScheme.onSurfaceVariant,
                  ),
                ),
              const SizedBox(height: 16),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: theme.colorScheme.primaryContainer.withValues(alpha: 0.5),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Why we recommend this:',
                      style: theme.textTheme.labelLarge?.copyWith(
                        color: theme.colorScheme.onPrimaryContainer,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      rec.reason,
                      style: theme.textTheme.bodyMedium?.copyWith(
                        color: theme.colorScheme.onPrimaryContainer,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  Icon(
                    item.type == 'book' ? Icons.book : Icons.auto_stories,
                    size: 16,
                    color: theme.colorScheme.primary,
                  ),
                  const SizedBox(width: 4),
                  Text(
                    item.type == 'book' ? 'Book' : 'Digital Asset',
                    style: theme.textTheme.bodySmall?.copyWith(
                      color: theme.colorScheme.primary,
                    ),
                  ),
                  const SizedBox(width: 16),
                  Text(
                    'Score: ${(rec.score * 100).round()}%',
                    style: theme.textTheme.bodySmall?.copyWith(
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: FilledButton.icon(
                  onPressed: () {
                    Navigator.pop(ctx);
                    if (item.type == 'book') {
                      context.push('/books/${item.id}');
                    } else if (item.type == 'digital_asset') {
                      context.push('/digital-library/${item.id}');
                    }
                  },
                  icon: const Icon(Icons.open_in_new),
                  label: const Text('View Details'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
