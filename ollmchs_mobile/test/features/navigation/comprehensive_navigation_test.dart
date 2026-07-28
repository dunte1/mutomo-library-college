import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mocktail/mocktail.dart';
import 'package:dio/dio.dart';
import 'package:go_router/go_router.dart';
import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/core/widgets/bottom_nav_shell.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_bloc.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_event.dart';
import 'package:ollmchs_library/features/auth/models/user_model.dart';
import 'package:ollmchs_library/features/auth/repositories/auth_repository.dart';
import 'package:ollmchs_library/features/dashboard/bloc/dashboard_bloc.dart';
import 'package:ollmchs_library/features/dashboard/screens/dashboard_screen.dart';
import 'package:ollmchs_library/features/books/screens/book_list_screen.dart';
import 'package:ollmchs_library/features/loans/screens/active_loans_screen.dart';
import 'package:ollmchs_library/features/messaging/bloc/messaging_bloc.dart';
import 'package:ollmchs_library/features/messaging/screens/inbox_screen.dart';
import 'package:ollmchs_library/features/digital_library/screens/digital_asset_list_screen.dart';
import 'package:ollmchs_library/features/profile/screens/profile_screen.dart';
import 'package:ollmchs_library/features/reservations/bloc/reservations_bloc.dart';
import 'package:ollmchs_library/features/books/bloc/books_bloc.dart';
import 'package:ollmchs_library/features/books/repositories/books_repository.dart';
import 'package:ollmchs_library/features/loans/bloc/loans_bloc.dart';
import 'package:ollmchs_library/features/loans/repositories/loans_repository.dart';
import 'package:ollmchs_library/features/reservations/repositories/reservations_repository.dart';
import 'package:ollmchs_library/features/fines/bloc/fines_bloc.dart';
import 'package:ollmchs_library/features/library_card/bloc/library_card_bloc.dart';
import 'package:ollmchs_library/features/digital_library/bloc/digital_library_bloc.dart';
import 'package:ollmchs_library/features/notifications/bloc/notifications_bloc.dart';
import 'package:ollmchs_library/features/profile/bloc/profile_bloc.dart';
import 'package:ollmchs_library/features/assignments/bloc/assignments_bloc.dart';
import 'package:ollmchs_library/features/subscriptions/bloc/subscriptions_bloc.dart';
import 'package:ollmchs_library/features/communication/bloc/communication_bloc.dart';
import 'package:ollmchs_library/features/finance/bloc/finance_bloc.dart';
import 'package:ollmchs_library/features/books/bloc/reviews_bloc.dart';
import 'package:ollmchs_library/features/teacher_assignments/bloc/teacher_assignments_bloc.dart';
import 'package:ollmchs_library/features/bookmarks/bloc/bookmarks_bloc.dart';

class MockApiClient extends Mock implements ApiClient {}
class MockAuthRepository extends Mock implements AuthRepository {}

