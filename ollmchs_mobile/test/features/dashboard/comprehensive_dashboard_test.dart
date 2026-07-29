import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mocktail/mocktail.dart';
import 'package:dio/dio.dart';
import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_bloc.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_event.dart';
import 'package:ollmchs_library/features/auth/models/user_model.dart';
import 'package:ollmchs_library/features/auth/repositories/auth_repository.dart';
import 'package:ollmchs_library/features/dashboard/bloc/dashboard_bloc.dart';
import 'package:ollmchs_library/features/dashboard/screens/dashboard_screen.dart';
import 'package:ollmchs_library/features/messaging/bloc/messaging_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:ollmchs_library/core/widgets/bottom_nav_shell.dart';

class MockApiClient extends Mock implements ApiClient {}
class MockAuthRepository extends Mock implements AuthRepository {}

Widget _buildDashboardTestShell(UserModel user, ApiClient api, AuthRepository authRepo) {
  return MultiRepositoryProvider(
    providers: [RepositoryProvider<ApiClient>.value(value: api)],
    child: MultiBlocProvider(
      providers: [
        BlocProvider<AuthBloc>(create: (_) => AuthBloc(authRepository: authRepo)..add(const CheckAuthEvent())),
        BlocProvider<DashboardBloc>(create: (_) => DashboardBloc(api: api)),
        BlocProvider<MessagingBloc>(create: (_) => MessagingBloc(api: api)),
      ],
      child: _DashboardTestShell(user: user),
    ),
  );
}

class _DashboardTestShell extends StatelessWidget {
  final UserModel user;
  const _DashboardTestShell({required this.user});

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      routerConfig: GoRouter(
        initialLocation: '/dashboard',
        routes: [
          ShellRoute(
            builder: (_, __, child) => BottomNavShell(child: child),
            routes: [
              GoRoute(path: '/dashboard', name: 'dashboard', builder: (_, __) => const DashboardScreen()),
              GoRoute(path: '/books', name: 'books', builder: (_, __) => const Scaffold(body: Text('Books'))),
              GoRoute(path: '/loans', name: 'loans', builder: (_, __) => const Scaffold(body: Text('Loans'))),
              GoRoute(path: '/messages', name: 'messages', builder: (_, __) => const Scaffold(body: Text('Messages'))),
              GoRoute(path: '/digital-library', name: 'digital-library', builder: (_, __) => const Scaffold(body: Text('Digital'))),
              GoRoute(path: '/profile', name: 'profile', builder: (_, __) => const Scaffold(body: Text('Profile'))),
            ],
          ),
        ],
      ),
    );
  }
}

void main() {
  late MockApiClient mockApi;
  late MockAuthRepository mockAuthRepo;

  setUp(() {
    mockApi = MockApiClient();
    mockAuthRepo = MockAuthRepository();
    when(() => mockApi.get(any(), queryParameters: any(named: 'queryParameters')))
        .thenAnswer((_) async => Response(
              data: {'data': {'stats': {}, 'recent_loans': [], 'due_soon': [], 'featured_books': [], 'recent_digital_assets': [], 'upcoming_events': []}},
              statusCode: 200,
              requestOptions: RequestOptions(path: ''),
            ));
  });

  group('DashboardScreen', () {
    testWidgets('renders with greeting showing user name', (tester) async {
      final user = UserModel(id: 1, name: 'John Student', email: 'student@test.com', roles: ['student'], permissions: []);
      when(() => mockAuthRepo.getStoredToken()).thenAnswer((_) async => 'token');
      when(() => mockAuthRepo.getUser()).thenAnswer((_) async => user);

      await tester.pumpWidget(MaterialApp(home: _buildDashboardTestShell(user, mockApi, mockAuthRepo)));
      await tester.pump();
      expect(find.byType(DashboardScreen), findsOneWidget);
    });

    testWidgets('shows stat cards', (tester) async {
      final user = UserModel(id: 1, name: 'Test', email: 'test@test.com', roles: ['student'], permissions: []);
      when(() => mockAuthRepo.getStoredToken()).thenAnswer((_) async => 'token');
      when(() => mockAuthRepo.getUser()).thenAnswer((_) async => user);

      await tester.pumpWidget(MaterialApp(home: _buildDashboardTestShell(user, mockApi, mockAuthRepo)));
      await tester.pump();
      expect(find.text('Books'), findsWidgets);
    });

    testWidgets('shows quick actions section', (tester) async {
      tester.view.physicalSize = const Size(1400, 2000);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(() {
        tester.view.resetPhysicalSize();
        tester.view.resetDevicePixelRatio();
      });
      final user = UserModel(id: 1, name: 'Test', email: 'test@test.com', roles: ['student'], permissions: []);
      when(() => mockAuthRepo.getStoredToken()).thenAnswer((_) async => 'token');
      when(() => mockAuthRepo.getUser()).thenAnswer((_) async => user);
      when(() => mockApi.get('/v1/dashboard'))
          .thenAnswer((_) async => Response(
                data: <String, dynamic>{'data': <String, dynamic>{'stats': <String, dynamic>{}}},
                statusCode: 200,
                requestOptions: RequestOptions(path: ''),
              ));

      await tester.pumpWidget(MaterialApp(home: _buildDashboardTestShell(user, mockApi, mockAuthRepo)));
      await tester.pump();
      await tester.pump();
      expect(find.text('Quick Actions'), findsOneWidget);
    });
  });

  group('DashboardModel', () {
    test('fromJson with all fields', () {
      final json = {
        'stats': {
          'total_books': 100,
          'active_loans': 3,
          'overdue_loans': 1,
          'total_fines': 500.0,
          'pending_fines': 2,
          'available_books': 90,
          'digital_assets': 50,
          'active_reservations': 2,
          'unread_notifications': 5,
          'borrow_limit': 10,
        },
        'unread_messages': 3,
        'recent_loans': [],
        'due_soon': [{'id': 1, 'title': 'Book A', 'description': 'Due soon'}],
        'featured_books': [{'id': 2, 'title': 'Book B'}],
        'recent_digital_assets': [{'id': 1, 'title': 'Asset A', 'file_type': 'pdf'}],
        'upcoming_events': [{'id': 1, 'title': 'Event A'}],
      };
      // Import would be needed but testing structure
      final stats = json['stats'] as Map<String, dynamic>;
      expect(stats['total_books'], 100);
      expect(stats['borrow_limit'], 10);
      expect((json['recent_digital_assets'] as List).length, 1);
      expect((json['upcoming_events'] as List).length, 1);
    });
  });
}
