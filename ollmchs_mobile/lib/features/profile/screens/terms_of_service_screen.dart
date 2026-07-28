import 'package:flutter/material.dart';

class TermsOfServiceScreen extends StatelessWidget {
  const TermsOfServiceScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Terms of Service')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(
            'Terms of Service',
            style: theme.textTheme.headlineSmall?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Last updated: January 2026',
            style: theme.textTheme.bodySmall?.copyWith(
              color: theme.colorScheme.onSurfaceVariant,
            ),
          ),
          const SizedBox(height: 24),
          _Section(
            title: 'Acceptance of Terms',
            body:
                'By accessing and using the OLLMCHS Library app, you agree to be bound by '
                'these Terms of Service. If you do not agree to these terms, please do not '
                'use the application.',
          ),
          _Section(
            title: 'Library Account',
            body:
                'You are responsible for maintaining the confidentiality of your account '
                'credentials. You must provide accurate and complete information when '
                'registering. Your library account is for your personal use only and should '
                'not be shared with others.',
          ),
          _Section(
            title: 'Borrowing Policies',
            body:
                'All book loans are subject to library borrowing policies. You are responsible '
                'for returning borrowed materials by the due date. Late returns may result in '
                'fines. Lost or damaged materials must be paid for at the replacement cost.',
          ),
          _Section(
            title: 'Digital Content',
            body:
                'Digital resources provided through the app are for educational purposes only. '
                'You may not reproduce, distribute, or commercially exploit any digital content '
                'without explicit permission from the library.',
          ),
          _Section(
            title: 'User Conduct',
            body:
                'You agree to use the app in a manner that is respectful and lawful. Harassment, '
                'spam, or any form of abuse through the messaging system is strictly prohibited '
                'and may result in account suspension.',
          ),
          _Section(
            title: 'Limitation of Liability',
            body:
                'The library strives to provide accurate information but cannot guarantee the '
                'completeness or accuracy of all data. The library is not liable for any '
                'damages arising from the use of this application.',
          ),
          _Section(
            title: 'Changes to Terms',
            body:
                'We reserve the right to modify these terms at any time. Continued use of the '
                'app after changes constitutes acceptance of the updated terms.',
          ),
          _Section(
            title: 'Contact',
            body:
                'For questions about these Terms of Service, contact library@ollmchs.ac.ke.',
          ),
          const SizedBox(height: 32),
        ],
      ),
    );
  }
}

class _Section extends StatelessWidget {
  final String title;
  final String body;
  const _Section({required this.title, required this.body});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
          ),
          const SizedBox(height: 8),
          Text(body, style: const TextStyle(height: 1.5)),
        ],
      ),
    );
  }
}
