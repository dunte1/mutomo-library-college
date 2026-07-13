import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:dio/dio.dart';
import 'package:go_router/go_router.dart';
import 'package:mocktail/mocktail.dart';
import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/core/widgets/bottom_nav_shell.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_bloc.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_event.dart';
import 'package:ollmchs_library/features/auth/models/user_model.dart';
import 'package:ollmchs_library/features/auth/repositories/auth_repository.dart';
import 'package:ollmchs_library/features/dashboard/bloc/dashboard_bloc.dart';
import 'package:ollmchs_library/features/dashboard/screens/dashboard_screen.dart';
import 'package:ollmchs_library/features/messaging/bloc/messaging_bloc.dart';

class MockApiClient extends Mock implements ApiClient {}

class MockAuthRepository extends Mock implements AuthRepository {}

Response<dynamic> _fakeResponse(Map<String, dynamic> data) {
  return Response<dynamic>(
    data: data,
    statusCode: 200,
    requestOptions: RequestOptions(path: ''),
  );
}

void main() {
  late MockApiClient mockApiClient;
  late MockAuthRepository mockAuthRepo;

  setUp(() {
    mockApiClient = MockApiClient();
    mockAuthRepo = MockAuthRepository();
    when(() => mockApiClient.get(any(),
            queryParameters: any(named: 'queryParameters')))
        .thenAnswer((_) async => _fakeResponse({
              'data': [],
              'meta': {'current_page': 1, 'last_page': 1}
            }));
  });

  UserModel studentUser() => UserModel(
        id: 1,
        name: 'Test Student',
        email: 'student@test.com',
        roles: ['student'],
        permissions: [],
      );

  UserModel adminUser() => UserModel(
        id: 2,
        name: 'Admin User',
        email: 'admin@test.com',
        roles: ['admin'],
        permissions: [],
      );

  UserModel lecturerUser() => UserModel(
        id: 3,
        name: 'Lecturer User',
        email: 'lecturer@test.com',
        roles: ['lecturer'],
        permissions: [],
      );

  Widget buildDashboardShell(UserModel user) {
    when(() => mockAuthRepo.getStoredToken()).thenAnswer((_) async => 'test-token');
    when(() => mockAuthRepo.getUser()).thenAnswer((_) async => user);

    return MaterialApp(
      home: MultiRepositoryProvider(
        providers: [
          RepositoryProvider<ApiClient>.value(value: mockApiClient),
        ],
        child: MultiBlocProvider(
          providers: [
            BlocProvider<AuthBloc>(
              create: (_) => AuthBloc(authRepository: mockAuthRepo)
                ..add(const CheckAuthEvent()),
            ),
            BlocProvider<DashboardBloc>(
              create: (_) => DashboardBloc(api: mockApiClient),
            ),
            BlocProvider<MessagingBloc>(
              create: (_) => MessagingBloc(api: mockApiClient),
            ),
          ],
          child: Builder(builder: (context) {
            return _DashboardTestShell(user: user);
          }),
        ),
      ),
    );
  }

  group('DashboardScreen - Hamburger Menu', () {
    testWidgets('hamburger icon button exists in the dashboard',
        (tester) async {
      await tester.pumpWidget(buildDashboardShell(studentUser()));
      await tester.pumpAndSettle();

      expect(find.byIcon(Icons.menu), findsOneWidget);
    });

    testWidgets('hamburger icon button is tappable', (tester) async {
      await tester.pumpWidget(buildDashboardShell(studentUser()));
      await tester.pumpAndSettle();

      final menuButton = find.byIcon(Icons.menu);
      expect(menuButton, findsOneWidget);

      await tester.tap(menuButton);
      await tester.pumpAndSettle();
    });
  });

  group('DashboardScreen - Messages Action Chip', () {
    testWidgets('Messages action chip visible for unrestricted student',
        (tester) async {
      await tester.pumpWidget(buildDashboardShell(studentUser()));
      await tester.pumpAndSettle();

      expect(find.text('Messages'), findsWidgets);
    });

    testWidgets('Messages chip visible for admin', (tester) async {
      await tester.pumpWidget(buildDashboardShell(adminUser()));
      await tester.pumpAndSettle();

      expect(find.text('Messages'), findsWidgets);
    });

    testWidgets('Messages chip visible for lecturer', (tester) async {
      await tester.pumpWidget(buildDashboardShell(lecturerUser()));
      await tester.pumpAndSettle();

      expect(find.text('Messages'), findsWidgets);
    });

    testWidgets('Messages chip hidden for user without view-messages permission',
        (tester) async {
      final restrictedUser = UserModel(
        id: 4,
        name: 'Restricted User',
        email: 'restricted@test.com',
        roles: ['student'],
        permissions: ['view-books'],
      );
      await tester.pumpWidget(buildDashboardShell(restrictedUser));
      await tester.pumpAndSettle();

      expect(find.text('Messages'), findsNothing);
    });
  });

  group('DashboardScreen - Layout', () {
    testWidgets('does not have its own Scaffold (no inner Scaffold)',
        (tester) async {
      await tester.pumpWidget(buildDashboardShell(studentUser()));
      await tester.pumpAndSettle();

      expect(find.byType(AppBar), findsNothing);
    });

    testWidgets('renders dashboard content with greeting', (tester) async {
      await tester.pumpWidget(buildDashboardShell(studentUser()));
      await tester.pumpAndSettle();

      expect(find.textContaining('Good'), findsWidgets);
    });
  });
}

class _DashboardTestShell extends StatelessWidget {
  final UserModel user;
  const _DashboardTestShell({required this.user});

  @override
  Widget build(BuildContext context) {
    final goRouter = GoRouter(
      initialLocation: '/dashboard',
      routes: [
        ShellRoute(
          builder: (context, state, child) =>
              BottomNavShell(child: child),
          routes: [
            GoRoute(
              path: '/dashboard',
              name: 'dashboard',
              builder: (_, __) => const DashboardScreen(),
            ),
            GoRoute(
              path: '/books',
              name: 'books',
              builder: (_, __) =>
                  const Scaffold(body: Center(child: Text('Books'))),
            ),
            GoRoute(
              path: '/loans',
              name: 'loans',
              builder: (_, __) =>
                  const Scaffold(body: Center(child: Text('Loans'))),
            ),
            GoRoute(
              path: '/messages',
              name: 'messages',
              builder: (_, __) =>
                  const Scaffold(body: Center(child: Text('Messages'))),
            ),
            GoRoute(
              path: '/digital-library',
              name: 'digital-library',
              builder: (_, __) =>
                  const Scaffold(body: Center(child: Text('Digital'))),
            ),
            GoRoute(
              path: '/profile',
              name: 'profile',
              builder: (_, __) =>
                  const Scaffold(body: Center(child: Text('Profile'))),
            ),
            GoRoute(
              path: '/notifications',
              name: 'notifications',
              builder: (_, __) => const Scaffold(
                  body: Center(child: Text('Notifications'))),
            ),
          ],
        ),
      ],
    );

    return MaterialApp.router(routerConfig: goRouter);
  }
}
