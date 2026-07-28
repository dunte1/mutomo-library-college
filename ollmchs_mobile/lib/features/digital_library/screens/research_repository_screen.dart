import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../bloc/digital_library_bloc.dart';
import '../models/digital_asset_model.dart';
import '../../../core/network/api_client.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/skeleton.dart';

class ResearchRepositoryScreen extends StatefulWidget {
  const ResearchRepositoryScreen({super.key});

  @override
  State<ResearchRepositoryScreen> createState() =>
      _ResearchRepositoryScreenState();
}

class _ResearchRepositoryScreenState extends State<ResearchRepositoryScreen> {
  final _searchController = TextEditingController();
  String? _typeFilter;
  List<DigitalAssetModel> _searchResults = [];
  bool _isSearching = false;

  @override
  void initState() {
    super.initState();
    context.read<DigitalLibraryBloc>().add(const LoadDigitalAssets());
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _onSearch(String query) async {
    if (query.trim().isEmpty) {
      setState(() {
        _isSearching = false;
        _searchResults = [];
      });
      return;
    }
    setState(() => _isSearching = true);
    try {
      final api = context.read<ApiClient>();
      final response = await api.get(
        '/v1/digital-assets',
        queryParameters: {'search': query, 'per_page': 50},
      );
      final data = response.data['data'] as List<dynamic>? ?? [];
      if (mounted) {
        setState(() {
          _searchResults = data
              .map((e) => DigitalAssetModel.fromJson(e as Map<String, dynamic>))
              .toList();
        });
      }
    } catch (_) {
      if (mounted) setState(() => _searchResults = []);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Research Repository'),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search research materials...',
                prefixIcon: const Icon(Icons.search, size: 20),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                isDense: true,
                contentPadding: const EdgeInsets.symmetric(vertical: 8),
              ),
              onSubmitted: _onSearch,
              onChanged: (v) {
                if (v.isEmpty) _onSearch('');
              },
            ),
          ),
          SizedBox(
            height: 40,
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12),
              children: [
                _FilterChip(
                  label: 'All',
                  selected: _typeFilter == null,
                  onTap: () => setState(() => _typeFilter = null),
                ),
                _FilterChip(
                  label: 'Thesis',
                  selected: _typeFilter == 'thesis',
                  onTap: () => setState(() => _typeFilter = 'thesis'),
                ),
                _FilterChip(
                  label: 'Journal',
                  selected: _typeFilter == 'journal',
                  onTap: () => setState(() => _typeFilter = 'journal'),
                ),
                _FilterChip(
                  label: 'Paper',
                  selected: _typeFilter == 'paper',
                  onTap: () => setState(() => _typeFilter = 'paper'),
                ),
                _FilterChip(
                  label: 'Report',
                  selected: _typeFilter == 'report',
                  onTap: () => setState(() => _typeFilter = 'report'),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          Expanded(
            child: _isSearching
                ? _buildSearchResults(theme)
                : BlocBuilder<DigitalLibraryBloc, DigitalLibraryState>(
                    builder: (context, state) {
                      if (state is DigitalLibraryLoading &&
                          state is! DigitalLibraryLoaded) {
                        return const Padding(
                          padding: EdgeInsets.all(16),
                          child: Column(
                            children: [
                              SkeletonCard(height: 80),
                              SkeletonCard(height: 80),
                              SkeletonCard(height: 80),
                            ],
                          ),
                        );
                      }
                      if (state is DigitalLibraryLoaded) {
                        var assets = state.assets;
                        if (_typeFilter != null) {
                          assets = assets
                              .where((a) =>
                                  (a.fileType ?? '').toLowerCase() ==
                                  _typeFilter!.toLowerCase())
                              .toList();
                        }
                        if (assets.isEmpty) {
                          return const EmptyState(
                            icon: Icons.science_outlined,
                            title: 'No research materials found',
                            subtitle:
                                'Theses, journals, and research papers will appear here.',
                          );
                        }
                        return RefreshIndicator(
                          onRefresh: () async {
                            context
                                .read<DigitalLibraryBloc>()
                                .add(const LoadDigitalAssets());
                          },
                          child: ListView.builder(
                            padding: const EdgeInsets.all(12),
                            itemCount: assets.length,
                            itemBuilder: (_, i) =>
                                _buildAssetTile(assets[i], theme),
                          ),
                        );
                      }
                      if (state is DigitalLibraryError) {
                        return Center(child: Text(state.error));
                      }
                      return const SizedBox.shrink();
                    },
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildSearchResults(ThemeData theme) {
    if (_searchResults.isEmpty) {
      return const EmptyState(
        icon: Icons.search_off,
        title: 'No results found',
        subtitle: 'Try adjusting your search terms',
      );
    }
    return ListView.builder(
      padding: const EdgeInsets.all(12),
      itemCount: _searchResults.length,
      itemBuilder: (_, i) => _buildAssetTile(_searchResults[i], theme),
    );
  }

  Widget _buildAssetTile(DigitalAssetModel a, ThemeData theme) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: Container(
          width: 48,
          height: 48,
          decoration: BoxDecoration(
            color: theme.colorScheme.tertiaryContainer,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(
            _iconForType(a.fileType ?? ''),
            color: theme.colorScheme.tertiary,
          ),
        ),
        title: Text(
          a.title,
          maxLines: 2,
          overflow: TextOverflow.ellipsis,
          style: const TextStyle(fontWeight: FontWeight.w500),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (a.authors.isNotEmpty)
              Text(
                a.authors.join(', '),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: theme.textTheme.bodySmall,
              ),
            Row(
              children: [
                Chip(
                  label: Text(
                    (a.fileType ?? '').toUpperCase(),
                    style: const TextStyle(fontSize: 9),
                  ),
                  visualDensity: VisualDensity.compact,
                ),
                if (a.fileSize != null) ...[
                  const SizedBox(width: 4),
                  Text(
                    a.fileSize!,
                    style: theme.textTheme.bodySmall,
                  ),
                ],
              ],
            ),
          ],
        ),
        isThreeLine: true,
        trailing: const Icon(Icons.chevron_right),
        onTap: () => context.pushNamed(
          'digital-asset-reader',
          pathParameters: {'id': '${a.id}'},
        ),
      ),
    );
  }

  IconData _iconForType(String type) {
    switch (type.toLowerCase()) {
      case 'pdf':
        return Icons.picture_as_pdf;
      case 'epub':
        return Icons.menu_book;
      case 'thesis':
        return Icons.school;
      case 'journal':
        return Icons.article;
      case 'paper':
        return Icons.description;
      default:
        return Icons.insert_drive_file;
    }
  }
}

class _FilterChip extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;
  const _FilterChip({
    required this.label,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: ChoiceChip(
        label: Text(label),
        selected: selected,
        onSelected: (_) => onTap(),
      ),
    );
  }
}
