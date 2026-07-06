import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/storage/local_storage_service.dart';

class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  final _pageController = PageController();
  final _storage = LocalStorageService();
  int _currentPage = 0;

  final _pages = const [
    _OnboardingPage(
      icon: Icons.menu_book_rounded,
      title: 'Browse the Catalog',
      description:
          'Explore thousands of books, journals, and digital resources available in the OLLMCHS library collection.',
      color: Color(0xFF1E4FA3),
    ),
    _OnboardingPage(
      icon: Icons.swap_horiz_rounded,
      title: 'Borrow & Return Easily',
      description:
          'Check out books, renew loans, and manage returns right from your phone — no queues needed.',
      color: Color(0xFF3B82F6),
    ),
    _OnboardingPage(
      icon: Icons.auto_stories_rounded,
      title: 'Digital Library',
      description:
          'Access e-books, research papers, lecture notes, and journals anytime, anywhere.',
      color: Color(0xFF10B981),
    ),
    _OnboardingPage(
      icon: Icons.notifications_active_rounded,
      title: 'Stay Notified',
      description:
          'Get reminders for due dates, overdue alerts, library announcements, and new arrivals.',
      color: Color(0xFFF59E0B),
    ),
  ];

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  void _onNext() async {
    if (_currentPage < _pages.length - 1) {
      _pageController.nextPage(
        duration: const Duration(milliseconds: 400),
        curve: Curves.easeInOut,
      );
    } else {
      final router = GoRouter.of(context);
      await _storage.setOnboardingSeen(true);
      if (!mounted) return;
      router.goNamed('login');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: PageView.builder(
                controller: _pageController,
                onPageChanged: (i) => setState(() => _currentPage = i),
                itemCount: _pages.length,
                itemBuilder: (_, i) => _pages[i],
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  TextButton(
                    onPressed: () async {
                      final router = GoRouter.of(context);
                      await _storage.setOnboardingSeen(true);
                      if (!mounted) return;
                      router.goNamed('login');
                    },
                    child: Text(
                      'Skip',
                      style: TextStyle(
                        color: Theme.of(context).colorScheme.onSurfaceVariant,
                      ),
                    ),
                  ),
                  Row(
                    children: List.generate(
                      _pages.length,
                      (i) => AnimatedContainer(
                        duration: const Duration(milliseconds: 300),
                        margin: const EdgeInsets.symmetric(horizontal: 4),
                        width: _currentPage == i ? 24 : 8,
                        height: 8,
                        decoration: BoxDecoration(
                          color: _currentPage == i
                              ? Theme.of(context).colorScheme.primary
                              : Theme.of(
                                  context,
                                ).colorScheme.primary.withValues(alpha: 0.3),
                          borderRadius: BorderRadius.circular(4),
                        ),
                      ),
                    ),
                  ),
                  FilledButton(
                    onPressed: _onNext,
                    child: Text(
                      _currentPage == _pages.length - 1
                          ? 'Get Started'
                          : 'Next',
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _OnboardingPage extends StatelessWidget {
  final IconData icon;
  final String title;
  final String description;
  final Color color;

  const _OnboardingPage({
    required this.icon,
    required this.title,
    required this.description,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 40),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(28),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(28),
            ),
            child: Icon(icon, size: 72, color: color),
          ),
          const SizedBox(height: 40),
          Text(
            title,
            textAlign: TextAlign.center,
            style: Theme.of(
              context,
            ).textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 16),
          Text(
            description,
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.bodyLarge?.copyWith(
              color: Theme.of(context).colorScheme.onSurfaceVariant,
            ),
          ),
        ],
      ),
    );
  }
}
