import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/messaging_bloc.dart';
import '../models/message_model.dart';
import '../../../core/widgets/skeleton.dart';

class InboxScreen extends StatefulWidget {
  const InboxScreen({super.key});

  @override
  State<InboxScreen> createState() => _InboxScreenState();
}

class _InboxScreenState extends State<InboxScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    context.read<MessagingBloc>().add(const LoadInbox());
    context.read<MessagingBloc>().add(const LoadSentMessages());
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Messages'),
        actions: [
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

          return TabBarView(
            controller: _tabController,
            children: [
              _buildInboxTab(state, theme),
              _buildSentTab(state, theme),
            ],
          );
        },
      ),
    );
  }

  Widget _buildInboxTab(MessagingState state, ThemeData theme) {
    final messages = state is MessagingLoaded ? state.inbox : <MessageModel>[];
    if (messages.isEmpty) {
      return const Center(child: Text('No messages'));
    }
    return RefreshIndicator(
      onRefresh: () async =>
          context.read<MessagingBloc>().add(const LoadInbox()),
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
                        const Icon(
                          Icons.warning,
                          color: Colors.orange,
                          size: 16,
                        ),
                    ],
                  ),
                  IconButton(
                    icon: const Icon(Icons.delete_outline, size: 20),
                    onPressed: () {
                      context.read<MessagingBloc>().add(DeleteMessage(msg.id));
                    },
                  ),
                ],
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

  Widget _buildSentTab(MessagingState state, ThemeData theme) {
    final messages = state is MessagingLoaded ? state.sent : <MessageModel>[];
    if (messages.isEmpty) {
      return const Center(child: Text('No sent messages'));
    }
    return ListView.builder(
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
              'To: ${msg.senderName ?? 'Unknown'} | ${DateFormat('MMM d, y').format(msg.sentAt)}',
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
    );
  }
}
