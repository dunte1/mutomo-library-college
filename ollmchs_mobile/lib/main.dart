import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'core/theme/app_theme.dart';
import 'core/theme/theme_cubit.dart';
import 'core/routing/app_router.dart';
import 'core/widgets/keyboard_shortcuts.dart';
import 'core/constants/environment.dart';
import 'core/network/api_client.dart';
import 'core/storage/local_storage_service.dart';
import 'core/storage/hive_cache_service.dart';
import 'core/services/push_notification_service.dart';
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

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  debugPrint('🔧 Environment: ${Environment.name} (web: ${Environment.isWeb})');
  debugPrint('🔗 API Base URL: ${Environment.apiBaseUrl}');

  if (Environment.firebaseEnabled) {
    await Firebase.initializeApp();
    await PushNotificationService().init();
  }

  await HiveCacheService.init();

  runApp(const OllmchsLibraryApp());
}

class OllmchsLibraryApp extends StatelessWidget {
  const OllmchsLibraryApp({super.key});

  @override
  Widget build(BuildContext context) {
    final apiClient = ApiClient(storageService: LocalStorageService());

    return MultiRepositoryProvider(
      providers: [
        RepositoryProvider.value(value: apiClient),
        RepositoryProvider(
          create: (_) => AuthRepository(apiClient, LocalStorageService()),
        ),
        RepositoryProvider(create: (_) => BooksRepository(apiClient)),
        RepositoryProvider(create: (_) => LoansRepository(apiClient)),
        RepositoryProvider(create: (_) => ReservationsRepository(apiClient)),
        RepositoryProvider(create: (_) => LocalStorageService()),
      ],
      child: MultiBlocProvider(
        providers: [
          BlocProvider(
            create: (_) => ThemeCubit(storage: LocalStorageService()),
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
              ),
            );
          },
        ),
      ),
    );
  }
}