void main() {
  late MockApiClient mockApi;
  late MockAuthRepository mockAuthRepo;

  setUp(() {
    mockApi = MockApiClient();
    mockAuthRepo = MockAuthRepository();
    when(() => mockApi.get(any(), queryParameters: any(named: 'queryParameters')))
        .thenAnswer((_) async => Response(
              data: {'data': [], 'meta': {'current_page': 1, 'last_page': 1}},
              statusCode: 200,
              requestOptions: RequestOptions(path: ''),
            ));
    when(() => mockApi.post(any(), data: any(named: 'data')))
        .thenAnswer((_) async => Response(data: {'data': {}}, statusCode: 200, requestOptions: RequestOptions(path: '')));
  });

  UserModel studentUser() => UserModel(
    id: 1, name: 'John Student', email: 'student@test.com',
    roles: ['student'], permissions: [],
  );

  Widget buildApp({UserModel? user}) {
    final u = user ?? studentUser();
    when(() => mockAuthRepo.getStoredToken()).thenAnswer((_) async => 'token');
    when(() => mockAuthRepo.getUser()).thenAnswer((_) async => u);

    return MultiRepositoryProvider(
      providers: [RepositoryProvider<ApiClient>.value(value: mockApi)],
      child: MultiBlocProvider(
        providers: [
          BlocProvider<AuthBloc>(create: (_) => AuthBloc(authRepository: mockAuthRepo)..add(const CheckAuthEvent())),
          BlocProvider<DashboardBloc>(create: (_) => DashboardBloc(api: mockApi)),
          BlocProvider<BooksBloc>(create: (_) => BooksBloc(repository: MockBooksRepo())),
          BlocProvider<LoansBloc>(create: (_) => LoansBloc(repository: MockLoansRepo())),
          BlocProvider<ReservationsBloc>(create: (_) => ReservationsBloc(repository: MockReservationsRepo())),
          BlocProvider<FinesBloc>(create: (_) => FinesBloc(api: mockApi)),
          BlocProvider<LibraryCardBloc>(create: (_) => LibraryCardBloc(api: mockApi)),
          BlocProvider<DigitalLibraryBloc>(create: (_) => DigitalLibraryBloc(api: mockApi)),
          BlocProvider<MessagingBloc>(create: (_) => MessagingBloc(api: mockApi)),
          BlocProvider<NotificationsBloc>(create: (_) => NotificationsBloc(api: mockApi)),
          BlocProvider<ProfileBloc>(create: (_) => ProfileBloc(api: mockApi)),
          BlocProvider<AssignmentsBloc>(create: (_) => AssignmentsBloc(api: mockApi)),
          BlocProvider<SubscriptionsBloc>(create: (_) => SubscriptionsBloc(api: mockApi)),
          BlocProvider<CommunicationBloc>(create: (_) => CommunicationBloc(api: mockApi)),
          BlocProvider<FinanceBloc>(create: (_) => FinanceBloc(api: mockApi)),
          BlocProvider<ReviewsBloc>(create: (_) => ReviewsBloc(api: mockApi)),
          BlocProvider<TeacherAssignmentsBloc>(create: (_) => TeacherAssignmentsBloc(api: mockApi)),
          BlocProvider<BookmarksBloc>(create: (_) => BookmarksBloc(api: mockApi)),
        ],
        child: Builder(builder: (context) {
          final goRouter = GoRouter(
            initialLocation: '/dashboard',
            routes: [
              ShellRoute(
                builder: (_, __, child) => BottomNavShell(child: child),
                routes: [
                  GoRoute(path: '/dashboard', name: 'dashboard', builder: (_, __) => const DashboardScreen()),
                  GoRoute(path: '/books', name: 'books', builder: (_, __) => const BookListScreen()),
                  GoRoute(path: '/loans', name: 'loans', builder: (_, __) => const ActiveLoansScreen()),
                  GoRoute(path: '/messages', name: 'messages', builder: (_, __) => const InboxScreen()),
                  GoRoute(path: '/digital-library', name: 'digital-library', builder: (_, __) => const DigitalAssetListScreen()),
                  GoRoute(path: '/profile', name: 'profile', builder: (_, __) => const ProfileScreen()),
                ],
              ),
            ],
          );
          return MaterialApp.router(routerConfig: goRouter);
        }),
      ),
    );
  }

  group('BottomNavShell Navigation', () {
    testWidgets('shows 6 tabs for student', (tester) async {
      await tester.pumpWidget(buildApp());
      await tester.pumpAndSettle();
      expect(find.byType(NavigationDestination), findsNWidgets(6));
    });

    testWidgets('tapping Books tab navigates to books', (tester) async {
      await tester.pumpWidget(buildApp());
      await tester.pumpAndSettle();
      await tester.tap(find.text('Books'));
      await tester.pumpAndSettle();
      expect(find.byType(BookListScreen), findsOneWidget);
    });

    testWidgets('tapping Loans tab navigates to loans', (tester) async {
      await tester.pumpWidget(buildApp());
      await tester.pumpAndSettle();
      await tester.tap(find.text('Loans'));
      await tester.pumpAndSettle();
      expect(find.byType(ActiveLoansScreen), findsOneWidget);
    });

    testWidgets('tapping Messages tab navigates to messages', (tester) async {
      await tester.pumpWidget(buildApp());
      await tester.pumpAndSettle();
      await tester.tap(find.text('Messages'));
      await tester.pumpAndSettle();
      expect(find.byType(InboxScreen), findsOneWidget);
    });

    testWidgets('tapping Digital tab navigates to digital library', (tester) async {
      await tester.pumpWidget(buildApp());
      await tester.pumpAndSettle();
      await tester.tap(find.text('Digital'));
      await tester.pumpAndSettle();
      expect(find.byType(DigitalAssetListScreen), findsOneWidget);
    });

    testWidgets('tapping Profile tab navigates to profile', (tester) async {
      await tester.pumpWidget(buildApp());
      await tester.pumpAndSettle();
      await tester.tap(find.text('Profile'));
      await tester.pumpAndSettle();
      expect(find.byType(ProfileScreen), findsOneWidget);
    });

    testWidgets('tapping Dashboard tab navigates back to dashboard', (tester) async {
      await tester.pumpWidget(buildApp());
      await tester.pumpAndSettle();
      await tester.tap(find.text('Books'));
      await tester.pumpAndSettle();
      await tester.tap(find.text('Dashboard'));
      await tester.pumpAndSettle();
      expect(find.byType(DashboardScreen), findsOneWidget);
    });

    testWidgets('drawer opens when menu icon tapped', (tester) async {
      await tester.pumpWidget(buildApp());
      await tester.pumpAndSettle();
      final menuButton = find.byIcon(Icons.menu);
      expect(menuButton, findsOneWidget);
      await tester.tap(menuButton);
      await tester.pumpAndSettle();
      expect(find.byType(Drawer), findsOneWidget);
    });

    testWidgets('drawer shows navigation items', (tester) async {
      await tester.pumpWidget(buildApp());
      await tester.pumpAndSettle();
      await tester.tap(find.byIcon(Icons.menu));
      await tester.pumpAndSettle();
      expect(find.text('Dashboard'), findsWidgets);
      expect(find.text('My Library'), findsOneWidget);
      expect(find.text('Digital Library'), findsOneWidget);
      expect(find.text('Messages'), findsWidgets);
      expect(find.text('Notifications'), findsOneWidget);
      expect(find.text('Profile'), findsWidgets);
      expect(find.text('Settings'), findsOneWidget);
      expect(find.text('Logout'), findsOneWidget);
    });

    testWidgets('drawer logout shows confirmation dialog', (tester) async {
      await tester.pumpWidget(buildApp());
      await tester.pumpAndSettle();
      await tester.tap(find.byIcon(Icons.menu));
      await tester.pumpAndSettle();
      await tester.tap(find.text('Logout'));
      await tester.pumpAndSettle();
      expect(find.text('Are you sure you want to logout?'), findsOneWidget);
      expect(find.text('Cancel'), findsOneWidget);
    });
  });
}

class MockBooksRepo extends Mock implements BooksRepository {}
class MockLoansRepo extends Mock implements LoansRepository {}
class MockReservationsRepo extends Mock implements ReservationsRepository {}
