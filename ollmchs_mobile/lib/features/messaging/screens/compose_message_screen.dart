import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import '../bloc/messaging_bloc.dart';
import '../../auth/bloc/auth_bloc.dart';
import '../../auth/bloc/auth_state.dart';
import '../../auth/models/user_model.dart';
import '../../../core/helpers/permission_helper.dart';
import '../../../core/network/api_client.dart';
import '../../../core/utils/type_parsers.dart';

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
  final _recipientController = TextEditingController();
  final _picker = ImagePicker();
  String _priority = 'normal';
  String _type = 'direct';
  List<int> _selectedRecipientIds = [];
  List<Map<String, dynamic>> _selectedRecipients = [];
  final List<File> _attachments = [];
  List<Map<String, dynamic>> _searchResults = [];
  bool _showRecipientPicker = false;
  bool _searchingRecipients = false;
  String? _searchError;
  bool _sending = false;
  bool _showTemplatePicker = false;

  UserModel? get _currentUser {
    final state = context.read<AuthBloc>().state;
    if (state is Authenticated) return state.user;
    return null;
  }

  bool get _isLecturer {
    final user = _currentUser;
    return user != null && PermissionHelper.isLecturer(user);
  }

  @override
  void initState() {
    super.initState();
    if (widget.recipientId != null) {
      _selectedRecipientIds = [widget.recipientId!];
      if (widget.recipientName != null) {
        _selectedRecipients = [
          {'id': widget.recipientId, 'name': widget.recipientName}
        ];
      }
    }
  }

  @override
  void dispose() {
    _subjectController.dispose();
    _bodyController.dispose();
    _recipientController.dispose();
    super.dispose();
  }

  Future<void> _searchUsers(String query) async {
    if (query.length < 2) {
      setState(() => _searchResults = []);
      return;
    }
    setState(() {
      _searchingRecipients = true;
      _searchError = null;
    });
    try {
      final api = context.read<ApiClient>();
      final response = await api.get('/v1/users/search', queryParameters: {'q': query});
      final data = response.data['data'] as List<dynamic>? ?? [];
      setState(() {
        _searchResults = data
            .map((e) => e as Map<String, dynamic>)
            .where((u) => !_selectedRecipientIds.contains(parseInt(u['id'])))
            .toList();
        _searchError = null;
      });
    } catch (_) {
      setState(() {
        _searchResults = [];
        _searchError = 'Search failed. Please try again.';
      });
    } finally {
      setState(() => _searchingRecipients = false);
    }
  }

  void _addRecipient(Map<String, dynamic> user) {
    setState(() {
      _selectedRecipientIds.add(parseInt(user['id']));
      _selectedRecipients.add(user);
      _recipientController.clear();
      _searchResults = [];
      _showRecipientPicker = false;
    });
  }

  void _removeRecipient(int index) {
    setState(() {
      _selectedRecipientIds.removeAt(index);
      _selectedRecipients.removeAt(index);
    });
  }

  Future<void> _pickAttachment() async {
    final result = await _picker.pickMultiImage();
    if (result.isNotEmpty) {
      setState(() {
        _attachments.addAll(result.map((x) => File(x.path)));
      });
    }
  }

  void _removeAttachment(int index) {
    setState(() => _attachments.removeAt(index));
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
          // Recipients
          if (_selectedRecipients.isNotEmpty)
            ..._selectedRecipients.asMap().entries.map((e) => Card(
                  child: ListTile(
                    dense: true,
                    leading: const Icon(Icons.person, size: 20),
                    title: Text(e.value['name'] as String? ?? ''),
                    trailing: IconButton(
                      icon: const Icon(Icons.close, size: 18),
                      onPressed: () => _removeRecipient(e.key),
                    ),
                  ),
                )),

          // Recipient picker toggle
          if (!_showRecipientPicker && _selectedRecipients.isEmpty)
            ListTile(
              leading: const Icon(Icons.person_add),
              title: const Text('Add recipient'),
              onTap: () => setState(() => _showRecipientPicker = true),
            ),

          if (_showRecipientPicker)
            Column(
              children: [
                TextField(
                  controller: _recipientController,
                  decoration: InputDecoration(
                    labelText: 'Search users...',
                    border: const OutlineInputBorder(),
                    suffixIcon: _searchingRecipients
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: Padding(
                              padding: EdgeInsets.all(12),
                              child: CircularProgressIndicator(strokeWidth: 2),
                            ),
                          )
                        : null,
                  ),
                  onChanged: _searchUsers,
                ),
                if (_searchResults.isNotEmpty)
                  SizedBox(
                    height: 160,
                    child: ListView.builder(
                      itemCount: _searchResults.length,
                      itemBuilder: (_, i) => ListTile(
                        dense: true,
                        leading: CircleAvatar(
                          radius: 16,
                          child: Text(
                            (_searchResults[i]['name'] as String? ?? '?')[0],
                          ),
                        ),
                        title: Text(_searchResults[i]['name'] as String? ?? ''),
                        subtitle: Text(_searchResults[i]['email'] as String? ?? ''),
                        onTap: () => _addRecipient(_searchResults[i]),
                      ),
                    ),
                  ),
                if (_searchError != null)
                  Padding(
                    padding: const EdgeInsets.only(top: 4),
                    child: Text(
                      _searchError!,
                      style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.error),
                    ),
                  ),
              ],
            ),

          const SizedBox(height: 12),

          // Type selector (lecturers see more options)
          if (_isLecturer)
            DropdownButtonFormField<String>(
              initialValue: _type,
              decoration: const InputDecoration(
                labelText: 'Type',
                border: OutlineInputBorder(),
              ),
              items: const [
                DropdownMenuItem(value: 'direct', child: Text('Direct')),
                DropdownMenuItem(value: 'department', child: Text('Department')),
                DropdownMenuItem(value: 'program', child: Text('Program')),
              ],
              onChanged: (v) => setState(() => _type = v ?? 'direct'),
            ),

          if (_isLecturer && _type == 'department')
            const Padding(
              padding: EdgeInsets.only(top: 4, bottom: 8),
              child: Text(
                'Sends to all users in your department.',
                style: TextStyle(fontSize: 12, color: Colors.grey),
              ),
            ),

          if (_isLecturer && _type == 'program')
            const Padding(
              padding: EdgeInsets.only(top: 4, bottom: 8),
              child: Text(
                'Sends to all users in your program.',
                style: TextStyle(fontSize: 12, color: Colors.grey),
              ),
            ),

          const SizedBox(height: 12),

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

          // Template controls (lecturers)
          if (_isLecturer) ...[
            Row(
              children: [
                OutlinedButton.icon(
                  icon: const Icon(Icons.bookmark_outline, size: 16),
                  label: const Text('Templates'),
                  onPressed: () {
                    context.read<MessagingBloc>().add(const LoadTemplates());
                    setState(() => _showTemplatePicker = !_showTemplatePicker);
                  },
                ),
                const SizedBox(width: 8),
                OutlinedButton.icon(
                  icon: const Icon(Icons.save_outlined, size: 16),
                  label: const Text('Save as Template'),
                  onPressed: _showSaveTemplateDialog,
                ),
              ],
            ),
            if (_showTemplatePicker)
              BlocBuilder<MessagingBloc, MessagingState>(
                builder: (context, state) {
                  if (state is MessagingLoaded && state.templates.isNotEmpty) {
                    return SizedBox(
                      height: 120,
                      child: ListView.builder(
                        itemCount: state.templates.length,
                        itemBuilder: (_, i) {
                          final t = state.templates[i];
                          return ListTile(
                            dense: true,
                            leading: const Icon(Icons.article_outlined, size: 18),
                            title: Text(t['name'] as String? ?? ''),
                            subtitle: Text(t['subject'] as String? ?? ''),
                            trailing: IconButton(
                              icon: const Icon(Icons.delete_outline, size: 16),
                              onPressed: () {
                                context.read<MessagingBloc>().add(
                                  DeleteTemplate(parseInt(t['id'])),
                                );
                              },
                            ),
                            onTap: () {
                              _subjectController.text = t['subject'] as String? ?? '';
                              _bodyController.text = t['body'] as String? ?? '';
                              if (t['priority'] != null) {
                                _priority = t['priority'] as String;
                              }
                              setState(() => _showTemplatePicker = false);
                            },
                          );
                        },
                      ),
                    );
                  }
                  if (state is MessagingLoaded && state.templates.isEmpty) {
                    return const Padding(
                      padding: EdgeInsets.all(8),
                      child: Text('No templates yet', style: TextStyle(color: Colors.grey)),
                    );
                  }
                  return const SizedBox.shrink();
                },
              ),
            const SizedBox(height: 8),
          ],

          // Attachments
          if (_attachments.isNotEmpty)
            ..._attachments.asMap().entries.map((e) => Card(
                  child: ListTile(
                    dense: true,
                    leading: const Icon(Icons.attach_file, size: 20),
                    title: Text(e.value.path.split('/').last),
                    trailing: IconButton(
                      icon: const Icon(Icons.close, size: 18),
                      onPressed: () => _removeAttachment(e.key),
                    ),
                  ),
                )),

          OutlinedButton.icon(
            onPressed: _pickAttachment,
            icon: const Icon(Icons.attach_file),
            label: const Text('Add Attachment'),
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

  void _showSaveTemplateDialog() {
    final nameController = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Save as Template'),
        content: TextField(
          controller: nameController,
          decoration: const InputDecoration(
            labelText: 'Template name',
            border: OutlineInputBorder(),
          ),
          autofocus: true,
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
          FilledButton(
            onPressed: () {
              if (nameController.text.trim().isNotEmpty &&
                  _subjectController.text.trim().isNotEmpty &&
                  _bodyController.text.trim().isNotEmpty) {
                context.read<MessagingBloc>().add(
                  SaveTemplate(
                    name: nameController.text.trim(),
                    subject: _subjectController.text.trim(),
                    body: _bodyController.text.trim(),
                    priority: _priority,
                  ),
                );
                Navigator.pop(ctx);
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Template saved')),
                );
              } else {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Subject and body required')),
                );
              }
            },
            child: const Text('Save'),
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

    List<MultipartFile>? attachmentFiles;
    if (_attachments.isNotEmpty) {
      attachmentFiles = await Future.wait(
        _attachments.map((f) => MultipartFile.fromFile(f.path)),
      );
    }

    if (!mounted) return;

    final bloc = context.read<MessagingBloc>();
    bloc.add(
      SendMessage(
        subject: _subjectController.text.trim(),
        body: _bodyController.text.trim(),
        recipientIds:
            _selectedRecipientIds.isNotEmpty ? _selectedRecipientIds : null,
        priority: _priority,
        type: _isLecturer ? _type : null,
        attachments: attachmentFiles,
      ),
    );

    final result = await bloc.stream.firstWhere(
      (s) => s is MessagingError || s is MessagingLoaded,
    );

    if (!mounted) return;
    setState(() => _sending = false);

    if (result is MessagingError) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result.error),
          backgroundColor: Theme.of(context).colorScheme.error,
        ),
      );
    } else {
      context.pop();
    }
  }
}
