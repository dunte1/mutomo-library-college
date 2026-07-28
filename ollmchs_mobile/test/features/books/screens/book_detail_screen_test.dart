import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mocktail/mocktail.dart';
import 'package:dio/dio.dart';
import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_bloc.dart';
import 'package:ollmchs_library/features/auth/models/user_model.dart';
import 'package:ollmchs_library/features/auth/repositories/auth_repository.dart';
import 'package:ollmchs_library/features/books/bloc/books_bloc.dart';
import 'package:ollmchs_library/features/books/bloc/reviews_bloc.dart';
import 'package:ollmchs_library/features/books/repositories/books_repository.dart';
import 'package:ollmchs_library/features/books/screens/book_detail_screen.dart';
import 'package:ollmchs_library/features/bookmarks/bloc/bookmarks_bloc.dart';
import 'package:ollmchs_library/features/messaging/bloc/messaging_bloc.dart';
import 'package:ollmchs_library/features/reservations/bloc/reservations_bloc.dart';
import 'package:ollmchs_library/features/reservations/repositories/reservations_repository.dart';

class MockApiClient extends Mock implements ApiClient {}
class MockAuthRepository extends Mock implements AuthRepository {}
class MockBooksRepo extends Mock implements BooksRepository {}
class MockReservationsRepo extends Mock implements ReservationsRepository {}

void main() {
  late MockApiClient mockApi;
  late MockAuthRepository mockAuthRepo;

  setUp(() {
    mockApi = MockApiClient();
    mockAuthRepo = MockAuthRepository();
    when(() => mockAuthRepo.getStoredToken()).thenAnswer((_) async => 'token');
    when(() => mockAuthRepo.getUser()).thenAnswer((_) async => UserModel(
      id: 1, name: 'Test', email: 'test@test.com', roles: ['student'], permissions: [],
    ));
    when(() => mockApi.get(any(), queryParameters: any(named: 'queryParameters')))
        .thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
  });

  Widget buildScreen() {
    return MaterialApp(
      home: MultiRepositoryProvider(
        providers: [RepositoryProvider<ApiClient>.value(value: mockApi)],
        child: MultiBlocProvider(
          providers: [
            BlocProvider<AuthBloc>(create: (_) => AuthBloc(authRepository: mockAuthRepo)),
            BlocProvider<BooksBloc>(create: (_) => BooksBloc(repository: MockBooksRepo())),
            BlocProvider<ReviewsBloc>(create: (_) => ReviewsBloc(api: mockApi)),
            BlocProvider<ReservationsBloc>(create: (_) => ReservationsBloc(repository: MockReservationsRepo())),
            BlocProvider<BookmarksBloc>(create: (_) => BookmarksBloc(api: mockApi)),
            BlocProvider<MessagingBloc>(create: (_) => MessagingBloc(api: mockApi)),
          ],
          child: const BookDetailScreen(bookId: 1),
        ),
      ),
    );
  }

  testWidgets('shows share button in AppBar', (tester) async {
    await tester.pumpWidget(buildScreen());
    await tester.pumpAndSettle();
    expect(find.byIcon(Icons.share), findsOneWidget);
  });
}
