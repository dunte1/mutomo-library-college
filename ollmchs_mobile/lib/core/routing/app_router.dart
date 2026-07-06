import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../features/auth/bloc/auth_bloc.dart';
import '../../features/auth/bloc/auth_state.dart';
import '../../features/auth/screens/splash_screen.dart';
import '../../features/auth/screens/onboarding_screen.dart';
import '../../features/auth/screens/login_screen.dart';
import '../../features/auth/screens/register_screen.dart';
import '../../features/auth/screens/forgot_password_screen.dart';
import '../../features/auth/screens/two_factor_screen.dart';
import '../../features/auth/screens/two_factor_setup_screen.dart';
import '../../features/dashboard/screens/dashboard_screen.dart';
import '../../features/books/screens/book_list_screen.dart';
import '../../features/books/screens/book_detail_screen.dart';
import '../../features/books/screens/book_search_screen.dart';
import '../../features/books/screens/review_list_screen.dart';
import '../../features/books/screens/category_list_screen.dart';
import '../../features/books/screens/category_book_list_screen.dart';
import '../../features/books/screens/new_arrivals_screen.dart';
import '../../features/authors/screens/author_list_screen.dart';
import '../../features/publishers/screens/publisher_list_screen.dart';
import '../../features/loans/screens/active_loans_screen.dart';
import '../../features/loans/screens/loan_history_screen.dart';
import '../../features/reservations/screens/reservation_list_screen.dart';
import '../../features/fines/screens/fines_screen.dart';
import '../../features/library_card/screens/library_card_screen.dart';
import '../../features/digital_library/screens/digital_asset_list_screen.dart';
import '../../features/digital_library/screens/digital_asset_reader_screen.dart';
import '../../features/digital_library/screens/reading_history_screen.dart';
import '../../features/messaging/screens/inbox_screen.dart';
import '../../features/messaging/screens/message_detail_screen.dart';
import '../../features/messaging/screens/compose_message_screen.dart';
import '../../features/notifications/screens/notification_list_screen.dart';
import '../../features/profile/screens/profile_screen.dart';
import '../../features/profile/screens/settings_screen.dart';
import '../../features/profile/screens/edit_profile_screen.dart';
import '../../features/profile/screens/notification_preferences_screen.dart';
import '../../features/assignments/screens/assignment_list_screen.dart';
import '../../features/assignments/screens/assignment_detail_screen.dart';
import '../../features/teacher_assignments/screens/teacher_assignment_list_screen.dart';
import '../../features/teacher_assignments/screens/teacher_assignment_form_screen.dart';
import '../../features/teacher_assignments/screens/teacher_assignment_progress_screen.dart';
import '../../features/teacher_assignments/models/teacher_assignment_model.dart';
import '../../features/subscriptions/screens/subscription_plans_screen.dart';
import '../../features/subscriptions/screens/my_subscription_screen.dart';
import '../../features/subscriptions/screens/subscription_checkout_screen.dart';
import '../../features/communication/screens/announcement_list_screen.dart';
import '../../features/communication/screens/announcement_detail_screen.dart';
import '../../features/communication/screens/event_list_screen.dart';
import '../../features/communication/screens/event_detail_screen.dart';
import '../../features/communication/screens/bulletin_list_screen.dart';
import '../../features/communication/screens/bulletin_detail_screen.dart';
import '../../features/finance/screens/payment_history_screen.dart';
import '../../features/finance/screens/receipt_detail_screen.dart';
import '../../features/auth/models/user_model.dart';
import '../helpers/permission_helper.dart';
import '../widgets/bottom_nav_shell.dart';

class AppRouter {
  AppRouter._();

  static final _rootNavigatorKey = GlobalKey<NavigatorState>();
  static final _shellNavigatorKey = GlobalKey<NavigatorState>();

  static final Set<String> _authRoutes = {
    '/login',
    '/register',
    '/forgot-password',
    '/two-factor',
  };

  // '/two-factor-setup' is deliberately NOT in _authRoutes — authenticated
  // users need to access it from Settings to enable 2FA.

  static final Map<String, bool Function(UserModel)> _permissionGates = {
    '/dashboard': (user) => PermissionHelper.canAccessDashboard(user),
    '/notifications': (user) => PermissionHelper.canAccessNotifications(user),
  };

