class BookCopyModel {
  final int id;
  final String? barcode;
  final String? rfidTag;
  final String? shelfLocation;
  final String status;
  final String? condition;
  final String? currentBorrower;

  BookCopyModel({
    required this.id,
    this.barcode,
    this.rfidTag,
    this.shelfLocation,
    this.status = 'available',
    this.condition,
    this.currentBorrower,
  });

  Map<String, dynamic> toJson() => {
    'id': id,
    'barcode': barcode,
    'rfid_tag': rfidTag,
    'shelf_location': shelfLocation,
    'status': status,
    'condition': condition,
    'current_borrower': currentBorrower,
  };

  bool get isAvailable => status == 'available';
  bool get isBorrowed => status == 'borrowed';
  bool get isReserved => status == 'reserved';
  bool get isDamaged => status == 'damaged';
  bool get isLost => status == 'lost';

  factory BookCopyModel.fromJson(Map<String, dynamic> json) {
    final borrow = json['current_borrow'] as Map<String, dynamic>?;
    String? borrowerName;
    if (borrow != null) {
      final user = borrow['user'] as Map<String, dynamic>?;
      borrowerName = user?['name'] as String?;
    }

    return BookCopyModel(
      id: json['id'] as int,
      barcode: json['barcode'] as String?,
      rfidTag: json['rfid_tag'] as String?,
      shelfLocation: json['shelf_location'] as String?,
      status: json['status'] as String? ?? 'available',
      condition: json['condition'] as String?,
      currentBorrower: borrowerName,
    );
  }
}
