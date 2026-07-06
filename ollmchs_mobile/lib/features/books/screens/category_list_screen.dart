import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../core/network/api_client.dart';
import '../models/category_model.dart';

class CategoryListScreen extends StatefulWidget {
  const CategoryListScreen({super.key});

  @override
  State<CategoryListScreen> createState() => _CategoryListScreenState();
}

class _CategoryListScreenState extends State<CategoryListScreen> {
  late final ApiClient _api;
  List<CategoryModel> _categories = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _api = context.read<ApiClient>();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final response = await _api.get('/v1/categories');
      final data = response.data['data'] as List<dynamic>? ?? [];
      setState(() {
        _categories = data
            .map((e) => CategoryModel.fromJson(e as Map<String, dynamic>))
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
      appBar: AppBar(title: const Text('Browse Categories')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
          ? Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(_error!, textAlign: TextAlign.center),
                  const SizedBox(height: 16),
                  FilledButton.tonal(
                    onPressed: _load,
                    child: const Text('Retry'),
                  ),
                ],
              ),
            )
          : _categories.isEmpty
          ? const Center(child: Text('No categories found'))
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView.builder(
                padding: const EdgeInsets.all(12),
                itemCount: _categories.length,
                itemBuilder: (_, i) =>
                    _buildCategoryCard(_categories[i], theme),
              ),
            ),
    );
  }

  Widget _buildCategoryCard(CategoryModel category, ThemeData theme) {
    final hasChildren = category.children.isNotEmpty;
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: theme.colorScheme.primaryContainer,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(Icons.category, color: theme.colorScheme.primary),
        ),
        title: Text(
          category.name,
          style: const TextStyle(fontWeight: FontWeight.w600),
        ),
        subtitle: Text(
          '${category.booksCount} books${hasChildren ? ' • ${category.children.length} subcategories' : ''}',
        ),
        trailing: const Icon(Icons.chevron_right),
        onTap: () => context.goNamed(
          'books-category',
          pathParameters: {'slug': category.slug},
          extra: category.name,
        ),
      ),
    );
  }
}
