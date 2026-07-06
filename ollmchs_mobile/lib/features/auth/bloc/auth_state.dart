import 'package:equatable/equatable.dart';
import '../models/user_model.dart';

abstract class AuthState extends Equatable {
  const AuthState();
  @override
  List<Object?> get props => [];
}

class AuthInitial extends AuthState {}

class AuthLoading extends AuthState {}

class Authenticated extends AuthState {
  final UserModel user;
  final String token;
  const Authenticated({required this.user, required this.token});
  @override
  List<Object?> get props => [user, token];
}

class AuthUnauthenticated extends AuthState {}

class AuthEmailUnverified extends AuthState {
  final String message;
  const AuthEmailUnverified({
    this.message = 'Please verify your email address',
  });
  @override
  List<Object?> get props => [message];
}

class AuthError extends AuthState {
  final String message;
  final Map<String, dynamic>? errors;
  const AuthError({required this.message, this.errors});
  @override
  List<Object?> get props => [message];
}

class PasswordResetLinkSent extends AuthState {
  final String message;
  const PasswordResetLinkSent({
    this.message = 'Password reset link sent to your email',
  });
  @override
  List<Object?> get props => [message];
}

class PasswordResetSuccess extends AuthState {
  final String message;
  const PasswordResetSuccess({
    this.message = 'Password has been reset successfully',
  });
  @override
  List<Object?> get props => [message];
}

class TwoFactorRequired extends AuthState {
  final int userId;
  final String tempToken;
  const TwoFactorRequired({required this.userId, required this.tempToken});
  @override
  List<Object?> get props => [userId, tempToken];
}
