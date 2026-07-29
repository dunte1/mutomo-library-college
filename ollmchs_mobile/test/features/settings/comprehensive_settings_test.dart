import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mocktail/mocktail.dart';
import 'package:dio/dio.dart';
import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/core/storage/local_storage_service.dart';
import 'package:ollmchs_library/core/theme/theme_cubit.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_bloc.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_state.dart';
import 'package:ollmchs_library/features/auth/models/user_model.dart';
import 'package:ollmchs_library/features/auth/repositories/auth_repository.dart';
import 'package:ollmchs_library/features/profile/screens/settings_screen.dart';
import 'package:ollmchs_library/features/profile/screens/notification_preferences_screen.dart';
import 'package:ollmchs_library/features/profile/screens/about_screen.dart';
import 'package:ollmchs_library/features/profile/screens/help_screen.dart';

class MockApiClient extends Mock implements ApiClient {}
class MockAuthRepository extends Mock implements AuthRepository {}
class MockLocalStorageService extends Mock implements LocalStorageService {}

void main() {
  late MockApiClient mockApi;
  late MockAuthRepository mockAuthRepo;
  late MockLocalStorageService mockStorage;

  setUp(() {
    mockApi = MockApiClient();
    mockAuthRepo = MockAuthRepository();
    mockStorage = MockLocalStorageService();
    when(() => mockApi.get(any(), queryParameters: any(named: 'queryParameters')))
        .thenAnswer((_) async => Response(data: {'data': {}}, statusCode: 200, requestOptions: RequestOptions(path: '')));
    when(() => mockApi.post(any(), data: any(named: 'data')))
        .thenAnswer((_) async => Response(data: {'data': {}}, statusCode: 200, requestOptions: RequestOptions(path: '')));
    when(() => mockApi.put(any(), data: any(named: 'data')))
        .thenAnswer((_) async => Response(data: {'data': {}}, statusCode: 200, requestOptions: RequestOptions(path: '')));
    when(() => mockStorage.getThemeMode()).thenAnswer((_) async => 'light');
    when(() => mockStorage.getAutoDownloads()).thenAnswer((_) async => false);
    when(() => mockStorage.getOfflineSync()).thenAnswer((_) async => false);
    when(() => mockStorage.getDownloadQuality()).thenAnswer((_) async => 'standard');
    when(() => mockStorage.getBiometricEnabled()).thenAnswer((_) async => false);
    when(() => mockStorage.getPinEnabled()).thenAnswer((_) async => false);
    when(() => mockStorage.getNotificationsEnabled()).thenAnswer((_) async => true);
  });

  Widget buildSettingsScreen() {
    return MaterialApp(
      home: MultiRepositoryProvider(
        providers: [
          RepositoryProvider<ApiClient>.value(value: mockApi),
          RepositoryProvider<LocalStorageService>.value(value: mockStorage),
        ],
        child: MultiBlocProvider(
          providers: [
            BlocProvider<AuthBloc>(
              create: (_) => AuthBloc(authRepository: mockAuthRepo)
                ..emit(Authenticated(
                  user: UserModel(id: 1, name: 'Test', email: 'test@test.com', roles: ['student']),
                  token: 'test-token',
                )),
            ),
            BlocProvider<ThemeCubit>(create: (_) => ThemeCubit(storage: mockStorage)),
          ],
          child: const SettingsScreen(),
        ),
      ),
    );
  }

  group('SettingsScreen', () {
    testWidgets('renders settings title', (tester) async {
      await tester.pumpWidget(buildSettingsScreen());
      await tester.pump();
      expect(find.text('Settings'), findsOneWidget);
    });

    testWidgets('shows appearance section', (tester) async {
      tester.view.physicalSize = const Size(800, 3000);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(() { tester.view.resetPhysicalSize(); tester.view.resetDevicePixelRatio(); });
      await tester.pumpWidget(buildSettingsScreen());
      await tester.pump();
      await tester.pump();
      expect(find.text('Appearance'), findsOneWidget);
    });

    testWidgets('shows dark mode toggle', (tester) async {
      tester.view.physicalSize = const Size(800, 3000);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(() { tester.view.resetPhysicalSize(); tester.view.resetDevicePixelRatio(); });
      await tester.pumpWidget(buildSettingsScreen());
      await tester.pump();
      await tester.pump();
      expect(find.text('Dark Mode'), findsOneWidget);
    });

    testWidgets('shows security section', (tester) async {
      await tester.pumpWidget(buildSettingsScreen());
      await tester.pump();
      await tester.pump();
      expect(find.text('Security', skipOffstage: false), findsOneWidget);
    });

    testWidgets('shows 2FA option', (tester) async {
      await tester.pumpWidget(buildSettingsScreen());
      await tester.pump();
      await tester.pump();
      expect(find.text('Two-Factor Authentication', skipOffstage: false), findsOneWidget);
    });

    testWidgets('shows account section', (tester) async {
      tester.view.physicalSize = const Size(800, 3000);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(() { tester.view.resetPhysicalSize(); tester.view.resetDevicePixelRatio(); });
      await tester.pumpWidget(buildSettingsScreen());
      await tester.pump();
      await tester.pump();
      expect(find.text('Account'), findsOneWidget);
    });

    testWidgets('shows sign out option', (tester) async {
      tester.view.physicalSize = const Size(800, 3000);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(() { tester.view.resetPhysicalSize(); tester.view.resetDevicePixelRatio(); });
      await tester.pumpWidget(buildSettingsScreen());
      await tester.pump();
      await tester.pump();
      expect(find.text('Sign Out'), findsOneWidget);
    });

    testWidgets('shows about section', (tester) async {
      tester.view.physicalSize = const Size(800, 3000);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(() { tester.view.resetPhysicalSize(); tester.view.resetDevicePixelRatio(); });
      await tester.pumpWidget(buildSettingsScreen());
      await tester.pump();
      await tester.pump();
      expect(find.text('About'), findsOneWidget);
    });
  });

  group('AboutScreen', () {
    testWidgets('renders about page', (tester) async {
      await tester.pumpWidget(MaterialApp(
        home: const AboutScreen(),
      ));
      await tester.pump();
      expect(find.text('About'), findsWidgets);
    });
  });

  group('HelpScreen', () {
    testWidgets('renders help page', (tester) async {
      await tester.pumpWidget(MaterialApp(
        home: const HelpScreen(),
      ));
      await tester.pump();
      expect(find.text('Help & Support'), findsOneWidget);
    });
  });
}
