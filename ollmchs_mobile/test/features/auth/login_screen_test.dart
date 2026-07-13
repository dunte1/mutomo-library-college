import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mocktail/mocktail.dart';
import 'package:ollmchs_library/core/services/biometric_service.dart';
import 'package:ollmchs_library/core/storage/local_storage_service.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_bloc.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_state.dart';
import 'package:ollmchs_library/features/auth/screens/login_screen.dart';

class MockLocalStorageService extends Mock implements LocalStorageService {}

class MockBiometricService extends Mock implements BiometricService {}

class MockAuthBloc extends Mock implements AuthBloc {}

void main() {
  late MockLocalStorageService mockStorage;
  late MockBiometricService mockBiometric;
  late MockAuthBloc mockAuthBloc;

  setUp(() {
    mockStorage = MockLocalStorageService();
    mockBiometric = MockBiometricService();
    mockAuthBloc = MockAuthBloc();

    // Default stubs
    when(() => mockBiometric.isAvailable).thenAnswer((_) async => false);
    when(() => mockStorage.getBiometricEnabled()).thenAnswer((_) async => false);
    when(() => mockStorage.getToken()).thenAnswer((_) async => null);
    when(() => mockAuthBloc.state).thenReturn(AuthInitial());
    when(() => mockAuthBloc.stream).thenAnswer((_) => const Stream.empty());
  });

  Widget buildTestWidget() {
    return MaterialApp(
      home: MultiRepositoryProvider(
        providers: [
          RepositoryProvider<LocalStorageService>.value(value: mockStorage),
          RepositoryProvider<BiometricService>.value(value: mockBiometric),
        ],
        child: BlocProvider<AuthBloc>.value(
          value: mockAuthBloc,
          child: const LoginScreen(),
        ),
      ),
    );
  }

  group('LoginScreen', () {
    testWidgets('renders email and password fields', (tester) async {
      await tester.pumpWidget(buildTestWidget());
      await tester.pump();

      expect(find.byType(TextFormField), findsNWidgets(2));
      expect(find.text('Email'), findsOneWidget);
      expect(find.text('Password'), findsOneWidget);
    });

    testWidgets('renders sign in button', (tester) async {
      await tester.pumpWidget(buildTestWidget());
      await tester.pump();

      expect(find.text('Sign In'), findsOneWidget);
    });

    testWidgets('renders app title and subtitle', (tester) async {
      await tester.pumpWidget(buildTestWidget());
      await tester.pump();

      expect(find.text('OLLMCHS Library'), findsOneWidget);
      expect(find.text('Sign in to your account'), findsOneWidget);
    });

    testWidgets('renders forgot password link', (tester) async {
      await tester.pumpWidget(buildTestWidget());
      await tester.pump();

      expect(find.text('Forgot Password?'), findsOneWidget);
    });

    testWidgets('renders sign up link', (tester) async {
      await tester.pumpWidget(buildTestWidget());
      await tester.pump();

      expect(find.text('Sign Up'), findsOneWidget);
    });

    testWidgets('renders powered by footer', (tester) async {
      await tester.pumpWidget(buildTestWidget());
      await tester.pump();

      expect(find.text('Powered by Duncowebsolutions © 2026'), findsOneWidget);
    });

    testWidgets('password field has visibility toggle', (tester) async {
      await tester.pumpWidget(buildTestWidget());
      await tester.pump();

      expect(find.byIcon(Icons.visibility_off), findsOneWidget);

      await tester.tap(find.byIcon(Icons.visibility_off));
      await tester.pump();

      expect(find.byIcon(Icons.visibility), findsOneWidget);
    });
  });
}
