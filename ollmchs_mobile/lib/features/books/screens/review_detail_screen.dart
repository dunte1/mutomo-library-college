import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class ReviewDetailScreen extends StatelessWidget {
  final int reviewId;
  final String? reviewerName;
  final int? rating;
  final String? comment;
  final DateTime? createdAt;

  const ReviewDetailScreen({
    super.key,
    required this.reviewId,
    this.reviewerName,
    this.rating,
    this.comment,
    this.createdAt,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Review')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Reviewer info
            Row(
              children: [
                CircleAvatar(
                  radius: 24,
                  child: Text(
                    (reviewerName ?? 'A')[0].toUpperCase(),
                    style: const TextStyle(fontSize: 18),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        reviewerName ?? 'Anonymous',
                        style: theme.textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      if (createdAt != null)
                        Text(
                          DateFormat('MMM d, y').format(createdAt!),
                          style: theme.textTheme.bodySmall?.copyWith(
                            color: theme.colorScheme.onSurfaceVariant,
                          ),
                        ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Rating
            if (rating != null) ...[
              Row(
                children: List.generate(5, (i) => Icon(
                  i < rating! ? Icons.star : Icons.star_border,
                  color: Colors.amber,
                  size: 28,
                )),
              ),
              const SizedBox(height: 16),
            ],

            // Comment
            if (comment != null && comment!.isNotEmpty)
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Text(
                    comment!,
                    style: theme.textTheme.bodyLarge?.copyWith(height: 1.5),
                  ),
                ),
              )
            else
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Text(
                    'No comment provided.',
                    style: theme.textTheme.bodyMedium?.copyWith(
                      color: theme.colorScheme.onSurfaceVariant,
                      fontStyle: FontStyle.italic,
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
