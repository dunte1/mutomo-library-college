import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../bloc/reservations_bloc.dart';
import '../bloc/reservations_event.dart';
import '../bloc/reservations_state.dart';
import '../../../core/widgets/skeleton.dart';

class ReservationListScreen extends StatefulWidget {
  const ReservationListScreen({super.key});

  @override
  State<ReservationListScreen> createState() => _ReservationListScreenState();
}

class _ReservationListScreenState extends State<ReservationListScreen> {
  @override
  void initState() {
    super.initState();
    context.read<ReservationsBloc>().add(const LoadReservations());
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Reservations')),
      body: BlocConsumer<ReservationsBloc, ReservationsState>(
        listener: (context, state) {
          if (state is ReservationsLoaded && state.message != null) {
            ScaffoldMessenger.of(
              context,
            ).showSnackBar(SnackBar(content: Text(state.message!)));
          }
        },
        builder: (context, state) {
          if (state is ReservationsLoading) {
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
          if (state is ReservationsError) {
            return Center(child: Text(state.error));
          }
          if (state is ReservationsLoaded) {
            if (state.reservations.isEmpty) {
              return Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.bookmark_border,
                      size: 64,
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                    const SizedBox(height: 16),
                    Text('No reservations', style: theme.textTheme.titleMedium),
                    const SizedBox(height: 8),
                    Text(
                      'Browse books and reserve available copies',
                      style: theme.textTheme.bodyMedium?.copyWith(
                        color: theme.colorScheme.onSurfaceVariant,
                      ),
                    ),
                  ],
                ),
              );
            }
            return RefreshIndicator(
              onRefresh: () async => context.read<ReservationsBloc>().add(
                const LoadReservations(),
              ),
              child: ListView.builder(
                padding: const EdgeInsets.all(12),
                itemCount: state.reservations.length,
                itemBuilder: (_, i) {
                  final reservation = state.reservations[i];
                  return Card(
                    child: ListTile(
                      leading: Container(
                        width: 40,
                        height: 56,
                        color: theme.colorScheme.surfaceContainerHighest,
                        child: reservation.bookCover != null
                            ? CachedNetworkImage(
                                imageUrl: reservation.bookCover!,
                                fit: BoxFit.cover,
                                placeholder: (_, __) => Container(color: Colors.grey[200]),
                                errorWidget: (_, __, ___) => Icon(Icons.broken_image, color: Colors.grey),
                              )
                            : const Icon(Icons.book),
                      ),
                      title: Text(
                        reservation.bookTitle,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      subtitle: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Position: #${reservation.position}'),
                          if (reservation.isAvailable)
                            Text(
                              'Available!',
                              style: TextStyle(
                                color: Colors.green,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                        ],
                      ),
                      trailing: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          if (reservation.isActive)
                            IconButton(
                              icon: const Icon(
                                Icons.cancel_outlined,
                                color: Colors.red,
                              ),
                              onPressed: () => context
                                  .read<ReservationsBloc>()
                                  .add(CancelReservation(reservation.id)),
                              tooltip: 'Cancel reservation',
                            ),
                          Chip(
                            label: Text(
                              reservation.status,
                              style: TextStyle(
                                fontSize: 11,
                                color: reservation.isActive
                                    ? Colors.orange
                                    : Colors.grey,
                              ),
                            ),
                            visualDensity: VisualDensity.compact,
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            );
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }
}
