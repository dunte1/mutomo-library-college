import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../storage/local_storage_service.dart';

class ThemeCubit extends Cubit<ThemeMode> {
  final LocalStorageService _storage;

  ThemeCubit({required LocalStorageService storage})
    : _storage = storage,
      super(ThemeMode.system) {
    _loadTheme();
  }

  Future<void> _loadTheme() async {
    final mode = await _storage.getThemeMode();
    emit(_parseThemeMode(mode));
  }

  Future<void> setThemeMode(String mode) async {
    await _storage.setThemeMode(mode);
    emit(_parseThemeMode(mode));
  }

  ThemeMode _parseThemeMode(String? mode) {
    switch (mode) {
      case 'dark':
        return ThemeMode.dark;
      case 'light':
        return ThemeMode.light;
      default:
        return ThemeMode.system;
    }
  }
}
