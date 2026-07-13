import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mocktail/mocktail.dart';
import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/core/services/biometric_service.dart';
import 'package:ollmchs_library/core/storage/local_storage_service.dart';
import 'package:ollmchs_library/core/theme/theme_cubit.dart';
import 'package:ollmchs_library/features/auth/repositories/auth_repository.dart';
import 'package:ollmchs_library/features/profile/screens/settings_screen.dart';

class MockLocalStorageService extends Mock implements LocalStorageService {}

class MockBiometricService extends Mock implements BiometricService {}

class MockApiClient extends Mock implements ApiClient {}

class MockAuthRepository extends Mock implements AuthRepository {}

class MockThemeCubit extends Mock implements ThemeCubit {}

void main() {
  late MockLocalStorageService mockStorage;
  late MockBiometricService mockBiometric;
  late MockApiClient mockApi;
  late MockAuthRepository mockAuthRepo;
  late MockThemeCubit mockThemeCubit;

  setUp(() {
    mockStorage = MockLocalStorageService();
    mockBiometric = MockBiometricService();
    mockApi = MockApiClient();
    mockAuthRepo = MockAuthRepository();
    mockThemeCubit = MockThemeCubit();

    // Default stubs
    when(() => mockStorage.getNotificationsEnabled()).thenAnswer((_) async => true);
    when(() => mockStorage.getBiometricEnabled()).thenAnswer((_) async => false);
    when(() => mockStorage.getPinEnabled()).thenAnswer((_) async => false);
    when(() => mockBiometric.isAvailable).thenAnswer((_) async => false);
    when(() => mockBiometric.unavailableReason).thenAnswer((_) async => 'No biometric hardware');
    when(() => mockThemeCubit.state).thenReturn(ThemeMode.system);
    when(() => mockApi.get(any(), queryParameters: any(named: 'queryParameters'))).thenAnswer(
      (_) async => Response(
        data: {'data': {'two_factor_enabled': false}},
        statusCode: 200,
        requestOptions: RequestOptions(path: '/v1/auth/user'),
      ),
    );
  });

  Widget buildTestWidget() {
    return MaterialApp(
      home: MultiRepositoryProvider(
        providers: [
          RepositoryProvider<LocalStorageService>.value(value: mockStorage),
          RepositoryProvider<ApiClient>.value(value: mockApi),
          RepositoryProvider<AuthRepository>.value(value: mockAuthRepo),
          RepositoryProvider<BiometricService>.value(value: mockBiometric),
          BlocProvider<ThemeCubit>.value(value: mockThemeCubit),
        ],
        child: const SettingsScreen(),
      ),
    );
  }

  group('SettingsScreen', () {
    testWidgets('renders settings title', (tester) async {
      await tester.pumpWidget(buildTestWidget());
      await tester.pump();

      expect(find.text('Settings'), findsOneWidget);
    });

    testWidgets('renders notifications section', (tester) async {
      await tester.pumpWidget(buildTestWidget());
      await tester.pump();

      expect(find.text('Notifications'), findsOneWidget);
      expect(find.text('Push Notifications'), findsOneWidget);
    });

    testWidgets('renders security section', (tester) async {
      await tester.pumpWidget(buildTestWidget());
      await tester.pump();

      expect(find.text('Security'), findsOneWidget);
      expect(find.text('Two-Factor Authentication'), findsOneWidget);
      expect(find.text('App PIN'), findsOneWidget);
    });

    testWidgets('renders session info', (tester) async {
      await tester.pumpWidget(buildTestWidget());
      await tester.pump();

      expect(find.text('Session'), findsOneWidget);
      expect(find.text('App Lock'), findsOneWidget);
      expect(find.text('Session Timeout'), findsOneWidget);
    });
  });
}
