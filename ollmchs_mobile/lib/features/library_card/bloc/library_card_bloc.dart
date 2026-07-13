import 'dart:io';
import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:path_provider/path_provider.dart';
import 'package:share_plus/share_plus.dart';
import '../../../core/errors/error_mapper.dart';
import '../../../core/network/api_client.dart';
import '../models/library_card_model.dart';

abstract class LibraryCardEvent extends Equatable {
  const LibraryCardEvent();
  @override
  List<Object?> get props => [];
}

class LoadLibraryCard extends LibraryCardEvent {
  const LoadLibraryCard();
}

class DownloadPdf extends LibraryCardEvent {
  const DownloadPdf();
}

class ShareLibraryCard extends LibraryCardEvent {
  const ShareLibraryCard();
}

abstract class LibraryCardState extends Equatable {
  const LibraryCardState();
  @override
  List<Object?> get props => [];
}

class LibraryCardInitial extends LibraryCardState {}

class LibraryCardLoading extends LibraryCardState {}

class LibraryCardLoaded extends LibraryCardState {
  final LibraryCardModel card;
  final String? pdfUrl;
  final bool pdfDownloading;
  final bool sharing;

  const LibraryCardLoaded({
    required this.card,
    this.pdfUrl,
    this.pdfDownloading = false,
    this.sharing = false,
  });
  @override
  List<Object?> get props => [card, pdfUrl, pdfDownloading, sharing];
}

class LibraryCardError extends LibraryCardState {
  final String error;
  const LibraryCardError(this.error);
  @override
  List<Object?> get props => [error];
}

class LibraryCardBloc extends Bloc<LibraryCardEvent, LibraryCardState> {
  final ApiClient _api;

  LibraryCardBloc({required this._api}) : super(LibraryCardInitial()) {
    on<LoadLibraryCard>(_onLoad);
    on<DownloadPdf>(_onDownloadPdf);
    on<ShareLibraryCard>(_onShare);
  }

  Future<void> _onLoad(
    LoadLibraryCard event,
    Emitter<LibraryCardState> emit,
  ) async {
    emit(LibraryCardLoading());
    try {
      final response = await _api.get('/v1/library-card');
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      final card = LibraryCardModel.fromJson(data);

      emit(LibraryCardLoaded(card: card));
    } catch (e) {
      final message = ErrorMapper.map(e);
      if (message.contains('not found')) {
        emit(const LibraryCardError(
          'No library card found. Please visit the library to get your card issued.',
        ));
      } else {
        emit(LibraryCardError(message));
      }
    }
  }

  Future<void> _onDownloadPdf(
    DownloadPdf event,
    Emitter<LibraryCardState> emit,
  ) async {
    final current = state;
    if (current is! LibraryCardLoaded) return;

    emit(LibraryCardLoaded(
      card: current.card,
      pdfDownloading: true,
      sharing: current.sharing,
    ));

    try {
      final dir = await getTemporaryDirectory();
      final filePath = '${dir.path}/library-card-${current.card.cardNumber}.pdf';

      await _api.download(
        '/api/v1/library-card/pdf',
        filePath,
      );

      emit(LibraryCardLoaded(
        card: current.card,
        pdfUrl: filePath,
        pdfDownloading: false,
        sharing: current.sharing,
      ));
    } catch (_) {
      emit(LibraryCardLoaded(
        card: current.card,
        pdfDownloading: false,
        sharing: current.sharing,
      ));
    }
  }

  Future<void> _onShare(
    ShareLibraryCard event,
    Emitter<LibraryCardState> emit,
  ) async {
    final current = state;
    if (current is! LibraryCardLoaded) return;

    emit(LibraryCardLoaded(
      card: current.card,
      pdfUrl: current.pdfUrl,
      pdfDownloading: current.pdfDownloading,
      sharing: true,
    ));

    final subject = 'Library Card - ${current.card.memberName ?? ''}';
    try {
      final dir = await getTemporaryDirectory();
      final filePath = '${dir.path}/library-card-${current.card.cardNumber}.pdf';

      bool fileExists = File(filePath).existsSync();
      if (!fileExists) {
        await _api.download(
          '/api/v1/library-card/pdf',
          filePath,
        );
        fileExists = File(filePath).existsSync();
      }

      if (fileExists) {
        await SharePlus.instance.share(
          ShareParams(files: [XFile(filePath)], subject: subject),
        );
      } else {
        final baseUrl = _api.baseUrl;
        await SharePlus.instance.share(
          ShareParams(uri: Uri.parse('${baseUrl}api/v1/library-card/pdf'), subject: subject),
        );
      }
    } catch (_) {
      // Fallback: share the web URL
      try {
        final baseUrl = _api.baseUrl;
        await SharePlus.instance.share(
          ShareParams(uri: Uri.parse('${baseUrl}api/v1/library-card/pdf'), subject: subject),
        );
      } catch (_) {}
    }

    emit(LibraryCardLoaded(
      card: current.card,
      pdfUrl: current.pdfUrl,
      pdfDownloading: current.pdfDownloading,
      sharing: false,
    ));
  }
}
