import 'dart:io';
import 'package:dio/dio.dart';
import 'package:path_provider/path_provider.dart';
import '../storage/local_storage_service.dart';

class DownloadService {
  final Dio _dio;
  final LocalStorageService _storage;

  DownloadService({Dio? dio, LocalStorageService? storage})
      : _dio = dio ?? Dio(),
        _storage = storage ?? LocalStorageService();

  Future<Directory> get _downloadsDir async {
    final dir = await getApplicationDocumentsDirectory();
    final downloadsDir = Directory('${dir.path}/downloads');
    if (!await downloadsDir.exists()) {
      await downloadsDir.create(recursive: true);
    }
    return downloadsDir;
  }

  Future<String> getDownloadPath(int assetId, String filename) async {
    final dir = await _downloadsDir;
    return '${dir.path}/asset_${assetId}_$filename';
  }

  Future<bool> isDownloaded(int assetId) async {
    final dir = await _downloadsDir;
    final files = await dir.list().toList();
    return files.any((f) => f.path.contains('asset_${assetId}_'));
  }

  Future<String?> getLocalPath(int assetId) async {
    final dir = await _downloadsDir;
    final files = await dir.list().toList();
    for (final file in files) {
      if (file.path.contains('asset_${assetId}_') && file is File) {
        return file.path;
      }
    }
    return null;
  }

  Future<void> download({
    required int assetId,
    required String url,
    required String filename,
    void Function(int received, int total)? onProgress,
  }) async {
    final token = await _storage.getToken();
    final savePath = await getDownloadPath(assetId, filename);

    await _dio.download(
      url,
      savePath,
      options: token != null
          ? Options(headers: {'Authorization': 'Bearer $token'})
          : null,
      onReceiveProgress: (received, total) {
        if (total > 0) {
          onProgress?.call(received, total);
        }
      },
    );
  }

  Future<void> delete(int assetId) async {
    final path = await getLocalPath(assetId);
    if (path != null) {
      final file = File(path);
      if (await file.exists()) {
        await file.delete();
      }
    }
  }

  Future<List<DownloadedAsset>> getDownloadedAssets() async {
    final dir = await _downloadsDir;
    final files = await dir.list().toList();
    final downloads = <DownloadedAsset>[];

    for (final file in files) {
      if (file is File) {
        final stat = await file.stat();
        final filename = file.path.split(Platform.pathSeparator).last;
        final match = RegExp(r'asset_(\d+)_(.+)').firstMatch(filename);
        if (match != null) {
          downloads.add(DownloadedAsset(
            assetId: int.parse(match.group(1)!),
            filename: match.group(2)!,
            localPath: file.path,
            fileSize: stat.size,
            downloadedAt: stat.modified,
          ));
        }
      }
    }

    downloads.sort((a, b) => b.downloadedAt.compareTo(a.downloadedAt));
    return downloads;
  }
}

class DownloadedAsset {
  final int assetId;
  final String filename;
  final String localPath;
  final int fileSize;
  final DateTime downloadedAt;

  const DownloadedAsset({
    required this.assetId,
    required this.filename,
    required this.localPath,
    required this.fileSize,
    required this.downloadedAt,
  });

  String get fileSizeFormatted {
    if (fileSize < 1024) return '$fileSize B';
    if (fileSize < 1024 * 1024) return '${(fileSize / 1024).toStringAsFixed(1)} KB';
    if (fileSize < 1024 * 1024 * 1024) {
      return '${(fileSize / (1024 * 1024)).toStringAsFixed(1)} MB';
    }
    return '${(fileSize / (1024 * 1024 * 1024)).toStringAsFixed(1)} GB';
  }
}
