import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../bloc/bookmarks_bloc.dart';
import '../models/bookmark_model.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/skeleton.dart';

class BookmarksScreen extends StatefulWidget {
  const BookmarksScreen({super.key});

  @override
  State<BookmarksScreen> createState() => _BookmarksScreenState();
}

class _BookmarksScreenState extends State<BookmarksScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    context.read<BookmarksBloc>().add(const LoadBookmarks(type: 'book'));
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  void _loadTab(int index) {
    switch (index) {
      case 0:
        context.read<BookmarksBloc>().add(const LoadBookmarks(type: 'book'));
        break;
      case 1:
        context.read<BookmarksBloc>().add(const LoadBookmarks(type: 'author'));
        break;
      case 2:
        context.read<BookmarksBloc>().add(const LoadBookmarks());
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Bookmarks'),
        bottom: TabBar(
          controller: _tabController,
          onTap: (i) => _loadTab(i),
          tabs: const [
            Tab(text: 'Books'),
            Tab(text: 'Authors'),
            Tab(text: 'All'),
          ],
        ),
      ),
      body: BlocConsumer<BookmarksBloc, BookmarksState>(
        listener: (context, state) {
          if (state is BookmarksLoaded && state.message != null) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text(state.message!)),
            );
          }
        },
        builder: (context, state) {
          if (state is BookmarksLoading) {
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
          if (state is BookmarksError) {
            return Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(state.error),
                  const SizedBox(height: 16),
                  FilledButton.tonal(
                    onPressed: () => _loadTab(_tabController.index),
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }
          if (state is BookmarksLoaded) {
            if (state.bookmarks.isEmpty) {
              return const EmptyState(
                icon: Icons.bookmark_border,
                title: 'No bookmarks',
                subtitle: 'Books and authors you bookmark will appear here',
              );
            }
            return RefreshIndicator(
              onRefresh: () async => _loadTab(_tabController.index),
              child: ListView.builder(
                padding: const EdgeInsets.all(12),
                itemCount: state.bookmarks.length,
                itemBuilder: (_, i) => _bookmarkTile(state.bookmarks[i]),
              ),
            );
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }

  Widget _bookmarkTile(BookmarkModel bookmark) {
    final theme = Theme.of(context);
    return Card(
      child: ListTile(
        leading: bookmark.coverImage != null
            ? ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: CachedNetworkImage(
                  imageUrl: bookmark.coverImage!,
                  width: 48,
                  height: 64,
                  fit: BoxFit.cover,
                  errorWidget: (_, __, ___) => Container(
                    width: 48,
                    height: 64,
                    color: theme.colorScheme.primaryContainer,
                    child: Icon(
                      bookmark.isBook ? Icons.book : Icons.person,
                      color: theme.colorScheme.primary,
                    ),
                  ),
                ),
              )
            : Container(
                width: 48,
                height: 64,
                decoration: BoxDecoration(
                  color: theme.colorScheme.primaryContainer,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(
                  bookmark.isBook ? Icons.book : Icons.person,
                  color: theme.colorScheme.primary,
                ),
              ),
        title: Text(
          bookmark.title,
          maxLines: 2,
          overflow: TextOverflow.ellipsis,
        ),
        subtitle: bookmark.subtitle != null
            ? Text(
                bookmark.subtitle!,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              )
            : null,
        trailing: IconButton(
          icon: const Icon(Icons.bookmark_remove, color: Colors.red),
          onPressed: () {
            context.read<BookmarksBloc>().add(RemoveBookmark(bookmark.id));
          },
        ),
        onTap: () {
          if (bookmark.isBook && bookmark.bookId != null) {
            context.goNamed(
              'book-detail',
              pathParameters: {'id': '${bookmark.bookId}'},
            );
          } else if (bookmark.isAuthor && bookmark.authorId != null) {
            context.goNamed(
              'author-detail',
              pathParameters: {'id': '${bookmark.authorId}'},
            );
          }
        },
      ),
    );
  }
}
