import 'dart:async';
import 'package:connectivity_plus/connectivity_plus.dart';

class ConnectivityService {
  static final ConnectivityService _instance = ConnectivityService._();
  factory ConnectivityService() => _instance;
  ConnectivityService._();

  final Connectivity _connectivity = Connectivity();
  final StreamController<bool> _controller = StreamController<bool>.broadcast();
  Stream<bool> get isConnected => _controller.stream;
  bool _isConnected = true;
  bool get currentStatus => _isConnected;

  StreamSubscription<List<ConnectivityResult>>? _subscription;

  Future<void> init() async {
    // Check initial status
    final results = await _connectivity.checkConnectivity();
    _isConnected = results.any((r) => r != ConnectivityResult.none);
    _controller.add(_isConnected);

    // Listen for changes
    _subscription = _connectivity.onConnectivityChanged.listen((results) {
      final connected = results.any((r) => r != ConnectivityResult.none);
      if (connected != _isConnected) {
        _isConnected = connected;
        _controller.add(connected);
      }
    });
  }

  void dispose() {
    _subscription?.cancel();
    _controller.close();
  }
}
