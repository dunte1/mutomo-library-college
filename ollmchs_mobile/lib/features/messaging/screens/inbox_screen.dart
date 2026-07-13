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
    return Scaffold(
      appBar: AppBar(
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
                decoration: const InputDecoration(
                  hintText: 'Search messages...',
                  border: InputBorder.none,
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
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'Inbox'),
            Tab(text: 'Sent'),
            Tab(text: 'Archive'),
          ],
        ),
      ),
      body: BlocBuilder<MessagingBloc, MessagingState>(
        builder: (context, state) {
          if (state is MessagingLoading && state is! MessagingLoaded) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [
                  SkeletonCard(height: 72),
                  SkeletonCard(height: 72),
                  SkeletonCard(height: 72),
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
    return Card(
      child: ListTile(
        leading: CircleAvatar(
          backgroundImage: msg.senderPhoto != null
              ? NetworkImage(msg.senderPhoto!)
              : null,
          child: msg.senderPhoto == null
              ? Text(
                  msg.senderName?.isNotEmpty == true
                      ? msg.senderName![0]
                      : '?',
                )
              : null,
        ),
        title: Text(
          msg.subject,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: TextStyle(
            fontWeight: msg.isRead ? FontWeight.normal : FontWeight.bold,
          ),
        ),
        subtitle: Text(
          msg.senderName ?? '',
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
        trailing: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  DateFormat('MMM d').format(msg.sentAt),
                  style: theme.textTheme.bodySmall,
                ),
                if (msg.isUrgent)
                  const Icon(Icons.warning, color: Colors.orange, size: 16),
              ],
            ),
            PopupMenuButton<String>(
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
        onTap: () => context.goNamed(
          'message-detail',
          pathParameters: {'id': '${msg.id}'},
        ),
      ),
    );
  }

  Widget _buildInboxTab(MessagingState state, ThemeData theme) {
    final messages = state is MessagingLoaded ? state.inbox : <MessageModel>[];
    if (messages.isEmpty) {
      return const EmptyState(
        icon: Icons.inbox_outlined,
        title: 'No messages',
        subtitle: 'Your inbox is empty',
      );
    }
    return RefreshIndicator(
      onRefresh: () async =>
          context.read<MessagingBloc>().add(const LoadInbox()),
      child: ListView.builder(
        padding: const EdgeInsets.all(12),
        itemCount: messages.length,
        itemBuilder: (_, i) => _messageTile(messages[i], theme),
      ),
    );
  }

  Widget _buildSentTab(MessagingState state, ThemeData theme) {
    final messages = state is MessagingLoaded ? state.sent : <MessageModel>[];
    if (messages.isEmpty) {
      return const EmptyState(
        icon: Icons.send_outlined,
        title: 'No sent messages',
        subtitle: 'Messages you send will appear here',
      );
    }
    return RefreshIndicator(
      onRefresh: () async =>
          context.read<MessagingBloc>().add(const LoadSentMessages()),
      child: ListView.builder(
        padding: const EdgeInsets.all(12),
        itemCount: messages.length,
        itemBuilder: (_, i) {
          final msg = messages[i];
          return Card(
            child: ListTile(
              title: Text(
                msg.subject,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              subtitle: Text(
                'To: ${msg.recipientNames?.join(', ') ?? msg.senderName ?? 'Unknown'} | ${DateFormat('MMM d, y').format(msg.sentAt)}',
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              trailing: Chip(
                label: Text(msg.priority, style: const TextStyle(fontSize: 10)),
                visualDensity: VisualDensity.compact,
              ),
              onTap: () => context.goNamed(
                'message-detail',
                pathParameters: {'id': '${msg.id}'},
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildArchiveTab(MessagingState state, ThemeData theme) {
    final messages = state is MessagingLoaded ? state.archived : <MessageModel>[];
    if (messages.isEmpty) {
      return const EmptyState(
        icon: Icons.archive_outlined,
        title: 'No archived messages',
        subtitle: 'Archived messages will appear here',
      );
    }
    return RefreshIndicator(
      onRefresh: () async =>
          context.read<MessagingBloc>().add(const LoadArchivedMessages()),
      child: ListView.builder(
        padding: const EdgeInsets.all(12),
        itemCount: messages.length,
        itemBuilder: (_, i) {
          final msg = messages[i];
          return Card(
            child: ListTile(
              leading: CircleAvatar(
                backgroundImage: msg.senderPhoto != null
                    ? NetworkImage(msg.senderPhoto!)
                    : null,
                child: msg.senderPhoto == null
                    ? Text(msg.senderName?.isNotEmpty == true ? msg.senderName![0] : '?')
                    : null,
              ),
              title: Text(msg.subject, maxLines: 1, overflow: TextOverflow.ellipsis),
              subtitle: Text(msg.senderName ?? '', maxLines: 1, overflow: TextOverflow.ellipsis),
              trailing: IconButton(
                icon: const Icon(Icons.unarchive_outlined),
                tooltip: 'Restore',
                onPressed: () {
                  context.read<MessagingBloc>().add(UnarchiveMessage(msg.id));
                },
              ),
              onTap: () => context.goNamed(
                'message-detail',
                pathParameters: {'id': '${msg.id}'},
              ),
            ),
          );
        },
      ),
    );
  }
}
