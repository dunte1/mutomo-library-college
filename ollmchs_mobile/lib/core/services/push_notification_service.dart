import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import '../constants/environment.dart';
import '../network/api_client.dart';

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  // Process background message data here if needed
}

class PushNotificationService {
  static final PushNotificationService _instance = PushNotificationService._();
  factory PushNotificationService() => _instance;
  PushNotificationService._();

  final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();
  final AndroidNotificationChannel _channel = const AndroidNotificationChannel(
    'ollmchs_library_channel',
    'Library Notifications',
    description: 'Notifications from OLLMCHS Library',
    importance: Importance.high,
  );

  ApiClient? _api;
  String? _currentToken;

  /// Callback for notification tap — set by the app to navigate.
  void Function(String? payload)? onNotificationTap;

  Future<void> init({ApiClient? api}) async {
    if (!Environment.firebaseEnabled) return;

    _api = api;

    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

    await _localNotifications
        .resolvePlatformSpecificImplementation<
          AndroidFlutterLocalNotificationsPlugin
        >()
        ?.createNotificationChannel(_channel);

    const androidSettings = AndroidInitializationSettings(
      '@mipmap/ic_launcher',
    );
    const iosSettings = DarwinInitializationSettings();
    await _localNotifications.initialize(
      const InitializationSettings(android: androidSettings, iOS: iosSettings),
      onDidReceiveNotificationResponse: (response) {
        onNotificationTap?.call(response.payload);
      },
    );

    final messaging = FirebaseMessaging.instance;

    await messaging.requestPermission(alert: true, badge: true, sound: true);

    final fcmToken = await messaging.getToken();
    if (fcmToken != null) {
      _currentToken = fcmToken;
      _registerTokenWithServer(fcmToken);
    }

    messaging.onTokenRefresh.listen((token) {
      _currentToken = token;
      _registerTokenWithServer(token);
    });

    FirebaseMessaging.onMessage.listen(_handleForegroundMessage);

    // Handle notification tap when app is opened from background
    FirebaseMessaging.onMessageOpenedApp.listen((message) {
      onNotificationTap?.call(message.data['route']);
    });

    // Handle notification tap when app was terminated
    final initialMessage = await messaging.getInitialMessage();
    if (initialMessage != null) {
      // Delay to ensure the app is fully initialized
      Future.delayed(const Duration(seconds: 1), () {
        onNotificationTap?.call(initialMessage.data['route']);
      });
    }
  }

  /// Re-register the current FCM token with the server using an authenticated API.
  Future<void> registerWithServer(ApiClient api) async {
    _api = api;
    if (_currentToken != null) {
      _registerTokenWithServer(_currentToken!);
    }
  }

  void _registerTokenWithServer(String token) {
    if (_api == null) return;
    try {
      _api!.post(
        '/v1/push/subscribe',
        data: {'token': token, 'platform': 'mobile'},
      );
    } catch (_) {}
  }

  /// Delete FCM token from server on logout.
  Future<void> unregisterToken(ApiClient api) async {
    if (_currentToken == null) return;
    try {
      await api.post(
        '/v1/push/unsubscribe',
        data: {'token': _currentToken},
      );
    } catch (_) {}
    _currentToken = null;
  }

  Future<void> _handleForegroundMessage(RemoteMessage message) async {
    final notification = message.notification;
    if (notification == null) return;

    await _localNotifications.show(
      notification.hashCode,
      notification.title,
      notification.body,
      NotificationDetails(
        android: AndroidNotificationDetails(
          _channel.id,
          _channel.name,
          channelDescription: _channel.description,
        ),
      ),
      payload: message.data['route'],
    );
  }
}
