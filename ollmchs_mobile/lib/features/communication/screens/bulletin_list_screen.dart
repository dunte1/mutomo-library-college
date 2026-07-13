import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../core/widgets/skeleton.dart';
import '../../../core/network/api_client.dart';
import '../../../core/utils/type_parsers.dart';

class BulletinModel {
  final int id;
  final String title;
  final String? content;
  final String? authorName;
  final DateTime? publishedAt;

  BulletinModel({
    required this.id,
    required this.title,
    this.content,
    this.authorName,
    this.publishedAt,
  });

  factory BulletinModel.fromJson(Map<String, dynamic> json) {
    final author = json['author'] as Map<String, dynamic>?;
    return BulletinModel(
      id: parseInt(json['id'], fieldName: 'id'),
      title: json['title'] as String? ?? '',
      content: json['content'] as String?,
      authorName: author?['name'] as String?,
      publishedAt: json['published_at'] != null
          ? DateTime.tryParse(json['published_at'] as String)
          : null,
    );
  }
}

class BulletinListScreen extends StatefulWidget {
  const BulletinListScreen({super.key});

  @override
  State<BulletinListScreen> createState() => _BulletinListScreenState();
}

class _BulletinListScreenState extends State<BulletinListScreen> {
  List<BulletinModel> _bulletins = [];
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
      final response = await api.get('/v1/bulletins');
      final data = response.data['data'] as List<dynamic>? ?? [];
      setState(() {
        _bulletins = data
            .map((e) => BulletinModel.fromJson(e as Map<String, dynamic>))
            .toList();
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
      appBar: AppBar(title: const Text('Bulletins')),
      body: _loading
          ? const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [SkeletonCard(), SkeletonCard(), SkeletonCard()],
              ),
            )
          : _error != null
          ? Center(child: Text(_error!))
          : _bulletins.isEmpty
          ? Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    Icons.campaign_outlined,
                    size: 64,
                    color: theme.colorScheme.onSurfaceVariant,
                  ),
                  const SizedBox(height: 16),
                  Text('No bulletins', style: theme.textTheme.titleMedium),
                ],
              ),
            )
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView.builder(
                padding: const EdgeInsets.all(12),
                itemCount: _bulletins.length,
                itemBuilder: (_, i) {
                  final b = _bulletins[i];
                  return Card(
                    child: ListTile(
                      leading: CircleAvatar(
                        backgroundColor: theme.colorScheme.primaryContainer,
                        child: Icon(
                          Icons.campaign,
                          color: theme.colorScheme.primary,
                        ),
                      ),
                      title: Text(
                        b.title,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      subtitle: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (b.authorName != null)
                            Text(
                              b.authorName!,
                              style: theme.textTheme.bodySmall,
                            ),
                          if (b.publishedAt != null)
                            Text(
                              DateFormat('MMM d, y').format(b.publishedAt!),
                              style: theme.textTheme.bodySmall,
                            ),
                        ],
                      ),
                      trailing: const Icon(Icons.chevron_right),
                      isThreeLine: true,
                      onTap: () => context.pushNamed(
                        'bulletin-detail',
                        pathParameters: {'id': '${b.id}'},
                      ),
                    ),
                  );
                },
              ),
            ),
    );
  }
}
