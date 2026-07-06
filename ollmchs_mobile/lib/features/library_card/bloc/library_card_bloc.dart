import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/network/api_client.dart';
import '../models/library_card_model.dart';

// Events
abstract class LibraryCardEvent extends Equatable {
  const LibraryCardEvent();
  @override
  List<Object?> get props => [];
}

class LoadLibraryCard extends LibraryCardEvent {
  const LoadLibraryCard();
}

class DownloadLibraryCardPdf extends LibraryCardEvent {
  const DownloadLibraryCardPdf();
}

// States
abstract class LibraryCardState extends Equatable {
  const LibraryCardState();
  @override
  List<Object?> get props => [];
}

class LibraryCardInitial extends LibraryCardState {}

class LibraryCardLoading extends LibraryCardState {}

class LibraryCardLoaded extends LibraryCardState {
  final LibraryCardModel card;
  final String? qrCodeUrl;
  final String? pdfUrl;
  final String? barcodeUrl;

  const LibraryCardLoaded({
    required this.card,
    this.qrCodeUrl,
    this.pdfUrl,
    this.barcodeUrl,
  });
  @override
  List<Object?> get props => [card, qrCodeUrl, pdfUrl, barcodeUrl];
}

class LibraryCardError extends LibraryCardState {
  final String error;
  const LibraryCardError(this.error);
  @override
  List<Object?> get props => [error];
}

// Bloc
class LibraryCardBloc extends Bloc<LibraryCardEvent, LibraryCardState> {
  final ApiClient _api;

  LibraryCardBloc({required this._api}) : super(LibraryCardInitial()) {
    on<LoadLibraryCard>(_onLoad);
    on<DownloadLibraryCardPdf>(_onDownloadPdf);
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
      final qrResponse = await _api.get('/v1/library-card/qr-code');
      final qrSvg =
          qrResponse.data['data']?['qr_code_svg'] as String? ??
          qrResponse.data['qr_code_svg'] as String?;

      emit(LibraryCardLoaded(card: card, qrCodeUrl: qrSvg));
    } catch (e) {
      emit(LibraryCardError('Failed to load library card: ${e.toString()}'));
    }
  }

  Future<void> _onDownloadPdf(
    DownloadLibraryCardPdf event,
    Emitter<LibraryCardState> emit,
  ) async {
    try {
      final response = await _api.get('/v1/library-card/pdf');
      final pdfUrl =
          response.data['pdf_url'] as String? ??
          (response.requestOptions.uri.toString());
      final current = state;
      if (current is LibraryCardLoaded) {
        emit(
          LibraryCardLoaded(
            card: current.card,
            qrCodeUrl: current.qrCodeUrl,
            pdfUrl: pdfUrl,
          ),
        );
      }
    } catch (_) {}
  }
}
