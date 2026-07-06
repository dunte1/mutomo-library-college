import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../core/network/api_client.dart';
import '../models/subscription_model.dart';

// Events
abstract class SubscriptionsEvent extends Equatable {
  const SubscriptionsEvent();
  @override
  List<Object?> get props => [];
}

class LoadSubscriptionPlans extends SubscriptionsEvent {
  const LoadSubscriptionPlans();
}

class LoadMySubscription extends SubscriptionsEvent {
  const LoadMySubscription();
}

class SubscribeToPlan extends SubscriptionsEvent {
  final int planId;
  final String? paymentMethod;
  final String? phone;
  const SubscribeToPlan({required this.planId, this.paymentMethod, this.phone});
  @override
  List<Object?> get props => [planId, paymentMethod ?? '', phone ?? ''];
}

class CancelSubscription extends SubscriptionsEvent {
  final int subscriptionId;
  const CancelSubscription(this.subscriptionId);
  @override
  List<Object?> get props => [subscriptionId];
}

// States
abstract class SubscriptionsState extends Equatable {
  const SubscriptionsState();
  @override
  List<Object?> get props => [];
}

class SubscriptionsInitial extends SubscriptionsState {}

class SubscriptionsLoading extends SubscriptionsState {}

class SubscriptionsLoaded extends SubscriptionsState {
  final List<SubscriptionPlanModel> plans;
  final UserSubscriptionModel? mySubscription;
  final String? message;

  const SubscriptionsLoaded({
    this.plans = const [],
    this.mySubscription,
    this.message,
  });
  @override
  List<Object?> get props => [plans, mySubscription, message];
}

class SubscriptionsError extends SubscriptionsState {
  final String error;
  const SubscriptionsError(this.error);
  @override
  List<Object?> get props => [error];
}

// Bloc
class SubscriptionsBloc extends Bloc<SubscriptionsEvent, SubscriptionsState> {
  final ApiClient _api;

  SubscriptionsBloc({required ApiClient api})
    : _api = api,
      super(SubscriptionsInitial()) {
    on<LoadSubscriptionPlans>(_onLoadPlans);
    on<LoadMySubscription>(_onLoadMy);
    on<SubscribeToPlan>(_onSubscribe);
    on<CancelSubscription>(_onCancel);
  }

  Future<void> _onLoadPlans(
    LoadSubscriptionPlans event,
    Emitter<SubscriptionsState> emit,
  ) async {
    emit(SubscriptionsLoading());
    try {
      final response = await _api.get('/v1/subscription-plans');
      final data = response.data['data'] as List<dynamic>? ?? [];
      final plans = data
          .map((e) => SubscriptionPlanModel.fromJson(e as Map<String, dynamic>))
          .toList();
      emit(SubscriptionsLoaded(plans: plans));
    } catch (e) {
      emit(SubscriptionsError('Failed to load plans: ${e.toString()}'));
    }
  }

  Future<void> _onLoadMy(
    LoadMySubscription event,
    Emitter<SubscriptionsState> emit,
  ) async {
    emit(SubscriptionsLoading());
    try {
      final response = await _api.get('/v1/subscriptions/my');
      final data = response.data['data'] as Map<String, dynamic>?;
      UserSubscriptionModel? sub;
      if (data != null) sub = UserSubscriptionModel.fromJson(data);
      emit(SubscriptionsLoaded(mySubscription: sub));
    } catch (e) {
      emit(SubscriptionsError('Failed to load subscription: ${e.toString()}'));
    }
  }

  Future<void> _onSubscribe(
    SubscribeToPlan event,
    Emitter<SubscriptionsState> emit,
  ) async {
    try {
      await _api.post(
        '/v1/subscriptions',
        data: {
          'plan_id': event.planId,
          if (event.paymentMethod != null)
            'payment_method': event.paymentMethod,
          if (event.phone != null) 'phone': event.phone,
        },
      );
      emit(
        SubscriptionsLoaded(
          message:
              'Subscribed successfully. ${event.paymentMethod == 'mpesa'
                  ? 'Check your phone for the M-Pesa STK push prompt.'
                  : event.paymentMethod == 'stripe'
                  ? 'Redirecting to secure checkout...'
                  : ''}',
        ),
      );
    } catch (e) {
      emit(SubscriptionsError('Subscription failed: ${e.toString()}'));
    }
  }

  Future<void> _onCancel(
    CancelSubscription event,
    Emitter<SubscriptionsState> emit,
  ) async {
    try {
      await _api.post('/v1/subscriptions/${event.subscriptionId}/cancel');
      add(const LoadMySubscription());
      emit(SubscriptionsLoaded(message: 'Subscription cancelled'));
    } catch (e) {
      emit(SubscriptionsError('Cancellation failed: ${e.toString()}'));
    }
  }
}
