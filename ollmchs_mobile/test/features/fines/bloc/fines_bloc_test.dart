import 'package:flutter_test/flutter_test.dart';
import 'package:bloc_test/bloc_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:dio/dio.dart';
import 'package:ollmchs_library/core/network/api_client.dart';
import 'package:ollmchs_library/features/fines/bloc/fines_bloc.dart';
import 'package:ollmchs_library/features/fines/models/fine_model.dart';

class MockApiClient extends Mock implements ApiClient {}

Response<dynamic> _fakeResponse(Map<String, dynamic> data) {
  return Response<dynamic>(
    data: data,
    statusCode: 200,
    requestOptions: RequestOptions(path: ''),
  );
}

void main() {
  late MockApiClient mockApi;

  setUp(() {
    mockApi = MockApiClient();
    when(() => mockApi.get(any(), queryParameters: any(named: 'queryParameters')))
        .thenAnswer((_) async => _fakeResponse({
              'data': [
                {
                  'id': 1,
                  'amount': 200.0,
                  'reason': 'Overdue',
                  'status': 'pending',
                  'assessed_at': '2026-07-20T10:00:00.000Z',
                },
                {
                  'id': 2,
                  'amount': 100.0,
                  'reason': 'Lost',
                  'status': 'paid',
                  'assessed_at': '2026-07-18T10:00:00.000Z',
                  'paid_at': '2026-07-19T10:00:00.000Z',
                },
              ],
            }));
    when(() => mockApi.post(any(), data: any(named: 'data')))
        .thenAnswer((_) async => _fakeResponse({'data': {}}));
  });

  group('FinesBloc', () {
    blocTest<FinesBloc, FinesState>(
      'emits [FinesLoading, FinesLoaded] when LoadFines succeeds',
      build: () => FinesBloc(api: mockApi),
      act: (bloc) => bloc.add(const LoadFines()),
      expect: () => [
        isA<FinesLoading>(),
        isA<FinesLoaded>().having((s) => s.fines.length, 'fines count', 2),
      ],
    );

    blocTest<FinesBloc, FinesState>(
      'emits [FinesLoading, FinesError] when LoadFines fails',
      build: () {
        when(() => mockApi.get(any(), queryParameters: any(named: 'queryParameters')))
            .thenThrow(Exception('Network error'));
        return FinesBloc(api: mockApi);
      },
      act: (bloc) => bloc.add(const LoadFines()),
      expect: () => [
        isA<FinesLoading>(),
        isA<FinesError>(),
      ],
    );

    blocTest<FinesBloc, FinesState>(
      'PayFine triggers API call and re-dispatches LoadFines',
      build: () => FinesBloc(api: mockApi),
      act: (bloc) => bloc.add(const PayFine(1)),
      verify: (_) {
        verify(() => mockApi.post('/v1/fines/1/pay')).called(1);
      },
    );

    blocTest<FinesBloc, FinesState>(
      'PayFineWithMethod sends payment method and phone',
      build: () {
        when(() => mockApi.post('/v1/fines/1/pay', data: any(named: 'data')))
            .thenAnswer((_) async => _fakeResponse({
                  'data': {
                    'payment_id': 10,
                    'amount': 200.0,
                    'status': 'pending',
                    'paid_at': '2026-07-25T14:30:00.000Z',
                  },
                }));
        return FinesBloc(api: mockApi);
      },
      act: (bloc) => bloc.add(const PayFineWithMethod(
        fineId: 1,
        paymentMethod: 'mpesa',
        phoneNumber: '+254712345678',
      )),
      verify: (_) {
        verify(() => mockApi.post('/v1/fines/1/pay', data: {
          'payment_method': 'mpesa',
          'phone_number': '+254712345678',
        })).called(1);
      },
    );

    blocTest<FinesBloc, FinesState>(
      'LoadFineDetail populates selectedFine',
      build: () {
        when(() => mockApi.get('/v1/fines/1')).thenAnswer((_) async => _fakeResponse({
              'data': {
                'id': 1,
                'amount': 200.0,
                'reason': 'Overdue',
                'status': 'pending',
                'assessed_at': '2026-07-20T10:00:00.000Z',
              },
            }));
        return FinesBloc(api: mockApi);
      },
      seed: () => const FinesLoaded(),
      act: (bloc) => bloc.add(const LoadFineDetail(1)),
      expect: () => [
        isA<FinesLoaded>().having((s) => s.selectedFine?.id, 'selected fine id', 1),
      ],
    );
  });
}
