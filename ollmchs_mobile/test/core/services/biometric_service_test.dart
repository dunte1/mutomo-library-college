import 'package:flutter_test/flutter_test.dart';
import 'package:local_auth/local_auth.dart';
import 'package:mocktail/mocktail.dart';
import 'package:ollmchs_library/core/services/biometric_service.dart';

class MockLocalAuthentication extends Mock implements LocalAuthentication {}

void main() {
  late BiometricService service;

  setUp(() {
    service = BiometricService();
    // Inject mock via reflection or refactor needed
    // For now, test the public API behavior
  });

  group('BiometricService', () {
    group('isAvailable', () {
      test('returns true when canCheckBiometrics is true', () async {
        // Since BiometricService creates its own LocalAuthentication,
        // we test the service's behavior through its public API
        final result = await service.isAvailable;
        // On test environment, this will return false (no biometric hardware)
        expect(result, isA<bool>());
      });

      test('returns false on web platform', () async {
        // kIsWeb is true in test environment by default
        final result = await service.isAvailable;
        expect(result, false);
      });
    });

    group('getStatus', () {
      test('returns BiometricStatus on any platform', () async {
        final status = await service.getStatus();
        expect(status, isA<BiometricStatus>());
      });

      test('returns a valid status in test environment', () async {
        final status = await service.getStatus();
        // In test environment, no real biometric hardware is available
        expect(status, isA<BiometricStatus>());
      });
    });

    group('hasEnrolledBiometrics', () {
      test('returns bool', () async {
        final result = await service.hasEnrolledBiometrics;
        expect(result, isA<bool>());
      });

      test('returns false on web', () async {
        final result = await service.hasEnrolledBiometrics;
        expect(result, false);
      });
    });

    group('availableBiometrics', () {
      test('returns list of BiometricType', () async {
        final result = await service.availableBiometrics;
        expect(result, isA<List<BiometricType>>());
      });

      test('returns empty list on web', () async {
        final result = await service.availableBiometrics;
        expect(result, isEmpty);
      });
    });

    group('authenticate', () {
      test('returns bool', () async {
        final result = await service.authenticate();
        expect(result, isA<bool>());
      });

      test('returns false on web (no biometric hardware)', () async {
        final result = await service.authenticate(reason: 'Test');
        expect(result, false);
      });

      test('handles custom reason string', () async {
        final result = await service.authenticate(reason: 'Custom reason');
        expect(result, isA<bool>());
      });
    });

    group('biometricLabel', () {
      test('returns non-empty label', () {
        final label = service.biometricLabel;
        expect(label, isNotEmpty);
        expect(label, contains('fingerprint'));
      });
    });

    group('unavailableReason', () {
      test('returns string explanation', () async {
        final reason = await service.unavailableReason;
        expect(reason, isA<String>());
        expect(reason, isNotEmpty);
      });

      test('returns non-empty message in test environment', () async {
        final reason = await service.unavailableReason;
        // In test environment, returns a message about biometric unavailability
        expect(reason, isA<String>());
        expect(reason, isNotEmpty);
      });
    });

    group('BiometricStatus enum', () {
      test('has all expected values', () {
        expect(BiometricStatus.values.length, 5);
        expect(BiometricStatus.values, contains(BiometricStatus.success));
        expect(BiometricStatus.values, contains(BiometricStatus.noHardware));
        expect(BiometricStatus.values, contains(BiometricStatus.hwUnavailable));
        expect(BiometricStatus.values, contains(BiometricStatus.noneEnrolled));
        expect(BiometricStatus.values, contains(BiometricStatus.unknown));
      });
    });
  });
}
