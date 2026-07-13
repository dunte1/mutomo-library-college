class AppConstants {
  AppConstants._();

  static const String appName = 'OLLMCHS Library';
  static const String appVersion = '1.0.0';
  static const String institutionName =
      'Our Lady of Lourdes Mutomo College of Health Sciences';

  static const int paginationPerPage = 15;
  static const int searchDebounceMs = 500;

  // Security timeouts (seconds)
  static const int appLockTimeoutSeconds = 300; // 5 minutes
  static const int sessionTimeoutSeconds = 1800; // 30 minutes

  static const String dateFormat = 'dd MMM yyyy';
  static const String timeFormat = 'HH:mm';
  static const String dateTimeFormat = 'dd MMM yyyy HH:mm';
}
