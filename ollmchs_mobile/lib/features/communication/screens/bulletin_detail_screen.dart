import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import '../../../core/network/api_client.dart';
import 'bulletin_list_screen.dart';

class BulletinDetailScreen extends StatefulWidget {
  final int bulletinId;
  const BulletinDetailScreen({super.key, required this.bulletinId});

  @override
  State<BulletinDetailScreen> createState() => _BulletinDetailScreenState();
}

class _BulletinDetailScreenState extends State<BulletinDetailScreen> {
  BulletinModel? _bulletin;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final api = context.read<ApiClient>();
      final response = await api.get('/v1/bulletins/${widget.bulletinId}');
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      setState(() {
        _bulletin = BulletinModel.fromJson(data);
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Bulletin')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
          ? Center(child: Text(_error!))
          : _bulletin != null
          ? SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    _bulletin!.title,
                    style: theme.textTheme.headlineSmall?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      if (_bulletin!.authorName != null)
                        Text(
                          _bulletin!.authorName!,
                          style: theme.textTheme.bodyMedium,
                        ),
                      if (_bulletin!.authorName != null &&
                          _bulletin!.publishedAt != null)
                        const Text(' • '),
                      if (_bulletin!.publishedAt != null)
                        Text(
                          DateFormat(
                            'MMM d, y',
                          ).format(_bulletin!.publishedAt!),
                          style: theme.textTheme.bodySmall,
                        ),
                    ],
                  ),
                  const SizedBox(height: 20),
                  if (_bulletin!.content != null)
                    Text(
                      _bulletin!.content!,
                      style: theme.textTheme.bodyLarge?.copyWith(height: 1.6),
                    ),
                ],
              ),
            )
          : const SizedBox.shrink(),
    );
  }
}
