import 'package:bloc_test/bloc_test.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_bloc.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_event.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_state.dart';
import 'package:ollmchs_library/features/auth/models/user_model.dart';
import '../../../helpers/mock_repositories.dart';

void main() {
  late MockAuthRepository mockAuthRepository;

  setUp(() {
    mockAuthRepository = MockAuthRepository();
  });

  group('AuthBloc', () {
    blocTest<AuthBloc, AuthState>(
      'emits [AuthLoading, Authenticated] when login is successful',
      build: () {
        when(() => mockAuthRepository.login(any(), any(), any())).thenAnswer(
          (_) async => {
            'user': UserModel(id: 1, name: 'Test', email: 'test@test.com'),
            'token': 'test-token',
          },
        );
        return AuthBloc(authRepository: mockAuthRepository);
      },
      act: (bloc) => bloc.add(
        const LoginEvent(email: 'test@test.com', password: 'password'),
      ),
      expect: () => [isA<AuthLoading>(), isA<Authenticated>()],
    );

    blocTest<AuthBloc, AuthState>(
      'emits [AuthLoading, AuthError] when login fails',
      build: () {
        when(() => mockAuthRepository.login(any(), any(), any()))
            .thenThrow(Exception('Invalid credentials'));
        return AuthBloc(authRepository: mockAuthRepository);
      },
      act: (bloc) => bloc.add(
        const LoginEvent(email: 'test@test.com', password: 'wrong'),
      ),
      expect: () => [isA<AuthLoading>(), isA<AuthError>()],
    );

    blocTest<AuthBloc, AuthState>(
      'emits [AuthLoading, Authenticated] when registration is successful',
      build: () {
        when(() => mockAuthRepository.register(
          name: any(named: 'name'),
          email: any(named: 'email'),
          phone: any(named: 'phone'),
          password: any(named: 'password'),
          passwordConfirmation: any(named: 'passwordConfirmation'),
          role: any(named: 'role'),
          admissionNumber: any(named: 'admissionNumber'),
          departmentId: any(named: 'departmentId'),
          programId: any(named: 'programId'),
        )).thenAnswer((_) async => {
          'user': UserModel(id: 2, name: 'New', email: 'new@test.com'),
          'token': 'new-token',
        });
        return AuthBloc(authRepository: mockAuthRepository);
      },
      act: (bloc) => bloc.add(const RegisterEvent(
        name: 'New',
        email: 'new@test.com',
        phone: '1234567890',
        password: 'password',
        passwordConfirmation: 'password',
        role: 'student',
        admissionNumber: 'ADM001',
      )),
      expect: () => [isA<AuthLoading>(), isA<Authenticated>()],
    );

    blocTest<AuthBloc, AuthState>(
      'emits [AuthLoading, AuthError] when registration fails',
      build: () {
        when(() => mockAuthRepository.register(
          name: any(named: 'name'),
          email: any(named: 'email'),
          phone: any(named: 'phone'),
          password: any(named: 'password'),
          passwordConfirmation: any(named: 'passwordConfirmation'),
          role: any(named: 'role'),
          admissionNumber: any(named: 'admissionNumber'),
          departmentId: any(named: 'departmentId'),
          programId: any(named: 'programId'),
        )).thenThrow(Exception('Email already taken'));
        return AuthBloc(authRepository: mockAuthRepository);
      },
      act: (bloc) => bloc.add(const RegisterEvent(
        name: 'New',
        email: 'existing@test.com',
        phone: '1234567890',
        password: 'password',
        passwordConfirmation: 'password',
        role: 'student',
      )),
      expect: () => [isA<AuthLoading>(), isA<AuthError>()],
    );

    blocTest<AuthBloc, AuthState>(
      'emits [AuthLoading, AuthUnauthenticated] when logout succeeds',
      build: () {
        when(() => mockAuthRepository.logout()).thenAnswer((_) async {});
        return AuthBloc(authRepository: mockAuthRepository);
      },
      act: (bloc) => bloc.add(const LogoutEvent()),
      expect: () => [isA<AuthLoading>(), isA<AuthUnauthenticated>()],
    );

    blocTest<AuthBloc, AuthState>(
      'emits [AuthLoading, AuthUnauthenticated] when logout throws',
      build: () {
        when(() => mockAuthRepository.logout()).thenThrow(Exception('err'));
        return AuthBloc(authRepository: mockAuthRepository);
      },
      act: (bloc) => bloc.add(const LogoutEvent()),
      expect: () => [isA<AuthLoading>(), isA<AuthUnauthenticated>()],
    );

    blocTest<AuthBloc, AuthState>(
      'emits [Authenticated] when CheckAuthEvent finds a token and user',
      build: () {
        when(() => mockAuthRepository.getStoredToken())
            .thenAnswer((_) async => 'stored-token');
        when(() => mockAuthRepository.getUser()).thenAnswer(
          (_) async => UserModel(id: 1, name: 'Test', email: 'test@test.com'),
        );
        return AuthBloc(authRepository: mockAuthRepository);
      },
      act: (bloc) => bloc.add(const CheckAuthEvent()),
      expect: () => [isA<Authenticated>()],
    );

    blocTest<AuthBloc, AuthState>(
      'emits [AuthUnauthenticated] when CheckAuthEvent finds no token',
      build: () {
        when(() => mockAuthRepository.getStoredToken())
            .thenAnswer((_) async => null);
        return AuthBloc(authRepository: mockAuthRepository);
      },
      act: (bloc) => bloc.add(const CheckAuthEvent()),
      expect: () => [isA<AuthUnauthenticated>()],
    );

    blocTest<AuthBloc, AuthState>(
      'emits [AuthUnauthenticated] when CheckAuthEvent finds token but getUser fails',
      build: () {
        when(() => mockAuthRepository.getStoredToken())
            .thenAnswer((_) async => 'stored-token');
        when(() => mockAuthRepository.getUser())
            .thenThrow(Exception('Network error'));
        return AuthBloc(authRepository: mockAuthRepository);
      },
      act: (bloc) => bloc.add(const CheckAuthEvent()),
      expect: () => [isA<AuthUnauthenticated>()],
    );
  });
}
