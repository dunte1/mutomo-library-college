import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:qr_flutter/qr_flutter.dart';
import '../bloc/auth_bloc.dart';
import '../bloc/auth_event.dart';
import '../bloc/auth_state.dart';
import '../../../core/services/flag_secure_service.dart';

class TwoFactorSetupScreen extends StatefulWidget {
  const TwoFactorSetupScreen({super.key});

  @override
  State<TwoFactorSetupScreen> createState() => _TwoFactorSetupScreenState();
}

class _TwoFactorSetupScreenState extends State<TwoFactorSetupScreen> {
  final _otpController = TextEditingController();
  bool _codesSaved = false;

  @override
  void initState() {
    super.initState();
    FlagSecureService.enable();
    _requestPassword();
  }

  void _requestPassword() async {
    final password = await _showPasswordDialog();
    if (password == null || password.isEmpty) {
      if (mounted) Navigator.of(context).pop();
      return;
    }
    if (mounted) {
      context.read<AuthBloc>().add(EnableTwoFactorSetupEvent(password: password));
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

  void _verifyAndActivate(String code) {
    if (code.length != 6) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Enter a 6-digit code from your authenticator app'),
        ),
      );
      return;
    }
    context.read<AuthBloc>().add(VerifyTwoFactorSetupEvent(code: code));
  }

  void _copyRecoveryCodes(List<String> codes) {
    Clipboard.setData(ClipboardData(text: codes.join('\n')));
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
      body: BlocConsumer<AuthBloc, AuthState>(
        listener: (context, state) {
          if (state is TwoFactorSetupVerified) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text(state.message)),
            );
            Navigator.of(context).pop(true);
          } else if (state is AuthError) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(state.message),
                backgroundColor: theme.colorScheme.error,
              ),
            );
          }
        },
        builder: (context, state) {
          if (state is AuthLoading && state is! TwoFactorSetupReady) {
            return const Center(child: CircularProgressIndicator());
          }

          if (state is AuthError && state is! TwoFactorSetupReady) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.error_outline, size: 48, color: theme.colorScheme.error),
                    const SizedBox(height: 16),
                    Text(state.message, textAlign: TextAlign.center),
                    const SizedBox(height: 16),
                    FilledButton.tonal(
                      onPressed: () => Navigator.of(context).pop(),
                      child: const Text('Go Back'),
                    ),
                  ],
                ),
              ),
            );
          }

          if (state is TwoFactorSetupReady) {
            return _buildSetupContent(theme, state);
          }

          return const Center(child: CircularProgressIndicator());
        },
      ),
    );
  }

  Widget _buildSetupContent(ThemeData theme, TwoFactorSetupReady state) {
    final isVerifying = context.watch<AuthBloc>().state is AuthLoading;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _stepHeader(theme, 1, 'Scan QR Code'),
          const SizedBox(height: 8),
          Text(
            'Open your authenticator app (Google Authenticator, Authy, etc.) and scan this QR code.',
            style: theme.textTheme.bodyMedium,
          ),
          const SizedBox(height: 16),
          if (state.qrCodeUrl.isNotEmpty)
            Center(
              child: Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: theme.colorScheme.outline),
                ),
                child: QrImageView(
                  data: state.qrCodeUrl,
                  version: QrVersions.auto,
                  size: 200,
                  eyeStyle: QrEyeStyle(
                    eyeShape: QrEyeShape.square,
                    color: theme.colorScheme.primary,
                  ),
                  dataModuleStyle: QrDataModuleStyle(
                    dataModuleShape: QrDataModuleShape.square,
                    color: theme.colorScheme.primary,
                  ),
                ),
              ),
            ),
          const SizedBox(height: 12),
          Text('Or enter this code manually:', style: theme.textTheme.bodySmall),
          const SizedBox(height: 4),
          SelectableText(
            state.secret,
            style: theme.textTheme.bodyLarge?.copyWith(
              fontFamily: 'monospace',
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 24),

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
                for (final code in state.recoveryCodes)
                  Padding(
                    padding: const EdgeInsets.symmetric(vertical: 2),
                    child: Text(
                      code,
                      style: const TextStyle(fontFamily: 'monospace', fontSize: 14),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              OutlinedButton.icon(
                onPressed: () => _copyRecoveryCodes(state.recoveryCodes),
                icon: const Icon(Icons.copy, size: 16),
                label: const Text('Copy Codes'),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: CheckboxListTile(
                  contentPadding: EdgeInsets.zero,
                  title: const Text('I have saved these codes'),
                  value: _codesSaved,
                  onChanged: (v) => setState(() => _codesSaved = v ?? false),
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),

          _stepHeader(theme, 3, 'Verify & Activate'),
          const SizedBox(height: 8),
          Text(
            'Enter the 6-digit code from your authenticator app to confirm setup is working.',
            style: theme.textTheme.bodyMedium,
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: _otpController,
            keyboardType: TextInputType.number,
            textAlign: TextAlign.center,
            maxLength: 6,
            enabled: !isVerifying,
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
          FilledButton(
            onPressed: isVerifying
                ? null
                : () => _verifyAndActivate(_otpController.text.trim()),
            style: FilledButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
            ),
            child: isVerifying
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
          style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
        ),
      ],
    );
  }
}
