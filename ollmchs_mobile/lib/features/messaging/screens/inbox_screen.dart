import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/messaging_bloc.dart';
import '../models/message_model.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/skeleton.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/bottom_nav_shell.dart';

class InboxScreen extends StatefulWidget {
  const InboxScreen({super.key});

  @override
  State<InboxScreen> createState() => _InboxScreenState();
}

class _InboxScreenState extends State<InboxScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final _searchController = TextEditingController();
  bool _isSearching = false;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    context.read<MessagingBloc>().add(const LoadInbox());
    context.read<MessagingBloc>().add(const LoadSentMessages());
    context.read<MessagingBloc>().add(const LoadArchivedMessages());
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _onSearch(String query) {
    context.read<MessagingBloc>().add(SearchMessages(query: query));
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final cs = theme.colorScheme;
    return Scaffold(
      appBar: AppBar(
        scrolledUnderElevation: 0,
        leading: context.isCompact
            ? IconButton(
                icon: const Icon(Icons.menu),
                onPressed: () => shellScaffoldKey.currentState?.openDrawer(),
              )
            : null,
        title: _isSearching
            ? TextField(
                controller: _searchController,
                autofocus: true,
                decoration: InputDecoration(
                  hintText: 'Search messages...',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(24),
                    borderSide: BorderSide.none,
                  ),
                  filled: true,
                  fillColor: theme.colorScheme.surfaceContainerHighest.withValues(alpha: 0.5),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 0),
                ),
                textInputAction: TextInputAction.search,
                onSubmitted: _onSearch,
                onChanged: (v) {
                  if (v.isEmpty) _onSearch('');
                },
              )
            : const Text('Messages'),
        actions: [
          if (_isSearching)
            IconButton(
              icon: const Icon(Icons.close),
              onPressed: () {
                setState(() => _isSearching = false);
                _searchController.clear();
                _onSearch('');
              },
            )
          else
            IconButton(
              icon: const Icon(Icons.search),
              tooltip: 'Search',
              onPressed: () => setState(() => _isSearching = true),
            ),
          IconButton(
            icon: const Icon(Icons.edit_outlined),
            tooltip: 'Compose',
            onPressed: () => context.pushNamed('compose-message'),
          ),
        ],
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(48),
          child: Container(
            decoration: BoxDecoration(
              border: Border(
                bottom: BorderSide(color: theme.dividerColor),
              ),
            ),
            child: TabBar(
              controller: _tabController,
              indicatorWeight: 3,
              indicatorPadding: const EdgeInsets.symmetric(horizontal: 12),
              labelStyle: theme.textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w600),
              tabs: const [
                Tab(text: 'Inbox'),
                Tab(text: 'Sent'),
                Tab(text: 'Archive'),
              ],
            ),
          ),
        ),
      ),
      body: BlocConsumer<MessagingBloc, MessagingState>(
        listener: (context, state) {
          if (state is MessagingError) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Row(
                  children: [
                    Icon(Icons.error_outline, color: cs.onError, size: 18),
                    const SizedBox(width: 8),
                    Expanded(child: Text(state.error)),
                  ],
                ),
                backgroundColor: cs.error,
                behavior: SnackBarBehavior.floating,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
            );
          } else if (state is MessagingLoaded && state.message != null) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Row(
                  children: [
                    Icon(Icons.check_circle, color: cs.onPrimary, size: 18),
                    const SizedBox(width: 8),
                    Expanded(child: Text(state.message!)),
                  ],
                ),
                backgroundColor: cs.primary,
                behavior: SnackBarBehavior.floating,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
            );
          }
        },
        builder: (context, state) {
          if (state is MessagingLoading && state is! MessagingLoaded) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [
                  SkeletonCard(height: 88),
                  SizedBox(height: 8),
                  SkeletonCard(height: 88),
                  SizedBox(height: 8),
                  SkeletonCard(height: 88),
                ],
              ),
            );
          }

          final loaded = state is MessagingLoaded ? state : null;

          if (loaded != null && loaded.isSearching) {
            return _buildSearchResults(loaded, theme);
          }

          return TabBarView(
            controller: _tabController,
            children: [
              _buildInboxTab(state, theme),
              _buildSentTab(state, theme),
              _buildArchiveTab(state, theme),
            ],
          );
        },
      ),
    );
  }

  Widget _buildSearchResults(MessagingLoaded state, ThemeData theme) {
    final results = state.searchResults;
    if (results.isEmpty) {
      return const EmptyState(
        icon: Icons.search_off,
        title: 'No results found',
        subtitle: 'Try adjusting your search terms',
      );
    }
    return ListView.builder(
      padding: const EdgeInsets.all(12),
      itemCount: results.length,
      itemBuilder: (_, i) {
        final msg = results[i];
        return _messageTile(msg, theme);
      },
    );
  }

  Widget _messageTile(MessageModel msg, ThemeData theme) {
    final cs = theme.colorScheme;
    final senderInitial = msg.senderName?.isNotEmpty == true ? msg.senderName![0].toUpperCase() : '?';
    final avatarColor = Colors.primaries[msg.id % Colors.primaries.length];
    return Card(
      elevation: 0,
      margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: theme.dividerColor.withValues(alpha: 0.5)),
      ),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => context.goNamed(
          'message-detail',
          pathParameters: {'id': '${msg.id}'},
        ),
        child: IntrinsicHeight(
          child: Row(
            children: [
              if (msg.isUrgent)
                Container(
                  width: 4,
                  color: Colors.orange,
                ),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 22,
                        backgroundColor: avatarColor.withValues(alpha: 0.15),
                        backgroundImage: msg.senderPhoto != null
                            ? NetworkImage(msg.senderPhoto!)
                            : null,
                        child: msg.senderPhoto == null
                            ? Text(
                                senderInitial,
                                style: TextStyle(
                                  color: avatarColor,
                                  fontWeight: FontWeight.w600,
                                  fontSize: 15,
                                ),
                              )
                            : null,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    msg.subject,
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: TextStyle(
                                      fontWeight: msg.isRead ? FontWeight.normal : FontWeight.w600,
                                      fontSize: 15,
                                    ),
                                  ),
                                ),
                                Text(
                                  DateFormat('MMM d').format(msg.sentAt),
                                  style: theme.textTheme.bodySmall?.copyWith(
                                    color: cs.onSurface.withValues(alpha: 0.5),
                                    fontSize: 12,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 2),
                            Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    msg.senderName ?? '',
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: theme.textTheme.bodySmall?.copyWith(
                                      color: cs.onSurface.withValues(alpha: 0.6),
                                    ),
                                  ),
                                ),
                                if (msg.isUrgent)
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                                    decoration: BoxDecoration(
                                      color: Colors.orange.withValues(alpha: 0.1),
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: Text(
                                      'URGENT',
                                      style: TextStyle(
                                        fontSize: 9,
                                        fontWeight: FontWeight.w700,
                                        color: Colors.orange.shade700,
                                        letterSpacing: 0.5,
                                      ),
                                    ),
                                  ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      PopupMenuButton<String>(
                        padding: EdgeInsets.zero,
                        icon: Icon(Icons.more_vert, size: 18, color: cs.onSurface.withValues(alpha: 0.5)),
                        onSelected: (v) {
                          if (v == 'archive') {
                            context.read<MessagingBloc>().add(ArchiveMessage(msg.id));
                          } else if (v == 'delete') {
                            context.read<MessagingBloc>().add(DeleteMessage(msg.id));
                          }
                        },
                        itemBuilder: (_) => [
                          const PopupMenuItem(value: 'archive', child: Text('Archive')),
                          const PopupMenuItem(value: 'delete', child: Text('Delete')),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTabContent({
    required List<MessageModel> messages,
    required IconData emptyIcon,
    required String emptyTitle,
    required String emptySubtitle,
    required VoidCallback onRefresh,
    required Widget Function(MessageModel msg) buildItem,
  }) {
    if (messages.isEmpty) {
      return EmptyState(
        icon: emptyIcon,
        title: emptyTitle,
        subtitle: emptySubtitle,
      );
    }
    return RefreshIndicator(
      onRefresh: () async => onRefresh(),
      child: ListView.builder(
        padding: const EdgeInsets.only(top: 4, bottom: 16),
        itemCount: messages.length,
        itemBuilder: (_, i) => buildItem(messages[i]),
      ),
    );
  }

  Widget _buildInboxTab(MessagingState state, ThemeData theme) {
    final messages = state is MessagingLoaded ? state.inbox : <MessageModel>[];
    return _buildTabContent(
      messages: messages,
      emptyIcon: Icons.inbox_outlined,
      emptyTitle: 'No messages',
      emptySubtitle: 'Your inbox is empty',
      onRefresh: () => context.read<MessagingBloc>().add(const LoadInbox()),
      buildItem: (msg) => _messageTile(msg, theme),
    );
  }

  Widget _buildSentTab(MessagingState state, ThemeData theme) {
    final messages = state is MessagingLoaded ? state.sent : <MessageModel>[];
    return _buildTabContent(
      messages: messages,
      emptyIcon: Icons.send_outlined,
      emptyTitle: 'No sent messages',
      emptySubtitle: 'Messages you send will appear here',
      onRefresh: () => context.read<MessagingBloc>().add(const LoadSentMessages()),
      buildItem: (msg) {
        final cs = theme.colorScheme;
        return Card(
          elevation: 0,
          margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
            side: BorderSide(color: theme.dividerColor.withValues(alpha: 0.5)),
          ),
          clipBehavior: Clip.antiAlias,
          child: InkWell(
            onTap: () => context.goNamed(
              'message-detail',
              pathParameters: {'id': '${msg.id}'},
            ),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 22,
                    backgroundColor: cs.primary.withValues(alpha: 0.1),
                    child: Icon(Icons.send_outlined, size: 18, color: cs.primary),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          msg.subject,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'To: ${msg.recipientNames?.join(', ') ?? msg.senderName ?? 'Unknown'}',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: theme.textTheme.bodySmall?.copyWith(
                            color: cs.onSurface.withValues(alpha: 0.6),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        DateFormat('MMM d').format(msg.sentAt),
                        style: theme.textTheme.bodySmall?.copyWith(
                          color: cs.onSurface.withValues(alpha: 0.5),
                          fontSize: 12,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                        decoration: BoxDecoration(
                          color: msg.priority == 'high' || msg.priority == 'urgent'
                              ? Colors.orange.withValues(alpha: 0.1)
                              : cs.surfaceContainerHighest,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          msg.priority == 'urgent' ? 'URGENT' : msg.priority == 'high' ? 'HIGH' : msg.priority,
                          style: TextStyle(
                            fontSize: 9,
                            fontWeight: FontWeight.w600,
                            color: msg.priority == 'high' || msg.priority == 'urgent'
                                ? Colors.orange.shade700
                                : cs.onSurface.withValues(alpha: 0.5),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildArchiveTab(MessagingState state, ThemeData theme) {
    final messages = state is MessagingLoaded ? state.archived : <MessageModel>[];
    return _buildTabContent(
      messages: messages,
      emptyIcon: Icons.archive_outlined,
      emptyTitle: 'No archived messages',
      emptySubtitle: 'Archived messages will appear here',
      onRefresh: () => context.read<MessagingBloc>().add(const LoadArchivedMessages()),
      buildItem: (msg) {
        final cs = theme.colorScheme;
        final senderInitial = msg.senderName?.isNotEmpty == true ? msg.senderName![0].toUpperCase() : '?';
        final avatarColor = Colors.primaries[msg.id % Colors.primaries.length];
        return Card(
          elevation: 0,
          margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
            side: BorderSide(color: theme.dividerColor.withValues(alpha: 0.5)),
          ),
          clipBehavior: Clip.antiAlias,
          child: InkWell(
            onTap: () => context.goNamed(
              'message-detail',
              pathParameters: {'id': '${msg.id}'},
            ),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 22,
                    backgroundColor: avatarColor.withValues(alpha: 0.15),
                    child: Text(
                      senderInitial,
                      style: TextStyle(color: avatarColor, fontWeight: FontWeight.w600, fontSize: 15),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          msg.subject,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          msg.senderName ?? '',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: theme.textTheme.bodySmall?.copyWith(
                            color: cs.onSurface.withValues(alpha: 0.6),
                          ),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: Icon(Icons.unarchive_outlined, color: cs.primary),
                    tooltip: 'Restore',
                    onPressed: () {
                      context.read<MessagingBloc>().add(UnarchiveMessage(msg.id));
                    },
                  ),
                  PopupMenuButton<String>(
                    padding: EdgeInsets.zero,
                    icon: Icon(Icons.more_vert, size: 18, color: cs.onSurface.withValues(alpha: 0.5)),
                    onSelected: (v) {
                      if (v == 'delete') {
                        context.read<MessagingBloc>().add(DeleteMessage(msg.id));
                      }
                    },
                    itemBuilder: (_) => [
                      const PopupMenuItem(value: 'delete', child: Text('Delete')),
                    ],
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}
