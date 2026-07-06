import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../core/storage/local_storage_service.dart';
import '../../../core/theme/theme_cubit.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/biometric_service.dart';
import '../../../core/network/api_client.dart';
import '../../auth/repositories/auth_repository.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  bool _notificationsEnabled = true;
  bool _useDarkMode = false;
  bool _biometricEnabled = false;
  bool _biometricAvailable = false;
  bool _twoFactorEnabled = false;
  bool _loading2FA = false;
  final _biometricService = BiometricService();

  @override
  void initState() {
    super.initState();
    _loadPreferences();
  }

  Future<void> _loadPreferences() async {
    final storage = LocalStorageService();
    final notifications = await storage.getNotificationsEnabled();
    final biometric = await storage.getBiometricEnabled();
    final available = await _biometricService.isAvailable;
    final themeMode = context.read<ThemeCubit>().state;

    // Fetch user profile to check 2FA status
    bool twoFactor = false;
    try {
      final api = context.read<ApiClient>();
      final response = await api.get('/v1/auth/user');
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      twoFactor = data['two_factor_enabled'] as bool? ?? false;
    } catch (_) {}

    if (mounted) {
      setState(() {
        _notificationsEnabled = notifications;
        _useDarkMode = themeMode == ThemeMode.dark;
        _biometricEnabled = biometric;
        _biometricAvailable = available;
        _twoFactorEnabled = twoFactor;
      });
    }
  }

  Future<void> _onBiometricChanged(bool v) async {
    final storage = LocalStorageService();
    if (v) {
      final available = await _biometricService.isAvailable;
      if (!available) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text(
                'Biometric authentication is not available on this device',
              ),
            ),
          );
        }
        return;
      }

      final authed = await _biometricService.authenticate(
        reason: 'Verify your identity to enable biometric login',
      );
      if (!authed || !mounted) return;

      final token = await storage.getToken();
      if (token == null) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Please log in first, then enable biometric login'),
            ),
          );
        }
        return;
      }

      setState(() => _biometricEnabled = true);
      await storage.setBiometricEnabled(true);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Biometric login enabled')),
        );
      }
    } else {
      setState(() => _biometricEnabled = false);
      await storage.setBiometricEnabled(false);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Biometric login disabled')),
        );
      }
    }
  }

  Future<void> _onTwoFactorChanged(bool v) async {
    if (v) {
      // Navigate to setup screen — it handles the enable flow
      final result = await context.push<bool>('/two-factor-setup');
      if (result == true && mounted) {
        setState(() => _twoFactorEnabled = true);
      }
    } else {
      // Disable 2FA — need password + current TOTP code
      await _disableTwoFactor();
    }
  }

  Future<void> _disableTwoFactor() async {
    final password = await _showPasswordDialog();
    if (password == null || password.isEmpty || !mounted) return;

    final code = await _showOtpDialog();
    if (code == null || code.isEmpty || !mounted) return;

    setState(() => _loading2FA = true);
    try {
      final repo = context.read<AuthRepository>();
      await repo.disableTwoFactor(password: password, code: code);
      setState(() {
        _twoFactorEnabled = false;
        _loading2FA = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Two-factor authentication disabled')),
        );
      }
    } catch (e) {
      setState(() => _loading2FA = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to disable 2FA: $e'),
            backgroundColor: Theme.of(context).colorScheme.error,
          ),
        );
      }
    }
  }

  Future<String?> _showPasswordDialog() async {
    final controller = TextEditingController();
    return showDialog<String>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        title: const Text('Confirm Password'),
        content: TextField(
          controller: controller,
          obscureText: true,
          decoration: const InputDecoration(
            labelText: 'Current Password',
            border: OutlineInputBorder(),
          ),
          autofocus: true,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.of(ctx).pop(controller.text),
            child: const Text('Confirm'),
          ),
        ],
      ),
    );
  }

  Future<String?> _showOtpDialog() async {
    final controller = TextEditingController();
    return showDialog<String>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        title: const Text('Enter Authenticator Code'),
        content: TextField(
          controller: controller,
          keyboardType: TextInputType.number,
          maxLength: 6,
          decoration: const InputDecoration(
            labelText: '6-digit code',
            counterText: '',
            border: OutlineInputBorder(),
          ),
          autofocus: true,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.of(ctx).pop(controller.text),
            child: const Text('Confirm'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Settings')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Notifications
          Text(
            'Notifications',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Card(
            child: SwitchListTile(
              title: const Text('Push Notifications'),
              subtitle: const Text(
                'Receive alerts for due dates, fines, and messages',
              ),
              value: _notificationsEnabled,
              onChanged: (v) {
                setState(() => _notificationsEnabled = v);
                LocalStorageService().setNotificationsEnabled(v);
              },
            ),
          ),
          const SizedBox(height: 16),

          // Security
          Text(
            'Security',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),

          // 2FA Toggle
          Card(
            child: SwitchListTile(
              title: const Text('Two-Factor Authentication'),
              subtitle: Text(
                _twoFactorEnabled
                    ? 'Enabled — requires authenticator code at login'
                    : 'Add an extra layer of security to your account',
              ),
              value: _twoFactorEnabled,
              onChanged: _loading2FA ? null : _onTwoFactorChanged,
              secondary: _loading2FA
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : Icon(
                      _twoFactorEnabled
                          ? Icons.security
                          : Icons.security_outlined,
                      color: _twoFactorEnabled
                          ? theme.colorScheme.primary
                          : null,
                    ),
            ),
          ),

          // Biometric Toggle (only on capable devices)
          if (_biometricAvailable)
            Card(
              child: SwitchListTile(
                title: const Text('Biometric Login'),
                subtitle: const Text('Use fingerprint or face to sign in'),
                value: _biometricEnabled,
                onChanged: _onBiometricChanged,
              ),
            ),
          const SizedBox(height: 16),

          // Appearance
          Text(
            'Appearance',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Card(
            child: SwitchListTile(
              title: const Text('Dark Mode'),
              subtitle: const Text('Use dark theme'),
              value: _useDarkMode,
              onChanged: (v) {
                setState(() => _useDarkMode = v);
                context.read<ThemeCubit>().setThemeMode(v ? 'dark' : 'light');
              },
            ),
          ),
          const SizedBox(height: 16),

          // About
          Text(
            'About',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.info_outline),
                  title: const Text('Version'),
                  subtitle: const Text('1.0.0'),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.library_books),
                  title: const Text('Library'),
                  subtitle: Text(AppConstants.appName),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
