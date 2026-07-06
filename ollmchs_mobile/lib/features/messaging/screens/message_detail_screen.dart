import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
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

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Message'),
        actions: [
          IconButton(
            icon: const Icon(Icons.delete_outline),
            onPressed: () {
              context.read<MessagingBloc>().add(
                DeleteMessage(widget.messageId),
              );
              Navigator.of(context).pop();
            },
          ),
        ],
      ),
      body: BlocBuilder<MessagingBloc, MessagingState>(
        builder: (context, state) {
          if (state is MessagingLoading) {
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
                        Row(
                          children: [
                            CircleAvatar(
                              backgroundImage: msg.senderPhoto != null
                                  ? NetworkImage(msg.senderPhoto!)
                                  : null,
                              child:
                                  msg.senderPhoto == null &&
                                      msg.senderName != null
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
                                    style: theme.textTheme.titleMedium
                                        ?.copyWith(fontWeight: FontWeight.bold),
                                  ),
                                  Text(
                                    DateFormat(
                                      'MMM d, y h:mm a',
                                    ).format(msg.sentAt),
                                    style: theme.textTheme.bodySmall,
                                  ),
                                ],
                              ),
                            ),
                            if (msg.isUrgent)
                              const Chip(
                                label: Text(
                                  'URGENT',
                                  style: TextStyle(
                                    fontSize: 10,
                                    color: Colors.red,
                                  ),
                                ),
                                visualDensity: VisualDensity.compact,
                              ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          msg.subject,
                          style: theme.textTheme.headlineSmall?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          msg.body,
                          style: theme.textTheme.bodyLarge?.copyWith(
                            height: 1.5,
                          ),
                        ),
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
                            decoration: const InputDecoration(
                              hintText: 'Write a reply...',
                              border: OutlineInputBorder(),
                              contentPadding: EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 8,
                              ),
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
