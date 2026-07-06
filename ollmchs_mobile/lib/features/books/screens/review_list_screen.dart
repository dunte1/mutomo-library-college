import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../bloc/reviews_bloc.dart';
import '../../../core/widgets/skeleton.dart';

class ReviewListScreen extends StatefulWidget {
  final int bookId;
  final String bookTitle;
  const ReviewListScreen({
    super.key,
    required this.bookId,
    required this.bookTitle,
  });

  @override
  State<ReviewListScreen> createState() => _ReviewListScreenState();
}

class _ReviewListScreenState extends State<ReviewListScreen> {
  @override
  void initState() {
    super.initState();
    context.read<ReviewsBloc>().add(LoadReviews(widget.bookId));
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Reviews'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            tooltip: 'Write a review',
            onPressed: () => _showReviewDialog(context),
          ),
        ],
      ),
      body: BlocConsumer<ReviewsBloc, ReviewsState>(
        listener: (context, state) {
          if (state is ReviewsLoaded && state.message != null) {
            ScaffoldMessenger.of(
              context,
            ).showSnackBar(SnackBar(content: Text(state.message!)));
          }
        },
        builder: (context, state) {
          if (state is ReviewsLoading) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [SkeletonCard(), SkeletonCard(), SkeletonCard()],
              ),
            );
          }
          if (state is ReviewsError) {
            return Center(child: Text(state.error));
          }
          if (state is ReviewsLoaded) {
            return Column(
              children: [
                if (state.stats.totalReviews > 0)
                  Card(
                    margin: const EdgeInsets.all(12),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Row(
                        children: [
                          Column(
                            children: [
                              Text(
                                state.stats.averageRating.toStringAsFixed(1),
                                style: theme.textTheme.headlineLarge?.copyWith(
                                  fontWeight: FontWeight.bold,
                                  color: theme.colorScheme.primary,
                                ),
                              ),
                              Text(
                                '${state.stats.totalReviews} reviews',
                                style: theme.textTheme.bodySmall,
                              ),
                            ],
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: Column(
                              children: [5, 4, 3, 2, 1].map((r) {
                                final count = state.stats.distribution[r] ?? 0;
                                final pct = state.stats.totalReviews > 0
                                    ? count / state.stats.totalReviews
                                    : 0.0;
                                return Padding(
                                  padding: const EdgeInsets.symmetric(
                                    vertical: 1,
                                  ),
                                  child: Row(
                                    children: [
                                      Text(
                                        '$r',
                                        style: theme.textTheme.bodySmall,
                                      ),
                                      const SizedBox(width: 4),
                                      const Icon(
                                        Icons.star,
                                        size: 12,
                                        color: Colors.amber,
                                      ),
                                      const SizedBox(width: 4),
                                      Expanded(
                                        child: LinearProgressIndicator(
                                          value: pct,
                                          minHeight: 6,
                                          borderRadius: BorderRadius.circular(
                                            3,
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 4),
                                      Text(
                                        '$count',
                                        style: theme.textTheme.bodySmall,
                                      ),
                                    ],
                                  ),
                                );
                              }).toList(),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                Expanded(
                  child: state.reviews.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                Icons.rate_review_outlined,
                                size: 64,
                                color: theme.colorScheme.onSurfaceVariant,
                              ),
                              const SizedBox(height: 16),
                              Text(
                                'No reviews yet',
                                style: theme.textTheme.titleMedium,
                              ),
                              const SizedBox(height: 8),
                              FilledButton.tonal(
                                onPressed: () => _showReviewDialog(context),
                                child: const Text('Be the first to review'),
                              ),
                            ],
                          ),
                        )
                      : RefreshIndicator(
                          onRefresh: () async => context
                              .read<ReviewsBloc>()
                              .add(LoadReviews(widget.bookId)),
                          child: ListView.builder(
                            padding: const EdgeInsets.all(12),
                            itemCount: state.reviews.length,
                            itemBuilder: (_, i) {
                              final r = state.reviews[i];
                              return Card(
                                child: Padding(
                                  padding: const EdgeInsets.all(12),
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Row(
                                        children: [
                                          CircleAvatar(
                                            radius: 16,
                                            backgroundImage:
                                                r.userAvatar != null
                                                ? NetworkImage(r.userAvatar!)
                                                : null,
                                            child: r.userAvatar == null
                                                ? Text(
                                                    r.userName?.isNotEmpty ==
                                                            true
                                                        ? r.userName![0]
                                                        : '?',
                                                    style: const TextStyle(
                                                      fontSize: 12,
                                                    ),
                                                  )
                                                : null,
                                          ),
                                          const SizedBox(width: 8),
                                          Expanded(
                                            child: Text(
                                              r.userName ?? 'Anonymous',
                                              style: theme.textTheme.bodyMedium
                                                  ?.copyWith(
                                                    fontWeight: FontWeight.w600,
                                                  ),
                                            ),
                                          ),
                                          Row(
                                            children: List.generate(
                                              5,
                                              (j) => Icon(
                                                j < r.rating
                                                    ? Icons.star
                                                    : Icons.star_border,
                                                size: 16,
                                                color: Colors.amber,
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                      if (r.review != null &&
                                          r.review!.isNotEmpty) ...[
                                        const SizedBox(height: 8),
                                        Text(
                                          r.review!,
                                          style: theme.textTheme.bodyMedium
                                              ?.copyWith(height: 1.4),
                                        ),
                                      ],
                                    ],
                                  ),
                                ),
                              );
                            },
                          ),
                        ),
                ),
              ],
            );
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }

  void _showReviewDialog(BuildContext context) {
    int rating = 5;
    final reviewController = TextEditingController();

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          title: Text('Review "${widget.bookTitle}"'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(
                  5,
                  (i) => IconButton(
                    icon: Icon(
                      i < rating ? Icons.star : Icons.star_border,
                      color: Colors.amber,
                      size: 32,
                    ),
                    onPressed: () => setDialogState(() => rating = i + 1),
                  ),
                ),
              ),
              TextField(
                controller: reviewController,
                decoration: const InputDecoration(
                  labelText: 'Your review (optional)',
                  border: OutlineInputBorder(),
                ),
                maxLines: 3,
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Cancel'),
            ),
            FilledButton(
              onPressed: () {
                context.read<ReviewsBloc>().add(
                  SubmitReview(
                    bookId: widget.bookId,
                    rating: rating,
                    review: reviewController.text,
                  ),
                );
                Navigator.pop(ctx);
              },
              child: const Text('Submit'),
            ),
          ],
        ),
      ),
    );
  }
}
