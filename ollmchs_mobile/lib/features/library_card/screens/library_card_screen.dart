import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:intl/intl.dart';
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
      appBar: AppBar(title: const Text('Library Card')),
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
            return Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    Icons.error_outline,
                    size: 48,
                    color: theme.colorScheme.error,
                  ),
                  const SizedBox(height: 16),
                  Text(state.error),
                  const SizedBox(height: 16),
                  FilledButton.tonal(
                    onPressed: () => context.read<LibraryCardBloc>().add(
                      const LoadLibraryCard(),
                    ),
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }
          if (state is LibraryCardLoaded) {
            final card = state.card;
            return SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  // Card Design
                  Card(
                    elevation: 4,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(24),
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(16),
                        gradient: LinearGradient(
                          colors: [
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

                          // Member photo
                          CircleAvatar(
                            radius: 32,
                            backgroundColor: Colors.white.withValues(
                              alpha: 0.3,
                            ),
                            backgroundImage: card.memberPhoto != null
                                ? NetworkImage(card.memberPhoto!)
                                : null,
                            child: card.memberPhoto == null
                                ? const Icon(
                                    Icons.person,
                                    size: 32,
                                    color: Colors.white,
                                  )
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
                            'ID: ${card.memberId}',
                            style: TextStyle(
                              color: Colors.white.withValues(alpha: 0.8),
                              fontSize: 14,
                            ),
                          ),

                          const SizedBox(height: 16),

                          // QR Code
                          if (state.qrCodeUrl != null)
                            Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: state.qrCodeUrl!.startsWith('http')
                                  ? CachedNetworkImage(
                                      imageUrl: state.qrCodeUrl!,
                                      height: 100,
                                      width: 100,
                                      placeholder: (_, __) => Container(color: Colors.grey[200]),
                                      errorWidget: (_, __, ___) => Icon(Icons.broken_image, color: Colors.grey),
                                    )
                                  : SvgPicture.string(
                                      state.qrCodeUrl!,
                                      height: 100,
                                      width: 100,
                                    ),
                            ),
                          const SizedBox(height: 16),

                          // Card Number
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.symmetric(
                              vertical: 8,
                              horizontal: 12,
                            ),
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
                            'Expires: ${card.expiresAt != null ? DateFormat('MMM d, y').format(card.expiresAt!) : 'N/A'}',
                            style: TextStyle(
                              color: Colors.white.withValues(alpha: 0.7),
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Member info
                  Card(
                    child: Column(
                      children: [
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
                      ],
                    ),
                  ),
                ],
              ),
            );
          }
          return const SizedBox.shrink();
        },
      ),
    );
  }
}
