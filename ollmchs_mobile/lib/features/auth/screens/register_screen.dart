import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../core/network/api_client.dart';
import '../../../core/services/flag_secure_service.dart';
import '../../../core/utils/type_parsers.dart';
import '../bloc/auth_bloc.dart';
import '../bloc/auth_event.dart';
import '../bloc/auth_state.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _admissionController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  bool _obscurePassword = true;
  bool _obscureConfirm = true;
  String _selectedRole = 'student';

  List<Map<String, dynamic>> _departments = [];
  List<Map<String, dynamic>> _programs = [];
  int? _selectedDepartmentId;
  int? _selectedProgramId;
  bool _isLoadingDepartments = false;

  @override
  void initState() {
    super.initState();
    FlagSecureService.enable();
    _fetchDepartments();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _admissionController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _fetchDepartments() async {
    setState(() => _isLoadingDepartments = true);
    try {
      final api = context.read<ApiClient>();
      final response = await api.get('/v1/departments');
      final data = response.data['data'] as List<dynamic>? ?? [];
      setState(() {
        _departments = data.cast<Map<String, dynamic>>();
        _isLoadingDepartments = false;
      });
    } catch (_) {
      setState(() => _isLoadingDepartments = false);
    }
  }

  Future<void> _fetchPrograms(int departmentId) async {
    try {
      final api = context.read<ApiClient>();
      final response = await api.get(
        '/v1/programs',
        queryParameters: {'department_id': departmentId},
      );
      final data = response.data['data'] as List<dynamic>? ?? [];
      setState(() {
        _programs = data.cast<Map<String, dynamic>>();
        _selectedProgramId = null;
      });
    } catch (_) {}
  }

  void _register() {
    if (_formKey.currentState!.validate()) {
      context.read<AuthBloc>().add(
        RegisterEvent(
          name: _nameController.text.trim(),
          email: _emailController.text.trim(),
          phone: _phoneController.text.trim(),
          password: _passwordController.text,
          passwordConfirmation: _confirmPasswordController.text,
          role: _selectedRole,
          admissionNumber: _selectedRole == 'student'
              ? _admissionController.text.trim()
              : null,
          departmentId: _selectedRole == 'student'
              ? _selectedDepartmentId
              : null,
          programId: _selectedRole == 'student' ? _selectedProgramId : null,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final showStudentFields = _selectedRole == 'student';

    return Scaffold(
      appBar: AppBar(title: const Text('Create Account')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                TextFormField(
                  controller: _nameController,
                  textInputAction: TextInputAction.next,
                  decoration: const InputDecoration(
                    labelText: 'Full Name',
                    prefixIcon: Icon(Icons.person_outlined),
                    border: OutlineInputBorder(),
                  ),
                  validator: (v) =>
                      v == null || v.trim().isEmpty ? 'Enter your name' : null,
                ),
                const SizedBox(height: 16),

                TextFormField(
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  textInputAction: TextInputAction.next,
                  decoration: const InputDecoration(
                    labelText: 'Email',
                    prefixIcon: Icon(Icons.email_outlined),
                    border: OutlineInputBorder(),
                  ),
                  validator: (v) {
                    if (v == null || v.trim().isEmpty) {
                      return 'Enter your email';
                    }
                    if (!v.contains('@')) return 'Enter a valid email';
                    return null;
                  },
                ),
                const SizedBox(height: 16),

                TextFormField(
                  controller: _phoneController,
                  keyboardType: TextInputType.phone,
                  textInputAction: TextInputAction.next,
                  decoration: const InputDecoration(
                    labelText: 'Phone Number (optional)',
                    prefixIcon: Icon(Icons.phone_outlined),
                    border: OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 16),

                DropdownButtonFormField<String>(
                  initialValue: _selectedRole,
                  decoration: const InputDecoration(
                    labelText: 'Role',
                    prefixIcon: Icon(Icons.badge_outlined),
                    border: OutlineInputBorder(),
                  ),
                  items: const [
                    DropdownMenuItem(value: 'student', child: Text('Student')),
                    DropdownMenuItem(value: 'lecturer', child: Text('Lecturer')),
                    DropdownMenuItem(value: 'staff', child: Text('Staff')),
                  ],
                  onChanged: (v) =>
                      setState(() => _selectedRole = v ?? 'student'),
                ),
                const SizedBox(height: 16),

                if (showStudentFields) ...[
                  TextFormField(
                    controller: _admissionController,
                    textInputAction: TextInputAction.next,
                    decoration: const InputDecoration(
                      labelText: 'Admission Number',
                      prefixIcon: Icon(Icons.badge_outlined),
                      border: OutlineInputBorder(),
                    ),
                  validator: (v) {
                    if (_selectedRole == 'student' &&
                        (v == null || v.trim().isEmpty)) {
                      return 'Enter your admission number';
                    }
                    return null;
                  },
                  ),
                  const SizedBox(height: 16),

                  if (_isLoadingDepartments)
                    const LinearProgressIndicator()
                  else
                    DropdownButtonFormField<int>(
                      initialValue: _selectedDepartmentId,
                      decoration: const InputDecoration(
                        labelText: 'Department',
                        prefixIcon: Icon(Icons.business_outlined),
                        border: OutlineInputBorder(),
                      ),
                      items: _departments
                          .map(
                            (d) => DropdownMenuItem(
                              value: parseInt(d['id'], fieldName: 'department_id'),
                              child: Text(d['name'] as String),
                            ),
                          )
                          .toList(),
                      onChanged: (v) {
                        setState(() => _selectedDepartmentId = v);
                        if (v != null) _fetchPrograms(v);
                      },
                      validator: (v) {
                        if (_selectedRole == 'student' && v == null) {
                          return 'Select your department';
                        }
                        return null;
                      },
                    ),
                  const SizedBox(height: 16),

                  DropdownButtonFormField<int>(
                    initialValue: _selectedProgramId,
                    decoration: const InputDecoration(
                      labelText: 'Program',
                      prefixIcon: Icon(Icons.school_outlined),
                      border: OutlineInputBorder(),
                    ),
                    items: _programs
                        .map(
                          (p) => DropdownMenuItem(
                            value: parseInt(p['id'], fieldName: 'program_id'),
                            child: Text(p['name'] as String),
                          ),
                        )
                        .toList(),
                    onChanged: (v) => setState(() => _selectedProgramId = v),
                    validator: (v) {
                      if (_selectedRole == 'student' && v == null) {
                        return 'Select your program';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),
                ],

                TextFormField(
                  controller: _passwordController,
                  obscureText: _obscurePassword,
                  textInputAction: TextInputAction.next,
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
                      onPressed: () =>
                          setState(() => _obscurePassword = !_obscurePassword),
                    ),
                  ),
                  validator: (v) {
                    if (v == null || v.length < 8) {
                      return 'Password must be at least 8 characters';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),

                TextFormField(
                  controller: _confirmPasswordController,
                  obscureText: _obscureConfirm,
                  textInputAction: TextInputAction.done,
                  decoration: InputDecoration(
                    labelText: 'Confirm Password',
                    prefixIcon: const Icon(Icons.lock_outlined),
                    border: const OutlineInputBorder(),
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscureConfirm
                            ? Icons.visibility_off
                            : Icons.visibility,
                      ),
                      onPressed: () =>
                          setState(() => _obscureConfirm = !_obscureConfirm),
                    ),
                  ),
                  validator: (v) {
                    if (v != _passwordController.text) {
                      return 'Passwords do not match';
                    }
                    return null;
                  },
                  onFieldSubmitted: (_) => _register(),
                ),
                const SizedBox(height: 24),

                BlocConsumer<AuthBloc, AuthState>(
                  listener: (context, state) {
                    if (state is Authenticated) {
                      context.goNamed('dashboard');
                    } else if (state is AuthEmailUnverified) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(state.message),
                          duration: const Duration(seconds: 4),
                        ),
                      );
                    }
                  },
                  builder: (context, state) {
                    final isLoading = state is AuthLoading;
                    return FilledButton(
                      onPressed: isLoading ? null : _register,
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
                              'Create Account',
                              style: TextStyle(fontSize: 16),
                            ),
                    );
                  },
                ),
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
                      "Already have an account? ",
                      style: Theme.of(context).textTheme.bodyMedium,
                    ),
                    TextButton(
                      onPressed: () => context.goNamed('login'),
                      child: const Text('Sign In'),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
