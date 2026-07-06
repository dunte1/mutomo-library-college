import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../bloc/auth_bloc.dart';
import '../bloc/auth_event.dart';
import '../bloc/auth_state.dart';
import '../../../core/services/biometric_service.dart';
import '../../../core/storage/local_storage_service.dart';
import '../../../core/network/api_client.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _storage = LocalStorageService();
  final _biometricService = BiometricService();
  bool _obscurePassword = true;
  bool _bioAvailable = false;
  bool _bioEnabled = false;
  bool _hasStoredToken = false;

  @override
  void initState() {
    super.initState();
    _checkBiometric();
  }

  Future<void> _checkBiometric() async {
    final available = await _biometricService.isAvailable;
    final enabled = await _storage.getBiometricEnabled();
    final token = await _storage.getToken();

    if (mounted) {
      setState(() {
        _bioAvailable = available;
        _bioEnabled = enabled;
        _hasStoredToken = token != null;
      });
    }
  }

  Future<void> _authenticateWithBiometric() async {
    final messenger = ScaffoldMessenger.of(context);
    final router = GoRouter.of(context);
    final api = context.read<ApiClient>();
    final authBloc = context.read<AuthBloc>();

    final authed = await _biometricService.authenticate(
      reason: 'Log in to OLLMCHS Library',
    );
    if (!authed || !mounted) return;

    // Token exists in secure storage — try to use it directly
    final token = await _storage.getToken();
    if (token == null) {
      if (mounted) {
        messenger.showSnackBar(
          const SnackBar(
            content: Text(
              'No saved session. Please log in with your password.',
            ),
          ),
        );
        setState(() => _bioEnabled = false);
      }
      return;
    }

    // Try to get user profile with stored token (Phase 3 interceptor will refresh if needed)
    try {
      await api.get('/v1/auth/user');
      // Token is valid (or was refreshed by interceptor) — navigate to dashboard
      if (mounted) {
        authBloc.add(const CheckAuthEvent());
        router.goNamed('dashboard');
      }
    } catch (_) {
      // Token is invalid and refresh failed — user must log in with password
      await _storage.clearAll();
      if (mounted) {
        setState(() {
          _bioEnabled = false;
          _hasStoredToken = false;
        });
        messenger.showSnackBar(
          const SnackBar(
            content: Text('Session expired. Please log in with your password.'),
          ),
        );
      }
    }
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _login() {
    if (_formKey.currentState!.validate()) {
      context.read<AuthBloc>().add(
        LoginEvent(
          email: _emailController.text.trim(),
          password: _passwordController.text,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: Form(
              key: _formKey,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Image.asset(
                    'assets/images/logo_primary.png',
                    width: 96,
                    height: 96,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'OLLMCHS Library',
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Sign in to your account',
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                      color: Theme.of(context).colorScheme.onSurfaceVariant,
                    ),
                  ),
                  const SizedBox(height: 32),
                  TextFormField(
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    textInputAction: TextInputAction.next,
                    decoration: const InputDecoration(
                      labelText: 'Email',
                      prefixIcon: Icon(Icons.email_outlined),
                      border: OutlineInputBorder(),
                    ),
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return 'Enter your email';
                      }
                      if (!value.contains('@')) {
                        return 'Enter a valid email';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _passwordController,
                    obscureText: _obscurePassword,
                    textInputAction: TextInputAction.done,
                    decoration: InputDecoration(
                      labelText: 'Password',
                      prefixIcon: const Icon(Icons.lock_outlined),
                      border: const OutlineInputBorder(),
                      suffixIcon: IconButton(
                        icon: Icon(
                          _obscurePassword
                              ? Icons.visibility_off
                              : Icons.visibility,
                        ),
                        onPressed: () => setState(
                          () => _obscurePassword = !_obscurePassword,
                        ),
                      ),
                    ),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'Enter your password';
                      }
                      return null;
                    },
                    onFieldSubmitted: (_) => _login(),
                  ),
                  const SizedBox(height: 8),
                  Align(
                    alignment: Alignment.centerRight,
                    child: TextButton(
                      onPressed: () => context.goNamed('forgot-password'),
                      child: const Text('Forgot Password?'),
                    ),
                  ),
                  const SizedBox(height: 16),
                  BlocConsumer<AuthBloc, AuthState>(
                    listener: (context, state) {
                      if (state is Authenticated) {
                        context.goNamed('dashboard');
                      } else if (state is TwoFactorRequired) {
                        context.goNamed(
                          'two-factor',
                          extra: {
                            'userId': state.userId,
                            'tempToken': state.tempToken,
                          },
                        );
                      }
                    },
                    builder: (context, state) {
                      final isLoading = state is AuthLoading;
                      return FilledButton(
                        onPressed: isLoading ? null : _login,
                        style: FilledButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 16),
                        ),
                        child: isLoading
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                ),
                              )
                            : const Text(
                                'Sign In',
                                style: TextStyle(fontSize: 16),
                              ),
                      );
                    },
                  ),
                  if (_bioAvailable && _bioEnabled && _hasStoredToken) ...[
                    const SizedBox(height: 12),
                    BlocBuilder<AuthBloc, AuthState>(
                      builder: (context, state) {
                        final isLoading = state is AuthLoading;
                        return OutlinedButton.icon(
                          onPressed: isLoading
                              ? null
                              : _authenticateWithBiometric,
                          icon: const Icon(Icons.fingerprint),
                          label: const Text('Sign in with Biometrics'),
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 16),
                          ),
                        );
                      },
                    ),
                  ],
                  const SizedBox(height: 16),
                  BlocBuilder<AuthBloc, AuthState>(
                    builder: (context, state) {
                      if (state is AuthError) {
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 16),
                          child: Text(
                            state.message,
                            style: TextStyle(
                              color: Theme.of(context).colorScheme.error,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        );
                      }
                      return const SizedBox.shrink();
                    },
                  ),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        "Don't have an account? ",
                        style: Theme.of(context).textTheme.bodyMedium,
                      ),
                      TextButton(
                        onPressed: () => context.goNamed('register'),
                        child: const Text('Sign Up'),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
