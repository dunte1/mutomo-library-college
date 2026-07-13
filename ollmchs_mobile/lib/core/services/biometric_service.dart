import 'package:local_auth/local_auth.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';

/// Detailed biometric availability status.
enum BiometricStatus {
  success,
  noHardware,
  hwUnavailable,
  noneEnrolled,
  unknown,
}

/// Service wrapper around local_auth that exposes detailed capability info
/// and maps all BiometricManager status codes.
class BiometricService {
  final LocalAuthentication _auth = LocalAuthentication();

  /// Whether the device has any biometric hardware.
  Future<bool> get isAvailable async {
    if (kIsWeb) return false;
    try {
      return await _auth.canCheckBiometrics || await _auth.isDeviceSupported();
    } catch (_) {
      return false;
    }
  }

  /// Get the detailed status of biometric capability.
  Future<BiometricStatus> getStatus() async {
    if (kIsWeb) return BiometricStatus.noHardware;
    try {
      final canCheck = await _auth.canCheckBiometrics;
      if (canCheck) return BiometricStatus.success;

      final supported = await _auth.isDeviceSupported();
      if (supported) return BiometricStatus.success;

      return BiometricStatus.noHardware;
    } on PlatformException catch (e) {
      switch (e.code) {
        case 'Reasons':
          // No hardware or hw unavailable
          if (e.message?.contains('no biometrics') == true) {
            return BiometricStatus.noHardware;
          }
          return BiometricStatus.hwUnavailable;
        default:
          return BiometricStatus.unknown;
      }
    } catch (_) {
      return BiometricStatus.unknown;
    }
  }

  /// Check if the device has enrolled biometrics (fingerprints/face).
  Future<bool> get hasEnrolledBiometrics async {
    if (kIsWeb) return false;
    try {
      final biometrics = await _auth.getAvailableBiometrics();
      return biometrics.isNotEmpty;
    } catch (_) {
      return false;
    }
  }

  /// Available biometric types on this device.
  Future<List<BiometricType>> get availableBiometrics async {
    try {
      return await _auth.getAvailableBiometrics();
    } catch (_) {
      return [];
    }
  }

  /// Perform biometric authentication with device credential fallback.
  ///
  /// Uses `biometricOnly: false` to allow PIN/pattern/password as fallback.
  Future<bool> authenticate({
    String reason = 'Log in to OLLMCHS Library',
  }) async {
    try {
      return await _auth.authenticate(
        localizedReason: reason,
        options: const AuthenticationOptions(
          stickyAuth: true,
          biometricOnly: false,
          useErrorDialogs: true,
        ),
      );
    } catch (_) {
      return false;
    }
  }

  /// Human-readable label for the available biometric type.
  String get biometricLabel {
    return 'Use fingerprint or face ID';
  }

  /// Returns a user-facing explanation of why biometrics are unavailable.
  Future<String> get unavailableReason async {
    final status = await getStatus();
    switch (status) {
      case BiometricStatus.success:
        return '';
      case BiometricStatus.noHardware:
        return 'This device does not have biometric hardware (fingerprint or face sensor).';
      case BiometricStatus.hwUnavailable:
        return 'Biometric hardware is currently unavailable. Try again later.';
      case BiometricStatus.noneEnrolled:
        return 'No biometrics enrolled. Add a fingerprint or face in device settings.';
      case BiometricStatus.unknown:
        return 'Unable to determine biometric availability on this device.';
    }
  }
}
