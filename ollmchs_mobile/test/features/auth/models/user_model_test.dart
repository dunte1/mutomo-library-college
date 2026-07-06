import 'package:flutter_test/flutter_test.dart';
import 'package:ollmchs_library/features/auth/models/user_model.dart';

void main() {
  group('UserModel', () {
    test('fromJson creates valid UserModel with all fields', () {
      final json = {
        'id': 1,
        'name': 'John Doe',
        'email': 'john@example.com',
        'phone': '+254712345678',
        'avatar': 'http://example.com/avatar.jpg',
        'roles': ['student'],
        'permissions': ['borrow-books', 'view-books'],
        'department': {'id': 1, 'name': 'Computer Science'},
        'program': {'id': 1, 'name': 'BSc. CS'},
        'is_active': true,
        'two_factor_enabled': false,
        'email_verified_at': '2024-01-01T00:00:00Z',
        'last_login_at': '2024-06-01T00:00:00Z',
        'notification_preferences': {'email': true, 'sms': false},
        'member': {'id': 1, 'library_card': 'LC001'},
        'subscription': {'id': 1, 'plan': 'premium'},
      };

      final user = UserModel.fromJson(json);
      expect(user.id, equals(1));
      expect(user.name, equals('John Doe'));
      expect(user.email, equals('john@example.com'));
      expect(user.phone, equals('+254712345678'));
      expect(user.avatar, equals('http://example.com/avatar.jpg'));
      expect(user.roles, contains('student'));
      expect(user.permissions, contains('borrow-books'));
      expect(user.department, equals('Computer Science'));
      expect(user.program, equals('BSc. CS'));
      expect(user.isActive, isTrue);
      expect(user.twoFactorEnabled, isFalse);
      expect(user.emailVerifiedAt, isNotNull);
      expect(user.lastLoginAt, isNotNull);
      expect(user.notificationPreferences, isNotNull);
      expect(user.notificationPreferences!['email'], isTrue);
      expect(user.member, isNotNull);
      expect(user.member!['library_card'], equals('LC001'));
      expect(user.subscription, isNotNull);
      expect(user.subscription!['plan'], equals('premium'));
    });

    test('fromJson handles minimal fields', () {
      final json = {
        'id': 1,
        'name': 'Jane',
        'email': 'jane@test.com',
      };
      final user = UserModel.fromJson(json);
      expect(user.id, equals(1));
      expect(user.name, equals('Jane'));
      expect(user.phone, isNull);
      expect(user.roles, isNull);
      expect(user.permissions, isNull);
      expect(user.department, isNull);
      expect(user.program, isNull);
      expect(user.isActive, isTrue);
      expect(user.twoFactorEnabled, isFalse);
      expect(user.member, isNull);
    });

    test('fromJson handles null is_active and two_factor_enabled', () {
      final json = {
        'id': 1,
        'name': 'Test',
        'email': 't@test.com',
        'is_active': null,
        'two_factor_enabled': null,
      };
      final user = UserModel.fromJson(json);
      expect(user.isActive, isTrue);
      expect(user.twoFactorEnabled, isFalse);
    });

    test('fromJson handles is_active false', () {
      final json = {
        'id': 1,
        'name': 'Inactive',
        'email': 'i@test.com',
        'is_active': false,
      };
      final user = UserModel.fromJson(json);
      expect(user.isActive, isFalse);
    });

    test('toJson returns expected fields without sensitive data', () {
      final user = UserModel(
        id: 1,
        name: 'Test',
        email: 'test@test.com',
        phone: '+254700000000',
        avatar: 'http://example.com/avatar.jpg',
        roles: ['admin'],
        isActive: true,
      );
      final json = user.toJson();
      expect(json['id'], equals(1));
      expect(json['name'], equals('Test'));
      expect(json['email'], equals('test@test.com'));
      expect(json['phone'], equals('+254700000000'));
      expect(json['avatar'], equals('http://example.com/avatar.jpg'));
      expect(json['roles'], contains('admin'));
      expect(json['is_active'], isTrue);
      expect(json.containsKey('password'), isFalse);
      expect(json.containsKey('permissions'), isFalse);
    });

    test('full JSON roundtrip', () {
      final json = {
        'id': 5,
        'name': 'Alice',
        'email': 'alice@test.com',
        'phone': '+254711111111',
        'avatar': 'http://example.com/alice.jpg',
        'roles': ['student', 'assistant'],
        'permissions': ['view-books'],
        'department': {'id': 2, 'name': 'Mathematics'},
        'program': {'id': 3, 'name': 'BSc. Math'},
        'is_active': true,
        'two_factor_enabled': true,
        'email_verified_at': '2024-03-15T12:00:00Z',
        'last_login_at': '2024-07-01T08:30:00Z',
        'notification_preferences': null,
        'member': null,
        'subscription': null,
      };

      final user = UserModel.fromJson(json);
      expect(user.id, equals(5));
      expect(user.name, equals('Alice'));
      expect(user.roles, contains('student'));
      expect(user.department, equals('Mathematics'));
      expect(user.twoFactorEnabled, isTrue);

      // Verify key fields survive toJson
      final out = user.toJson();
      expect(out['id'], equals(5));
      expect(out['name'], equals('Alice'));
      expect(out['email'], equals('alice@test.com'));
    });
  });
}
