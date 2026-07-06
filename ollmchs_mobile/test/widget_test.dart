import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_bloc.dart';
import 'package:ollmchs_library/features/auth/bloc/auth_state.dart';
import 'package:ollmchs_library/features/auth/screens/splash_screen.dart';

class MockAuthBloc extends Mock implements AuthBloc {}

void main() {
  testWidgets('SplashScreen renders without errors with mocked AuthBloc',
      (WidgetTester tester) async {
    final mockAuthBloc = MockAuthBloc();

    when(() => mockAuthBloc.state).thenReturn(AuthInitial());
    when(() => mockAuthBloc.stream).thenAnswer((_) => const Stream.empty());
    when(() => mockAuthBloc.close()).thenAnswer((_) async {});

    await tester.pumpWidget(
      MaterialApp(
        home: BlocProvider<AuthBloc>.value(
          value: mockAuthBloc,
          child: const SplashScreen(),
        ),
      ),
    );

    await tester.pump();
    await tester.pump(const Duration(milliseconds: 600));

    expect(find.text('OLLMCHS Library'), findsOneWidget);
    expect(find.text('Knowledge at your fingertips'), findsOneWidget);
    expect(find.byType(CircularProgressIndicator), findsOneWidget);
    expect(find.byType(Image), findsOneWidget);
  });
}
