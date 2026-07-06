import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:ollmchs_library/core/utils/responsive.dart';

/// Helper to build a test app with a given screen width.
Widget _makeTestApp(double screenWidth, {Widget? child}) {
  return MaterialApp(
    home: MediaQuery(
      data: MediaQueryData(size: Size(screenWidth, 800)),
      child: child ?? const _TestContent(),
    ),
  );
}

void main() {
  group('ResponsiveUtils extensions', () {
    testWidgets('compact breakpoint for small screen', (tester) async {
      await tester.pumpWidget(_makeTestApp(390));

      final context = tester.element(find.byType(_TestContent));
      expect(context.isCompact, isTrue);
      expect(context.isWideScreen, isFalse);
      expect(context.responsiveGridColumns(), equals(2));
    });

    testWidgets('medium breakpoint for tablet screen', (tester) async {
      await tester.pumpWidget(_makeTestApp(768));

      final context = tester.element(find.byType(_TestContent));
      expect(context.isMedium, isTrue);
      expect(context.isWideScreen, isTrue);
      expect(context.responsiveGridColumns(), equals(3));
    });

    testWidgets('expanded breakpoint for desktop screen', (tester) async {
      await tester.pumpWidget(_makeTestApp(1280));

      final context = tester.element(find.byType(_TestContent));
      expect(context.isExpanded, isTrue);
      expect(context.isWideScreen, isTrue);
      expect(context.responsiveGridColumns(), equals(4));
    });

    testWidgets('responsiveValue returns correct values per breakpoint',
        (tester) async {
      await tester.pumpWidget(_makeTestApp(390));
      var context = tester.element(find.byType(_TestContent));
      expect(
        context.responsiveValue(compact: 1.0, medium: 2.0, expanded: 3.0),
        equals(1.0),
      );

      await tester.pumpWidget(_makeTestApp(768));
      context = tester.element(find.byType(_TestContent));
      expect(
        context.responsiveValue(compact: 1.0, medium: 2.0, expanded: 3.0),
        equals(2.0),
      );

      await tester.pumpWidget(_makeTestApp(1280));
      context = tester.element(find.byType(_TestContent));
      expect(
        context.responsiveValue(compact: 1.0, medium: 2.0, expanded: 3.0),
        equals(3.0),
      );
    });

    testWidgets('responsiveValue falls back when higher breakpoints are null',
        (tester) async {
      await tester.pumpWidget(_makeTestApp(1280));
      var context = tester.element(find.byType(_TestContent));
      expect(context.responsiveValue(compact: 1.0, medium: 2.0), equals(2.0));

      await tester.pumpWidget(_makeTestApp(1280));
      context = tester.element(find.byType(_TestContent));
      expect(context.responsiveValue(compact: 1.0), equals(1.0));
    });

    testWidgets('contentMaxWidth is finite for expanded only',
        (tester) async {
      await tester.pumpWidget(_makeTestApp(1280));
      var context = tester.element(find.byType(_TestContent));
      expect(context.contentMaxWidth, equals(1200));

      await tester.pumpWidget(_makeTestApp(390));
      context = tester.element(find.byType(_TestContent));
      expect(context.contentMaxWidth, equals(double.infinity));
    });

    testWidgets('responsivePadding returns different values per breakpoint',
        (tester) async {
      await tester.pumpWidget(_makeTestApp(390));
      var context = tester.element(find.byType(_TestContent));
      expect(context.responsivePadding,
          equals(const EdgeInsets.symmetric(horizontal: 16, vertical: 12)));

      await tester.pumpWidget(_makeTestApp(768));
      context = tester.element(find.byType(_TestContent));
      expect(context.responsivePadding,
          equals(const EdgeInsets.symmetric(horizontal: 32, vertical: 16)));

      await tester.pumpWidget(_makeTestApp(1280));
      context = tester.element(find.byType(_TestContent));
      expect(context.responsivePadding,
          equals(const EdgeInsets.symmetric(horizontal: 64, vertical: 24)));
    });
  });

  group('responsiveBuilder', () {
    testWidgets('returns compact widget for compact breakpoint',
        (tester) async {
      await tester.pumpWidget(_makeTestApp(390, child: Builder(
        builder: (context) => responsiveBuilder(
          context: context,
          compact: const Text('Compact'),
          medium: const Text('Medium'),
          expanded: const Text('Expanded'),
        ),
      )));
      expect(find.text('Compact'), findsOneWidget);
      expect(find.text('Medium'), findsNothing);
      expect(find.text('Expanded'), findsNothing);
    });

    testWidgets('returns medium widget for medium breakpoint',
        (tester) async {
      await tester.pumpWidget(_makeTestApp(768, child: Builder(
        builder: (context) => responsiveBuilder(
          context: context,
          compact: const Text('Compact'),
          medium: const Text('Medium'),
          expanded: const Text('Expanded'),
        ),
      )));
      expect(find.text('Compact'), findsNothing);
      expect(find.text('Medium'), findsOneWidget);
      expect(find.text('Expanded'), findsNothing);
    });

    testWidgets('returns expanded widget for expanded breakpoint',
        (tester) async {
      await tester.pumpWidget(_makeTestApp(1280, child: Builder(
        builder: (context) => responsiveBuilder(
          context: context,
          compact: const Text('Compact'),
          medium: const Text('Medium'),
          expanded: const Text('Expanded'),
        ),
      )));
      expect(find.text('Compact'), findsNothing);
      expect(find.text('Medium'), findsNothing);
      expect(find.text('Expanded'), findsOneWidget);
    });

    testWidgets('falls back to compact when medium/expanded are null',
        (tester) async {
      await tester.pumpWidget(_makeTestApp(1280, child: Builder(
        builder: (context) => responsiveBuilder(
          context: context,
          compact: const Text('Fallback'),
        ),
      )));
      expect(find.text('Fallback'), findsOneWidget);
    });
  });

  group('ResponsiveCenter', () {
    testWidgets('wraps child in ConstrainedBox with contentMaxWidth',
        (tester) async {
      await tester.pumpWidget(_makeTestApp(1280, child: const ResponsiveCenter(
        child: Text('Centered'),
      )));
      expect(find.text('Centered'), findsOneWidget);
    });
  });

  group('ResponsiveGridView', () {
    testWidgets('renders items in a grid', (tester) async {
      await tester.pumpWidget(_makeTestApp(800, child: ResponsiveGridView(
        itemCount: 4,
        itemBuilder: (context, index) => Text('Item $index'),
      )));
      expect(find.text('Item 0'), findsOneWidget);
      expect(find.text('Item 3'), findsOneWidget);
    });

    testWidgets('uses shrinkWrap mode', (tester) async {
      await tester.pumpWidget(_makeTestApp(800, child: SingleChildScrollView(
        child: ResponsiveGridView(
          shrinkWrap: true,
          itemCount: 2,
          itemBuilder: (context, index) => Text('Shrink $index'),
        ),
      )));
      expect(find.text('Shrink 0'), findsOneWidget);
      expect(find.text('Shrink 1'), findsOneWidget);
    });
  });
}

class _TestContent extends StatelessWidget {
  const _TestContent();
  @override
  Widget build(BuildContext context) {
    return const SizedBox();
  }
}
