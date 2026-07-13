import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../bloc/messaging_bloc.dart';

class MessageDetailScreen extends StatefulWidget {
  final int messageId;
  const MessageDetailScreen({super.key, required this.messageId});

  @override
  State<MessageDetailScreen> createState() => _MessageDetailScreenState();
}

class _MessageDetailScreenState extends State<MessageDetailScreen> {
  final _replyController = TextEditingController();
  bool _replyAll = false;

  @override
  void initState() {
    super.initState();
    context.read<MessagingBloc>().add(LoadMessageDetail(widget.messageId));
  }

  @override
  void dispose() {
    _replyController.dispose();
    super.dispose();
  }

  void _showForwardDialog() {
    final controller = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Forward Message'),
        content: TextField(
          controller: controller,
          decoration: const InputDecoration(
            labelText: 'Recipient user IDs (comma separated)',
            border: OutlineInputBorder(),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
          FilledButton(
            onPressed: () {
              final ids = controller.text
                  .split(',')
                  .map((s) => int.tryParse(s.trim()))
                  .whereType<int>()
                  .toList();
              if (ids.isNotEmpty) {
                context.read<MessagingBloc>().add(
                  ForwardMessage(messageId: widget.messageId, recipientIds: ids),
                );
                Navigator.pop(ctx);
              }
            },
            child: const Text('Forward'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Message'),
        actions: [
          PopupMenuButton<String>(
            onSelected: (v) {
              if (v == 'reply-all') setState(() => _replyAll = !_replyAll);
              if (v == 'forward') _showForwardDialog();
              if (v == 'mark-unread') {
                context.read<MessagingBloc>().add(MarkAsUnread(widget.messageId));
              }
              if (v == 'archive') {
                context.read<MessagingBloc>().add(ArchiveMessage(widget.messageId));
                context.pop();
              }
            },
            itemBuilder: (_) => [
              PopupMenuItem(
                value: 'reply-all',
                child: Row(
                  children: [
                    Icon(_replyAll ? Icons.reply_all : Icons.reply),
                    const SizedBox(width: 8),
                    Text(_replyAll ? 'Reply to Sender' : 'Reply All'),
                  ],
                ),
              ),
              const PopupMenuItem(value: 'forward', child: Row(
                children: [Icon(Icons.forward), SizedBox(width: 8), Text('Forward')],
              )),
              const PopupMenuItem(value: 'mark-unread', child: Row(
                children: [Icon(Icons.markunread), SizedBox(width: 8), Text('Mark Unread')],
              )),
              const PopupMenuItem(value: 'archive', child: Row(
                children: [Icon(Icons.archive), SizedBox(width: 8), Text('Archive')],
              )),
            ],
          ),
          IconButton(
            icon: const Icon(Icons.delete_outline),
            onPressed: () {
              context.read<MessagingBloc>().add(DeleteMessage(widget.messageId));
              context.pop();
            },
          ),
        ],
      ),
      body: BlocBuilder<MessagingBloc, MessagingState>(
        builder: (context, state) {
          if (state is MessagingLoading && state is! MessagingLoaded) {
            return const Center(child: CircularProgressIndicator());
          }
          if (state is MessagingLoaded && state.selectedMessage != null) {
            final msg = state.selectedMessage!;
            return Column(
              children: [
                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Sender info
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            CircleAvatar(
                              backgroundImage: msg.senderPhoto != null
                                  ? NetworkImage(msg.senderPhoto!)
                                  : null,
                              child: msg.senderPhoto == null && msg.senderName != null
                                  ? Text(msg.senderName![0])
                                  : null,
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    msg.senderName ?? 'Unknown',
                                    style: theme.textTheme.titleMedium?.copyWith(
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  Text(
                                    DateFormat('MMM d, y h:mm a').format(msg.sentAt),
                                    style: theme.textTheme.bodySmall,
                                  ),
                                ],
                              ),
                            ),
                            if (msg.isUrgent)
                              const Chip(
                                label: Text('URGENT', style: TextStyle(fontSize: 10, color: Colors.red)),
                                visualDensity: VisualDensity.compact,
                              ),
                          ],
                        ),
                        const SizedBox(height: 16),

                        // Subject
                        Text(
                          msg.subject,
                          style: theme.textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 12),

                        // Body
                        Text(msg.body, style: theme.textTheme.bodyLarge?.copyWith(height: 1.5)),

                        // Attachments
                        if (msg.hasAttachments) ...[
                          const SizedBox(height: 16),
                          const Divider(),
                          Text('Attachments', style: theme.textTheme.titleSmall?.copyWith(fontWeight: FontWeight.bold)),
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              const Icon(Icons.attach_file, size: 16),
                              const SizedBox(width: 4),
                              Text('Tap to view attachments', style: theme.textTheme.bodySmall),
                            ],
                          ),
                        ],
                      ],
                    ),
                  ),
                ),
                // Reply box
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: theme.colorScheme.surface,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.05),
                        blurRadius: 4,
                      ),
                    ],
                  ),
                  child: SafeArea(
                    child: Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: _replyController,
                            decoration: InputDecoration(
                              hintText: _replyAll ? 'Reply to all...' : 'Write a reply...',
                              border: const OutlineInputBorder(),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              prefixIcon: _replyAll ? const Icon(Icons.reply_all, size: 18) : null,
                            ),
                            maxLines: 2,
                            minLines: 1,
                          ),
                        ),
                        const SizedBox(width: 8),
                        IconButton.filled(
                          onPressed: () {
                            if (_replyController.text.trim().isNotEmpty) {
                              context.read<MessagingBloc>().add(
                                ReplyToMessage(
                                  messageId: widget.messageId,
                                  body: _replyController.text.trim(),
                                  replyAll: _replyAll,
                                ),
                              );
                              _replyController.clear();
                            }
                          },
                          icon: const Icon(Icons.send),
                        ),
                      ],
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
}
