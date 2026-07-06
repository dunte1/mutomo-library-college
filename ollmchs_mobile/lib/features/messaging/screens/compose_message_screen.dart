import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../bloc/messaging_bloc.dart';

class ComposeMessageScreen extends StatefulWidget {
  final int? recipientId;
  final String? recipientName;
  const ComposeMessageScreen({super.key, this.recipientId, this.recipientName});

  @override
  State<ComposeMessageScreen> createState() => _ComposeMessageScreenState();
}

class _ComposeMessageScreenState extends State<ComposeMessageScreen> {
  final _subjectController = TextEditingController();
  final _bodyController = TextEditingController();
  String _priority = 'normal';
  bool _sending = false;

  @override
  void dispose() {
    _subjectController.dispose();
    _bodyController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Compose Message'),
        actions: [
          TextButton(
            onPressed: _sending ? null : _onSend,
            child: _sending
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Text('Send'),
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (widget.recipientName != null)
            Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: Card(
                child: ListTile(
                  leading: const Icon(Icons.person),
                  title: const Text('To'),
                  subtitle: Text(widget.recipientName!),
                ),
              ),
            ),
          TextField(
            controller: _subjectController,
            decoration: const InputDecoration(
              labelText: 'Subject',
              border: OutlineInputBorder(),
            ),
            textInputAction: TextInputAction.next,
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(
            initialValue: _priority,
            decoration: const InputDecoration(
              labelText: 'Priority',
              border: OutlineInputBorder(),
            ),
            items: const [
              DropdownMenuItem(value: 'normal', child: Text('Normal')),
              DropdownMenuItem(value: 'high', child: Text('High')),
              DropdownMenuItem(value: 'urgent', child: Text('Urgent')),
            ],
            onChanged: (v) => setState(() => _priority = v ?? 'normal'),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _bodyController,
            decoration: const InputDecoration(
              labelText: 'Message',
              border: OutlineInputBorder(),
              alignLabelWithHint: true,
            ),
            maxLines: 10,
            minLines: 6,
            textInputAction: TextInputAction.newline,
          ),
        ],
      ),
    );
  }

  Future<void> _onSend() async {
    if (_subjectController.text.trim().isEmpty ||
        _bodyController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Subject and message are required')),
      );
      return;
    }

    setState(() => _sending = true);
    context.read<MessagingBloc>().add(
      SendMessage(
        subject: _subjectController.text.trim(),
        body: _bodyController.text.trim(),
        recipientId: widget.recipientId,
        priority: _priority,
      ),
    );

    if (mounted) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Message sent')));
      context.pop();
    }
  }
}
