import 'package:flutter/foundation.dart';

/// Environment-aware configuration for the OLLMCHS Library mobile app.
///
/// Automatically detects the environment:
/// - Web debug → localhost:8000
/// - Web release → production URL
/// - Native debug → localhost:8000 (Android emulator uses 10.0.2.2)
/// - Native release → production URL
class Environment {
  Environment._();

  /// Whether the app is running in debug mode.
  static bool get isDebug => kDebugMode;

  /// Whether the app is running on the web.
  static bool get isWeb => kIsWeb;

  /// Whether running on Android (important for localhost access).
  static bool get isAndroid =>
      !kIsWeb && defaultTargetPlatform == TargetPlatform.android;

  /// The environment name.
  static String get name {
    if (kReleaseMode) return 'production';
    if (kProfileMode) return 'staging';
    return 'development';
  }

  /// API base URL.
  ///
  /// Can be overridden at build time via:
  ///   --dart-define=API_BASE_URL=http://YOUR_LAN_IP:8000/api
  static String get apiBaseUrl {
    const definedUrl = String.fromEnvironment('API_BASE_URL');
    if (definedUrl.isNotEmpty) return definedUrl;

    if (kReleaseMode) {
      return 'https://collegelibrary.duncowebsolutions.co.ke/api';
    }

    // Development: use localhost
    if (isAndroid) {
      // Android emulator uses 10.0.2.2 to reach host machine
      return 'http://10.0.2.2:8000/api';
    }

    return 'http://localhost:8000/api';
  }

  /// WebSocket / Pusher URL (for real-time features).
  static String get wsUrl {
    if (kReleaseMode) return 'https://collegelibrary.duncowebsolutions.co.ke';
    return 'http://localhost:8000';
  }

  /// Whether Firebase should initialize.
  /// Firebase desktop support is limited — only enable on mobile platforms.
  static bool get firebaseEnabled {
    if (isWeb) return false;
    if (defaultTargetPlatform == TargetPlatform.android ||
        defaultTargetPlatform == TargetPlatform.iOS) {
      return true;
    }
    return false;
  }

  /// Whether to use secure storage or local storage fallback.
  static bool get useSecureStorage => !isWeb;

  /// App display name.
  static String get appName => 'OLLMCHS Library';

  /// API request timeout.
  static Duration get requestTimeout => const Duration(seconds: 30);
}
