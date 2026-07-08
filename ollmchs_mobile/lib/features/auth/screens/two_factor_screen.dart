import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../bloc/auth_bloc.dart';
import '../bloc/auth_event.dart';
import '../bloc/auth_state.dart';

class TwoFactorScreen extends StatefulWidget {
  final int userId;
  final String tempToken;

  const TwoFactorScreen({
    super.key,
    required this.userId,
    required this.tempToken,
  });

  @override
  State<TwoFactorScreen> createState() => _TwoFactorScreenState();
}

class _TwoFactorScreenState extends State<TwoFactorScreen> {
  final _codeController = TextEditingController();
  final _recoveryController = TextEditingController();
  final _focusNode = FocusNode();
  bool _useRecoveryCode = false;

  @override
  void initState() {
    super.initState();
    _focusNode.requestFocus();
  }

  void _verify() {
    final code = _codeController.text.trim();
    if (code.length != 6) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter a 6-digit code')),
      );
      return;
    }

    context.read<AuthBloc>().add(
      VerifyTwoFactorEvent(
        userId: widget.userId,
        code: code,
        tempToken: widget.tempToken,
      ),
    );
  }

  void _verifyRecovery() {
    final code = _recoveryController.text.trim();
    if (code.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter a recovery code')),
      );
      return;
    }

    context.read<AuthBloc>().add(
      VerifyTwoFactorRecoveryEvent(
        userId: widget.userId,
        recoveryCode: code,
      ),
    );
  }

  void _toggleMode() {
    setState(() {
      _useRecoveryCode = !_useRecoveryCode;
      _codeController.clear();
      _recoveryController.clear();
    });
    if (!_useRecoveryCode) {
      _focusNode.requestFocus();
    }
  }

  @override
  void dispose() {
    _codeController.dispose();
    _recoveryController.dispose();
    _focusNode.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Two-Factor Verification')),
      body: BlocListener<AuthBloc, AuthState>(
        listener: (context, state) {
          if (state is Authenticated) {
            context.goNamed('dashboard');
          } else if (state is AuthError) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(state.message),
                backgroundColor: theme.colorScheme.error,
              ),
            );
            if (_useRecoveryCode) {
              _recoveryController.clear();
            } else {
              _codeController.clear();
              _focusNode.requestFocus();
            }
          }
        },
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Icon(
                  Icons.security,
                  size: 64,
                  color: theme.colorScheme.primary,
                ),
                const SizedBox(height: 16),
                Text(
                  _useRecoveryCode ? 'Recovery Code' : 'Enter Verification Code',
                  textAlign: TextAlign.center,
                  style: theme.textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  _useRecoveryCode
                      ? 'Enter one of your recovery codes'
                      : 'Enter the 6-digit code from your authenticator app',
                  textAlign: TextAlign.center,
                  style: theme.textTheme.bodyMedium?.copyWith(
                    color: theme.colorScheme.onSurfaceVariant,
                  ),
                ),
                const SizedBox(height: 32),

                // Input field
                if (_useRecoveryCode)
                  TextFormField(
                    controller: _recoveryController,
                    textCapitalization: TextCapitalization.characters,
                    textAlign: TextAlign.center,
                    style: theme.textTheme.headlineSmall?.copyWith(
                      letterSpacing: 4,
                      fontWeight: FontWeight.bold,
                      fontFamily: 'monospace',
                    ),
                    decoration: const InputDecoration(
                      hintText: 'XXXX-XXXX-XXXX',
                      border: OutlineInputBorder(),
                    ),
                    onFieldSubmitted: (_) => _verifyRecovery(),
                  )
                else
                  TextFormField(
                    controller: _codeController,
                    focusNode: _focusNode,
                    keyboardType: TextInputType.number,
                    textAlign: TextAlign.center,
                    maxLength: 6,
                    style: theme.textTheme.headlineMedium?.copyWith(
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
                    onChanged: (value) {
                      if (value.length == 6) _verify();
                    },
                  ),
                const SizedBox(height: 24),

                // Verify Button
                BlocBuilder<AuthBloc, AuthState>(
                  builder: (context, state) {
                    final isLoading = state is AuthLoading;
                    return FilledButton(
                      onPressed: isLoading
                          ? null
                          : (_useRecoveryCode ? _verifyRecovery : _verify),
                      style: FilledButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 16),
                      ),
                      child: isLoading
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Text(
                              'Verify',
                              style: TextStyle(fontSize: 16),
                            ),
                    );
                  },
                ),
                const SizedBox(height: 16),

                // Toggle between TOTP and recovery code
                TextButton(
                  onPressed: _toggleMode,
                  child: Text(
                    _useRecoveryCode
                        ? 'Use authenticator app instead'
                        : 'Use a recovery code',
                  ),
                ),

                const SizedBox(height: 24),

                // Back to login
                TextButton(
                  onPressed: () {
                    context.read<AuthBloc>().add(const LogoutEvent());
                    context.goNamed('login');
                  },
                  child: const Text('Back to Login'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
