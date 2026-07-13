import 'dart:convert';
import 'dart:math';
import 'package:crypto/crypto.dart';
import 'package:flutter/material.dart';
import '../storage/local_storage_service.dart';
import '../services/biometric_service.dart';

class AppLockScreen extends StatefulWidget {
  final VoidCallback onUnlocked;

  const AppLockScreen({super.key, required this.onUnlocked});

  @override
  State<AppLockScreen> createState() => _AppLockScreenState();
}

class _AppLockScreenState extends State<AppLockScreen> {
  final _biometricService = BiometricService();
  final _storage = LocalStorageService();
  bool _showFallback = false;
  bool _pinMode = false;
  final bool _pinSetupMode = false;
  String? _errorMessage;
  final _pinController = TextEditingController();
  final _pinConfirmController = TextEditingController();
  bool _obscurePin = true;

  @override
  void initState() {
    super.initState();
    _tryBiometric();
  }

  @override
  void dispose() {
    _pinController.dispose();
    _pinConfirmController.dispose();
    super.dispose();
  }

  Future<void> _tryBiometric() async {
    final enabled = await _storage.getBiometricEnabled();
    final available = await _biometricService.isAvailable;

    if (!available || !enabled) {
      if (mounted) _checkPinFallback();
      return;
    }

    final authenticated = await _biometricService.authenticate(
      reason: 'Authenticate to unlock the app',
    );

    if (authenticated) {
      widget.onUnlocked();
      return;
    }

    if (mounted) {
      _checkPinFallback();
    }
  }

  Future<void> _checkPinFallback() async {
    final pinEnabled = await _storage.getPinEnabled();
    if (mounted) {
      setState(() {
        _pinMode = pinEnabled;
        if (!pinEnabled) {
          _showFallback = true;
        } else {
          _showFallback = true;
        }
      });
    }
  }

  Future<void> _verifyPin() async {
    final pin = _pinController.text;
    if (pin.length < 4 || pin.length > 8) {
      setState(() => _errorMessage = 'PIN must be 4-8 digits');
      return;
    }

    final storedHash = await _storage.getPinHash();
    if (storedHash == null) {
      setState(() => _errorMessage = 'No PIN configured');
      return;
    }

    final parts = storedHash.split(':');
    if (parts.length != 2) {
      setState(() => _errorMessage = 'Invalid PIN configuration');
      return;
    }

    final salt = parts[0];
    final expectedHash = parts[1];
    final inputHash = _hashPin(pin, salt);

    if (inputHash == expectedHash) {
      widget.onUnlocked();
    } else {
      setState(() {
        _errorMessage = 'Incorrect PIN';
        _pinController.clear();
      });
    }
  }

  Future<void> _setupPin() async {
    final pin = _pinController.text;
    final confirm = _pinConfirmController.text;

    if (pin.length < 4 || pin.length > 8) {
      setState(() => _errorMessage = 'PIN must be 4-8 digits');
      return;
    }
    if (pin != confirm) {
      setState(() => _errorMessage = 'PINs do not match');
      return;
    }

    final salt = _generateSalt();
    final hash = _hashPin(pin, salt);
    await _storage.savePinHash('$salt:$hash');
    await _storage.setPinEnabled(true);

    // Unlock immediately after successful PIN setup
    widget.onUnlocked();
  }

  String _generateSalt() {
    final rng = Random.secure();
    final bytes = List<int>.generate(16, (_) => rng.nextInt(256));
    return base64Url.encode(bytes);
  }

