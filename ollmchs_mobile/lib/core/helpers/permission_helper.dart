import '../../features/auth/models/user_model.dart';

class PermissionHelper {
  PermissionHelper._();

  static const String roleStudent = 'student';
  static const String roleLecturer = 'lecturer';
  static const String roleStaff = 'staff';
  static const String roleAdmin = 'admin';
  static const String roleLibrarian = 'librarian';
  static const String roleSuperAdmin = 'super-admin';
  static const String roleAssistantLibrarian = 'assistant-librarian';
  static const String roleFinanceOfficer = 'finance-officer';
  static const String roleIctAdmin = 'ict-admin';
  static const String roleDepartmentHead = 'department-head';

  // Server-side permission names (kebab-case)
  static const String permissionViewDashboard = 'view-dashboard';
  static const String permissionViewBooks = 'view-books';
  static const String permissionCreateBooks = 'create-books';
  static const String permissionEditBooks = 'edit-books';
  static const String permissionDeleteBooks = 'delete-books';
  static const String permissionViewCirculation = 'view-circulation';
  static const String permissionBorrowBooks = 'borrow-books';
  static const String permissionReturnBooks = 'return-books';
  static const String permissionRenewBooks = 'renew-books';
  static const String permissionViewBorrows = 'view-borrows';
  static const String permissionManageReservations = 'manage-reservations';
  static const String permissionViewMembers = 'view-members';
  static const String permissionCreateMembers = 'create-members';
  static const String permissionEditMembers = 'edit-members';
  static const String permissionDeleteMembers = 'delete-members';
  static const String permissionSuspendMembers = 'suspend-members';
  static const String permissionViewDigitalAssets = 'view-digital-assets';
  static const String permissionUploadDigitalAssets = 'upload-digital-assets';
  static const String permissionDownloadDigitalAssets =
      'download-digital-assets';
  static const String permissionViewRecommendations = 'view-recommendations';
  static const String permissionViewLibraryCards = 'view-library-cards';
  static const String permissionManageLibraryCards = 'manage-library-cards';
  static const String permissionViewFinancialReports = 'view-financial-reports';
  static const String permissionViewTransactions = 'view-transactions';
  static const String permissionManageFines = 'manage-fines';
  static const String permissionCollectPayments = 'collect-payments';
  static const String permissionGenerateInvoices = 'generate-invoices';
  static const String permissionGenerateReceipts = 'generate-receipts';
  static const String permissionProcessRefunds = 'process-refunds';
  static const String permissionManageSettings = 'manage-settings';
  static const String permissionManageRoles = 'manage-roles';
  static const String permissionManagePermissions = 'manage-permissions';
  static const String permissionViewAuditLogs = 'view-audit-logs';
  static const String permissionViewReports = 'view-reports';
  static const String permissionGenerateReports = 'generate-reports';
  static const String permissionSendNotifications = 'send-notifications';
  static const String permissionViewNotificationLogs = 'view-notification-logs';
  static const String permissionSendMessages = 'send-messages';
  static const String permissionViewMessages = 'view-messages';
  static const String permissionReplyMessages = 'reply-messages';
  static const String permissionManageAnnouncements = 'manage-announcements';
  static const String permissionManageBulletins = 'manage-bulletins';
  static const String permissionManageEvents = 'manage-events';
  static const String permissionManageBroadcasts = 'manage-broadcasts';
  static const String permissionManageTemplates = 'manage-templates';
  static const String permissionViewAssignments = 'view-assignments';
  static const String permissionCreateAssignments = 'create-assignments';
  static const String permissionCompleteAssignments = 'complete-assignments';
  static const String permissionManageSubscriptions = 'manage-subscriptions';
  static const String permissionViewSubscriptions = 'view-subscriptions';
  static const String permissionManagePricing = 'manage-pricing';
  static const String permissionViewAnalytics = 'view-analytics';

  static bool hasRole(UserModel user, String role) =>
      user.roles?.contains(role) ?? false;

  static bool hasAnyRole(UserModel user, List<String> roles) =>
      roles.any((r) => hasRole(user, r));

  static bool hasPermission(UserModel user, String permission) =>
      user.permissions?.contains(permission) ?? false;

  static bool hasAnyPermission(UserModel user, List<String> permissions) =>
      permissions.any((p) => hasPermission(user, p));

