import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/network/api_client.dart';
import '../models/publisher_model.dart';

class PublisherListScreen extends StatefulWidget {
  const PublisherListScreen({super.key});

  @override
  State<PublisherListScreen> createState() => _PublisherListScreenState();
}

class _PublisherListScreenState extends State<PublisherListScreen> {
  List<PublisherModel> _publishers = [];
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
      final response = await api.get('/v1/publishers');
      final data = response.data['data'] as List<dynamic>? ?? [];
      setState(() {
        _publishers = data
            .map((e) => PublisherModel.fromJson(e as Map<String, dynamic>))
            .toList();
        _loading = false;
        _error = null;
      });
    } catch (e) {
      setState(() {
        _loading = false;
        _error = e.toString();
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Publishers')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
          ? Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    Icons.error_outline,
                    size: 48,
                    color: theme.colorScheme.error,
                  ),
                  const SizedBox(height: 16),
                  Text(_error!, textAlign: TextAlign.center),
                  const SizedBox(height: 16),
                  FilledButton.tonal(
                    onPressed: _load,
                    child: const Text('Retry'),
                  ),
                ],
              ),
            )
          : _publishers.isEmpty
          ? const Center(child: Text('No publishers found'))
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView.separated(
                padding: const EdgeInsets.all(12),
                itemCount: _publishers.length,
                separatorBuilder: (_, __) => const Divider(),
                itemBuilder: (_, i) {
                  final publisher = _publishers[i];
                  return ListTile(
                    leading: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: theme.colorScheme.primaryContainer,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Icon(
                        Icons.business,
                        color: theme.colorScheme.primary,
                      ),
                    ),
                    title: Text(
                      publisher.name,
                      style: const TextStyle(fontWeight: FontWeight.w600),
                    ),
                    subtitle: Text('${publisher.booksCount} books'),
                  );
                },
              ),
            ),
    );
  }
}
