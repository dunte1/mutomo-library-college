import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:ollmchs_library/core/widgets/skeleton.dart';
import 'package:ollmchs_library/features/library_card/bloc/library_card_bloc.dart';
import 'package:ollmchs_library/features/library_card/models/library_card_model.dart';
import 'package:ollmchs_library/features/library_card/screens/library_card_screen.dart';

class MockLibraryCardBloc extends Mock implements LibraryCardBloc {}

void main() {
  late MockLibraryCardBloc mockBloc;
  late LibraryCardModel activeCard;

  setUp(() {
    mockBloc = MockLibraryCardBloc();
    activeCard = LibraryCardModel(
      id: 1,
      cardNumber: 'CRD-000001',
      status: 'active',
      issuedAt: DateTime(2026, 1, 1),
      expiresAt: DateTime(2027, 1, 1),
      barcode: 'BAR-000001',
      qrCodeSvg: '<svg><rect/></svg>',
      memberName: 'Test User',
      memberId: 'OLLMCHS-2026-000001',
      memberStatus: 'active',
      membershipType: 'student',
      memberEmail: 'test@test.com',
      memberPhone: '+254712345678',
      department: 'Computer Science',
    );
  });

  Widget buildWidget(LibraryCardState state) {
    when(() => mockBloc.state).thenReturn(state);
    when(() => mockBloc.stream).thenAnswer((_) => const Stream.empty());
    when(() => mockBloc.close()).thenAnswer((_) async {});

    return MaterialApp(
      home: BlocProvider<LibraryCardBloc>.value(
        value: mockBloc,
        child: const LibraryCardScreen(),
      ),
    );
  }

  testWidgets('shows loading skeleton when state is LibraryCardLoading',
      (WidgetTester tester) async {
    await tester.pumpWidget(buildWidget(LibraryCardLoading()));
    await tester.pump();

    expect(find.byType(Skeleton), findsWidgets);
    expect(find.text('Library Card'), findsOneWidget);
  });

  testWidgets('shows error state with retry button',
      (WidgetTester tester) async {
    await tester.pumpWidget(buildWidget(
      LibraryCardError('No library card found.'),
    ));
    await tester.pump();

    expect(find.text('Could not load library card'), findsOneWidget);
    expect(find.text('No library card found.'), findsOneWidget);
    expect(find.text('Retry'), findsOneWidget);
    expect(find.byType(FilledButton), findsOneWidget);
  });

  testWidgets('shows loaded state with card details',
      (WidgetTester tester) async {
    await tester.pumpWidget(buildWidget(
      LibraryCardLoaded(card: activeCard),
    ));
    await tester.pump();

    expect(find.text('Library Card'), findsOneWidget);
    expect(find.text('Test User'), findsOneWidget);
    expect(find.text('OLLMCHS Library'), findsOneWidget);
    expect(find.text('CRD-000001'), findsOneWidget);
    expect(find.text('Student'), findsOneWidget);
    expect(find.text('test@test.com'), findsOneWidget);
    expect(find.text('+254712345678'), findsOneWidget);
    expect(find.text('Active'), findsWidgets);
    expect(find.text('Computer Science'), findsOneWidget);
    expect(find.byIcon(Icons.download), findsOneWidget);
    expect(find.byIcon(Icons.share), findsOneWidget);
  });

  testWidgets('shows expired banner when card is expired',
      (WidgetTester tester) async {
    final expiredCard = LibraryCardModel(
      id: 2,
      cardNumber: 'CRD-000002',
      status: 'expired',
      issuedAt: DateTime(2020, 1, 1),
      expiresAt: DateTime(2021, 1, 1),
      memberName: 'Expired User',
      memberId: 'OLLMCHS-2026-000002',
      memberStatus: 'active',
    );

    await tester.pumpWidget(buildWidget(
      LibraryCardLoaded(card: expiredCard),
    ));
    await tester.pump();

    expect(find.text('Membership Expired'), findsOneWidget);
  });

  testWidgets('shows suspended banner when member is suspended',
      (WidgetTester tester) async {
    final suspendedCard = LibraryCardModel(
      id: 3,
      cardNumber: 'CRD-000003',
      status: 'active',
      issuedAt: DateTime(2026, 1, 1),
      memberName: 'Suspended User',
      memberId: 'OLLMCHS-2026-000003',
      memberStatus: 'suspended',
    );

    await tester.pumpWidget(buildWidget(
      LibraryCardLoaded(card: suspendedCard),
    ));
    await tester.pump();

    expect(find.text('Membership Suspended'), findsOneWidget);
  });

  testWidgets('shows inactive banner when member is inactive',
      (WidgetTester tester) async {
    final inactiveCard = LibraryCardModel(
      id: 4,
      cardNumber: 'CRD-000004',
      status: 'active',
      issuedAt: DateTime(2026, 1, 1),
      memberName: 'Inactive User',
      memberId: 'OLLMCHS-2026-000004',
      memberStatus: 'inactive',
    );

    await tester.pumpWidget(buildWidget(
      LibraryCardLoaded(card: inactiveCard),
    ));
    await tester.pump();

    expect(find.text('Membership Inactive'), findsOneWidget);
  });

  testWidgets('no banner when card and member are both active',
      (WidgetTester tester) async {
    await tester.pumpWidget(buildWidget(
      LibraryCardLoaded(card: activeCard),
    ));
    await tester.pump();

    expect(find.text('Membership Expired'), findsNothing);
    expect(find.text('Membership Suspended'), findsNothing);
    expect(find.text('Membership Inactive'), findsNothing);
  });
}
