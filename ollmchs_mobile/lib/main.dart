import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_crashlytics/firebase_crashlytics.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'core/theme/app_theme.dart';
import 'core/theme/theme_cubit.dart';
import 'core/routing/app_router.dart';
import 'core/widgets/keyboard_shortcuts.dart';
import 'core/widgets/app_lock_screen.dart';
import 'core/constants/environment.dart';
import 'core/network/api_client.dart';
import 'core/storage/local_storage_service.dart';
import 'core/storage/hive_cache_service.dart';
import 'core/services/push_notification_service.dart';
import 'core/services/session_service.dart';
import 'core/services/flag_secure_service.dart';
import 'core/services/connectivity_service.dart';
import 'features/auth/bloc/auth_bloc.dart';
import 'features/auth/repositories/auth_repository.dart';
import 'features/books/bloc/books_bloc.dart';
import 'features/books/repositories/books_repository.dart';
import 'features/loans/bloc/loans_bloc.dart';
import 'features/loans/repositories/loans_repository.dart';
import 'features/reservations/bloc/reservations_bloc.dart';
import 'features/reservations/repositories/reservations_repository.dart';
import 'features/fines/bloc/fines_bloc.dart';
import 'features/library_card/bloc/library_card_bloc.dart';
import 'features/digital_library/bloc/digital_library_bloc.dart';
import 'features/messaging/bloc/messaging_bloc.dart';
import 'features/notifications/bloc/notifications_bloc.dart';
import 'features/profile/bloc/profile_bloc.dart';
import 'features/dashboard/bloc/dashboard_bloc.dart';
import 'features/assignments/bloc/assignments_bloc.dart';
import 'features/teacher_assignments/bloc/teacher_assignments_bloc.dart';
import 'features/subscriptions/bloc/subscriptions_bloc.dart';
import 'features/communication/bloc/communication_bloc.dart';
import 'features/finance/bloc/finance_bloc.dart';
import 'features/books/bloc/reviews_bloc.dart';
import 'features/bookmarks/bloc/bookmarks_bloc.dart';
import 'features/auth/bloc/auth_event.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  debugPrint('Environment: ${Environment.name} (web: ${Environment.isWeb})');
  debugPrint('API Base URL: ${Environment.apiBaseUrl}');

  // Global error boundary — catches all unhandled errors
  FlutterError.onError = (details) {
    FlutterError.presentError(details);
    if (!kIsWeb) {
      FirebaseCrashlytics.instance.recordFlutterFatalError(details);
    }
  };

  // Catch async errors that escape the framework
  PlatformDispatcher.instance.onError = (error, stack) {
    if (!kIsWeb) {
      FirebaseCrashlytics.instance.recordError(error, stack, fatal: true);
    }
    return true;
  };

  try {
    if (Environment.firebaseEnabled) {
      await Firebase.initializeApp();
      // Enable Crashlytics in release builds only
      await FirebaseCrashlytics.instance.setCrashlyticsCollectionEnabled(kReleaseMode);
      await PushNotificationService().init();
    }
  } catch (e) {
    debugPrint('Firebase init skipped: $e');
  }

  try {
    await HiveCacheService.init();
  } catch (e) {
    debugPrint('Hive init failed: $e');
  }

  try {
    await ConnectivityService().init();
  } catch (e) {
    debugPrint('Connectivity init failed: $e');
  }

  // Run app with zone guard to catch async errors
  runApp(const OllmchsLibraryApp());
}

class OllmchsLibraryApp extends StatefulWidget {
  const OllmchsLibraryApp({super.key});

  @override
  State<OllmchsLibraryApp> createState() => _OllmchsLibraryAppState();
}

class _OllmchsLibraryAppState extends State<OllmchsLibraryApp> {
  late final LocalStorageService _storageService;
  late final SessionService _sessionService;
  bool _isAppLocked = false;
  bool _sessionInitialized = false;

  @override
  void initState() {
    super.initState();
    _storageService = LocalStorageService();
    _sessionService = SessionService(
      storage: _storageService,
      onLockRequested: _onAppLock,
      onSessionExpired: _onSessionExpired,
    );
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (!_sessionInitialized) {
      _sessionInitialized = true;
      _sessionService.initialize();
    }
  }

  @override
  void dispose() {
    _sessionService.dispose();
    super.dispose();
  }

