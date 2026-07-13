import 'package:dio/dio.dart';
import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/errors/error_mapper.dart';
import '../../../core/network/api_client.dart';
import '../../auth/models/user_model.dart';

// Events
abstract class ProfileEvent extends Equatable {
  const ProfileEvent();
  @override
  List<Object?> get props => [];
}

class LoadProfile extends ProfileEvent {
  const LoadProfile();
}

class UpdateProfile extends ProfileEvent {
  final String? name;
  final String? phone;
  final String? email;
  final Map<String, dynamic>? notificationPreferences;
  const UpdateProfile({
    this.name,
    this.phone,
    this.email,
    this.notificationPreferences,
  });
  @override
  List<Object?> get props => [name ?? '', phone ?? '', email ?? ''];
}

class UploadProfilePhoto extends ProfileEvent {
  final String imagePath;
  const UploadProfilePhoto(this.imagePath);
  @override
  List<Object?> get props => [imagePath];
}

class RemoveProfilePhoto extends ProfileEvent {
  const RemoveProfilePhoto();
}

class ChangePassword extends ProfileEvent {
  final String currentPassword;
  final String newPassword;
  const ChangePassword({
    required this.currentPassword,
    required this.newPassword,
  });
  @override
  List<Object?> get props => [currentPassword, newPassword];
}

// States
abstract class ProfileState extends Equatable {
  const ProfileState();
  @override
  List<Object?> get props => [];
}

class ProfileInitial extends ProfileState {}

class ProfileLoading extends ProfileState {}

class ProfileLoaded extends ProfileState {
  final UserModel user;
  final String? message;
  const ProfileLoaded({required this.user, this.message});
  @override
  List<Object?> get props => [user, message];
}

class ProfileError extends ProfileState {
  final String error;
  const ProfileError(this.error);
  @override
  List<Object?> get props => [error];
}

class ProfilePhotoUploading extends ProfileState {
  final UserModel user;
  const ProfilePhotoUploading({required this.user});
  @override
  List<Object?> get props => [user];
}

// Bloc
class ProfileBloc extends Bloc<ProfileEvent, ProfileState> {
  final ApiClient _api;

  ProfileBloc({required this._api}) : super(ProfileInitial()) {
    on<LoadProfile>(_onLoad);
    on<UpdateProfile>(_onUpdate);
    on<UploadProfilePhoto>(_onUploadPhoto);
    on<RemoveProfilePhoto>(_onRemovePhoto);
    on<ChangePassword>(_onChangePassword);
  }

  Future<void> _onLoad(LoadProfile event, Emitter<ProfileState> emit) async {
    emit(ProfileLoading());
    try {
      final response = await _api.get('/v1/profile');
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      emit(ProfileLoaded(user: UserModel.fromJson(data)));
    } catch (e) {
      emit(ProfileError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onUpdate(
    UpdateProfile event,
    Emitter<ProfileState> emit,
  ) async {
    try {
      final body = <String, dynamic>{};
      if (event.name != null) body['name'] = event.name;
      if (event.phone != null) body['phone'] = event.phone;
      if (event.email != null) body['email'] = event.email;
      if (event.notificationPreferences != null) {
        body['notification_preferences'] = event.notificationPreferences;
      }

      final response = await _api.put('/v1/profile', data: body);
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      emit(
        ProfileLoaded(
          user: UserModel.fromJson(data),
          message: 'Profile updated',
        ),
      );
    } catch (e) {
      emit(ProfileError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onUploadPhoto(
    UploadProfilePhoto event,
    Emitter<ProfileState> emit,
  ) async {
    final current = state;
    if (current is ProfileLoaded) {
      emit(ProfilePhotoUploading(user: current.user));
    }
    try {
      final formData = FormData.fromMap({
        'avatar': await MultipartFile.fromFile(event.imagePath),
      });
      await _api.post('/v1/profile/avatar', data: formData);
      // Reload full profile — avatar upload response only contains avatar URL
      final response = await _api.get('/v1/profile');
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      emit(
        ProfileLoaded(user: UserModel.fromJson(data), message: 'Photo updated'),
      );
    } catch (e) {
      emit(ProfileError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onRemovePhoto(
    RemoveProfilePhoto event,
    Emitter<ProfileState> emit,
  ) async {
    final current = state;
    if (current is ProfileLoaded) {
      emit(ProfilePhotoUploading(user: current.user));
    }
    try {
      await _api.delete('/v1/profile/avatar');
      final response = await _api.get('/v1/profile');
      final data =
          response.data['data'] as Map<String, dynamic>? ??
          response.data as Map<String, dynamic>;
      emit(
        ProfileLoaded(user: UserModel.fromJson(data), message: 'Photo removed'),
      );
    } catch (e) {
      emit(ProfileError(ErrorMapper.map(e)));
    }
  }

  Future<void> _onChangePassword(
    ChangePassword event,
    Emitter<ProfileState> emit,
  ) async {
    try {
      await _api.post(
        '/v1/auth/change-password',
        data: {
          'current_password': event.currentPassword,
          'new_password': event.newPassword,
          'new_password_confirmation': event.newPassword,
        },
      );
      final current = state;
      if (current is ProfileLoaded) {
        emit(ProfileLoaded(user: current.user, message: 'Password changed'));
      }
    } catch (e) {
      emit(ProfileError(ErrorMapper.map(e)));
    }
  }
}
