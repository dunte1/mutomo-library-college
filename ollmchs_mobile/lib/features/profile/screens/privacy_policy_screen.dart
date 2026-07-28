import 'package:flutter/material.dart';

class PrivacyPolicyScreen extends StatelessWidget {
  const PrivacyPolicyScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Privacy Policy')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(
            'Privacy Policy',
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
            title: 'Information We Collect',
            body:
                'We collect information you provide directly, including your name, email address, '
                'student ID, and program of study. We also collect usage data such as books '
                'borrowed, reading history, and app interactions to improve our services.',
          ),
          _Section(
            title: 'How We Use Your Information',
            body:
                'Your information is used to manage your library account, process book loans '
                'and reservations, send notifications about due dates and fines, and improve '
                'the library services. We do not sell or share your personal information with '
                'third parties for marketing purposes.',
          ),
          _Section(
            title: 'Data Security',
            body:
                'We implement appropriate technical and organizational measures to protect your '
                'personal information against unauthorized access, alteration, disclosure, or '
                'destruction. All data is transmitted using secure encrypted connections.',
          ),
          _Section(
            title: 'Data Retention',
            body:
                'We retain your personal information for as long as your account is active or '
                'as needed to provide you services. Borrowing history may be retained for '
                'institutional reporting purposes as required by library policy.',
          ),
          _Section(
            title: 'Your Rights',
            body:
                'You have the right to access, correct, or delete your personal information. '
                'You can update your profile information through the app settings. For data '
                'deletion requests, please contact the library administration.',
          ),
          _Section(
            title: 'Contact Us',
            body:
                'If you have questions about this Privacy Policy, please contact us at '
                'library@ollmchs.ac.ke or visit the OLLMCHS Main Library.',
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
