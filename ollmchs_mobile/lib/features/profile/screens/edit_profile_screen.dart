import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../bloc/profile_bloc.dart';
import '../../auth/models/user_model.dart';

class EditProfileScreen extends StatefulWidget {
  final UserModel user;
  const EditProfileScreen({super.key, required this.user});

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  late TextEditingController _nameController;
  late TextEditingController _phoneController;
  late TextEditingController _emailController;
  bool _notifyDueDates = true;
  bool _notifyFines = true;
  bool _notifyMessages = true;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController(text: widget.user.name);
    _phoneController = TextEditingController(text: widget.user.phone ?? '');
    _emailController = TextEditingController(text: widget.user.email);
    final prefs = widget.user.notificationPreferences;
    if (prefs != null) {
      _notifyDueDates = prefs['due_dates'] as bool? ?? true;
      _notifyFines = prefs['fines'] as bool? ?? true;
      _notifyMessages = prefs['messages'] as bool? ?? true;
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Edit Profile'),
        actions: [
          TextButton(
            onPressed: _saving ? null : _onSave,
            child: _saving
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Text('Save'),
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(
            'Profile Information',
            style: Theme.of(
              context,
            ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  TextField(
                    controller: _nameController,
                    decoration: const InputDecoration(
                      labelText: 'Full Name',
                      border: OutlineInputBorder(),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _phoneController,
                    decoration: const InputDecoration(
                      labelText: 'Phone Number',
                      border: OutlineInputBorder(),
                    ),
                    keyboardType: TextInputType.phone,
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _emailController,
                    decoration: const InputDecoration(
                      labelText: 'Email',
                      border: OutlineInputBorder(),
                    ),
                    keyboardType: TextInputType.emailAddress,
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 24),
          Text(
            'Notification Preferences',
            style: Theme.of(
              context,
            ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          Card(
            child: Column(
              children: [
                SwitchListTile(
                  title: const Text('Due Date Reminders'),
                  subtitle: const Text('Get notified before books are due'),
                  value: _notifyDueDates,
                  onChanged: (v) => setState(() => _notifyDueDates = v),
                ),
                const Divider(height: 1),
                SwitchListTile(
                  title: const Text('Fine Notifications'),
                  subtitle: const Text('Get notified when fines are assessed'),
                  value: _notifyFines,
                  onChanged: (v) => setState(() => _notifyFines = v),
                ),
                const Divider(height: 1),
                SwitchListTile(
                  title: const Text('Message Alerts'),
                  subtitle: const Text(
                    'Get notified when you receive messages',
                  ),
                  value: _notifyMessages,
                  onChanged: (v) => setState(() => _notifyMessages = v),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _onSave() async {
    setState(() => _saving = true);
    context.read<ProfileBloc>().add(
      UpdateProfile(
        name: _nameController.text,
        phone: _phoneController.text,
        email: _emailController.text,
        notificationPreferences: {
          'due_dates': _notifyDueDates,
          'fines': _notifyFines,
          'messages': _notifyMessages,
        },
      ),
    );
    if (mounted) Navigator.of(context).pop();
  }
}
