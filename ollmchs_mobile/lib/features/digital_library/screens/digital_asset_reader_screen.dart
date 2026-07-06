import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../../core/network/api_client.dart';
import '../bloc/digital_library_bloc.dart';

class DigitalAssetReaderScreen extends StatefulWidget {
  final int assetId;
  const DigitalAssetReaderScreen({super.key, required this.assetId});

  @override
  State<DigitalAssetReaderScreen> createState() =>
      _DigitalAssetReaderScreenState();
}

class _DigitalAssetReaderScreenState extends State<DigitalAssetReaderScreen> {
  bool _loading = true;
  String? _fileUrl;
  String? _filename;
  String? _error;
  int? _pageCount;
  double _progress = 0;

  @override
  void initState() {
    super.initState();
    _loadAsset();
  }

  Future<void> _loadAsset() async {
    try {
      final api = context.read<ApiClient>();
      final response = await api.get('/v1/digital-assets/${widget.assetId}');
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      if (!mounted) return;
      setState(() {
        _fileUrl = data['file_url'] as String? ?? data['file_path'] as String?;
        _filename = data['title'] as String? ?? 'Asset #${widget.assetId}';
        _pageCount = data['page_count'] as int?;
        _loading = false;
      });
      context.read<DigitalLibraryBloc>().add(
        UpdateReadingProgress(assetId: widget.assetId, progress: 0),
      );
    } catch (e) {
      setState(() {
        _error = 'Failed to load asset: $e';
        _loading = false;
      });
    }
  }

  Future<void> _openAsset() async {
    if (_fileUrl == null) return;
    final uri = Uri.parse(_fileUrl!);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  void _onProgressChanged(double value) {
    setState(() => _progress = value);
    context.read<DigitalLibraryBloc>().add(
      UpdateReadingProgress(
        assetId: widget.assetId,
        progress: value,
        lastPage: _pageCount != null
            ? 'Page ${(value / 100 * _pageCount!).round()}'
            : null,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: Text(_filename ?? 'Digital Asset')),
      body: Center(
        child: _loading
            ? const CircularProgressIndicator()
            : _error != null
            ? Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    Icons.error_outline,
                    size: 64,
                    color: theme.colorScheme.error,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    _error!,
                    style: theme.textTheme.bodyMedium,
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 16),
                  FilledButton.tonal(
                    onPressed: () {
                      setState(() {
                        _loading = true;
                        _error = null;
                      });
                      _loadAsset();
                    },
                    child: const Text('Retry'),
                  ),
                ],
              )
            : ListView(
                shrinkWrap: true,
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 32,
                ),
                children: [
                  Icon(
                    Icons.picture_as_pdf,
                    size: 80,
                    color: theme.colorScheme.primary,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    _filename ?? 'Digital Asset #${widget.assetId}',
                    textAlign: TextAlign.center,
                    style: theme.textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  if (_pageCount != null)
                    Text(
                      '$_pageCount pages',
                      textAlign: TextAlign.center,
                      style: theme.textTheme.bodyMedium?.copyWith(
                        color: theme.colorScheme.onSurfaceVariant,
                      ),
                    ),
                  const SizedBox(height: 8),
                  Text(
                    'Open in external viewer',
                    textAlign: TextAlign.center,
                    style: theme.textTheme.bodyMedium?.copyWith(
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                  ),
                  const SizedBox(height: 32),
                  FilledButton.icon(
                    onPressed: _fileUrl != null ? _openAsset : null,
                    icon: const Icon(Icons.open_in_new),
                    label: const Text('Open'),
                  ),
                  const SizedBox(height: 32),
                  Text(
                    'Reading Progress',
                    style: theme.textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Slider(
                    value: _progress,
                    max: 100,
                    divisions: 100,
                    label: '${_progress.round()}%',
                    onChanged: _onProgressChanged,
                  ),
                  Text(
                    '${_progress.round()}% complete',
                    textAlign: TextAlign.center,
                    style: theme.textTheme.bodySmall?.copyWith(
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                  ),
                ],
              ),
      ),
    );
  }
}
