import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/errors/error_mapper.dart';
import '../../../core/services/push_notification_service.dart';
import '../../../core/utils/type_parsers.dart';
import 'auth_event.dart';
import 'auth_state.dart';
import '../repositories/auth_repository.dart';

class AuthBloc extends Bloc<AuthEvent, AuthState> {
  final AuthRepository _authRepository;

  AuthBloc({required this._authRepository}) : super(AuthInitial()) {
    on<LoginEvent>(_onLogin);
    on<RegisterEvent>(_onRegister);
    on<LogoutEvent>(_onLogout);
    on<CheckAuthEvent>(_onCheckAuth);
    on<ForgotPasswordEvent>(_onForgotPassword);
    on<ResetPasswordEvent>(_onResetPassword);
    on<VerifyTwoFactorEvent>(_onVerifyTwoFactor);
    on<EnableTwoFactorSetupEvent>(_onEnableTwoFactorSetup);
    on<VerifyTwoFactorSetupEvent>(_onVerifyTwoFactorSetup);
    on<VerifyTwoFactorRecoveryEvent>(_onVerifyTwoFactorRecovery);
  }

  Future<void> _onLogin(LoginEvent event, Emitter<AuthState> emit) async {
    emit(AuthLoading());
    try {
      final result = await _authRepository.login(
        event.email,
        event.password,
        event.deviceName,
      );

      // 2FA required
      if (result['requires_two_factor'] == true) {
        emit(
          TwoFactorRequired(
            userId: parseInt(result['user_id'], fieldName: 'user_id'),
            tempToken: result['temp_token'] as String,
          ),
        );
        return;
      }

      emit(
        Authenticated(
          user: result['user'] as dynamic,
          token: result['token'] as String,
        ),
      );

      // Register FCM token with server (non-blocking, best-effort)
      try {
        PushNotificationService().registerWithServer(_authRepository.apiClient);
      } catch (_) {}
    } catch (e) {
      emit(AuthError(message: ErrorMapper.map(e)));
    }
  }

  Future<void> _onVerifyTwoFactor(
    VerifyTwoFactorEvent event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    try {
      final result = await _authRepository.verifyTwoFactor(
        userId: event.userId,
        code: event.code,
        tempToken: event.tempToken,
      );
      final token = result['token'] as String;
      // Get user profile with the new token
      final user = await _authRepository.getUser();
      emit(Authenticated(user: user, token: token));

      // Register FCM token with server after 2FA (non-blocking, best-effort)
      try {
        PushNotificationService().registerWithServer(_authRepository.apiClient);
      } catch (_) {}
    } catch (e) {
      emit(AuthError(message: ErrorMapper.map(e)));
    }
  }

  Future<void> _onRegister(RegisterEvent event, Emitter<AuthState> emit) async {
    emit(AuthLoading());
    try {
      final result = await _authRepository.register(
        name: event.name,
        email: event.email,
        phone: event.phone,
        password: event.password,
        passwordConfirmation: event.passwordConfirmation,
        role: event.role,
        admissionNumber: event.admissionNumber,
        departmentId: event.departmentId,
        programId: event.programId,
      );
      emit(
        Authenticated(
          user: result['user'] as dynamic,
          token: result['token'] as String,
        ),
      );

      // Register FCM token with server after registration (non-blocking, best-effort)
      try {
        PushNotificationService().registerWithServer(_authRepository.apiClient);
      } catch (_) {}
    } catch (e) {
      emit(AuthError(message: ErrorMapper.map(e)));
    }
  }

  Future<void> _onLogout(LogoutEvent event, Emitter<AuthState> emit) async {
    emit(AuthLoading());
    try {
      await _authRepository.logout();
    } catch (_) {}
    emit(AuthUnauthenticated());
  }

  Future<void> _onCheckAuth(
    CheckAuthEvent event,
    Emitter<AuthState> emit,
  ) async {
    final token = await _authRepository.getStoredToken();
    if (token != null) {
      try {
        final user = await _authRepository.getUser();
        emit(Authenticated(user: user, token: token));

        // Register FCM token on auto-login from stored token (non-blocking, best-effort)
        try {
          PushNotificationService().registerWithServer(_authRepository.apiClient);
        } catch (_) {}
      } catch (_) {
        emit(AuthUnauthenticated());
      }
    } else {
      emit(AuthUnauthenticated());
    }
  }

  Future<void> _onForgotPassword(
    ForgotPasswordEvent event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    try {
      await _authRepository.forgotPassword(event.email);
      emit(PasswordResetLinkSent());
    } catch (e) {
      emit(AuthError(message: ErrorMapper.map(e)));
    }
  }

  Future<void> _onResetPassword(
    ResetPasswordEvent event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    try {
      await _authRepository.resetPassword(
        token: event.token,
        email: event.email,
        password: event.password,
        passwordConfirmation: event.passwordConfirmation,
      );
      emit(PasswordResetSuccess());
    } catch (e) {
      emit(AuthError(message: ErrorMapper.map(e)));
    }
  }

  Future<void> _onEnableTwoFactorSetup(
    EnableTwoFactorSetupEvent event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    try {
      final result = await _authRepository.enableTwoFactor(password: event.password);
      emit(TwoFactorSetupReady(
        secret: result['secret'] as String,
        qrCodeUrl: result['qr_code_url'] as String,
        recoveryCodes: (result['recovery_codes'] as List<dynamic>).cast<String>(),
      ));
    } catch (e) {
      emit(AuthError(message: ErrorMapper.map(e)));
    }
  }

  Future<void> _onVerifyTwoFactorSetup(
    VerifyTwoFactorSetupEvent event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    try {
      await _authRepository.verifyTwoFactorSetup(code: event.code);
      emit(const TwoFactorSetupVerified());
    } catch (e) {
      emit(AuthError(message: ErrorMapper.map(e)));
    }
  }

  Future<void> _onVerifyTwoFactorRecovery(
    VerifyTwoFactorRecoveryEvent event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    try {
      final result = await _authRepository.verifyTwoFactorRecovery(
        userId: event.userId,
        recoveryCode: event.recoveryCode,
      );
      final token = result['token'] as String;
      final user = await _authRepository.getUser();
      emit(Authenticated(user: user, token: token));

      // Register FCM token with server after 2FA recovery (non-blocking, best-effort)
      try {
        PushNotificationService().registerWithServer(_authRepository.apiClient);
      } catch (_) {}
    } catch (e) {
      emit(AuthError(message: ErrorMapper.map(e)));
    }
  }
}
