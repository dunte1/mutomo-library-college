import 'dart:convert';
import 'dart:math';
import 'package:crypto/crypto.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../core/storage/local_storage_service.dart';
import '../../../core/theme/theme_cubit.dart';
import '../../../core/services/biometric_service.dart';
import '../../../core/network/api_client.dart';
import '../../auth/repositories/auth_repository.dart';
import '../../auth/bloc/auth_bloc.dart';
import '../../auth/bloc/auth_event.dart';

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
  bool _biometricEnrolled = false;
  String _biometricUnavailableReason = '';
  bool _pinEnabled = false;
  bool _twoFactorEnabled = false;
  bool _loading2FA = false;
  bool _autoDownloads = false;
  bool _offlineSync = true;
  String _downloadQuality = 'standard';
  final _biometricService = BiometricService();

  @override
  void initState() {
    super.initState();
    _loadPreferences();
  }

  Future<void> _loadPreferences() async {
    final storage = context.read<LocalStorageService>();
    final notifications = await storage.getNotificationsEnabled();
    final biometric = await storage.getBiometricEnabled();
    final pin = await storage.getPinEnabled();
    final autoDl = await storage.getAutoDownloads();
    final offlineS = await storage.getOfflineSync();
    final dlQuality = await storage.getDownloadQuality();
    final available = await _biometricService.isAvailable;
    final enrolled = available ? await _biometricService.hasEnrolledBiometrics : false;
    if (!context.mounted) return;
    final themeMode = context.read<ThemeCubit>().state;

    String unavailableReason = '';
    if (!available) {
      unavailableReason = await _biometricService.unavailableReason;
      // Auto-disable if device no longer supports biometrics
      if (biometric) {
        await storage.setBiometricEnabled(false);
      }
    }

    // Fetch user profile to check 2FA status
    bool twoFactor = false;
    try {
      if (!context.mounted) return;
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
        _biometricEnabled = biometric && available && enrolled;
        _biometricAvailable = available;
        _biometricEnrolled = enrolled;
        _biometricUnavailableReason = unavailableReason;
        _pinEnabled = pin;
        _twoFactorEnabled = twoFactor;
        _autoDownloads = autoDl;
        _offlineSync = offlineS;
        _downloadQuality = dlQuality;
      });
    }
  }

  Future<void> _onBiometricChanged(bool v) async {
    final storage = context.read<LocalStorageService>();
    if (v) {
      final available = await _biometricService.isAvailable;
      if (!available) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                await _biometricService.unavailableReason,
              ),
            ),
          );
        }
        return;
      }

      // Check if biometrics are enrolled
      final hasEnrolled = await _biometricService.hasEnrolledBiometrics;
      if (!hasEnrolled) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text(
                'No biometrics enrolled. Add a fingerprint or face in your device settings, then try again.',
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

  Future<void> _onPinChanged(bool v) async {
    final storage = context.read<LocalStorageService>();
    if (v) {
      if (!mounted) return;
      final pin = await _showPinSetupDialog();
      if (pin == null || pin.isEmpty || !mounted) return;

      // Hash and store PIN
      final rng = Random.secure();
      final salt = base64Url.encode(List<int>.generate(16, (_) => rng.nextInt(256)));
      final hash = _hashPin(pin, salt);
      await storage.savePinHash('$salt:$hash');
      await storage.setPinEnabled(true);
      setState(() => _pinEnabled = true);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('App PIN enabled')),
        );
      }
    } else {
      await storage.setPinEnabled(false);
      await storage.clearPin();
      setState(() => _pinEnabled = false);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('App PIN disabled')),
        );
      }
    }
  }

  String _hashPin(String pin, String salt) {
    final combined = '$pin:$salt';
    final bytes = utf8.encode(combined);
    return sha256.convert(bytes).toString();
  }

  Future<String?> _showPinSetupDialog() async {
    final pinController = TextEditingController();
    final confirmController = TextEditingController();
    final formKey = GlobalKey<FormState>();
    String? result;

    await showDialog<void>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        title: const Text('Set App PIN'),
        content: Form(
          key: formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text('Choose a 4-8 digit PIN to unlock the app.'),
              const SizedBox(height: 16),
              TextFormField(
                controller: pinController,
                obscureText: true,
                keyboardType: TextInputType.number,
                maxLength: 8,
                decoration: const InputDecoration(
                  labelText: 'New PIN',
                  counterText: '',
                  border: OutlineInputBorder(),
                ),
                validator: (v) {
                  if (v == null || v.length < 4 || v.length > 8) {
                    return 'PIN must be 4-8 digits';
                  }
                  return null;
                },
                autofocus: true,
              ),
              const SizedBox(height: 8),
              TextFormField(
                controller: confirmController,
                obscureText: true,
                keyboardType: TextInputType.number,
                maxLength: 8,
                decoration: const InputDecoration(
                  labelText: 'Confirm PIN',
                  counterText: '',
                  border: OutlineInputBorder(),
                ),
                validator: (v) {
                  if (v != pinController.text) return 'PINs do not match';
                  return null;
                },
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () {
              if (formKey.currentState!.validate()) {
                result = pinController.text;
                Navigator.of(ctx).pop();
              }
            },
            child: const Text('Set PIN'),
          ),
        ],
      ),
    );

    return result;
  }

  Future<void> _onTwoFactorChanged(bool v) async {
    if (v) {
      final result = await context.push<bool>('/two-factor-setup');
      if (result == true && mounted) {
        setState(() => _twoFactorEnabled = true);
      }
    } else {
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
                context.read<LocalStorageService>().setNotificationsEnabled(v);
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

          // Biometric Toggle
          if (_biometricAvailable && _biometricEnrolled)
            Card(
              child: SwitchListTile(
                title: const Text('Biometric Login'),
                subtitle: const Text('Use fingerprint or face to sign in'),
                value: _biometricEnabled,
                onChanged: _onBiometricChanged,
                secondary: Icon(
                  _biometricEnabled
                      ? Icons.fingerprint
                      : Icons.fingerprint_outlined,
                  color: _biometricEnabled
                      ? theme.colorScheme.primary
                      : null,
                ),
              ),
            ),

          // Biometrics: hardware exists but no enrollment
          if (_biometricAvailable && !_biometricEnrolled)
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Icon(
                      Icons.fingerprint_outlined,
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'Biometric hardware detected. '
                        'Enroll a fingerprint or face in device settings to enable biometric login.',
                        style: theme.textTheme.bodySmall?.copyWith(
                          color: theme.colorScheme.onSurfaceVariant,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

          // Biometrics unavailable explanation
          if (!_biometricAvailable && _biometricUnavailableReason.isNotEmpty)
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Icon(
                      Icons.info_outline,
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        _biometricUnavailableReason,
                        style: theme.textTheme.bodySmall?.copyWith(
                          color: theme.colorScheme.onSurfaceVariant,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

          // App PIN Toggle
          Card(
            child: SwitchListTile(
              title: const Text('App PIN'),
              subtitle: Text(
                _pinEnabled
                    ? 'Enter PIN to unlock the app'
                    : 'Set a PIN to unlock the app when locked',
              ),
              value: _pinEnabled,
              onChanged: _onPinChanged,
              secondary: Icon(
                _pinEnabled ? Icons.pin : Icons.pin_outlined,
                color: _pinEnabled ? theme.colorScheme.primary : null,
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Session Info
          Text(
            'Session',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.timer_outlined),
                  title: const Text('App Lock'),
                  subtitle: const Text(
                    'App locks after 5 minutes in background',
                  ),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.access_time),
                  title: const Text('Session Timeout'),
                  subtitle: const Text(
                    'Full login required after 30 minutes inactivity',
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Downloads & Offline
          Text(
            'Downloads & Storage',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Card(
            child: Column(
              children: [
                SwitchListTile(
                  title: const Text('Auto Downloads'),
                  subtitle: const Text('Automatically download assigned readings'),
                  value: _autoDownloads,
                  onChanged: (v) {
                    setState(() => _autoDownloads = v);
                    context.read<LocalStorageService>().setAutoDownloads(v);
                  },
                ),
                const Divider(height: 1),
                SwitchListTile(
                  title: const Text('Offline Sync'),
                  subtitle: const Text('Sync data when connected to the internet'),
                  value: _offlineSync,
                  onChanged: (v) {
                    setState(() => _offlineSync = v);
                    context.read<LocalStorageService>().setOfflineSync(v);
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.high_quality_outlined),
                  title: const Text('Download Quality'),
                  subtitle: Text(
                    _downloadQuality == 'high'
                        ? 'High quality (larger files)'
                        : 'Standard quality (smaller files)',
                  ),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () {
                    showDialog(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        title: const Text('Download Quality'),
                        content: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            RadioListTile<String>(
                              title: const Text('High Quality'),
                              subtitle: const Text('Best quality, larger files'),
                              value: 'high',
                              groupValue: _downloadQuality,
                              onChanged: (v) {
                                setState(() => _downloadQuality = v!);
                                context.read<LocalStorageService>().setDownloadQuality(v!);
                                Navigator.pop(ctx);
                              },
                            ),
                            RadioListTile<String>(
                              title: const Text('Standard Quality'),
                              subtitle: const Text('Good quality, smaller files'),
                              value: 'standard',
                              groupValue: _downloadQuality,
                              onChanged: (v) {
                                setState(() => _downloadQuality = v!);
                                context.read<LocalStorageService>().setDownloadQuality(v!);
                                Navigator.pop(ctx);
                              },
                            ),
                          ],
                        ),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(ctx),
                            child: const Text('Cancel'),
                          ),
                        ],
                      ),
                    );
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.storage_outlined),
                  title: const Text('Clear Cache'),
                  subtitle: const Text('Free up storage space'),
                  onTap: () async {
                    final confirmed = await showDialog<bool>(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        title: const Text('Clear Cache'),
                        content: const Text('This will clear all cached data. Your downloads will not be affected.'),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(ctx, false),
                            child: const Text('Cancel'),
                          ),
                          FilledButton(
                            onPressed: () => Navigator.pop(ctx, true),
                            child: const Text('Clear'),
                          ),
                        ],
                      ),
                    );
                    if (confirmed == true && context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Cache cleared')),
                      );
                    }
                  },
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Privacy & Security
          Text(
            'Privacy & Security',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.lock_outline),
                  title: const Text('Change Password'),
                  subtitle: const Text('Update your account password'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => context.pushNamed('change-password'),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.privacy_tip_outlined),
                  title: const Text('Privacy Policy'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => context.pushNamed('privacy-policy'),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.description_outlined),
                  title: const Text('Terms of Service'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => context.pushNamed('terms-of-service'),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Danger Zone
          Text(
            'Account',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
              color: theme.colorScheme.error,
            ),
          ),
          const SizedBox(height: 8),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.logout, color: Colors.orange),
                  title: const Text('Sign Out'),
                  subtitle: const Text('Sign out from all devices'),
                  onTap: () async {
                    final confirmed = await showDialog<bool>(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        title: const Text('Sign Out'),
                        content: const Text('Are you sure you want to sign out from all devices?'),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(ctx, false),
                            child: const Text('Cancel'),
                          ),
                          FilledButton(
                            onPressed: () => Navigator.pop(ctx, true),
                            child: const Text('Sign Out'),
                          ),
                        ],
                      ),
                    );
                    if (confirmed == true && context.mounted) {
                      context.read<AuthBloc>().add(const LogoutEvent());
                    }
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: Icon(Icons.delete_forever_outlined, color: theme.colorScheme.error),
                  title: Text(
                    'Delete Account',
                    style: TextStyle(color: theme.colorScheme.error),
                  ),
                  subtitle: const Text('Permanently delete your account and data'),
                  onTap: () async {
                    final password = await _showPasswordDialog();
                    if (password == null || password.isEmpty || !mounted) return;

                    final confirmed = await showDialog<bool>(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        title: const Text('Delete Account'),
                        content: const Text(
                          'This action is irreversible. All your data, including borrowing history, reservations, and messages will be permanently deleted.',
                        ),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(ctx, false),
                            child: const Text('Cancel'),
                          ),
                          FilledButton(
                            style: FilledButton.styleFrom(
                              backgroundColor: theme.colorScheme.error,
                            ),
                            onPressed: () => Navigator.pop(ctx, true),
                            child: const Text('Delete'),
                          ),
                        ],
                      ),
                    );
                    if (confirmed == true && context.mounted) {
                      try {
                        final repo = context.read<AuthRepository>();
                        await repo.deleteAccount(password: password);
                        if (context.mounted) {
                          context.read<AuthBloc>().add(const LogoutEvent());
                        }
                      } catch (e) {
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text('Failed to delete account: $e'),
                              backgroundColor: theme.colorScheme.error,
                            ),
                          );
                        }
                      }
                    }
                  },
                ),
              ],
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
                  title: const Text('About OLLMCHS Library'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => context.goNamed('about'),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.help_outline),
                  title: const Text('Help & Support'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => context.goNamed('help'),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.info_outline),
                  title: const Text('Version'),
                  subtitle: const Text('1.0.0'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
