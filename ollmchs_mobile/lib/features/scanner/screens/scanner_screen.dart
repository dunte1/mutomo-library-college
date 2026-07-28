import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../../../core/network/api_client.dart';

class ScannerScreen extends StatefulWidget {
  const ScannerScreen({super.key});

  @override
  State<ScannerScreen> createState() => _ScannerScreenState();
}

class _ScannerScreenState extends State<ScannerScreen> {
  MobileScannerController? _controller;
  bool _isProcessing = false;
  String? _lastResult;
  bool _torchOn = false;

  @override
  void initState() {
    super.initState();
    _controller = MobileScannerController(
      detectionSpeed: DetectionSpeed.normal,
      facing: CameraFacing.back,
      torchEnabled: false,
    );
  }

  @override
  void dispose() {
    _controller?.dispose();
    super.dispose();
  }

  void _onDetect(BarcodeCapture capture) {
    if (_isProcessing) return;
    final barcodes = capture.barcodes;
    if (barcodes.isEmpty) return;

    final code = barcodes.first.rawValue;
    if (code == null || code == _lastResult) return;

    _lastResult = code;
    _isProcessing = true;

    // Try to navigate based on the scanned data
    _handleScannedCode(code);
  }

  void _handleScannedCode(String code) {
    // Check if it's a URL
    if (code.startsWith('http://') || code.startsWith('https://')) {
      Navigator.of(context).pop(code);
      return;
    }

    // Check if it matches a book barcode pattern (ISBN-like)
    if (RegExp(r'^\d{10,13}$').hasMatch(code)) {
      _showResult('ISBN: $code', 'This appears to be a book ISBN.');
      return;
    }

    // Check if it's a library card number
    if (code.toUpperCase().startsWith('LIB') ||
        code.toUpperCase().startsWith('CARD') ||
        code.toUpperCase().startsWith('MEM')) {
      _verifyLibraryCard(code);
      return;
    }

    // Generic result
    _showResult('Scanned Code', code);
  }

  Future<void> _verifyLibraryCard(String cardCode) async {
    try {
      final api = context.read<ApiClient>();
      final response = await api.get(
        '/v1/library-cards/verify',
        queryParameters: {'card_number': cardCode},
      );
      final data = response.data['data'] as Map<String, dynamic>?;
      if (mounted && data != null) {
        _showVerificationResult(cardCode, data);
      } else if (mounted) {
        _showResult('Card Not Found', 'No card found with number: $cardCode');
      }
    } catch (e) {
      if (mounted) {
        _showResult('Verification Failed', 'Could not verify card: $e');
      }
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  void _showVerificationResult(String cardCode, Map<String, dynamic> data) {
    final memberName = data['member_name'] as String? ?? data['name'] as String?;
    final status = data['status'] as String? ?? data['card_status'] as String?;
    final isActive = status == 'active';
    final expiry = data['expires_at'] as String?;

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        icon: Icon(
          isActive ? Icons.check_circle : Icons.warning,
          color: isActive ? Colors.green : Colors.orange,
          size: 48,
        ),
        title: Text(isActive ? 'Valid Card' : 'Card Issue'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (memberName != null) _resultRow('Name', memberName),
            _resultRow('Card #', cardCode),
            if (status != null) _resultRow('Status', status.toUpperCase()),
            if (expiry != null) _resultRow('Expires', expiry),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              setState(() => _isProcessing = false);
            },
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }

  Widget _resultRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontWeight: FontWeight.w500)),
          Text(value),
        ],
      ),
    );
  }

  void _showResult(String title, String content) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(title),
        content: Text(content),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              setState(() => _isProcessing = false);
            },
            child: const Text('Scan Again'),
          ),
          FilledButton(
            onPressed: () {
              Navigator.pop(ctx);
              Navigator.of(context).pop(content);
            },
            child: const Text('Use'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Scan QR / Barcode'),
        actions: [
          IconButton(
            icon: Icon(
              _torchOn ? Icons.flash_on : Icons.flash_off,
            ),
            onPressed: () async {
              await _controller?.toggleTorch();
              if (mounted) {
                setState(() => _torchOn = !_torchOn);
              }
            },
          ),
        ],
      ),
      body: Stack(
        fit: StackFit.expand,
        children: [
          MobileScanner(
            controller: _controller!,
            onDetect: _onDetect,
          ),
          // Overlay with scan area
          CustomPaint(
            painter: _ScannerOverlayPainter(),
          ),
          // Instructions
          Positioned(
            bottom: 48,
            left: 0,
            right: 0,
            child: Container(
              margin: const EdgeInsets.symmetric(horizontal: 32),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: Colors.black54,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                'Point your camera at a QR code or barcode',
                style: theme.textTheme.bodyMedium?.copyWith(
                  color: Colors.white,
                ),
                textAlign: TextAlign.center,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ScannerOverlayPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.black45
      ..style = PaintingStyle.fill;

    final scanArea = Rect.fromCenter(
      center: Offset(size.width / 2, size.height / 2),
      width: size.width * 0.7,
      height: size.width * 0.7,
    );

    // Draw overlay
    canvas.drawPath(
      Path.combine(
        PathOperation.difference,
        Path()..addRect(Rect.fromLTWH(0, 0, size.width, size.height)),
        Path()..addRRect(RRect.fromRectAndRadius(scanArea, const Radius.circular(12))),
      ),
      paint,
    );

    // Draw scan area border
    final borderPaint = Paint()
      ..color = Colors.white
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2;
    canvas.drawRRect(
      RRect.fromRectAndRadius(scanArea, const Radius.circular(12)),
      borderPaint,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
