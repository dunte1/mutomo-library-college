import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';

/// Controls FLAG_SECURE on Android to prevent screenshots and
/// recent-apps previews of sensitive screens.
///
/// On iOS/web/desktop, this is a no-op (iOS has similar built-in protection
/// via `secureField` in some contexts, but no direct API equivalent).
class FlagSecureService {
  static const _channel = MethodChannel('com.ollmchs/flag_secure');
  static bool _isEnabled = false;

  /// Enable FLAG_SECURE — blocks screenshots and recent-apps previews.
  static Future<void> enable() async {
    if (_isEnabled) return;
    if (kIsWeb) return;
    try {
      await _channel.invokeMethod('setFlagSecure', {'enabled': true});
      _isEnabled = true;
    } catch (_) {}
  }

  /// Disable FLAG_SECURE — allows screenshots and previews again.
  static Future<void> disable() async {
    if (!_isEnabled) return;
    if (kIsWeb) return;
    try {
      await _channel.invokeMethod('setFlagSecure', {'enabled': false});
      _isEnabled = false;
    } catch (_) {}
  }

  /// Toggle FLAG_SECURE on/off.
  static Future<void> toggle() async {
    if (_isEnabled) {
      await disable();
    } else {
      await enable();
    }
  }

  /// Whether FLAG_SECURE is currently active.
  static bool get isEnabled => _isEnabled;
}
