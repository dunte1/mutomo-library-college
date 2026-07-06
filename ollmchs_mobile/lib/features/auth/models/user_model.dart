class UserModel {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? avatar;
  final List<String>? roles;
  final List<String>? permissions;
  final String? department;
  final String? program;
  final bool isActive;
  final bool twoFactorEnabled;
  final String? emailVerifiedAt;
  final String? lastLoginAt;
  final Map<String, dynamic>? notificationPreferences;
  final Map<String, dynamic>? member;
  final Map<String, dynamic>? subscription;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.avatar,
    this.roles,
    this.permissions,
    this.department,
    this.program,
    this.isActive = true,
    this.twoFactorEnabled = false,
    this.emailVerifiedAt,
    this.lastLoginAt,
    this.notificationPreferences,
    this.member,
    this.subscription,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] as int,
      name: json['name'] as String,
      email: json['email'] as String,
      phone: json['phone'] as String?,
      avatar: json['avatar'] as String?,
      roles: (json['roles'] as List?)?.map((e) => e as String).toList(),
      permissions: (json['permissions'] as List?)
          ?.map((e) => e as String)
          .toList(),
      department: json['department'] != null
          ? (json['department'] as Map)['name'] as String?
          : null,
      program: json['program'] != null
          ? (json['program'] as Map)['name'] as String?
          : null,
      isActive: json['is_active'] as bool? ?? true,
      twoFactorEnabled: json['two_factor_enabled'] as bool? ?? false,
      emailVerifiedAt: json['email_verified_at'] as String?,
      lastLoginAt: json['last_login_at'] as String?,
      notificationPreferences:
          json['notification_preferences'] as Map<String, dynamic>?,
      member: json['member'] as Map<String, dynamic>?,
      subscription: json['subscription'] as Map<String, dynamic>?,
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'name': name,
    'email': email,
    'phone': phone,
    'avatar': avatar,
    'roles': roles,
    'is_active': isActive,
  };
}