  static bool hasAllPermissions(UserModel user, List<String> permissions) =>
      permissions.every((p) => hasPermission(user, p));

  static bool isStudent(UserModel user) => hasRole(user, roleStudent);
  static bool isLecturer(UserModel user) => hasRole(user, roleLecturer);
  static bool isStaff(UserModel user) => hasAnyRole(user, [
    roleStaff,
    roleLibrarian,
    roleAssistantLibrarian,
    roleFinanceOfficer,
    roleIctAdmin,
    roleDepartmentHead,
  ]);
  static bool isAdmin(UserModel user) =>
      hasAnyRole(user, [roleAdmin, roleSuperAdmin]);
  static bool isStudentOrLecturer(UserModel user) =>
      isStudent(user) || isLecturer(user);

  static bool _permissionsUnrestricted(UserModel user) =>
      user.permissions == null || user.permissions!.isEmpty;

  static bool canAccessDashboard(UserModel user) =>
      _permissionsUnrestricted(user) ||
      hasPermission(user, permissionViewDashboard) ||
      isAdmin(user);
  static bool canViewBooks(UserModel user) =>
      _permissionsUnrestricted(user) ||
      hasPermission(user, permissionViewBooks) ||
      isAdmin(user);
  static bool canCreateBooks(UserModel user) =>
      hasPermission(user, permissionCreateBooks) || isAdmin(user);
  static bool canBorrowBooks(UserModel user) =>
      _permissionsUnrestricted(user) ||
      hasPermission(user, permissionBorrowBooks) ||
      isAdmin(user);
  static bool canReturnBooks(UserModel user) =>
      hasPermission(user, permissionReturnBooks) || isAdmin(user);
  static bool canViewCirculation(UserModel user) =>
      hasPermission(user, permissionViewCirculation) || isAdmin(user);
  static bool canViewMembers(UserModel user) =>
      hasPermission(user, permissionViewMembers) || isAdmin(user);
  static bool canCreateMembers(UserModel user) =>
      hasPermission(user, permissionCreateMembers) || isAdmin(user);
  static bool canAccessDigitalLibrary(UserModel user) =>
      _permissionsUnrestricted(user) ||
      hasPermission(user, permissionViewDigitalAssets) ||
      isAdmin(user);
  static bool canDownloadDigitalAssets(UserModel user) =>
      hasPermission(user, permissionDownloadDigitalAssets) || isAdmin(user);
  static bool canViewLibraryCards(UserModel user) =>
      hasPermission(user, permissionViewLibraryCards) || isAdmin(user);
  static bool canManageLibraryCards(UserModel user) =>
      hasPermission(user, permissionManageLibraryCards) || isAdmin(user);
  static bool canViewFinancialReports(UserModel user) =>
      hasPermission(user, permissionViewFinancialReports) || isAdmin(user);
  static bool canManageFines(UserModel user) =>
      hasPermission(user, permissionManageFines) || isAdmin(user);
  static bool canCollectPayments(UserModel user) =>
      hasPermission(user, permissionCollectPayments) || isAdmin(user);
  static bool canManageSettings(UserModel user) =>
      hasPermission(user, permissionManageSettings) || isAdmin(user);
  static bool canViewReports(UserModel user) =>
      hasPermission(user, permissionViewReports) || isAdmin(user);
  static bool canSendMessages(UserModel user) =>
      hasPermission(user, permissionSendMessages) || isAdmin(user);
  static bool canViewMessages(UserModel user) =>
      _permissionsUnrestricted(user) ||
      hasPermission(user, permissionViewMessages) ||
      isAdmin(user);
  static bool canAccessNotifications(UserModel user) =>
      _permissionsUnrestricted(user) ||
      hasAnyPermission(user, [
        permissionViewNotificationLogs,
        permissionSendNotifications,
      ]) ||
      isAdmin(user);
  static bool canViewRecommendations(UserModel user) =>
      hasPermission(user, permissionViewRecommendations) || isAdmin(user);
  static bool canViewAssignments(UserModel user) =>
      hasPermission(user, permissionViewAssignments) || isAdmin(user);
  static bool canCreateAssignments(UserModel user) =>
      hasPermission(user, permissionCreateAssignments) || isAdmin(user);
  static bool canManageSubscriptions(UserModel user) =>
      hasPermission(user, permissionManageSubscriptions) || isAdmin(user);
}
