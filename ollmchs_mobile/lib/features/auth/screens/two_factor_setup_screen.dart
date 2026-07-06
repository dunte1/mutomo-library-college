import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/flutter_svg.dart';
import '../../auth/repositories/auth_repository.dart';

class TwoFactorSetupScreen extends StatefulWidget {
  const TwoFactorSetupScreen({super.key});

  @override
  State<TwoFactorSetupScreen> createState() => _TwoFactorSetupScreenState();
}

class _TwoFactorSetupScreenState extends State<TwoFactorSetupScreen> {
  bool _loading = true;
  String? _error;
  String? _secret;
  String? _qrCodeUrl;
  List<String> _recoveryCodes = [];
  bool _codesSaved = false;
  bool _verifying = false;
  final _otpController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _enableTwoFactor();
  }

  Future<void> _enableTwoFactor() async {
    if (!mounted) return;
    final password = await _showPasswordDialog();
    if (password == null || password.isEmpty) {
      if (mounted) Navigator.of(context).pop();
      return;
    }

    try {
      final repo = context.read<AuthRepository>();
      final result = await repo.enableTwoFactor(password: password);

      if (mounted) {
        setState(() {
          _secret = result['secret'];
          _qrCodeUrl = result['qr_code_url'];
          _recoveryCodes = result['recovery_codes'];
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = 'Failed to start 2FA setup: $e';
          _loading = false;
        });
      }
    }
  }

  Future<void> _verifyAndActivate() async {
    final code = _otpController.text.trim();
    if (code.length != 6) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Enter a 6-digit code from your authenticator app'),
        ),
      );
      return;
    }

    setState(() => _verifying = true);
    try {
      final repo = context.read<AuthRepository>();
      await repo.verifyTwoFactorSetup(code: code);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Two-factor authentication is now active'),
          ),
        );
        Navigator.of(context).pop(true);
      }
    } catch (e) {
      setState(() => _verifying = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Invalid code. Make sure your authenticator app is synced and try again.',
            ),
            backgroundColor: Theme.of(context).colorScheme.error,
          ),
        );
        _otpController.clear();
      }
    }
  }

  Future<String?> _showPasswordDialog() async {
    final controller = TextEditingController();
    return showDialog<String>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        title: const Text('Confirm Identity'),
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

  void _copyRecoveryCodes() {
    final text = _recoveryCodes.join('\n');
    Clipboard.setData(ClipboardData(text: text));
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Recovery codes copied to clipboard')),
    );
  }

  @override
  void dispose() {
    _otpController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Enable Two-Factor Authentication')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
          ? Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.error_outline,
                      size: 48,
                      color: theme.colorScheme.error,
                    ),
                    const SizedBox(height: 16),
                    Text(_error!, textAlign: TextAlign.center),
                    const SizedBox(height: 16),
                    FilledButton.tonal(
                      onPressed: () => Navigator.of(context).pop(),
                      child: const Text('Go Back'),
                    ),
                  ],
                ),
              ),
            )
          : SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Step 1: Scan QR code
                  _stepHeader(theme, 1, 'Scan QR Code'),
                  const SizedBox(height: 8),
                  Text(
                    'Open your authenticator app (Google Authenticator, Authy, etc.) and scan this QR code.',
                    style: theme.textTheme.bodyMedium,
                  ),
                  const SizedBox(height: 16),

                  if (_qrCodeUrl != null && _qrCodeUrl!.isNotEmpty)
                    Center(
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: theme.colorScheme.outline),
                        ),
                        child: _qrCodeUrl!.startsWith('http')
                            ? CachedNetworkImage(
                                imageUrl: _qrCodeUrl!,
                                height: 200,
                                width: 200,
                                placeholder: (_, __) => Container(color: Colors.grey[200]),
                                errorWidget: (_, __, ___) => Icon(Icons.broken_image, color: Colors.grey),
                              )
                            : SvgPicture.string(
                                _qrCodeUrl!,
                                height: 200,
                                width: 200,
                              ),
                      ),
                    ),

                  if (_secret != null) ...[
                    const SizedBox(height: 12),
                    Text(
                      'Or enter this code manually:',
                      style: theme.textTheme.bodySmall,
                    ),
                    const SizedBox(height: 4),
                    SelectableText(
                      _secret!,
                      style: theme.textTheme.bodyLarge?.copyWith(
                        fontFamily: 'monospace',
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],

                  const SizedBox(height: 24),

                  // Step 2: Save recovery codes
                  _stepHeader(theme, 2, 'Save Recovery Codes'),
                  const SizedBox(height: 8),
                  Text(
                    'Save these codes somewhere safe. You can use each code once if you lose access to your authenticator app.',
                    style: theme.textTheme.bodyMedium,
                  ),
                  const SizedBox(height: 12),

                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: theme.colorScheme.surfaceContainerHighest,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        for (final code in _recoveryCodes)
                          Padding(
                            padding: const EdgeInsets.symmetric(vertical: 2),
                            child: Text(
                              code,
                              style: const TextStyle(
                                fontFamily: 'monospace',
                                fontSize: 14,
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 12),

                  Row(
                    children: [
                      OutlinedButton.icon(
                        onPressed: _copyRecoveryCodes,
                        icon: const Icon(Icons.copy, size: 16),
                        label: const Text('Copy Codes'),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: CheckboxListTile(
                          contentPadding: EdgeInsets.zero,
                          title: const Text('I have saved these codes'),
                          value: _codesSaved,
                          onChanged: (v) =>
                              setState(() => _codesSaved = v ?? false),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 24),

                  // Step 3: Verify with authenticator code
                  _stepHeader(theme, 3, 'Verify & Activate'),
                  const SizedBox(height: 8),
                  Text(
                    'Enter the 6-digit code from your authenticator app to confirm setup is working. 2FA will only be activated after successful verification.',
                    style: theme.textTheme.bodyMedium,
                  ),
                  const SizedBox(height: 12),

                  TextFormField(
                    controller: _otpController,
                    keyboardType: TextInputType.number,
                    textAlign: TextAlign.center,
                    maxLength: 6,
                    enabled: _codesSaved && !_verifying,
                    style: theme.textTheme.headlineSmall?.copyWith(
                      letterSpacing: 8,
                      fontWeight: FontWeight.bold,
                    ),
                    inputFormatters: [
                      FilteringTextInputFormatter.digitsOnly,
                      LengthLimitingTextInputFormatter(6),
                    ],
                    decoration: const InputDecoration(
                      hintText: '000000',
                      counterText: '',
                      border: OutlineInputBorder(),
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Activate button
                  FilledButton(
                    onPressed: _codesSaved && !_verifying
                        ? _verifyAndActivate
                        : null,
                    style: FilledButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                    ),
                    child: _verifying
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Text(
                            'Activate Two-Factor Authentication',
                            style: TextStyle(fontSize: 16),
                          ),
                  ),

                  const SizedBox(height: 12),

                  TextButton(
                    onPressed: () => Navigator.of(context).pop(),
                    child: const Text('Cancel'),
                  ),
                ],
              ),
            ),
    );
  }

  Widget _stepHeader(ThemeData theme, int number, String title) {
    return Row(
      children: [
        CircleAvatar(
          radius: 14,
          backgroundColor: theme.colorScheme.primary,
          child: Text(
            '$number',
            style: TextStyle(
              color: theme.colorScheme.onPrimary,
              fontSize: 12,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
        const SizedBox(width: 8),
        Text(
          title,
          style: theme.textTheme.titleMedium?.copyWith(
            fontWeight: FontWeight.bold,
          ),
        ),
      ],
    );
  }
}
