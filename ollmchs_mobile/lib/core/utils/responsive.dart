import 'package:flutter/material.dart';

enum ScreenBreakpoint { compact, medium, expanded }

extension ResponsiveUtils on BuildContext {
  ScreenBreakpoint get breakpoint {
    final width = MediaQuery.sizeOf(this).width;
    if (width >= 840) return ScreenBreakpoint.expanded;
    if (width >= 600) return ScreenBreakpoint.medium;
    return ScreenBreakpoint.compact;
  }

  bool get isCompact => breakpoint == ScreenBreakpoint.compact;
  bool get isMedium => breakpoint == ScreenBreakpoint.medium;
  bool get isExpanded => breakpoint == ScreenBreakpoint.expanded;

  double responsiveValue({required double compact, double? medium, double? expanded}) {
    switch (breakpoint) {
      case ScreenBreakpoint.expanded:
        return expanded ?? medium ?? compact;
      case ScreenBreakpoint.medium:
        return medium ?? compact;
      case ScreenBreakpoint.compact:
        return compact;
    }
  }

  int responsiveGridColumns() {
    switch (breakpoint) {
      case ScreenBreakpoint.expanded:
        return 4;
      case ScreenBreakpoint.medium:
        return 3;
      case ScreenBreakpoint.compact:
        return 2;
    }
  }

  EdgeInsets get responsivePadding {
    switch (breakpoint) {
      case ScreenBreakpoint.expanded:
        return const EdgeInsets.symmetric(horizontal: 64, vertical: 24);
      case ScreenBreakpoint.medium:
        return const EdgeInsets.symmetric(horizontal: 32, vertical: 16);
      case ScreenBreakpoint.compact:
        return const EdgeInsets.symmetric(horizontal: 16, vertical: 12);
    }
  }

  double get contentMaxWidth {
    switch (breakpoint) {
      case ScreenBreakpoint.expanded:
        return 1200;
      case ScreenBreakpoint.medium:
        return double.infinity;
      case ScreenBreakpoint.compact:
        return double.infinity;
    }
  }

  bool get isWideScreen =>
      breakpoint != ScreenBreakpoint.compact;
}

Widget responsiveBuilder({
  required BuildContext context,
  required Widget compact,
  Widget? medium,
  Widget? expanded,
}) {
  final bp = context.breakpoint;
  if (bp == ScreenBreakpoint.expanded && expanded != null) return expanded;
  if (bp == ScreenBreakpoint.medium && medium != null) return medium;
  return compact;
}

class ResponsiveCenter extends StatelessWidget {
  final Widget child;
  const ResponsiveCenter({super.key, required this.child});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: ConstrainedBox(
        constraints: BoxConstraints(maxWidth: context.contentMaxWidth),
        child: child,
      ),
    );
  }
}

class ResponsiveGridView extends StatelessWidget {
  final SliverGridDelegate Function(int crossAxisCount)? gridDelegate;
  final int itemCount;
  final Widget Function(BuildContext, int) itemBuilder;
  final EdgeInsets? padding;
  final bool shrinkWrap;
  final ScrollPhysics? physics;

  const ResponsiveGridView({
    super.key,
    required this.itemCount,
    required this.itemBuilder,
    this.gridDelegate,
    this.padding,
    this.shrinkWrap = false,
    this.physics,
  });

  @override
  Widget build(BuildContext context) {
    final crossAxisCount = context.responsiveGridColumns();
    final delegate = gridDelegate != null
        ? gridDelegate!(crossAxisCount)
        : SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: crossAxisCount,
            crossAxisSpacing: context.responsiveValue(compact: 8, medium: 12, expanded: 16),
            mainAxisSpacing: context.responsiveValue(compact: 8, medium: 12, expanded: 16),
            childAspectRatio: context.responsiveValue(compact: 0.65, medium: 0.75, expanded: 0.85),
          );

    if (shrinkWrap) {
      return GridView.builder(
        shrinkWrap: true,
        physics: physics ?? const NeverScrollableScrollPhysics(),
        padding: padding ?? context.responsivePadding,
        gridDelegate: delegate,
        itemCount: itemCount,
        itemBuilder: itemBuilder,
      );
    }

    return GridView.builder(
      padding: padding ?? context.responsivePadding,
      gridDelegate: delegate,
      itemCount: itemCount,
      itemBuilder: itemBuilder,
    );
  }
}
