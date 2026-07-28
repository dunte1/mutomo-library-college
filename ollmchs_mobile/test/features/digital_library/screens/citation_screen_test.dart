import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mocktail/mocktail.dart';
import 'package:dio/dio.dart';
import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/features/digital_library/bloc/digital_library_bloc.dart';
import 'package:ollmchs_library/features/digital_library/screens/citation_screen.dart';

class MockApiClient extends Mock implements ApiClient {}

void main() {
  late MockApiClient mockApi;

  setUp(() {
    mockApi = MockApiClient();
    when(() => mockApi.get(any(), queryParameters: any(named: 'queryParameters')))
        .thenAnswer((_) async => Response(data: {'data': []}, statusCode: 200, requestOptions: RequestOptions(path: '')));
    when(() => mockApi.post(any(), data: any(named: 'data')))
        .thenAnswer((_) async => Response(data: {'data': {}}, statusCode: 200, requestOptions: RequestOptions(path: '')));
  });

  Widget buildScreen(DigitalLibraryState state) {
    return MaterialApp(
      home: BlocProvider<DigitalLibraryBloc>(
        create: (_) => DigitalLibraryBloc(api: mockApi)..emit(state),
        child: const CitationScreen(assetId: 1, assetTitle: 'Test Book'),
      ),
    );
  }

  group('CitationScreen', () {
    testWidgets('shows all 6 style chips', (tester) async {
      await tester.pumpWidget(buildScreen(const DigitalLibraryLoaded()));
      await tester.pump();

      expect(find.text('APA'), findsOneWidget);
      expect(find.text('MLA'), findsOneWidget);
      expect(find.text('Chicago'), findsOneWidget);
      expect(find.text('Harvard'), findsOneWidget);
      expect(find.text('Vancouver'), findsOneWidget);
      expect(find.text('IEEE'), findsOneWidget);
    });

    testWidgets('shows generate button', (tester) async {
      await tester.pumpWidget(buildScreen(const DigitalLibraryLoaded()));
      await tester.pump();

      expect(find.text('Generate Citation'), findsOneWidget);
    });

    testWidgets('shows empty state when no citations', (tester) async {
      await tester.pumpWidget(buildScreen(const DigitalLibraryLoaded()));
      await tester.pump();

      expect(find.text('No citations generated yet'), findsOneWidget);
    });

    testWidgets('shows asset title', (tester) async {
      await tester.pumpWidget(buildScreen(const DigitalLibraryLoaded()));
      await tester.pump();

      expect(find.text('Test Book'), findsOneWidget);
    });
  });
}
