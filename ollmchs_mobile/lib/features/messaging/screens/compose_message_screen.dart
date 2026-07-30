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
    final cs = Theme.of(context).colorScheme;
    return Scaffold(
      appBar: AppBar(
        scrolledUnderElevation: 0,
        title: const Text('Compose Message'),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 4),
            child: FilledButton(
              onPressed: _sending ? null : _onSend,
              style: FilledButton.styleFrom(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              ),
              child: _sending
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                    )
                  : const Text('Send'),
            ),
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Recipients
          if (_selectedRecipients.isNotEmpty)
            ..._selectedRecipients.asMap().entries.map((e) => Card(
              elevation: 0,
              margin: const EdgeInsets.only(bottom: 6),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
                side: BorderSide(color: cs.outline.withValues(alpha: 0.3)),
              ),
              child: ListTile(
                dense: true,
                leading: CircleAvatar(
                  radius: 16,
                  backgroundColor: cs.primary.withValues(alpha: 0.1),
                  child: Icon(Icons.person, size: 16, color: cs.primary),
                ),
                title: Text(
                  e.value['name'] as String? ?? '',
                  style: const TextStyle(fontWeight: FontWeight.w500),
                ),
                trailing: IconButton(
                  icon: Icon(Icons.close, size: 18, color: cs.error),
                  onPressed: () => _removeRecipient(e.key),
                ),
              ),
            )),

          // Recipient picker toggle
          if (!_showRecipientPicker && _selectedRecipients.isEmpty)
            InkWell(
              borderRadius: BorderRadius.circular(12),
              onTap: () => setState(() => _showRecipientPicker = true),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
                decoration: BoxDecoration(
                  border: Border.all(color: cs.outline.withValues(alpha: 0.3)),
                  borderRadius: BorderRadius.circular(12),
                  color: cs.surface,
                ),
                child: Row(
                  children: [
                    Icon(Icons.person_add_outlined, size: 20, color: cs.primary),
                    const SizedBox(width: 10),
                    Text(
                      'Add recipient',
                      style: TextStyle(color: cs.onSurface.withValues(alpha: 0.6)),
                    ),
                  ],
                ),
              ),
            ),

          if (_showRecipientPicker)
            Column(
              children: [
                TextField(
                  controller: _recipientController,
                  decoration: InputDecoration(
                    labelText: 'Search users...',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    suffixIcon: _searchingRecipients
                        ? const Padding(
                            padding: EdgeInsets.all(12),
                            child: SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            ),
                          )
                        : null,
                  ),
                  onChanged: _searchUsers,
                ),
                if (_searchResults.isNotEmpty)
                  Container(
                    margin: const EdgeInsets.only(top: 6),
                    decoration: BoxDecoration(
                      border: Border.all(color: cs.outline.withValues(alpha: 0.2)),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    constraints: const BoxConstraints(maxHeight: 180),
                    child: ListView.builder(
                      padding: EdgeInsets.zero,
                      itemCount: _searchResults.length,
                      itemBuilder: (_, i) => InkWell(
                        onTap: () => _addRecipient(_searchResults[i]),
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          child: Row(
                            children: [
                              CircleAvatar(
                                radius: 18,
                                backgroundColor: cs.primary.withValues(alpha: 0.1),
                                child: Text(
                                  (_searchResults[i]['name'] as String? ?? '?')[0],
                                  style: TextStyle(color: cs.primary, fontWeight: FontWeight.w600),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      _searchResults[i]['name'] as String? ?? '',
                                      style: const TextStyle(fontWeight: FontWeight.w500),
                                    ),
                                    Text(
                                      _searchResults[i]['email'] as String? ?? '',
                                      style: TextStyle(
                                        fontSize: 12,
                                        color: cs.onSurface.withValues(alpha: 0.5),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                if (_searchError != null)
                  Padding(
                    padding: const EdgeInsets.only(top: 6, left: 4),
                    child: Row(
                      children: [
                        Icon(Icons.error_outline, size: 14, color: cs.error),
                        const SizedBox(width: 4),
                        Text(
                          _searchError!,
                          style: TextStyle(fontSize: 12, color: cs.error),
                        ),
                      ],
                    ),
                  ),
              ],
            ),

          const SizedBox(height: 16),

          // Type selector
          if (_isLecturer)
            DropdownButtonFormField<String>(
              initialValue: _type,
              decoration: InputDecoration(
                labelText: 'Type',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              ),
              items: const [
                DropdownMenuItem(value: 'direct', child: Text('Direct')),
                DropdownMenuItem(value: 'department', child: Text('Department')),
                DropdownMenuItem(value: 'program', child: Text('Program')),
              ],
              onChanged: (v) => setState(() => _type = v ?? 'direct'),
            ),

          if (_isLecturer && _type == 'department')
            Padding(
              padding: const EdgeInsets.only(top: 6, left: 4),
              child: Row(
                children: [
                  Icon(Icons.info_outline, size: 14, color: cs.onSurface.withValues(alpha: 0.5)),
                  const SizedBox(width: 4),
                  Text(
                    'Sends to all users in your department.',
                    style: TextStyle(fontSize: 12, color: cs.onSurface.withValues(alpha: 0.5)),
                  ),
                ],
              ),
            ),

          if (_isLecturer && _type == 'program')
            Padding(
              padding: const EdgeInsets.only(top: 6, left: 4),
              child: Row(
                children: [
                  Icon(Icons.info_outline, size: 14, color: cs.onSurface.withValues(alpha: 0.5)),
                  const SizedBox(width: 4),
                  Text(
                    'Sends to all users in your program.',
                    style: TextStyle(fontSize: 12, color: cs.onSurface.withValues(alpha: 0.5)),
                  ),
                ],
              ),
            ),

          if (_isLecturer) const SizedBox(height: 12),

          TextField(
            controller: _subjectController,
            decoration: InputDecoration(
              labelText: 'Subject',
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            ),
            textInputAction: TextInputAction.next,
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(
            initialValue: _priority,
            decoration: InputDecoration(
              labelText: 'Priority',
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            ),
            items: const [
              DropdownMenuItem(value: 'normal', child: Text('Normal')),
              DropdownMenuItem(value: 'high', child: Text('High')),
              DropdownMenuItem(value: 'urgent', child: Text('Urgent')),
            ],
            onChanged: (v) => setState(() => _priority = v ?? 'normal'),
          ),
          const SizedBox(height: 16),

          // Template controls (lecturers)
          if (_isLecturer) ...[
            Row(
              children: [
                OutlinedButton.icon(
                  icon: const Icon(Icons.bookmark_outline, size: 16),
                  label: const Text('Templates'),
                  style: OutlinedButton.styleFrom(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                  ),
                  onPressed: () {
                    context.read<MessagingBloc>().add(const LoadTemplates());
                    setState(() => _showTemplatePicker = !_showTemplatePicker);
                  },
                ),
                const SizedBox(width: 8),
                OutlinedButton.icon(
                  icon: const Icon(Icons.save_outlined, size: 16),
                  label: const Text('Save as Template'),
                  style: OutlinedButton.styleFrom(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                  ),
                  onPressed: _showSaveTemplateDialog,
                ),
              ],
            ),
            if (_showTemplatePicker)
              BlocBuilder<MessagingBloc, MessagingState>(
                builder: (context, state) {
                  if (state is MessagingLoaded && state.templates.isNotEmpty) {
                    return Container(
                      margin: const EdgeInsets.only(top: 8),
                      decoration: BoxDecoration(
                        border: Border.all(color: cs.outline.withValues(alpha: 0.2)),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      constraints: const BoxConstraints(maxHeight: 140),
                      child: ListView.builder(
                        padding: EdgeInsets.zero,
                        itemCount: state.templates.length,
                        itemBuilder: (_, i) {
                          final t = state.templates[i];
                          return InkWell(
                            onTap: () {
                              _subjectController.text = t['subject'] as String? ?? '';
                              _bodyController.text = t['body'] as String? ?? '';
                              if (t['priority'] != null) {
                                _priority = t['priority'] as String;
                              }
                              setState(() => _showTemplatePicker = false);
                            },
                            child: Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                              child: Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.all(6),
                                    decoration: BoxDecoration(
                                      color: cs.primaryContainer.withValues(alpha: 0.5),
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Icon(Icons.article_outlined, size: 16, color: cs.primary),
                                  ),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          t['name'] as String? ?? '',
                                          style: const TextStyle(fontWeight: FontWeight.w500),
                                        ),
                                        Text(
                                          t['subject'] as String? ?? '',
                                          style: TextStyle(
                                            fontSize: 12,
                                            color: cs.onSurface.withValues(alpha: 0.5),
                                          ),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ],
                                    ),
                                  ),
                                  IconButton(
                                    icon: Icon(Icons.delete_outline, size: 16, color: cs.error),
                                    onPressed: () {
                                      context.read<MessagingBloc>().add(
                                        DeleteTemplate(parseInt(t['id'])),
                                      );
                                    },
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
                    );
                  }
                  if (state is MessagingLoaded && state.templates.isEmpty) {
                    return Container(
                      margin: const EdgeInsets.only(top: 8),
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        border: Border.all(color: cs.outline.withValues(alpha: 0.2)),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Center(
                        child: Text(
                          'No templates yet',
                          style: TextStyle(color: cs.onSurface.withValues(alpha: 0.5)),
                        ),
                      ),
                    );
                  }
                  return const SizedBox.shrink();
                },
              ),
            const SizedBox(height: 12),
          ],

          // Attachments
          if (_attachments.isNotEmpty)
            ..._attachments.asMap().entries.map((e) => Card(
              elevation: 0,
              margin: const EdgeInsets.only(bottom: 6),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
                side: BorderSide(color: cs.outline.withValues(alpha: 0.3)),
              ),
              child: ListTile(
                dense: true,
                leading: Container(
                  padding: const EdgeInsets.all(6),
                  decoration: BoxDecoration(
                    color: cs.primaryContainer.withValues(alpha: 0.5),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Icon(Icons.attach_file, size: 16, color: cs.primary),
                ),
                title: Text(
                  e.value.path.split('/').last,
                  style: const TextStyle(fontSize: 13),
                  overflow: TextOverflow.ellipsis,
                ),
                trailing: IconButton(
                  icon: Icon(Icons.close, size: 18, color: cs.error),
                  onPressed: () => _removeAttachment(e.key),
                ),
              ),
            )),

          OutlinedButton.icon(
            onPressed: _pickAttachment,
            icon: const Icon(Icons.attach_file, size: 18),
            label: const Text('Add Attachment'),
            style: OutlinedButton.styleFrom(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              padding: const EdgeInsets.symmetric(vertical: 14),
            ),
          ),

          const SizedBox(height: 16),
          TextField(
            controller: _bodyController,
            decoration: InputDecoration(
              labelText: 'Message',
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              alignLabelWithHint: true,
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
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
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            Icon(Icons.bookmark_outline, size: 22, color: Theme.of(context).colorScheme.primary),
            const SizedBox(width: 8),
            const Text('Save as Template'),
          ],
        ),
        content: TextField(
          controller: nameController,
          decoration: InputDecoration(
            labelText: 'Template name',
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          ),
          autofocus: true,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
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
                  SnackBar(
                    content: const Row(
                      children: [
                        Icon(Icons.check_circle, size: 18, color: Colors.white),
                        SizedBox(width: 8),
                        Text('Template saved'),
                      ],
                    ),
                    behavior: SnackBarBehavior.floating,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                );
              } else {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: const Row(
                      children: [
                        Icon(Icons.warning, size: 18, color: Colors.white),
                        SizedBox(width: 8),
                        Expanded(child: Text('Subject and body required')),
                      ],
                    ),
                    backgroundColor: Theme.of(context).colorScheme.error,
                    behavior: SnackBarBehavior.floating,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
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
        SnackBar(
          content: const Row(
            children: [
              Icon(Icons.warning, size: 18, color: Colors.white),
              SizedBox(width: 8),
              Expanded(child: Text('Subject and message are required')),
            ],
          ),
          backgroundColor: Theme.of(context).colorScheme.error,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
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
          content: Row(
            children: [
              Icon(Icons.error_outline, size: 18, color: Theme.of(context).colorScheme.onError),
              const SizedBox(width: 8),
              Expanded(child: Text(result.error)),
            ],
          ),
          backgroundColor: Theme.of(context).colorScheme.error,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
    } else {
      context.pop();
    }
  }
}
