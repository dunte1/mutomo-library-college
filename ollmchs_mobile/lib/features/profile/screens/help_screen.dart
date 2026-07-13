import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

class HelpScreen extends StatelessWidget {
  const HelpScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Help & Support')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _HelpSection(
            title: 'FAQs',
            children: [
              _FaqItem(
                question: 'How do I borrow a book?',
                answer:
                    'Go to the Books tab, find a book you want, tap on it, and press the Reserve button. You will be notified when the book is ready for pickup.',
              ),
              _FaqItem(
                question: 'How do I return a book?',
                answer:
                    'Bring the physical book to the library. The librarian will process the return and it will be reflected in your account.',
              ),
              _FaqItem(
                question: 'How do I renew a loan?',
                answer:
                    'Go to Loans > Active, find the loan you want to renew, and tap the Renew button. You can renew up to the maximum allowed times.',
              ),
              _FaqItem(
                question: 'How do I reserve a book?',
                answer:
                    'Open a book detail page and tap Reserve. If all copies are currently borrowed, you will be placed in the queue.',
              ),
              _FaqItem(
                question: 'How do I pay a fine?',
                answer:
                    'Go to Fines, select the fine you want to pay, and follow the payment instructions. Online payment options may be available.',
              ),
              _FaqItem(
                question: 'How do I enable biometric login?',
                answer:
                    'Go to Settings > Biometric Login and toggle it on. You will need to authenticate with your fingerprint or face to confirm.',
              ),
              _FaqItem(
                question: 'How do I enable two-factor authentication?',
                answer:
                    'Go to Settings > Two-Factor Authentication and follow the setup wizard. You will need an authenticator app like Google Authenticator.',
              ),
            ],
          ),
          const SizedBox(height: 24),
          _HelpSection(
            title: 'Contact',
            children: [
              ListTile(
                leading: const Icon(Icons.email_outlined),
                title: const Text('Email Support'),
                subtitle: const Text('library@ollmchs.ac.ke'),
                trailing: const Icon(Icons.open_in_new, size: 18),
                onTap: () => _launchUrl('mailto:library@ollmchs.ac.ke'),
              ),
              ListTile(
                leading: const Icon(Icons.phone_outlined),
                title: const Text('Call Librarian'),
                subtitle: const Text('+254 700 000 000'),
                trailing: const Icon(Icons.open_in_new, size: 18),
                onTap: () => _launchUrl('tel:+254700000000'),
              ),
              ListTile(
                leading: const Icon(Icons.location_on_outlined),
                title: const Text('Visit Library'),
                subtitle: const Text('OLLMCHS Main Library, Mutomo'),
                onTap: () {},
              ),
            ],
          ),
          const SizedBox(height: 24),
          _HelpSection(
            title: 'Report a Problem',
            children: [
              ListTile(
                leading: const Icon(Icons.bug_report_outlined),
                title: const Text('Report a Bug'),
                subtitle: const Text('Something not working? Let us know.'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => _showReportDialog(context, 'Bug Report'),
              ),
              ListTile(
                leading: const Icon(Icons.feedback_outlined),
                title: const Text('Send Feedback'),
                subtitle: const Text('Suggestions, feature requests, or general feedback.'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => _showReportDialog(context, 'Feedback'),
              ),
            ],
          ),
          const SizedBox(height: 32),
          Text(
            'OLLMCHS Library App v1.0.0',
            textAlign: TextAlign.center,
            style: theme.textTheme.bodySmall?.copyWith(
              color: theme.colorScheme.onSurfaceVariant,
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _launchUrl(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  void _showReportDialog(BuildContext context, String title) {
    final controller = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(title),
        content: TextField(
          controller: controller,
          decoration: const InputDecoration(
            labelText: 'Describe your issue or feedback',
            border: OutlineInputBorder(),
            alignLabelWithHint: true,
          ),
          maxLines: 5,
          minLines: 3,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () {
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Thank you for your feedback!')),
              );
            },
            child: const Text('Submit'),
          ),
        ],
      ),
    );
  }
}

class _HelpSection extends StatelessWidget {
  final String title;
  final List<Widget> children;

  const _HelpSection({required this.title, required this.children});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.bold,
              ),
        ),
        const SizedBox(height: 8),
        Card(child: Column(children: children)),
      ],
    );
  }
}

class _FaqItem extends StatefulWidget {
  final String question;
  final String answer;

  const _FaqItem({required this.question, required this.answer});

  @override
  State<_FaqItem> createState() => _FaqItemState();
}

class _FaqItemState extends State<_FaqItem> {
  bool _expanded = false;

  @override
  Widget build(BuildContext context) {
    return ExpansionTile(
      title: Text(
        widget.question,
        style: const TextStyle(fontWeight: FontWeight.w500),
      ),
      trailing: Icon(_expanded ? Icons.expand_less : Icons.expand_more),
      onExpansionChanged: (v) => setState(() => _expanded = v),
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
          child: Text(widget.answer),
        ),
      ],
    );
  }
}
