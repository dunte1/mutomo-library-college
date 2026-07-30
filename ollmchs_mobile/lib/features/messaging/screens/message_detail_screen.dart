import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
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

  String _formatFileSize(int bytes) {
    if (bytes < 1024) return '$bytes B';
    if (bytes < 1024 * 1024) return '${(bytes / 1024).toStringAsFixed(1)} KB';
    return '${(bytes / (1024 * 1024)).toStringAsFixed(1)} MB';
  }

  void _showForwardDialog() {
    final controller = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            Icon(Icons.forward, size: 22, color: Theme.of(context).colorScheme.primary),
            const SizedBox(width: 8),
            const Text('Forward Message'),
          ],
        ),
        content: TextField(
          controller: controller,
          decoration: InputDecoration(
            labelText: 'Recipient user IDs',
            hintText: 'e.g. 1, 2, 3',
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          ),
          keyboardType: TextInputType.number,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
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
    final cs = theme.colorScheme;
    return Scaffold(
      appBar: AppBar(
        scrolledUnderElevation: 0,
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
                    Icon(_replyAll ? Icons.reply_all : Icons.reply, size: 18),
                    const SizedBox(width: 8),
                    Text(_replyAll ? 'Reply to Sender' : 'Reply All'),
                  ],
                ),
              ),
              const PopupMenuItem(value: 'forward', child: Row(
                children: [Icon(Icons.forward, size: 18), SizedBox(width: 8), Text('Forward')],
              )),
              const PopupMenuItem(value: 'mark-unread', child: Row(
                children: [Icon(Icons.markunread, size: 18), SizedBox(width: 8), Text('Mark Unread')],
              )),
              const PopupMenuItem(value: 'archive', child: Row(
                children: [Icon(Icons.archive, size: 18), SizedBox(width: 8), Text('Archive')],
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
            return const Center(
              child: CircularProgressIndicator(),
            );
          }
          if (state is MessagingLoaded && state.selectedMessage != null) {
            final msg = state.selectedMessage!;
            final senderInitial = msg.senderName?.isNotEmpty == true
                ? msg.senderName![0].toUpperCase()
                : '?';
            final avatarColor = Colors.primaries[msg.id % Colors.primaries.length];
            return Column(
              children: [
                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Sender card
                        Card(
                          elevation: 0,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                            side: BorderSide(color: theme.dividerColor.withValues(alpha: 0.5)),
                          ),
                          child: Padding(
                            padding: const EdgeInsets.all(14),
                            child: Row(
                              children: [
                                CircleAvatar(
                                  radius: 26,
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
                                            fontSize: 18,
                                          ),
                                        )
                                      : null,
                                ),
                                const SizedBox(width: 14),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        msg.senderName ?? 'Unknown',
                                        style: theme.textTheme.titleMedium?.copyWith(
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        DateFormat('MMM d, y  h:mm a').format(msg.sentAt),
                                        style: theme.textTheme.bodySmall?.copyWith(
                                          color: cs.onSurface.withValues(alpha: 0.5),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                if (msg.isUrgent)
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: Colors.red.withValues(alpha: 0.1),
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Text(
                                      'URGENT',
                                      style: TextStyle(
                                        fontSize: 10,
                                        fontWeight: FontWeight.w700,
                                        color: Colors.red.shade700,
                                        letterSpacing: 0.5,
                                      ),
                                    ),
                                  ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Subject
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: cs.primaryContainer.withValues(alpha: 0.3),
                            borderRadius: BorderRadius.circular(12),
                            border: Border(
                              left: BorderSide(color: cs.primary, width: 3),
                            ),
                        ),
                          child: Text(
                            msg.subject,
                            style: theme.textTheme.titleLarge?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Body
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: cs.surface,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: theme.dividerColor.withValues(alpha: 0.3)),
                          ),
                          child: Text(
                            msg.body,
                            style: theme.textTheme.bodyLarge?.copyWith(
                              height: 1.6,
                              color: cs.onSurface.withValues(alpha: 0.85),
                            ),
                          ),
                        ),

                        // Attachments
                        if (msg.hasAttachments && msg.attachments.isNotEmpty) ...[
                          const SizedBox(height: 20),
                          Text(
                            'Attachments',
                            style: theme.textTheme.titleSmall?.copyWith(
                              fontWeight: FontWeight.w700,
                              color: cs.onSurface.withValues(alpha: 0.7),
                            ),
                          ),
                          const SizedBox(height: 8),
                          ...msg.attachments.map((a) => Card(
                            elevation: 0,
                            margin: const EdgeInsets.only(bottom: 6),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10),
                              side: BorderSide(color: theme.dividerColor.withValues(alpha: 0.4)),
                            ),
                            child: InkWell(
                              onTap: () async {
                                final url = a['url'] as String?;
                                if (url != null) {
                                  final uri = Uri.parse(url);
                                  if (await canLaunchUrl(uri)) {
                                    await launchUrl(uri, mode: LaunchMode.externalApplication);
                                  }
                                }
                              },
                              borderRadius: BorderRadius.circular(10),
                              child: Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                child: Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.all(8),
                                      decoration: BoxDecoration(
                                        color: cs.primaryContainer.withValues(alpha: 0.5),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Icon(Icons.attach_file, size: 18, color: cs.primary),
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: Text(
                                        a['file_name'] as String? ?? 'Attachment',
                                        style: theme.textTheme.bodyMedium?.copyWith(
                                          fontWeight: FontWeight.w500,
                                        ),
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                    if (a['file_size'] != null)
                                      Text(
                                        _formatFileSize(a['file_size'] as int),
                                        style: theme.textTheme.bodySmall?.copyWith(
                                          color: cs.onSurface.withValues(alpha: 0.5),
                                        ),
                                      ),
                                    const SizedBox(width: 8),
                                    Icon(Icons.open_in_new, size: 14, color: cs.primary),
                                  ],
                                ),
                              ),
                            ),
                          )),
                        ],

                        // Replies
                        if (msg.replies.isNotEmpty) ...[
                          const SizedBox(height: 20),
                          Text(
                            'Replies (${msg.replies.length})',
                            style: theme.textTheme.titleSmall?.copyWith(
                              fontWeight: FontWeight.w700,
                              color: cs.onSurface.withValues(alpha: 0.7),
                            ),
                          ),
                          const SizedBox(height: 8),
                          ...msg.replies.asMap().entries.map((entry) {
                            final i = entry.key;
                            final reply = entry.value;
                            final rInitial = reply.senderName?.isNotEmpty == true
                                ? reply.senderName![0].toUpperCase()
                                : '?';
                            final rColor = Colors.primaries[(reply.id + 3) % Colors.primaries.length];
                            return IntrinsicHeight(
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.stretch,
                                children: [
                                  SizedBox(
                                    width: 24,
                                    child: Column(
                                      children: [
                                        CircleAvatar(
                                          radius: 11,
                                          backgroundColor: rColor.withValues(alpha: 0.15),
                                          child: Text(
                                            rInitial,
                                            style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: rColor),
                                          ),
                                        ),
                                        if (i < msg.replies.length - 1)
                                          Expanded(
                                            child: Container(
                                              width: 2,
                                              color: theme.dividerColor.withValues(alpha: 0.4),
                                            ),
                                          ),
                                      ],
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Container(
                                      margin: const EdgeInsets.only(bottom: 10),
                                      padding: const EdgeInsets.all(12),
                                      decoration: BoxDecoration(
                                        color: cs.surface,
                                        borderRadius: BorderRadius.circular(10),
                                        border: Border.all(color: theme.dividerColor.withValues(alpha: 0.3)),
                                      ),
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Row(
                                            children: [
                                              Text(
                                                reply.senderName ?? 'Unknown',
                                                style: theme.textTheme.bodyMedium?.copyWith(
                                                  fontWeight: FontWeight.w600,
                                                ),
                                              ),
                                              const SizedBox(width: 6),
                                              Text(
                                                DateFormat('MMM d, y').format(reply.sentAt),
                                                style: theme.textTheme.bodySmall?.copyWith(
                                                  color: cs.onSurface.withValues(alpha: 0.4),
                                                  fontSize: 11,
                                                ),
                                              ),
                                            ],
                                          ),
                                          const SizedBox(height: 4),
                                          Text(
                                            reply.body,
                                            style: theme.textTheme.bodyMedium?.copyWith(
                                              height: 1.4,
                                              color: cs.onSurface.withValues(alpha: 0.85),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          }),
                        ],
                      ],
                    ),
                  ),
                ),
                // Reply box
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  decoration: BoxDecoration(
                    color: cs.surface,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.06),
                        blurRadius: 8,
                        offset: const Offset(0, -2),
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
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(24),
                                borderSide: BorderSide.none,
                              ),
                              filled: true,
                              fillColor: cs.surfaceContainerHighest.withValues(alpha: 0.5),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                              prefixIcon: _replyAll
                                  ? Padding(
                                      padding: const EdgeInsets.only(left: 8),
                                      child: Icon(Icons.reply_all, size: 18, color: cs.primary),
                                    )
                                  : null,
                            ),
                            maxLines: 2,
                            minLines: 1,
                            textCapitalization: TextCapitalization.sentences,
                          ),
                        ),
                        const SizedBox(width: 8),
                        CircleAvatar(
                          backgroundColor: cs.primary,
                          radius: 22,
                          child: IconButton(
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
                            icon: const Icon(Icons.send, size: 18),
                            color: cs.onPrimary,
                            splashRadius: 20,
                          ),
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
