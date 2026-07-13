import 'dart:io';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/services/download_service.dart';
import '../../../core/widgets/empty_state.dart';

class DownloadedAssetsScreen extends StatefulWidget {
  const DownloadedAssetsScreen({super.key});

  @override
  State<DownloadedAssetsScreen> createState() => _DownloadedAssetsScreenState();
}

class _DownloadedAssetsScreenState extends State<DownloadedAssetsScreen> {
  final _downloadService = DownloadService();
  List<DownloadedAsset> _downloads = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadDownloads();
  }

  Future<void> _loadDownloads() async {
    final downloads = await _downloadService.getDownloadedAssets();
    setState(() {
      _downloads = downloads;
      _loading = false;
    });
  }

  Future<void> _openAsset(DownloadedAsset asset) async {
    final file = File(asset.localPath);
    if (await file.exists()) {
      final uri = Uri.file(asset.localPath);
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      }
    }
  }

  Future<void> _deleteAsset(DownloadedAsset asset) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete Download'),
        content: Text('Remove "${asset.filename}" from offline storage?'),
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
      await _downloadService.delete(asset.assetId);
      _loadDownloads();
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Downloaded Assets')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _downloads.isEmpty
          ? const EmptyState(
              icon: Icons.download_done_outlined,
              title: 'No downloads',
              subtitle: 'Downloaded content will appear here',
            )
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: _downloads.length,
              itemBuilder: (context, index) {
                final asset = _downloads[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: ListTile(
                    leading: CircleAvatar(
                      child: Icon(
                        _getIconForFilename(asset.filename),
                      ),
                    ),
                    title: Text(
                      asset.filename,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '${asset.fileSizeFormatted} • Downloaded ${_formatDate(asset.downloadedAt)}',
                          style: theme.textTheme.bodySmall,
                        ),
                      ],
                    ),
                    trailing: PopupMenuButton<String>(
                      onSelected: (value) {
                        switch (value) {
                          case 'open':
                            _openAsset(asset);
                            break;
                          case 'delete':
                            _deleteAsset(asset);
                            break;
                        }
                      },
                      itemBuilder: (context) => [
                        const PopupMenuItem(
                          value: 'open',
                          child: ListTile(
                            leading: Icon(Icons.open_in_new),
                            title: Text('Open'),
                            dense: true,
                          ),
                        ),
                        const PopupMenuItem(
                          value: 'delete',
                          child: ListTile(
                            leading: Icon(Icons.delete_outline),
                            title: Text('Delete'),
                            dense: true,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
    );
  }

  IconData _getIconForFilename(String filename) {
    final ext = filename.split('.').last.toLowerCase();
    switch (ext) {
      case 'pdf':
        return Icons.picture_as_pdf;
      case 'epub':
        return Icons.auto_stories;
      case 'doc':
      case 'docx':
        return Icons.description;
      case 'ppt':
      case 'pptx':
        return Icons.slideshow;
      default:
        return Icons.insert_drive_file;
    }
  }

  String _formatDate(DateTime date) {
    final now = DateTime.now();
    final diff = now.difference(date);
    if (diff.inDays > 0) return '${diff.inDays}d ago';
    if (diff.inHours > 0) return '${diff.inHours}h ago';
    if (diff.inMinutes > 0) return '${diff.inMinutes}m ago';
    return 'just now';
  }
}
