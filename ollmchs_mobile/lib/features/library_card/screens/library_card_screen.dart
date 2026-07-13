import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:intl/intl.dart';
import 'package:qr_flutter/qr_flutter.dart';
import 'package:share_plus/share_plus.dart';
import '../bloc/library_card_bloc.dart';
import '../../../core/widgets/skeleton.dart';

class LibraryCardScreen extends StatefulWidget {
  const LibraryCardScreen({super.key});

  @override
  State<LibraryCardScreen> createState() => _LibraryCardScreenState();
}

class _LibraryCardScreenState extends State<LibraryCardScreen> {
  @override
  void initState() {
    super.initState();
    context.read<LibraryCardBloc>().add(const LoadLibraryCard());
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Library Card'),
        actions: [
          BlocBuilder<LibraryCardBloc, LibraryCardState>(
            buildWhen: (previous, current) => current is LibraryCardLoaded,
            builder: (context, state) {
              if (state is LibraryCardLoaded) {
                return Row(
                  children: [
                    IconButton(
                      icon: state.pdfDownloading
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Icon(Icons.download),
                      tooltip: 'Download PDF',
                      onPressed: state.pdfDownloading
                          ? null
                          : () => context
                              .read<LibraryCardBloc>()
                              .add(const DownloadPdf()),
                    ),
                    IconButton(
                      icon: state.sharing
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Icon(Icons.share),
                      tooltip: 'Share',
                      onPressed: state.sharing
                          ? null
                          : () => context
                              .read<LibraryCardBloc>()
                              .add(const ShareLibraryCard()),
                    ),
                  ],
                );
              }
              return const SizedBox.shrink();
            },
          ),
        ],
      ),
      body: BlocBuilder<LibraryCardBloc, LibraryCardState>(
        builder: (context, state) {
          if (state is LibraryCardLoading) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [
                  Skeleton(height: 220, borderRadius: 16),
                  SizedBox(height: 24),
                  Skeleton(width: 150, height: 16),
                  SizedBox(height: 8),
                  Skeleton(height: 14),
                ],
              ),
            );
          }
          if (state is LibraryCardError) {
            return _buildErrorState(theme, state.error);
          }
          if (state is LibraryCardLoaded) {
            return _buildLoadedState(theme, state);
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }

  Widget _buildErrorState(ThemeData theme, String error) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.error_outline, size: 64, color: theme.colorScheme.error),
            const SizedBox(height: 16),
            Text(
              'Could not load library card',
              style: theme.textTheme.titleMedium,
            ),
            const SizedBox(height: 8),
            Text(
              error,
              textAlign: TextAlign.center,
              style: TextStyle(color: theme.colorScheme.onSurfaceVariant),
            ),
            const SizedBox(height: 24),
            FilledButton.tonal(
              onPressed: () =>
                  context.read<LibraryCardBloc>().add(const LoadLibraryCard()),
              child: const Text('Retry'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLoadedState(ThemeData theme, LibraryCardLoaded state) {
    final card = state.card;
    final isExpired = card.isExpired || card.isMemberExpired;
    final isSuspended = card.isMemberSuspended;
    final isInactive = card.isMemberInactive;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          _buildStatusBanner(theme, card, isExpired, isSuspended, isInactive),
          const SizedBox(height: 16),
          _buildLibraryCard(theme, card, isExpired || isSuspended || isInactive),
          const SizedBox(height: 16),
          _buildMemberInfo(theme, card),
          if (state.pdfUrl != null) ...[
            const SizedBox(height: 12),
            _buildPdfDownloadedBanner(theme, state.pdfUrl!),
          ],
        ],
      ),
    );
  }

  Widget _buildStatusBanner(
    ThemeData theme,
    dynamic card,
    bool isExpired,
    bool isSuspended,
    bool isInactive,
  ) {
    if (card.isActive && card.isMemberActive) return const SizedBox.shrink();

    IconData icon;
    String title;
    String subtitle;
    Color color;

    if (isExpired) {
      icon = Icons.timer_off;
      title = 'Membership Expired';
      subtitle = 'Please renew your membership to continue using library services.';
      color = theme.colorScheme.error;
    } else if (isSuspended) {
      icon = Icons.block;
      title = 'Membership Suspended';
      subtitle = 'Your membership has been suspended. Please contact the library administrator.';
      color = Colors.orange;
    } else if (isInactive) {
      icon = Icons.pause_circle;
      title = 'Membership Inactive';
      subtitle = 'Your membership is inactive. Please visit the library to activate it.';
      color = Colors.orange;
    } else {
      icon = Icons.info_outline;
      title = 'Card ${card.status}';
      subtitle = 'Your library card is ${card.status}. Please contact the library.';
      color = Colors.orange;
    }

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Row(
        children: [
          Icon(icon, color: color, size: 28),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title,
                    style: TextStyle(
                        fontWeight: FontWeight.w600, color: color)),
                const SizedBox(height: 2),
                Text(subtitle,
                    style: TextStyle(fontSize: 12, color: color.withValues(alpha: 0.8))),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLibraryCard(
    ThemeData theme,
    dynamic card,
    bool isDisabled,
  ) {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          gradient: LinearGradient(
            colors: isDisabled
                ? [Colors.grey.shade400, Colors.grey.shade600]
                : [
                    theme.colorScheme.primary,
                    theme.colorScheme.primaryContainer,
                  ],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: Column(
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(
                    Icons.local_library,
                    color: Colors.white,
                    size: 32,
                  ),
                ),
                const SizedBox(width: 12),
                Text(
                  'OLLMCHS Library',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),
            CircleAvatar(
              radius: 32,
              backgroundColor: Colors.white.withValues(alpha: 0.3),
              backgroundImage: card.memberPhoto != null
                  ? NetworkImage(card.memberPhoto!)
                  : null,
              child: card.memberPhoto == null
                  ? const Icon(Icons.person, size: 32, color: Colors.white)
                  : null,
            ),
            const SizedBox(height: 12),
            Text(
              card.memberName ?? 'Member',
              style: const TextStyle(
                color: Colors.white,
                fontSize: 18,
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              'ID: ${card.memberId ?? ''}',
              style: TextStyle(
                color: Colors.white.withValues(alpha: 0.8),
                fontSize: 14,
              ),
            ),
            const SizedBox(height: 16),
            if (card.qrCodeSvg != null)
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: card.qrCodeSvg!.startsWith('http')
                    ? CachedNetworkImage(
                        imageUrl: card.qrCodeSvg!,
                        height: 100,
                        width: 100,
                        placeholder: (_, __) =>
                            Container(color: Colors.grey[200]),
                        errorWidget: (_, __, ___) =>
                            const Icon(Icons.broken_image, color: Colors.grey),
                      )
                    : SvgPicture.string(card.qrCodeSvg!, height: 100, width: 100),
              )
            else
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: QrImageView(
                  data: card.cardNumber,
                  version: QrVersions.auto,
                  size: 100,
                  backgroundColor: Colors.white,
                ),
              ),
            const SizedBox(height: 16),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                card.cardNumber,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 16,
                  letterSpacing: 2,
                ),
              ),
            ),
            const SizedBox(height: 12),
            Text(
              card.department != null ? card.department! : '',
              style: TextStyle(
                color: Colors.white.withValues(alpha: 0.7),
                fontSize: 13,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              card.expiresAt != null
                  ? 'Expires: ${DateFormat('MMM d, y').format(card.expiresAt!)}'
                  : 'No expiry date',
              style: TextStyle(
                color: Colors.white.withValues(alpha: 0.7),
                fontSize: 12,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMemberInfo(ThemeData theme, dynamic card) {
    return Card(
      child: Column(
        children: [
          if (card.membershipType != null)
            ListTile(
              leading: const Icon(Icons.badge_outlined),
              title: const Text('Membership Type'),
              subtitle: Text(_formatMembershipType(card.membershipType!)),
            ),
          if (card.memberEmail != null)
            ListTile(
              leading: const Icon(Icons.email_outlined),
              title: const Text('Email'),
              subtitle: Text(card.memberEmail!),
            ),
          if (card.memberPhone != null)
            ListTile(
              leading: const Icon(Icons.phone_outlined),
              title: const Text('Phone'),
              subtitle: Text(card.memberPhone!),
            ),
          if (card.memberStatus != null)
            ListTile(
              leading: const Icon(Icons.info_outline),
              title: const Text('Status'),
              subtitle: Text(_formatStatus(card.memberStatus!)),
              trailing: _statusBadge(card.memberStatus!),
            ),
        ],
      ),
    );
  }

  Widget _buildPdfDownloadedBanner(ThemeData theme, String pdfUrl) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.green.shade50,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.green.shade200),
      ),
      child: Row(
        children: [
          const Icon(Icons.check_circle, color: Colors.green),
          const SizedBox(width: 12),
          const Expanded(
            child: Text('PDF downloaded successfully',
                style: TextStyle(color: Colors.green)),
          ),
          TextButton(
            onPressed: () => SharePlus.instance.share(
              ShareParams(files: [XFile(pdfUrl)], subject: 'Library Card'),
            ),
            child: const Text('Share'),
          ),
        ],
      ),
    );
  }

  Widget _statusBadge(String status) {
    final Color color;
    switch (status) {
      case 'active':
        color = Colors.green;
      case 'suspended':
        color = Colors.orange;
      case 'expired':
        color = Colors.red;
      case 'inactive':
        color = Colors.grey;
      default:
        color = Colors.grey;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        status[0].toUpperCase() + status.substring(1),
        style: TextStyle(color: color, fontSize: 12, fontWeight: FontWeight.w600),
      ),
    );
  }

  String _formatMembershipType(String type) {
    switch (type) {
      case 'student':
        return 'Student';
      case 'teacher':
        return 'Teacher / Lecturer';
      case 'staff':
        return 'Staff';
      case 'external':
        return 'External Member';
      default:
        return type;
    }
  }

  String _formatStatus(String status) {
    switch (status) {
      case 'active':
        return 'Active';
      case 'suspended':
        return 'Suspended';
      case 'expired':
        return 'Expired';
      case 'inactive':
        return 'Inactive';
      default:
        return status;
    }
  }
}
