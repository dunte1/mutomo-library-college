class LibraryCardModel {
  final int id;
  final String memberId;
  final String cardNumber;
  final String? barcode;
  final String? qrCodeUrl;
  final String status;
  final DateTime issuedAt;
  final DateTime? expiresAt;
  final String? memberName;
  final String? memberPhoto;
  final String? memberEmail;
  final String? memberPhone;

  LibraryCardModel({
    required this.id,
    required this.memberId,
    required this.cardNumber,
    this.barcode,
    this.qrCodeUrl,
    required this.status,
    required this.issuedAt,
    this.expiresAt,
    this.memberName,
    this.memberPhoto,
    this.memberEmail,
    this.memberPhone,
  });

  bool get isActive => status == 'active';
  bool get isExpired => status == 'expired';

  factory LibraryCardModel.fromJson(Map<String, dynamic> json) {
    final member = json['member'] as Map<String, dynamic>?;
    final user = member?['user'] as Map<String, dynamic>?;

    return LibraryCardModel(
      id: json['id'] as int,
      memberId:
          json['member_id'] as String? ?? member?['member_id'] as String? ?? '',
      cardNumber:
          json['card_number'] as String? ?? json['barcode'] as String? ?? '',
      barcode: json['barcode'] as String?,
      qrCodeUrl:
          json['qr_code_svg'] as String? ?? json['qr_code_url'] as String?,
      status: json['status'] as String? ?? 'active',
      issuedAt: DateTime.parse(json['issued_at'] as String),
      expiresAt: json['expires_at'] != null
          ? DateTime.tryParse(json['expires_at'] as String)
          : null,
      memberName:
          user?['name'] as String? ??
          member?['full_name'] as String? ??
          member?['name'] as String?,
      memberPhoto:
          user?['profile_photo_url'] as String? ?? member?['photo'] as String?,
      memberEmail: user?['email'] as String?,
      memberPhone: user?['phone'] as String? ?? member?['phone'] as String?,
    );
  }
}