  void _onAppLock() {
    if (mounted) {
      setState(() => _isAppLocked = true);
      FlagSecureService.enable();
    }
  }

  void _onSessionExpired() {
    _sessionService.setLocked(false);
    FlagSecureService.disable();
    setState(() => _isAppLocked = false);
    context.read<AuthBloc>().add(const LogoutEvent());
  }

  void _onAppUnlocked() {
    setState(() => _isAppLocked = false);
    FlagSecureService.disable();
    _sessionService.unlock();
  }

  @override
  Widget build(BuildContext context) {
    final apiClient = ApiClient(storageService: _storageService);

    return MultiRepositoryProvider(
      providers: [
        RepositoryProvider.value(value: _storageService),
        RepositoryProvider.value(value: apiClient),
        RepositoryProvider(
          create: (_) => AuthRepository(apiClient, _storageService),
        ),
        RepositoryProvider(create: (_) => BooksRepository(apiClient)),
        RepositoryProvider(create: (_) => LoansRepository(apiClient)),
        RepositoryProvider(create: (_) => ReservationsRepository(apiClient)),
      ],
      child: MultiBlocProvider(
        providers: [
          BlocProvider(
            create: (_) => ThemeCubit(storage: _storageService),
          ),
          BlocProvider(
            create: (context) =>
                AuthBloc(authRepository: context.read<AuthRepository>()),
          ),
          BlocProvider(
            create: (context) =>
                BooksBloc(repository: context.read<BooksRepository>()),
          ),
          BlocProvider(
            create: (context) =>
                LoansBloc(repository: context.read<LoansRepository>()),
          ),
          BlocProvider(
            create: (context) => ReservationsBloc(
              repository: context.read<ReservationsRepository>(),
            ),
          ),
          BlocProvider(
            create: (context) => FinesBloc(api: context.read<ApiClient>()),
          ),
          BlocProvider(
            create: (context) =>
                LibraryCardBloc(api: context.read<ApiClient>()),
          ),
          BlocProvider(
            create: (context) =>
                DigitalLibraryBloc(api: context.read<ApiClient>()),
          ),
          BlocProvider(
            create: (context) => MessagingBloc(api: context.read<ApiClient>()),
          ),
          BlocProvider(
            create: (context) =>
                NotificationsBloc(api: context.read<ApiClient>()),
          ),
          BlocProvider(
            create: (context) => ProfileBloc(api: context.read<ApiClient>()),
          ),
          BlocProvider(
            create: (context) => DashboardBloc(api: context.read<ApiClient>()),
          ),
          BlocProvider(
            create: (context) =>
                AssignmentsBloc(api: context.read<ApiClient>()),
          ),
          BlocProvider(
            create: (context) =>
                SubscriptionsBloc(api: context.read<ApiClient>()),
          ),
          BlocProvider(
            create: (context) =>
                CommunicationBloc(api: context.read<ApiClient>()),
          ),
          BlocProvider(
            create: (context) => FinanceBloc(api: context.read<ApiClient>()),
          ),
          BlocProvider(
            create: (context) => ReviewsBloc(api: context.read<ApiClient>()),
          ),
          BlocProvider(
            create: (context) =>
                TeacherAssignmentsBloc(api: context.read<ApiClient>()),
          ),
          BlocProvider(
            create: (context) => BookmarksBloc(api: context.read<ApiClient>()),
          ),
        ],
        child: BlocBuilder<ThemeCubit, ThemeMode>(
          builder: (context, themeMode) {
            return AppKeyboardShortcuts(
              child: MaterialApp.router(
                title: Environment.appName,
                debugShowCheckedModeBanner: false,
                theme: AppTheme.lightTheme,
                darkTheme: AppTheme.darkTheme,
                themeMode: themeMode,
                routerConfig: AppRouter.router,
                builder: (context, child) {
                  // When locked, show only the lock screen (no child to prevent UI flash)
                  if (_isAppLocked) {
                    return MaterialApp(
                      debugShowCheckedModeBanner: false,
                      theme: AppTheme.lightTheme,
                      darkTheme: AppTheme.darkTheme,
                      themeMode: themeMode,
                      home: AppLockScreen(onUnlocked: _onAppUnlocked),
                    );
                  }
                  return child ?? const SizedBox.shrink();
                },
              ),
            );
          },
        ),
      ),
    );
  }
}


