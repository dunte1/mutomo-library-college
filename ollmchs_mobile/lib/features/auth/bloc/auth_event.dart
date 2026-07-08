import 'package:equatable/equatable.dart';

abstract class AuthEvent extends Equatable {
  const AuthEvent();
  @override
  List<Object?> get props => [];
}

class LoginEvent extends AuthEvent {
  final String email;
  final String password;
  final String? deviceName;
  const LoginEvent({
    required this.email,
    required this.password,
    this.deviceName,
  });
  @override
  List<Object?> get props => [email, password];
}

class RegisterEvent extends AuthEvent {
  final String name;
  final String email;
  final String phone;
  final String password;
  final String passwordConfirmation;
  final String role;
  final String? admissionNumber;
  final int? departmentId;
  final int? programId;

  const RegisterEvent({
    required this.name,
    required this.email,
    required this.phone,
    required this.password,
    required this.passwordConfirmation,
    required this.role,
    this.admissionNumber,
    this.departmentId,
    this.programId,
  });
  @override
  List<Object?> get props => [name, email, password, role];
}

class LogoutEvent extends AuthEvent {
  const LogoutEvent();
}

class CheckAuthEvent extends AuthEvent {
  const CheckAuthEvent();
}

class ForgotPasswordEvent extends AuthEvent {
  final String email;
  const ForgotPasswordEvent({required this.email});
  @override
  List<Object?> get props => [email];
}

class ResetPasswordEvent extends AuthEvent {
  final String token;
  final String email;
  final String password;
  final String passwordConfirmation;
  const ResetPasswordEvent({
    required this.token,
    required this.email,
    required this.password,
    required this.passwordConfirmation,
  });
  @override
  List<Object?> get props => [token, email, password, passwordConfirmation];
}

class VerifyTwoFactorEvent extends AuthEvent {
  final int userId;
  final String code;
  final String tempToken;
  const VerifyTwoFactorEvent({
    required this.userId,
    required this.code,
    required this.tempToken,
  });
  @override
  List<Object?> get props => [userId, code, tempToken];
}

class EnableTwoFactorSetupEvent extends AuthEvent {
  final String password;
  const EnableTwoFactorSetupEvent({required this.password});
  @override
  List<Object?> get props => [password];
}

class VerifyTwoFactorSetupEvent extends AuthEvent {
  final String code;
  const VerifyTwoFactorSetupEvent({required this.code});
  @override
  List<Object?> get props => [code];
}

class VerifyTwoFactorRecoveryEvent extends AuthEvent {
  final int userId;
  final String recoveryCode;
  const VerifyTwoFactorRecoveryEvent({
    required this.userId,
    required this.recoveryCode,
  });
  @override
  List<Object?> get props => [userId, recoveryCode];
}
