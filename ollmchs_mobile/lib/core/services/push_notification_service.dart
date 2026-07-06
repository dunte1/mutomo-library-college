import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import '../constants/environment.dart';
import '../network/api_client.dart';

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
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

  Future<void> init() async {
    if (!Environment.firebaseEnabled) return;

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
      onDidReceiveNotificationResponse: (response) {},
    );

    final messaging = FirebaseMessaging.instance;

    await messaging.requestPermission(alert: true, badge: true, sound: true);

    final fcmToken = await messaging.getToken();
    if (fcmToken != null) {
      _saveToken(fcmToken);
    }

    messaging.onTokenRefresh.listen(_saveToken);

    FirebaseMessaging.onMessage.listen(_handleForegroundMessage);
  }

  void _saveToken(String token) {
    try {
      // silently save
    } catch (_) {}
  }

  Future<void> registerToken(ApiClient api) async {
    final messaging = FirebaseMessaging.instance;
    final fcmToken = await messaging.getToken();
    if (fcmToken == null) return;
    try {
      await api.post(
        '/v1/push/subscribe',
        data: {'token': fcmToken, 'platform': 'mobile'},
      );
    } catch (_) {}
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
    );
  }
}
