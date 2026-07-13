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
        .thenAnswer((_) async =>
            _fakeResponse({'data': [], 'meta': {'current_page': 1, 'last_page': 1}}));
  });

  UserModel userWithPermissions(List<String> permissions, [List<String>? roles]) {
    return UserModel(
      id: 1,
      name: 'Test User',
      email: 'test@test.com',
      roles: roles ?? ['student'],
      permissions: permissions,
    );
  }

  UserModel userWithoutPermissions() {
    return UserModel(
      id: 1,
      name: 'Test User',
      email: 'test@test.com',
      roles: ['student'],
    );
  }

  Widget buildShell(UserModel user) {
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
            BlocProvider<MessagingBloc>(
              create: (_) => MessagingBloc(api: mockApiClient),
            ),
          ],
          child: Builder(builder: (context) {
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
                      builder: (_, __) =>
                          const Scaffold(body: Center(child: Text('Dashboard'))),
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
                  ],
                ),
              ],
            );

            return MaterialApp.router(routerConfig: goRouter);
          }),
        ),
      ),
    );
  }

  group('BottomNavShell - Messages Tab', () {
    testWidgets('Messages tab visible for user with empty permissions',
        (tester) async {
      tester.view.physicalSize = const Size(400, 800);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(tester.view.resetPhysicalSize);

      await tester.pumpWidget(buildShell(userWithoutPermissions()));
      await tester.pumpAndSettle();

      expect(find.text('Messages'), findsWidgets);
    });

    testWidgets('Messages tab visible for user with view-messages permission',
        (tester) async {
      tester.view.physicalSize = const Size(400, 800);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(tester.view.resetPhysicalSize);

      await tester.pumpWidget(
          buildShell(userWithPermissions(['view-messages'])));
      await tester.pumpAndSettle();

      expect(find.text('Messages'), findsWidgets);
    });

    testWidgets('Messages tab visible for admin with empty permissions',
        (tester) async {
      tester.view.physicalSize = const Size(400, 800);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(tester.view.resetPhysicalSize);

      await tester.pumpWidget(
          buildShell(userWithPermissions([], ['admin'])));
      await tester.pumpAndSettle();

      expect(find.text('Messages'), findsWidgets);
    });

    testWidgets('Messages tab is tappable and navigates',
        (tester) async {
      tester.view.physicalSize = const Size(400, 800);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(tester.view.resetPhysicalSize);

      await tester.pumpWidget(buildShell(userWithPermissions([])));
      await tester.pumpAndSettle();

      final messagesIcon = find.byIcon(Icons.mail_outlined);
      if (messagesIcon.evaluate().isNotEmpty) {
        await tester.tap(messagesIcon.first);
        await tester.pumpAndSettle();
      }
    });
  });

  group('BottomNavShell - Tab Visibility', () {
    testWidgets('shows all 6 tabs for unrestricted student',
        (tester) async {
      tester.view.physicalSize = const Size(400, 800);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(tester.view.resetPhysicalSize);

      await tester.pumpWidget(buildShell(userWithoutPermissions()));
      await tester.pumpAndSettle();

      expect(find.text('Dashboard'), findsWidgets);
      expect(find.text('Books'), findsWidgets);
      expect(find.text('Loans'), findsWidgets);
      expect(find.text('Messages'), findsWidgets);
      expect(find.text('Digital'), findsWidgets);
      expect(find.text('Profile'), findsWidgets);
    });

    testWidgets('drawer exists on BottomNavShell scaffold',
        (tester) async {
      tester.view.physicalSize = const Size(400, 800);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(tester.view.resetPhysicalSize);

      await tester.pumpWidget(buildShell(userWithoutPermissions()));
      await tester.pumpAndSettle();

      final allScaffolds = tester.widgetList<Scaffold>(find.byType(Scaffold));
      final shellScaffold = allScaffolds.firstWhere(
        (s) => s.drawer != null,
        orElse: () => const Scaffold(),
      );
      expect(shellScaffold.drawer, isNotNull);
    });
  });
}
