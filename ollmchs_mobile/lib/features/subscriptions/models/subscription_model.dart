import '../../../core/utils/type_parsers.dart';

class SubscriptionPlanModel {
  final int id;
  final String name;
  final String? description;
  final double price;
  final String? currency;
  final int durationDays;
  final List<String> features;
  final bool isPopular;

  SubscriptionPlanModel({
    required this.id,
    required this.name,
    this.description,
    required this.price,
    this.currency,
    this.durationDays = 30,
    this.features = const [],
    this.isPopular = false,
  });

  factory SubscriptionPlanModel.fromJson(Map<String, dynamic> json) {
    return SubscriptionPlanModel(
      id: parseInt(json['id'], fieldName: 'id'),
      name: json['name'] as String? ?? '',
      description: json['description'] as String?,
      price: (json['price'] as num).toDouble(),
      currency: json['currency'] as String? ?? 'KES',
      durationDays: parseIntOrNull(json['duration_days']) ?? 30,
      features:
          (json['features'] as List<dynamic>?)
              ?.map((e) => e.toString())
              .toList() ??
          [],
      isPopular: json['is_popular'] as bool? ?? false,
    );
  }
}

class UserSubscriptionModel {
  final int id;
  final int planId;
  final String planName;
  final String status;
  final DateTime startAt;
  final DateTime? endAt;
  final bool autoRenew;
  final String? paymentMethod;

  UserSubscriptionModel({
    required this.id,
    required this.planId,
    required this.planName,
    this.status = 'active',
    required this.startAt,
    this.endAt,
    this.autoRenew = false,
    this.paymentMethod,
  });

  bool get isActive => status == 'active';
  bool get isExpired => status == 'expired';
  bool get isExpiringSoon =>
      endAt != null &&
      endAt!.difference(DateTime.now()).inDays <= 7 &&
      endAt!.isAfter(DateTime.now());

  factory UserSubscriptionModel.fromJson(Map<String, dynamic> json) {
    final plan = json['plan'] as Map<String, dynamic>?;
    return UserSubscriptionModel(
      id: parseInt(json['id'], fieldName: 'id'),
      planId: parseIntOrNull(plan?['id']) ?? parseIntOrNull(json['plan_id']) ?? 0,
      planName: plan?['name'] as String? ?? json['plan_name'] as String? ?? '',
      status: json['status'] as String? ?? 'active',
      startAt: DateTime.parse(json['start_at'] as String),
      endAt: json['end_at'] != null
          ? DateTime.tryParse(json['end_at'] as String)
          : null,
      autoRenew: json['auto_renew'] as bool? ?? false,
      paymentMethod: json['payment_method'] as String?,
    );
  }
}
