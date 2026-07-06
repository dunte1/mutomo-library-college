import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:go_router/go_router.dart';

class AppKeyboardShortcuts extends StatefulWidget {
  final Widget child;
  const AppKeyboardShortcuts({super.key, required this.child});

  @override
  State<AppKeyboardShortcuts> createState() => _AppKeyboardShortcutsState();
}

class _AppKeyboardShortcutsState extends State<AppKeyboardShortcuts> {
  @override
  void initState() {
    super.initState();
    HardwareKeyboard.instance.addHandler(_onKeyEvent);
  }

  @override
  void dispose() {
    HardwareKeyboard.instance.removeHandler(_onKeyEvent);
    super.dispose();
  }

  bool _onKeyEvent(KeyEvent event) {
    if (event is! KeyDownEvent) return false;

    if (event.logicalKey == LogicalKeyboardKey.keyD &&
        HardwareKeyboard.instance.isControlPressed) {
      context.go('/dashboard');
      return true;
    }
    if (event.logicalKey == LogicalKeyboardKey.keyB &&
        HardwareKeyboard.instance.isControlPressed) {
      context.go('/books');
      return true;
    }
    if (event.logicalKey == LogicalKeyboardKey.keyL &&
        HardwareKeyboard.instance.isControlPressed) {
      context.go('/loans');
      return true;
    }
    if (event.logicalKey == LogicalKeyboardKey.keyP &&
        HardwareKeyboard.instance.isControlPressed) {
      context.go('/profile');
      return true;
    }
    if (event.logicalKey == LogicalKeyboardKey.keyF &&
        HardwareKeyboard.instance.isControlPressed &&
        HardwareKeyboard.instance.isShiftPressed) {
      context.go('/books/search');
      return true;
    }
    if (event.logicalKey == LogicalKeyboardKey.escape) {
      if (context.canPop()) {
        context.pop();
        return true;
      }
    }
    return false;
  }

  @override
  Widget build(BuildContext context) => widget.child;
}
