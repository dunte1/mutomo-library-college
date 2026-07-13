import 'package:flutter_test/flutter_test.dart';
import 'package:ollmchs_library/core/helpers/permission_helper.dart';
import 'package:ollmchs_library/features/auth/models/user_model.dart';

void main() {
  group('PermissionHelper - role checks', () {
    test('isAdmin returns true for admin role', () {
      final user = UserModel(
        id: 1,
        name: 'Admin',
        email: 'admin@test.com',
        roles: ['admin'],
      );
      expect(PermissionHelper.isAdmin(user), isTrue);
    });

    test('isAdmin returns true for super-admin role', () {
      final user = UserModel(
        id: 1,
        name: 'SuperAdmin',
        email: 'sa@test.com',
        roles: ['super-admin'],
      );
      expect(PermissionHelper.isAdmin(user), isTrue);
    });

    test('isAdmin returns false for student role', () {
      final user = UserModel(
        id: 1,
        name: 'Student',
        email: 'student@test.com',
        roles: ['student'],
      );
      expect(PermissionHelper.isAdmin(user), isFalse);
    });

    test('isStudent returns true for student role', () {
      final user = UserModel(
        id: 1,
        name: 'Student',
        email: 's@test.com',
        roles: ['student'],
      );
      expect(PermissionHelper.isStudent(user), isTrue);
    });

    test('isLecturer returns true for lecturer role', () {
      final user = UserModel(
        id: 1,
        name: 'Lecturer',
        email: 'l@test.com',
        roles: ['lecturer'],
      );
      expect(PermissionHelper.isLecturer(user), isTrue);
    });

    test('isStaff returns true for staff-related roles', () {
      final librarian = UserModel(
        id: 1,
        name: 'Lib',
        email: 'lib@test.com',
        roles: ['librarian'],
      );
      expect(PermissionHelper.isStaff(librarian), isTrue);

      final financeOfficer = UserModel(
        id: 2,
        name: 'Finance',
        email: 'f@test.com',
        roles: ['finance-officer'],
      );
      expect(PermissionHelper.isStaff(financeOfficer), isTrue);

      final ictAdmin = UserModel(
        id: 3,
        name: 'ICT',
        email: 'ict@test.com',
        roles: ['ict-admin'],
      );
      expect(PermissionHelper.isStaff(ictAdmin), isTrue);
    });

    test('isStaff returns false for non-staff roles', () {
      final user = UserModel(
        id: 1,
        name: 'Student',
        email: 's@test.com',
        roles: ['student'],
      );
      expect(PermissionHelper.isStaff(user), isFalse);
    });

    test('isStudentOrLecturer returns true for student or lecturer', () {
      final student = UserModel(
        id: 1,
        name: 'S',
        email: 's@test.com',
        roles: ['student'],
      );
      expect(PermissionHelper.isStudentOrLecturer(student), isTrue);

      final lecturer = UserModel(
        id: 2,
        name: 'L',
        email: 'l@test.com',
        roles: ['lecturer'],
      );
      expect(PermissionHelper.isStudentOrLecturer(lecturer), isTrue);

      final admin = UserModel(
        id: 3,
        name: 'A',
        email: 'a@test.com',
        roles: ['admin'],
      );
      expect(PermissionHelper.isStudentOrLecturer(admin), isFalse);
    });
  });

  group('PermissionHelper - permission checks', () {
    test('hasPermission checks user permissions', () {
      final user = UserModel(
        id: 1,
        name: 'Test',
        email: 't@test.com',
        permissions: ['borrow-books', 'view-books'],
      );
      expect(PermissionHelper.hasPermission(user, 'borrow-books'), isTrue);
      expect(PermissionHelper.hasPermission(user, 'return-books'), isFalse);
    });

    test('hasPermission returns false when permissions is null', () {
      final user = UserModel(id: 1, name: 'Test', email: 't@test.com');
      expect(PermissionHelper.hasPermission(user, 'anything'), isFalse);
    });

    test('hasAnyPermission returns true if any permission matches', () {
      final user = UserModel(
        id: 1,
        name: 'Test',
        email: 't@test.com',
        permissions: ['view-books'],
      );
      expect(
        PermissionHelper.hasAnyPermission(
          user,
          ['view-books', 'edit-books'],
        ),
        isTrue,
      );
      expect(
        PermissionHelper.hasAnyPermission(user, ['delete-books']),
        isFalse,
      );
    });

    test('hasAllPermissions returns true only if all match', () {
      final user = UserModel(
        id: 1,
        name: 'Test',
        email: 't@test.com',
        permissions: ['view-books', 'borrow-books'],
      );
      expect(
        PermissionHelper.hasAllPermissions(
          user,
          ['view-books', 'borrow-books'],
        ),
        isTrue,
      );
      expect(
        PermissionHelper.hasAllPermissions(user, ['view-books', 'edit-books']),
        isFalse,
      );
    });
  });

  group('PermissionHelper - feature access', () {
    test('canAccessDashboard returns true for unrestricted users', () {
      final user = UserModel(id: 1, name: 'Test', email: 't@test.com');
      expect(PermissionHelper.canAccessDashboard(user), isTrue);
    });

    test('canAccessDashboard respects view-dashboard permission', () {
      final user = UserModel(
        id: 1,
        name: 'Test',
        email: 't@test.com',
        permissions: ['view-books'],
      );
      expect(PermissionHelper.canAccessDashboard(user), isFalse);

      final user2 = UserModel(
        id: 2,
        name: 'Test2',
        email: 't2@test.com',
        permissions: ['view-dashboard'],
      );
      expect(PermissionHelper.canAccessDashboard(user2), isTrue);
    });

    test('canAccessDashboard returns true for admin regardless of permissions',
        () {
      final admin = UserModel(
        id: 1,
        name: 'Admin',
        email: 'a@test.com',
        roles: ['admin'],
        permissions: [],
      );
      expect(PermissionHelper.canAccessDashboard(admin), isTrue);
    });

    test('canViewBooks returns true for unrestricted users', () {
      final user = UserModel(id: 1, name: 'Test', email: 't@test.com');
      expect(PermissionHelper.canViewBooks(user), isTrue);
    });

    test('canViewBooks respects view-books permission', () {
      final user = UserModel(
        id: 1,
        name: 'Test',
        email: 't@test.com',
        permissions: ['view-circulation'],
      );
      expect(PermissionHelper.canViewBooks(user), isFalse);

      final user2 = UserModel(
        id: 2,
        name: 'Test2',
        email: 't2@test.com',
        permissions: ['view-books'],
      );
      expect(PermissionHelper.canViewBooks(user2), isTrue);
    });

    test('canCreateBooks requires specific permission or admin', () {
      final user = UserModel(
        id: 1,
        name: 'Test',
        email: 't@test.com',
        permissions: ['view-books'],
      );
      expect(PermissionHelper.canCreateBooks(user), isFalse);

      final admin = UserModel(
        id: 2,
        name: 'Admin',
        email: 'a@test.com',
        roles: ['admin'],
      );
      expect(PermissionHelper.canCreateBooks(admin), isTrue);

      final creator = UserModel(
        id: 3,
        name: 'Creator',
        email: 'c@test.com',
        permissions: ['create-books'],
      );
      expect(PermissionHelper.canCreateBooks(creator), isTrue);
    });

    test('canBorrowBooks returns true for unrestricted users', () {
      final user = UserModel(id: 1, name: 'Test', email: 't@test.com');
      expect(PermissionHelper.canBorrowBooks(user), isTrue);
    });

    test('canReturnBooks requires specific permission or admin', () {
      final user = UserModel(
        id: 1,
        name: 'Test',
        email: 't@test.com',
        permissions: ['borrow-books'],
      );
      expect(PermissionHelper.canReturnBooks(user), isFalse);
    });

    test('canAccessDigitalLibrary returns true for unrestricted users', () {
      final user = UserModel(id: 1, name: 'Test', email: 't@test.com');
      expect(PermissionHelper.canAccessDigitalLibrary(user), isTrue);
    });

    test('canAccessNotifications returns true for unrestricted users', () {
      final user = UserModel(id: 1, name: 'Test', email: 't@test.com');
      expect(PermissionHelper.canAccessNotifications(user), isTrue);
    });

    test('canManageSettings requires specific permission or admin', () {
      final user = UserModel(
        id: 1,
        name: 'Test',
        email: 't@test.com',
        permissions: ['view-books'],
      );
      expect(PermissionHelper.canManageSettings(user), isFalse);
    });

    test('canViewReports requires specific permission or admin', () {
      final user = UserModel(
        id: 1,
        name: 'Test',
        email: 't@test.com',
        permissions: ['view-books'],
      );
      expect(PermissionHelper.canViewReports(user), isFalse);
    });

    test('canViewMessages returns true for unrestricted users', () {
      final user = UserModel(id: 1, name: 'Test', email: 't@test.com');
      expect(PermissionHelper.canViewMessages(user), isTrue);
    });

    test('canViewMessages returns true for users with empty permissions list',
        () {
      final user = UserModel(
        id: 1,
        name: 'Test',
        email: 't@test.com',
        permissions: [],
      );
      expect(PermissionHelper.canViewMessages(user), isTrue);
    });

    test('canViewMessages respects view-messages permission', () {
      final user = UserModel(
        id: 1,
        name: 'Test',
        email: 't@test.com',
        permissions: ['view-books'],
      );
      expect(PermissionHelper.canViewMessages(user), isFalse);

      final user2 = UserModel(
        id: 2,
        name: 'Test2',
        email: 't2@test.com',
        permissions: ['view-messages'],
      );
      expect(PermissionHelper.canViewMessages(user2), isTrue);
    });

    test('canViewMessages returns true for admin regardless of permissions',
        () {
      final admin = UserModel(
        id: 1,
        name: 'Admin',
        email: 'a@test.com',
        roles: ['admin'],
        permissions: [],
      );
      expect(PermissionHelper.canViewMessages(admin), isTrue);
    });

    test('canSendMessages requires specific permission or admin', () {
      final user = UserModel(
        id: 1,
        name: 'Test',
        email: 't@test.com',
        permissions: ['view-books'],
      );
      expect(PermissionHelper.canSendMessages(user), isFalse);

      final admin = UserModel(
        id: 2,
        name: 'Admin',
        email: 'a@test.com',
        roles: ['admin'],
      );
      expect(PermissionHelper.canSendMessages(admin), isTrue);

      final sender = UserModel(
        id: 3,
        name: 'Sender',
        email: 's@test.com',
        permissions: ['send-messages'],
      );
      expect(PermissionHelper.canSendMessages(sender), isTrue);
    });

    test('canManageFines requires specific permission', () {
      final user = UserModel(
        id: 1,
        name: 'Test',
        email: 't@test.com',
        permissions: ['view-books'],
      );
      expect(PermissionHelper.canManageFines(user), isFalse);

      final admin = UserModel(
        id: 2,
        name: 'Admin',
        email: 'a@test.com',
        roles: ['admin'],
      );
      expect(PermissionHelper.canManageFines(admin), isTrue);
    });

    test('canDownloadDigitalAssets requires permission or admin', () {
      final user = UserModel(
        id: 1,
        name: 'Test',
        email: 't@test.com',
        permissions: ['view-books'],
      );
      expect(PermissionHelper.canDownloadDigitalAssets(user), isFalse);

      final downloader = UserModel(
        id: 2,
        name: 'DL',
        email: 'dl@test.com',
        permissions: ['download-digital-assets'],
      );
      expect(PermissionHelper.canDownloadDigitalAssets(downloader), isTrue);
    });

    test('canViewMembers requires permission or admin', () {
      final user = UserModel(id: 1, name: 'Test', email: 't@test.com');
      expect(PermissionHelper.canViewMembers(user), isFalse);

      final admin = UserModel(
        id: 2,
        name: 'Admin',
        email: 'a@test.com',
        roles: ['admin'],
      );
      expect(PermissionHelper.canViewMembers(admin), isTrue);
    });

    test('canViewFinancialReports requires permission or admin', () {
      final user = UserModel(
        id: 1,
        name: 'Test',
        email: 't@test.com',
        permissions: ['view-books'],
      );
      expect(PermissionHelper.canViewFinancialReports(user), isFalse);
    });
  });
}
