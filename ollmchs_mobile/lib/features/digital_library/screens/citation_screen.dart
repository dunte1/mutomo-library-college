import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../bloc/digital_library_bloc.dart';

class CitationScreen extends StatefulWidget {
  final int assetId;
  final String assetTitle;
  const CitationScreen({
    super.key,
    required this.assetId,
    required this.assetTitle,
  });

  @override
  State<CitationScreen> createState() => _CitationScreenState();
}

class _CitationScreenState extends State<CitationScreen> {
  String _selectedStyle = 'APA';

  static const _styles = ['APA', 'MLA', 'Chicago', 'Harvard', 'Vancouver', 'IEEE'];

  @override
  void initState() {
    super.initState();
    context.read<DigitalLibraryBloc>().add(LoadCitations(assetId: widget.assetId));
  }

  void _generateCitation() {
    context.read<DigitalLibraryBloc>().add(GenerateCitation(
      assetId: widget.assetId,
      style: _selectedStyle,
    ));
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Citation Generator')),
      body: BlocConsumer<DigitalLibraryBloc, DigitalLibraryState>(
        listener: (context, state) {
          if (state is DigitalLibraryLoaded && state.message != null) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text(state.message!)),
            );
          }
        },
        builder: (context, state) {
          if (state is! DigitalLibraryLoaded) {
            return const Center(child: CircularProgressIndicator());
          }

          final citations = state.citations[widget.assetId] ?? [];
          final currentCitation = citations.isNotEmpty
              ? citations.lastWhere(
                  (c) => c.style == _selectedStyle,
                  orElse: () => citations.first,
                )
              : null;

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Asset title
                Text(
                  widget.assetTitle,
                  style: theme.textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 16),

                // Style selector
                Text(
                  'Citation Style',
                  style: theme.textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: _styles.map((style) {
                    final isSelected = _selectedStyle == style;
                    return ChoiceChip(
                      label: Text(style),
                      selected: isSelected,
                      onSelected: (_) {
                        setState(() => _selectedStyle = style);
                      },
                    );
                  }).toList(),
                ),
                const SizedBox(height: 16),

                // Generate button
                SizedBox(
                  width: double.infinity,
                  child: FilledButton.icon(
                    onPressed: _generateCitation,
                    icon: const Icon(Icons.format_quote),
                    label: const Text('Generate Citation'),
                  ),
                ),
                const SizedBox(height: 24),

                // Generated citation
                if (currentCitation != null) ...[
                  Text(
                    'Generated Citation ($_selectedStyle)',
                    style: theme.textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            currentCitation.citationText,
                            style: theme.textTheme.bodyMedium?.copyWith(
                              fontStyle: FontStyle.italic,
                            ),
                          ),
                          const SizedBox(height: 12),
                          Row(
                            children: [
                              OutlinedButton.icon(
                                onPressed: () {
                                  Clipboard.setData(
                                    ClipboardData(text: currentCitation.citationText),
                                  );
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(content: Text('Copied to clipboard')),
                                  );
                                },
                                icon: const Icon(Icons.copy, size: 16),
                                label: const Text('Copy'),
                              ),
                              const SizedBox(width: 8),
                              OutlinedButton.icon(
                                onPressed: () {
                                  Clipboard.setData(
                                    ClipboardData(text: currentCitation.citationText),
                                  );
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(content: Text('Citation copied to clipboard')),
                                  );
                                },
                                icon: const Icon(Icons.share, size: 16),
                                label: const Text('Share'),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                ] else if (citations.isNotEmpty) ...[
                  // Show available citations
                  Text(
                    'Available Citations',
                    style: theme.textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  ...citations.map((c) => Card(
                    child: ListTile(
                      title: Text(c.style, style: const TextStyle(fontWeight: FontWeight.w600)),
                      subtitle: Text(
                        c.citationText,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      trailing: IconButton(
                        icon: const Icon(Icons.copy, size: 16),
                        onPressed: () {
                          Clipboard.setData(ClipboardData(text: c.citationText));
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Copied to clipboard')),
                          );
                        },
                      ),
                    ),
                  )),
                ] else ...[
                  Center(
                    child: Padding(
                      padding: const EdgeInsets.all(32),
                      child: Column(
                        children: [
                          Icon(
                            Icons.format_quote,
                            size: 64,
                            color: theme.colorScheme.onSurfaceVariant,
                          ),
                          const SizedBox(height: 16),
                          Text(
                            'No citations generated yet',
                            style: theme.textTheme.titleMedium,
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Select a style and tap Generate to create a citation.',
                            style: theme.textTheme.bodyMedium?.copyWith(
                              color: theme.colorScheme.onSurfaceVariant,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ],
            ),
          );
        },
      ),
    );
  }
}
