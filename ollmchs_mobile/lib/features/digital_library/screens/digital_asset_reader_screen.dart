import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../../core/network/api_client.dart';
import '../../../../core/services/download_service.dart';
import '../../../../core/utils/type_parsers.dart';
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

  // Download state
  bool _isDownloaded = false;
  bool _isDownloading = false;
  double _downloadProgress = 0;
  String? _localPath;
  final _downloadService = DownloadService();

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

      final isDownloaded = await _downloadService.isDownloaded(widget.assetId);
      final localPath = await _downloadService.getLocalPath(widget.assetId);

      setState(() {
        _fileUrl = data['file_url'] as String? ?? data['file_path'] as String?;
        _filename = data['title'] as String? ?? 'Asset #${widget.assetId}';
        _pageCount = parseIntOrNull(data['page_count']);
        _loading = false;
        _isDownloaded = isDownloaded;
        _localPath = localPath;
      });
      if (!context.mounted) return;
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
    // Try local file first, then URL
    if (_isDownloaded && _localPath != null) {
      final file = File(_localPath!);
      if (await file.exists()) {
        final uri = Uri.file(_localPath!);
        if (await canLaunchUrl(uri)) {
          await launchUrl(uri, mode: LaunchMode.externalApplication);
          return;
        }
      }
    }

    if (_fileUrl != null) {
      final uri = Uri.parse(_fileUrl!);
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      }
    }
  }

  Future<void> _downloadAsset() async {
    if (_fileUrl == null || _isDownloading) return;

    setState(() {
      _isDownloading = true;
      _downloadProgress = 0;
    });

    try {
      await _downloadService.download(
        assetId: widget.assetId,
        url: _fileUrl!,
        filename: _filename ?? 'asset_${widget.assetId}',
        onProgress: (received, total) {
          if (mounted) {
            setState(() => _downloadProgress = received / total);
          }
        },
      );

      final localPath = await _downloadService.getLocalPath(widget.assetId);

      if (mounted) {
        setState(() {
          _isDownloaded = true;
          _isDownloading = false;
          _localPath = localPath;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Downloaded for offline reading')),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isDownloading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Download failed: $e')),
        );
      }
    }
  }

  Future<void> _deleteDownload() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete Download'),
        content: const Text('Remove this file from offline storage?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Delete'),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      await _downloadService.delete(widget.assetId);
      if (mounted) {
        setState(() {
          _isDownloaded = false;
          _localPath = null;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Download removed')),
        );
      }
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
      appBar: AppBar(
        title: Text(_filename ?? 'Digital Asset'),
        actions: [
          if (!_loading && _error == null)
            PopupMenuButton<String>(
              onSelected: (value) {
                switch (value) {
                  case 'download':
                    _downloadAsset();
                    break;
                  case 'delete':
                    _deleteDownload();
                    break;
                }
              },
              itemBuilder: (context) => [
                if (!_isDownloaded)
                  const PopupMenuItem(
                    value: 'download',
                    child: ListTile(
                      leading: Icon(Icons.download),
                      title: Text('Download for Offline'),
                      dense: true,
                    ),
                  ),
                if (_isDownloaded)
                  const PopupMenuItem(
                    value: 'delete',
                    child: ListTile(
                      leading: Icon(Icons.delete_outline),
                      title: Text('Remove Download'),
                      dense: true,
                    ),
                  ),
              ],
            ),
        ],
      ),
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
                  if (_isDownloaded)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: Colors.green.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.check_circle, color: Colors.green, size: 16),
                          const SizedBox(width: 4),
                          Text(
                            'Available offline',
                            style: theme.textTheme.bodySmall?.copyWith(color: Colors.green),
                          ),
                        ],
                      ),
                    ),
                  const SizedBox(height: 32),
                  FilledButton.icon(
                    onPressed: _fileUrl != null ? _openAsset : null,
                    icon: const Icon(Icons.open_in_new),
                    label: Text(_isDownloaded ? 'Open Local Copy' : 'Open'),
                  ),
                  const SizedBox(height: 12),
                  if (!_isDownloaded)
                    OutlinedButton.icon(
                      onPressed: _isDownloading ? null : _downloadAsset,
                      icon: _isDownloading
                          ? const SizedBox(
                              width: 16,
                              height: 16,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Icon(Icons.download),
                      label: Text(_isDownloading
                          ? 'Downloading ${(_downloadProgress * 100).round()}%'
                          : 'Download for Offline'),
                    ),
                  if (_isDownloaded)
                    OutlinedButton.icon(
                      onPressed: _deleteDownload,
                      icon: const Icon(Icons.delete_outline),
                      label: const Text('Remove Download'),
                    ),
                  if (_isDownloading) ...[
                    const SizedBox(height: 16),
                    LinearProgressIndicator(value: _downloadProgress),
                    const SizedBox(height: 8),
                    Text(
                      '${(_downloadProgress * 100).round()}% downloaded',
                      textAlign: TextAlign.center,
                      style: theme.textTheme.bodySmall?.copyWith(
                        color: theme.colorScheme.onSurfaceVariant,
                      ),
                    ),
                  ],
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
