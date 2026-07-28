import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../bloc/messaging_bloc.dart';

class TemplateEditScreen extends StatefulWidget {
  final int? templateId;
  final String? initialName;
  final String? initialSubject;
  final String? initialBody;
  final String? initialPriority;

  const TemplateEditScreen({
    super.key,
    this.templateId,
    this.initialName,
    this.initialSubject,
    this.initialBody,
    this.initialPriority,
  });

  @override
  State<TemplateEditScreen> createState() => _TemplateEditScreenState();
}

class _TemplateEditScreenState extends State<TemplateEditScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _nameController;
  late final TextEditingController _subjectController;
  late final TextEditingController _bodyController;
  String _priority = 'normal';
  bool _saving = false;

  bool get _isEditing => widget.templateId != null;

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController(text: widget.initialName ?? '');
    _subjectController = TextEditingController(text: widget.initialSubject ?? '');
    _bodyController = TextEditingController(text: widget.initialBody ?? '');
    _priority = widget.initialPriority ?? 'normal';
  }

  @override
  void dispose() {
    _nameController.dispose();
    _subjectController.dispose();
    _bodyController.dispose();
    super.dispose();
  }

  Future<void> _onSave() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    try {
      final api = context.read<MessagingBloc>();
      if (_isEditing) {
        // For now, use the same save endpoint; backend can differentiate
        api.add(SaveTemplate(
          name: _nameController.text.trim(),
          subject: _subjectController.text.trim(),
          body: _bodyController.text.trim(),
          priority: _priority,
        ));
      } else {
        api.add(SaveTemplate(
          name: _nameController.text.trim(),
          subject: _subjectController.text.trim(),
          body: _bodyController.text.trim(),
          priority: _priority,
        ));
      }

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(_isEditing ? 'Template updated' : 'Template created')),
        );
        context.pop(true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to save: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: Text(_isEditing ? 'Edit Template' : 'New Template'),
        actions: [
          TextButton(
            onPressed: _saving ? null : _onSave,
            child: _saving
                ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
                : const Text('Save'),
          ),
        ],
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            TextFormField(
              controller: _nameController,
              decoration: const InputDecoration(
                labelText: 'Template Name',
                border: OutlineInputBorder(),
              ),
              validator: (v) => v == null || v.trim().isEmpty ? 'Enter a name' : null,
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _subjectController,
              decoration: const InputDecoration(
                labelText: 'Subject',
                border: OutlineInputBorder(),
              ),
              validator: (v) => v == null || v.trim().isEmpty ? 'Enter a subject' : null,
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _bodyController,
              decoration: const InputDecoration(
                labelText: 'Body',
                border: OutlineInputBorder(),
                alignLabelWithHint: true,
              ),
              maxLines: 8,
              validator: (v) => v == null || v.trim().isEmpty ? 'Enter body text' : null,
            ),
            const SizedBox(height: 16),
            Text('Priority', style: theme.textTheme.titleSmall?.copyWith(fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            SegmentedButton<String>(
              segments: const [
                ButtonSegment(value: 'normal', label: Text('Normal')),
                ButtonSegment(value: 'high', label: Text('High')),
                ButtonSegment(value: 'urgent', label: Text('Urgent')),
              ],
              selected: {_priority},
              onSelectionChanged: (v) => setState(() => _priority = v.first),
            ),
          ],
        ),
      ),
    );
  }
}
