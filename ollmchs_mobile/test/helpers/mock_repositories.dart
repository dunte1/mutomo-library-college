import 'package:mocktail/mocktail.dart';
import 'package:ollmchs_library/features/auth/repositories/auth_repository.dart';
import 'package:ollmchs_library/features/books/repositories/books_repository.dart';
import 'package:ollmchs_library/features/loans/repositories/loans_repository.dart';
import 'package:ollmchs_library/features/reservations/repositories/reservations_repository.dart';

class MockAuthRepository extends Mock implements AuthRepository {}

class MockBooksRepository extends Mock implements BooksRepository {}

class MockLoansRepository extends Mock implements LoansRepository {}

class MockReservationsRepository extends Mock implements ReservationsRepository {}
