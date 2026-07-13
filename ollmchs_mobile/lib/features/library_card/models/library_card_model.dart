import '../../../core/utils/type_parsers.dart';

class LibraryCardModel {
  final int id;
  final String cardNumber;
  final String? barcode;
  final String? qrCodeSvg;
  final String status;
  final DateTime issuedAt;
  final DateTime? expiresAt;
  final String? memberName;
  final String? memberId;
  final String? membershipType;
  final String? memberStatus;
  final String? memberPhoto;
  final String? memberEmail;
  final String? memberPhone;
  final String? department;

  LibraryCardModel({
    required this.id,
    required this.cardNumber,
    this.barcode,
    this.qrCodeSvg,
    required this.status,
    required this.issuedAt,
    this.expiresAt,
    this.memberName,
    this.memberId,
    this.membershipType,
    this.memberStatus,
    this.memberPhoto,
    this.memberEmail,
    this.memberPhone,
    this.department,
  });

  bool get isActive => status == 'active';
  bool get isExpired => status == 'expired';
  bool get isLost => status == 'lost';
  bool get isReplaced => status == 'replaced';

  bool get isMemberActive => memberStatus == 'active';
  bool get isMemberSuspended => memberStatus == 'suspended';
  bool get isMemberExpired => memberStatus == 'expired';
  bool get isMemberInactive => memberStatus == 'inactive';

  factory LibraryCardModel.fromJson(Map<String, dynamic> json) {
    final member = json['member'] as Map<String, dynamic>?;

    return LibraryCardModel(
      id: parseInt(json['id'], fieldName: 'id'),
      cardNumber: json['card_number'] as String? ?? '',
      barcode: json['barcode'] as String?,
      qrCodeSvg: json['qr_code_svg'] as String?,
      status: json['status'] as String? ?? 'active',
      issuedAt: DateTime.parse(json['issued_at'] as String),
      expiresAt: json['expires_at'] != null
          ? DateTime.tryParse(json['expires_at'] as String)
          : null,
      memberName: member?['full_name'] as String?,
      memberId: member?['member_id'] as String?,
      membershipType: member?['membership_type'] as String?,
      memberStatus: member?['member_status'] as String?,
      memberPhoto: member?['photo'] as String?,
      memberEmail: member?['email'] as String?,
      memberPhone: member?['phone'] as String?,
      department: member?['department'] as String?,
    );
  }
}
