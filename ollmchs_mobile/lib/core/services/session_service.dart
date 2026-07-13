import 'dart:async';
import 'package:flutter/material.dart';
import '../constants/app_constants.dart';
import '../storage/local_storage_service.dart';

/// Manages app lifecycle, app lock (5-min background), and session timeout (30-min inactivity).
///
/// This is the single source of truth for all session/security state.
class SessionService extends WidgetsBindingObserver {
  final LocalStorageService _storage;
  final VoidCallback? onLockRequested;
  final VoidCallback? onSessionExpired;

  Timer? _sessionTimer;
  Timer? _lockCheckTimer;
  bool _isLocked = false;

  bool get isLocked => _isLocked;

  SessionService({
    required LocalStorageService storage,
    this.onLockRequested,
    this.onSessionExpired,
  }) : _storage = storage;

  /// Must be called once at app startup. Adds this as a lifecycle observer.
  void initialize() {
    WidgetsBinding.instance.addObserver(this);
    _checkOnStartup();
  }

  /// Check lock/session state on cold start (handles process death).
  Future<void> _checkOnStartup() async {
    if (await _isSessionExpired()) {
      _isLocked = true;
      onSessionExpired?.call();
      return;
    }
    if (await _shouldLock()) {
      _isLocked = true;
      onLockRequested?.call();
      return;
    }
    _touchActivity();
  }

  /// Clean up resources. Call on app dispose.
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _sessionTimer?.cancel();
    _lockCheckTimer?.cancel();
  }

  /// Mark that the user performed an interactive action.
  /// Resets the session inactivity timer.
  void touchActivity() {
    _touchActivity();
  }

  /// Unlock the app (after successful biometric/password authentication).
  void unlock() {
    _isLocked = false;
    _touchActivity();
  }

  /// Set the locked state externally (e.g., if lock is triggered programmatically).
  void setLocked(bool locked) {
    _isLocked = locked;
  }

  /// Record that the app went to background.
  Future<void> _recordBackground() async {
    await _storage.saveLastBackgroundTimestamp(DateTime.now());
  }

  /// Check if app should be locked when returning to foreground.
  Future<bool> _shouldLock() async {
    final lastBg = await _storage.getLastBackgroundTimestamp();
    if (lastBg == null) return false;
    final elapsed = DateTime.now().difference(lastBg);
    return elapsed.inSeconds >= AppConstants.appLockTimeoutSeconds;
  }

  /// Check if the session has expired (30 min inactivity).
  Future<bool> _isSessionExpired() async {
    final lastActivity = await _storage.getLastUserActivity();
    if (lastActivity == null) return true;
    final elapsed = DateTime.now().difference(lastActivity);
    return elapsed.inSeconds >= AppConstants.sessionTimeoutSeconds;
  }

  void _touchActivity() {
    _storage.saveLastUserActivity(DateTime.now());
    _resetSessionTimer();
  }

  void _resetSessionTimer() {
    _sessionTimer?.cancel();
    _sessionTimer = Timer(
      const Duration(seconds: AppConstants.appLockTimeoutSeconds),
      _onSessionInactivityLock,
    );
  }

  Future<void> _onSessionInactivityLock() async {
    _isLocked = true;
    onLockRequested?.call();
  }

  // ---- WidgetsBindingObserver overrides ----

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    switch (state) {
      case AppLifecycleState.inactive:
      case AppLifecycleState.hidden:
        // App is transitioning to background
        _recordBackground();
        _lockCheckTimer?.cancel();
        break;

      case AppLifecycleState.paused:
        // Fully backgrounded
        _recordBackground();
        break;

      case AppLifecycleState.resumed:
        _onResumed();
        break;

      case AppLifecycleState.detached:
        break;
    }
  }

  Future<void> _onResumed() async {
    // Check session expiry first (30 min)
    if (await _isSessionExpired()) {
      _isLocked = true;
      onSessionExpired?.call();
      return;
    }

    // Check app lock (5 min background)
    if (await _shouldLock()) {
      _isLocked = true;
      onLockRequested?.call();
      return;
    }

    // Touch activity to reset inactivity timer
    _touchActivity();
  }
}
