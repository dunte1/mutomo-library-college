import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/communication_bloc.dart';
import '../models/event_model.dart';
import '../../../core/widgets/skeleton.dart';
import '../../../core/widgets/empty_state.dart';

class EventCalendarScreen extends StatefulWidget {
  const EventCalendarScreen({super.key});

  @override
  State<EventCalendarScreen> createState() => _EventCalendarScreenState();
}

class _EventCalendarScreenState extends State<EventCalendarScreen> {
  DateTime _selectedDate = DateTime.now();
  DateTime _focusedMonth = DateTime.now();

  @override
  void initState() {
    super.initState();
    context.read<CommunicationBloc>().add(const LoadEvents());
  }

  List<EventModel> _eventsForDay(List<EventModel> events, DateTime day) {
    return events.where((e) {
      final start = DateTime(e.startAt.year, e.startAt.month, e.startAt.day);
      final end =
          e.endAt != null ? DateTime(e.endAt!.year, e.endAt!.month, e.endAt!.day) : start;
      final target = DateTime(day.year, day.month, day.day);
      return !target.isBefore(start) && !target.isAfter(end);
    }).toList();
  }

  List<DateTime> _daysInMonth(DateTime month) {
    final first = DateTime(month.year, month.month, 1);
    final last = DateTime(month.year, month.month + 1, 0);
    return List.generate(last.day, (i) => DateTime(first.year, first.month, i + 1));
  }

  int _firstDayOffset(DateTime month) {
    return DateTime(month.year, month.month, 1).weekday % 7;
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Event Calendar'),
        actions: [
          IconButton(
            icon: const Icon(Icons.view_list),
            tooltip: 'List View',
            onPressed: () => context.pop(),
          ),
        ],
      ),
      body: BlocBuilder<CommunicationBloc, CommunicationState>(
        builder: (context, state) {
          if (state is CommunicationLoading && state is! CommunicationLoaded) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [SkeletonCard(height: 300), SkeletonCard(height: 200)],
              ),
            );
          }
          final events = state is CommunicationLoaded
              ? state.events
              : <EventModel>[];
          final dayEvents = _eventsForDay(events, _selectedDate);
          final days = _daysInMonth(_focusedMonth);
          final offset = _firstDayOffset(_focusedMonth);

          return Column(
            children: [
              // Month navigation
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    IconButton(
                      icon: const Icon(Icons.chevron_left),
                      onPressed: () {
                        setState(() {
                          _focusedMonth = DateTime(
                            _focusedMonth.year,
                            _focusedMonth.month - 1,
                          );
                        });
                      },
                    ),
                    Text(
                      DateFormat('MMMM yyyy').format(_focusedMonth),
                      style: theme.textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.chevron_right),
                      onPressed: () {
                        setState(() {
                          _focusedMonth = DateTime(
                            _focusedMonth.year,
                            _focusedMonth.month + 1,
                          );
                        });
                      },
                    ),
                  ],
                ),
              ),
              // Day headers
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 12),
                child: Row(
                  children: ['S', 'M', 'T', 'W', 'T', 'F', 'S']
                      .map((d) => Expanded(
                            child: Center(
                              child: Text(
                                d,
                                style: theme.textTheme.bodySmall?.copyWith(
                                  fontWeight: FontWeight.bold,
                                  color: theme.colorScheme.onSurfaceVariant,
                                ),
                              ),
                            ),
                          ))
                      .toList(),
                ),
              ),
              const SizedBox(height: 4),
              // Calendar grid
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 12),
                child: GridView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 7,
                  ),
                  itemCount: offset + days.length,
                  itemBuilder: (_, i) {
                    if (i < offset) return const SizedBox();
                    final day = days[i - offset];
                    final hasEvents = _eventsForDay(events, day).isNotEmpty;
                    final isSelected = DateTime(
                      _selectedDate.year,
                      _selectedDate.month,
                      _selectedDate.day,
                    ) == DateTime(day.year, day.month, day.day);
                    final isToday = DateTime.now().year == day.year &&
                        DateTime.now().month == day.month &&
                        DateTime.now().day == day.day;

                    return GestureDetector(
                      onTap: () => setState(() => _selectedDate = day),
                      child: Container(
                        margin: const EdgeInsets.all(2),
                        decoration: BoxDecoration(
                          color: isSelected
                              ? theme.colorScheme.primary
                              : isToday
                                  ? theme.colorScheme.primaryContainer
                                  : null,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              '${day.day}',
                              style: TextStyle(
                                fontWeight: FontWeight.w500,
                                color: isSelected
                                    ? theme.colorScheme.onPrimary
                                    : null,
                              ),
                            ),
                            if (hasEvents)
                              Container(
                                width: 6,
                                height: 6,
                                margin: const EdgeInsets.only(top: 2),
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: isSelected
                                      ? theme.colorScheme.onPrimary
                                      : theme.colorScheme.primary,
                                ),
                              ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
              ),
              const SizedBox(height: 8),
              const Divider(),
              // Events for selected day
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                child: Row(
                  children: [
                    Text(
                      DateFormat('EEEE, MMM d').format(_selectedDate),
                      style: theme.textTheme.titleSmall?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const Spacer(),
                    Text(
                      '${dayEvents.length} event${dayEvents.length == 1 ? '' : 's'}',
                      style: theme.textTheme.bodySmall?.copyWith(
                        color: theme.colorScheme.onSurfaceVariant,
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: dayEvents.isEmpty
                    ? const EmptyState(
                        icon: Icons.event_outlined,
                        title: 'No events on this day',
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        itemCount: dayEvents.length,
                        itemBuilder: (_, i) {
                          final e = dayEvents[i];
                          return Card(
                            child: ListTile(
                              leading: Container(
                                width: 48,
                                height: 48,
                                decoration: BoxDecoration(
                                  color: e.isOngoing
                                      ? theme.colorScheme.primaryContainer
                                      : theme.colorScheme.surfaceContainerHighest,
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
                                        color: e.isOngoing
                                            ? theme.colorScheme.primary
                                            : null,
                                      ),
                                    ),
                                    Text(
                                      DateFormat('MMM').format(e.startAt),
                                      style: TextStyle(
                                        fontSize: 10,
                                        color: e.isOngoing
                                            ? theme.colorScheme.primary
                                            : null,
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
                                    '${DateFormat('h:mm a').format(e.startAt)}${e.endAt != null ? ' - ${DateFormat('h:mm a').format(e.endAt!)}' : ''}',
                                  ),
                                  if (e.location != null)
                                    Text(e.location!,
                                        style: theme.textTheme.bodySmall),
                                ],
                              ),
                              trailing: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  if (e.isOngoing)
                                    const Chip(
                                      label: Text(
                                        'Ongoing',
                                        style: TextStyle(
                                          fontSize: 10,
                                          color: Colors.green,
                                        ),
                                      ),
                                      visualDensity: VisualDensity.compact,
                                    ),
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
              ),
            ],
          );
        },
      ),
    );
  }
}