  String _hashPin(String pin, String salt) {
    final combined = '$pin:$salt';
    final bytes = utf8.encode(combined);
    return sha256.convert(bytes).toString();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return PopScope(
      canPop: false,
      child: Scaffold(
        backgroundColor: theme.colorScheme.surface,
        body: SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: _buildContent(theme),
          ),
        ),
      ),
    );
  }

  Widget _buildContent(ThemeData theme) {
    if (_pinSetupMode) return _buildPinSetupView(theme);
    if (_showFallback && _pinMode) return _buildPinUnlockView(theme);
    if (_showFallback) return _buildGoToLoginView(theme);
    return _buildBiometricView(theme);
  }

  Widget _buildBiometricView(ThemeData theme) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.lock_outline, size: 80, color: theme.colorScheme.primary),
          const SizedBox(height: 24),
          Text(
            'App Locked',
            style: theme.textTheme.headlineMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Authenticate to continue',
            style: theme.textTheme.bodyLarge?.copyWith(
              color: theme.colorScheme.onSurfaceVariant,
            ),
          ),
          const SizedBox(height: 32),
          FilledButton.icon(
            onPressed: _tryBiometric,
            icon: const Icon(Icons.fingerprint),
            label: const Text('Authenticate'),
            style: FilledButton.styleFrom(
              padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 16),
            ),
          ),
          const SizedBox(height: 16),
          TextButton(
            onPressed: _checkPinFallback,
            child: const Text('Use PIN instead'),
          ),
        ],
      ),
    );
  }

  Widget _buildPinUnlockView(ThemeData theme) {
    return Center(
      child: SingleChildScrollView(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.lock_outline, size: 64, color: theme.colorScheme.primary),
            const SizedBox(height: 24),
            Text(
              'Enter PIN',
              style: theme.textTheme.headlineMedium?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 32),
            SizedBox(
              width: 300,
              child: TextField(
                controller: _pinController,
                obscureText: _obscurePin,
                keyboardType: TextInputType.number,
                maxLength: 8,
                textInputAction: TextInputAction.done,
                decoration: InputDecoration(
                  labelText: 'PIN (4-8 digits)',
                  counterText: '',
                  prefixIcon: const Icon(Icons.pin_outlined),
                  border: const OutlineInputBorder(),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscurePin ? Icons.visibility_off : Icons.visibility,
                    ),
                    onPressed: () => setState(() => _obscurePin = !_obscurePin),
                  ),
                ),
                onSubmitted: (_) => _verifyPin(),
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: 300,
              child: FilledButton(
                onPressed: _verifyPin,
                style: FilledButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: const Text('Unlock'),
              ),
            ),
            if (_errorMessage != null) ...[
              const SizedBox(height: 16),
              Text(
                _errorMessage!,
                style: TextStyle(color: theme.colorScheme.error),
                textAlign: TextAlign.center,
              ),
            ],
            const SizedBox(height: 24),
            TextButton(
              onPressed: () => setState(() {
                _showFallback = false;
                _errorMessage = null;
              }),
              child: const Text('Try biometrics'),
            ),
            const SizedBox(height: 8),
            TextButton(
              onPressed: _exitToLogin,
              child: Text(
                'Exit to Login',
                style: TextStyle(color: theme.colorScheme.error),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildGoToLoginView(ThemeData theme) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.lock_outline, size: 64, color: theme.colorScheme.error),
          const SizedBox(height: 24),
          Text(
            'Unable to Unlock',
            style: theme.textTheme.headlineSmall?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'No biometric or PIN available.\nPlease log in with your password.',
            textAlign: TextAlign.center,
            style: theme.textTheme.bodyLarge?.copyWith(
              color: theme.colorScheme.onSurfaceVariant,
            ),
          ),
          const SizedBox(height: 32),
          FilledButton.icon(
            onPressed: _exitToLogin,
            icon: const Icon(Icons.login),
            label: const Text('Go to Login'),
            style: FilledButton.styleFrom(
              padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 16),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPinSetupView(ThemeData theme) {
    return Center(
      child: SingleChildScrollView(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.pin, size: 64, color: theme.colorScheme.primary),
            const SizedBox(height: 24),
            Text(
              'Set App PIN',
              style: theme.textTheme.headlineMedium?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Choose a 4-8 digit PIN to unlock the app',
              style: theme.textTheme.bodyLarge?.copyWith(
                color: theme.colorScheme.onSurfaceVariant,
              ),
            ),
            const SizedBox(height: 32),
            SizedBox(
              width: 300,
              child: TextField(
                controller: _pinController,
                obscureText: _obscurePin,
                keyboardType: TextInputType.number,
                maxLength: 8,
                textInputAction: TextInputAction.next,
                decoration: InputDecoration(
                  labelText: 'New PIN',
                  counterText: '',
                  prefixIcon: const Icon(Icons.pin_outlined),
                  border: const OutlineInputBorder(),
                ),
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: 300,
              child: TextField(
                controller: _pinConfirmController,
                obscureText: _obscurePin,
                keyboardType: TextInputType.number,
                maxLength: 8,
                textInputAction: TextInputAction.done,
                decoration: InputDecoration(
                  labelText: 'Confirm PIN',
                  counterText: '',
                  prefixIcon: const Icon(Icons.pin_outlined),
                  border: const OutlineInputBorder(),
                ),
                onSubmitted: (_) => _setupPin(),
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: 300,
              child: FilledButton(
                onPressed: _setupPin,
                style: FilledButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: const Text('Set PIN'),
              ),
            ),
            if (_errorMessage != null) ...[
              const SizedBox(height: 16),
              Text(
                _errorMessage!,
                style: TextStyle(color: theme.colorScheme.error),
                textAlign: TextAlign.center,
              ),
            ],
          ],
        ),
      ),
    );
  }

  void _exitToLogin() {
    widget.onUnlocked();
  }
}