  static final GoRouter router = GoRouter(
    navigatorKey: _rootNavigatorKey,
    initialLocation: '/splash',
    redirect: (context, state) {
      final authState = context.read<AuthBloc>().state;
      final location = state.uri.toString();
      final isAuthRoute = _authRoutes.any((r) => location == r);
      final isSplash = location == '/splash';
      final isOnboarding = location == '/onboarding';

      if (authState is Authenticated) {
        final user = authState.user;

        if (isSplash) return '/dashboard';
        if (isOnboarding) return '/dashboard';
        if (isAuthRoute) return '/dashboard';

        final gate = _permissionGates.entries.firstWhere(
          (e) => location == e.key || location.startsWith('${e.key}/'),
          orElse: () => MapEntry('', (_) => true),
        );
        if (!gate.value(user)) {
          return '/dashboard';
        }

        return null;
      }

      if (isSplash || isOnboarding) return null;
      if (isAuthRoute) return null;

      return '/login';
    },
    routes: [
      GoRoute(
        path: '/splash',
        name: 'splash',
        builder: (_, __) => const SplashScreen(),
      ),
      GoRoute(
        path: '/onboarding',
        name: 'onboarding',
        builder: (_, __) => const OnboardingScreen(),
      ),
      GoRoute(
        path: '/login',
        name: 'login',
        builder: (_, __) => const LoginScreen(),
      ),
      GoRoute(
        path: '/register',
        name: 'register',
        builder: (_, __) => const RegisterScreen(),
      ),
      GoRoute(
        path: '/forgot-password',
        name: 'forgot-password',
        builder: (_, __) => const ForgotPasswordScreen(),
      ),
      GoRoute(
        path: '/two-factor',
        name: 'two-factor',
        builder: (_, state) {
          final extras = state.extra as Map<String, dynamic>?;
          return TwoFactorScreen(
            userId: extras?['userId'] as int? ?? 0,
            tempToken: extras?['tempToken'] as String? ?? '',
          );
        },
      ),
      GoRoute(
        path: '/two-factor-setup',
        name: 'two-factor-setup',
        builder: (_, __) => const TwoFactorSetupScreen(),
      ),
      ShellRoute(
        navigatorKey: _shellNavigatorKey,
        builder: (_, __, child) => BottomNavShell(child: child),
        routes: [
          GoRoute(
            path: '/dashboard',
            name: 'dashboard',
            builder: (_, __) => const DashboardScreen(),
          ),
          GoRoute(
            path: '/categories',
            name: 'categories',
            builder: (_, __) => const CategoryListScreen(),
          ),
          GoRoute(
            path: '/books',
            name: 'books',
            builder: (_, __) => const BookListScreen(),
            routes: [
              GoRoute(
                path: 'search',
                name: 'books-search',
                builder: (_, __) => const BookSearchScreen(),
              ),
              GoRoute(
                path: 'new-arrivals',
                name: 'books-new-arrivals',
                builder: (_, __) => const NewArrivalsScreen(),
              ),
              GoRoute(
                path: 'category/:slug',
                name: 'books-category',
                builder: (_, state) => CategoryBookListScreen(
                  categorySlug: state.pathParameters['slug']!,
                ),
              ),
              GoRoute(
                path: ':id',
                name: 'book-detail',
                builder: (_, state) => BookDetailScreen(
                  bookId: int.parse(state.pathParameters['id']!),
                ),
                routes: [
                  GoRoute(
                    path: 'reviews',
                    name: 'book-reviews',
                    builder: (_, state) => ReviewListScreen(
                      bookId: int.parse(state.pathParameters['id']!),
                      bookTitle: state.extra as String? ?? '',
                    ),
                  ),
                ],
              ),
            ],
          ),
          GoRoute(
            path: '/loans',
            name: 'loans',
            builder: (_, __) => const ActiveLoansScreen(),
            routes: [
              GoRoute(
                path: 'history',
                name: 'loan-history',
                builder: (_, __) => const LoanHistoryScreen(),
              ),
            ],
          ),
          GoRoute(
            path: '/reservations',
            name: 'reservations',
            builder: (_, __) => const ReservationListScreen(),
          ),
          GoRoute(
            path: '/fines',
            name: 'fines',
            builder: (_, __) => const FinesScreen(),
          ),
          GoRoute(
            path: '/library-card',
            name: 'library-card',
            builder: (_, __) => const LibraryCardScreen(),
          ),
          GoRoute(
            path: '/digital-library',
            name: 'digital-library',
            builder: (_, __) => const DigitalAssetListScreen(),
            routes: [
              GoRoute(
                path: 'reading-history',
                name: 'reading-history',
                builder: (_, __) => const ReadingHistoryScreen(),
              ),
              GoRoute(
                path: ':id',
                name: 'digital-asset-reader',
                builder: (_, state) => DigitalAssetReaderScreen(
                  assetId: int.parse(state.pathParameters['id']!),
                ),
              ),
            ],
          ),
          GoRoute(
            path: '/messages',
            name: 'messages',
            builder: (_, __) => const InboxScreen(),
            routes: [
              GoRoute(
                path: 'compose',
                name: 'compose-message',
                builder: (_, state) {
                  final extra = state.extra as Map<String, dynamic>?;
                  return ComposeMessageScreen(
                    recipientId: extra?['recipientId'] as int?,
                    recipientName: extra?['recipientName'] as String?,
                  );
                },
              ),
              GoRoute(
                path: ':id',
                name: 'message-detail',
                builder: (_, state) => MessageDetailScreen(
                  messageId: int.parse(state.pathParameters['id']!),
                ),
              ),
            ],
          ),
          GoRoute(
            path: '/notifications',
            name: 'notifications',
            builder: (_, __) => const NotificationListScreen(),
          ),
          GoRoute(
            path: '/profile',
            name: 'profile',
            builder: (_, __) => const ProfileScreen(),
            routes: [
              GoRoute(
                path: 'settings',
                name: 'settings',
                builder: (_, __) => const SettingsScreen(),
              ),
              GoRoute(
                path: 'edit',
                name: 'edit-profile',
                builder: (_, state) =>
                    EditProfileScreen(user: state.extra as UserModel),
              ),
              GoRoute(
                path: 'notifications',
                name: 'notification-preferences',
                builder: (_, __) => const NotificationPreferencesScreen(),
              ),
            ],
          ),
          GoRoute(
            path: '/assignments',
            name: 'assignments',
            builder: (_, __) => const AssignmentListScreen(),
            routes: [
              GoRoute(
                path: ':id',
                name: 'assignment-detail',
                builder: (_, state) => AssignmentDetailScreen(
                  assignmentId: int.parse(state.pathParameters['id']!),
                ),
              ),
            ],
          ),
          GoRoute(
            path: '/teacher-assignments',
            name: 'teacher-assignments',
            builder: (_, __) => const TeacherAssignmentListScreen(),
            routes: [
              GoRoute(
                path: 'create',
                name: 'teacher-assignment-form',
                builder: (_, state) => TeacherAssignmentFormScreen(
                  editAssignment: state.extra as TeacherAssignmentModel?,
                ),
              ),
              GoRoute(
                path: ':id/progress',
                name: 'teacher-assignment-progress',
                builder: (_, state) => TeacherAssignmentProgressScreen(
                  assignmentId: int.parse(state.pathParameters['id']!),
                ),
              ),
            ],
          ),
          GoRoute(
            path: '/subscriptions',
            name: 'subscriptions',
            builder: (_, __) => const SubscriptionPlansScreen(),
            routes: [
              GoRoute(
                path: 'my',
                name: 'my-subscription',
                builder: (_, __) => const MySubscriptionScreen(),
              ),
              GoRoute(
                path: 'checkout',
                name: 'subscription-checkout',
                builder: (_, state) {
                  final extra = state.extra as Map<String, dynamic>?;
                  return SubscriptionCheckoutScreen(
                    planId: extra?['planId'] as int? ?? 0,
                    planName: extra?['planName'] as String? ?? '',
                    planPrice: extra?['planPrice'] as String? ?? '0',
                    planCurrency: extra?['planCurrency'] as String?,
                    planDuration: extra?['planDuration'] as String?,
                    planDurationDays: extra?['planDurationDays'] as int?,
                  );
                },
              ),
            ],
          ),
          GoRoute(
            path: '/bulletins',
            name: 'bulletins',
            builder: (_, __) => const BulletinListScreen(),
            routes: [
              GoRoute(
                path: ':id',
                name: 'bulletin-detail',
                builder: (_, state) => BulletinDetailScreen(
                  bulletinId: int.parse(state.pathParameters['id']!),
                ),
              ),
            ],
          ),
          GoRoute(
            path: '/announcements',
            name: 'announcements',
            builder: (_, __) => const AnnouncementListScreen(),
            routes: [
              GoRoute(
                path: ':id',
                name: 'announcement-detail',
                builder: (_, state) => AnnouncementDetailScreen(
                  announcementId: int.parse(state.pathParameters['id']!),
                ),
              ),
            ],
          ),
          GoRoute(
            path: '/events',
            name: 'events',
            builder: (_, __) => const EventListScreen(),
            routes: [
              GoRoute(
                path: ':id',
                name: 'event-detail',
                builder: (_, state) => EventDetailScreen(
                  eventId: int.parse(state.pathParameters['id']!),
                ),
              ),
            ],
          ),
          GoRoute(
            path: '/authors',
            name: 'authors',
            builder: (_, __) => const AuthorListScreen(),
          ),
          GoRoute(
            path: '/publishers',
            name: 'publishers',
            builder: (_, __) => const PublisherListScreen(),
          ),
          GoRoute(
            path: '/payments',
            name: 'payments',
            builder: (_, __) => const PaymentHistoryScreen(),
            routes: [
              GoRoute(
                path: ':id',
                name: 'receipt-detail',
                builder: (_, state) => ReceiptDetailScreen(
                  paymentId: int.parse(state.pathParameters['id']!),
                ),
              ),
            ],
          ),
        ],
      ),
    ],
  );
}
