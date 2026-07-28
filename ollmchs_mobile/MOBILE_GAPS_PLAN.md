# OLLMCHS Library Mobile App — Gap Fixes Implementation Plan

## Codebase Pattern Reference (Established Conventions)

- **BLoC**: Single-file `*_bloc.dart` with Events + States + Bloc class. God-loaded-state `Loaded` holds ALL data. States: `Initial → Loading → Loaded (message?) → Error(message)`. Secondary loads (`catch (_) {}`), primary loads emit `Error(ErrorMapper.map(e))`. After mutations, re-dispatch load event.
- **Screen**: `StatefulWidget` + `BlocConsumer`/`BlocBuilder`. Loading = `Skeleton`/`SkeletonCard`. Error = `Center + Text + retry`. Empty = `EmptyState(icon, title, subtitle)`. Pull-to-refresh via `RefreshIndicator`. SnackBar for feedback. Navigation: `context.goNamed()` / `context.pushNamed()`.
- **Model**: Uses `TypeParsers` (`parseInt`, `parseDouble`, `parseIntOrNull`, `parseDoubleOrNull`). `fromJson` factory.
- **Router**: `GoRouter` in `app_router.dart`. Routes inside `ShellRoute` for bottom nav. `pathParameters` for IDs, `state.extra` for objects.
- **API**: `_api.get('/v1/...')`, `response.data['data']` for lists, `response.data['data']` or `response.data` for single objects.
- **Tests**: `bloc_test` + `mocktail`. Widget tests: `MockApiClient` + `MultiBlocProvider` + `GoRouter`.

---

## Phase 1: Fines Overhaul (P0 — Critical)

### 1A. FineModel Enhancement

**File**: `lib/features/fines/models/fine_model.dart`

Add fields for payment details:
```dart
final String? mpesaReference;
final String? paymentMethod;
final DateTime? paidAt; // already exists
final double? amountPaid; // already exists
final String? paymentStatus; // e.g. 'pending', 'processing', 'confirmed', 'failed'
final String? receiptNumber;
final String? borrowRecordId; // link to borrow record
```

Add `fromJson` parsing for the new fields. Add `copyWith` method for state updates.

**Tests**: `test/features/fines/models/fine_model_test.dart`
- fromJson roundtrip with all fields
- fromJson with missing optional fields
- `isPending`, `isPaid` getters

### 1B. PaymentResult Model (New)

**File**: `lib/features/fines/models/payment_result.dart`

```dart
class PaymentResult {
  final int paymentId;
  final String? receiptNumber;
  final String? mpesaReference;
  final double amount;
  final String status; // 'pending', 'confirmed', 'failed'
  final DateTime paidAt;
  final String? message;
}
```

With `fromJson` factory using `TypeParsers`.

**Tests**: `test/features/fines/models/payment_result_test.dart`

### 1C. FinesBloc Enhancement

**File**: `lib/features/fines/bloc/fines_bloc.dart`

Add new events:
```dart
class PayFineWithMethod extends FinesEvent {
  final int fineId;
  final String paymentMethod; // 'mpesa' or 'stripe'
  final String? phoneNumber;
}
class PollPaymentStatus extends FinesEvent {
  final int fineId;
}
class LoadFineDetail extends FinesEvent {
  final int fineId;
}
```

Add to `FinesLoaded`:
```dart
final FineModel? selectedFine;
final PaymentResult? lastPaymentResult;
```

Handler for `PayFineWithMethod`:
- Emit `FinesLoading()` (or update Loaded with message)
- POST `/v1/fines/${fineId}/pay` with `{ payment_method, phone_number }`
- Re-dispatch `LoadFines()` + `PollPaymentStatus(fineId)`

Handler for `PollPaymentStatus`:
- GET `/v1/fines/${fineId}/payment-status`
- Update `lastPaymentResult` in Loaded state
- If status == 'pending', schedule re-poll after 3s (use `add(PollPaymentStatus(...))` with delay)
- If status == 'confirmed' or 'failed', stop polling

Handler for `LoadFineDetail`:
- GET `/v1/fines/${fineId}`
- Update `selectedFine` in Loaded state

**Tests**: `test/features/fines/bloc/fines_bloc_test.dart`
- `PayFineWithMethod` triggers API call, re-dispatches LoadFines
- `PollPaymentStatus` updates payment result
- `LoadFineDetail` populates selectedFine
- Error cases for each

### 1D. PaymentScreen (New)

**File**: `lib/features/fines/screens/payment_screen.dart`

Full-screen payment flow:
1. Header: Fine details (reason, amount, book title)
2. Payment method selector: SegmentedButton with M-Pesa / Stripe
3. M-Pesa section: Phone number TextField (prefilled from profile if available)
4. Stripe section: Card input placeholder (or redirect to Stripe SDK)
5. "Pay KES X.XX" FilledButton
6. After submit: Show loading spinner + "Waiting for confirmation..."
7. Poll status: Show real-time status updates
8. On success: Navigate to PaymentConfirmationScreen
9. On failure: Show error + retry button

Use `BlocConsumer<FinesBloc, FinesState>`:
- Loading: `CircularProgressIndicator` overlay
- Success message: Navigate to confirmation
- Error: SnackBar

### 1E. PaymentConfirmationScreen (New)

**File**: `lib/features/fines/screens/payment_confirmation_screen.dart`

Receipt display:
- Success icon (checkmark in circle)
- "Payment Successful" / "Payment Failed" title
- Receipt card with: Receipt #, M-Pesa Ref, Amount, Date, Status
- "Done" button → `context.goNamed('fines')`
- "Share Receipt" button using `share_plus`

### 1F. FineDetailScreen (New)

**File**: `lib/features/fines/screens/fine_detail_screen.dart`

- Fetches fine details via `LoadFineDetail`
- Shows: fine reason, amount, assessed date, book title, status
- If pending: "Pay Now" button → navigate to PaymentScreen
- If paid: show payment details (method, reference, date)

### 1G. FinesScreen Enhancement

**File**: `lib/features/fines/screens/fines_screen.dart`

Changes:
- Replace simple `_confirmPayFine` dialog with navigation to `PaymentScreen`
- Add `onTap` to fine cards → navigate to `FineDetailScreen`
- Add TabBar: Outstanding / Paid tabs (filter fines by status)
- Keep total pending card at top

### 1H. Router Updates

**File**: `lib/core/routing/app_router.dart`

Add under `/fines`:
```dart
GoRoute(
  path: ':id',
  name: 'fine-detail',
  builder: (_, state) => FineDetailScreen(
    fineId: int.parse(state.pathParameters['id']!),
  ),
),
GoRoute(
  path: ':id/pay',
  name: 'fine-payment',
  builder: (_, state) => PaymentScreen(
    fineId: int.parse(state.pathParameters['id']!),
  ),
),
GoRoute(
  path: 'payment-confirmation',
  name: 'payment-confirmation',
  builder: (_, state) {
    final extra = state.extra as Map<String, dynamic>?;
    return PaymentConfirmationScreen(
      receiptNumber: extra?['receiptNumber'] as String?,
      mpesaReference: extra?['mpesaReference'] as String?,
      amount: (extra?['amount'] as num?)?.toDouble() ?? 0,
      paidAt: extra?['paidAt'] as DateTime?,
      status: extra?['status'] as String? ?? 'confirmed',
    );
  },
),
```

### Phase 1 Tests Summary
| Test file | What it covers |
|---|---|
| `test/features/fines/models/fine_model_test.dart` | fromJson roundtrip, edge cases, getters |
| `test/features/fines/models/payment_result_test.dart` | fromJson roundtrip |
| `test/features/fines/bloc/fines_bloc_test.dart` | PayFineWithMethod, PollPaymentStatus, LoadFineDetail, error handling |
| `test/features/fines/screens/payment_screen_test.dart` | Renders, payment method selection, submit |
| `test/features/fines/screens/payment_confirmation_screen_test.dart` | Renders receipt details |
| `test/features/fines/screens/fine_detail_screen_test.dart` | Renders fine info, pay button |

---

## Phase 2: Citation Generator (P0)

### 2A. CitationModel (New)

**File**: `lib/features/digital_library/models/citation_model.dart`

```dart
class CitationModel {
  final int assetId;
  final String style; // 'APA', 'MLA', 'Chicago', 'Harvard', 'Vancouver', 'IEEE'
  final String citationText;
  final DateTime generatedAt;
}
```

With `fromJson` factory.

**Tests**: `test/features/digital_library/models/citation_model_test.dart`

### 2B. DigitalLibraryBloc Enhancement

**File**: `lib/features/digital_library/bloc/digital_library_bloc.dart`

Add events:
```dart
class GenerateCitation extends DigitalLibraryEvent {
  final int assetId;
  final String style;
}
class LoadCitations extends DigitalLibraryEvent {
  final int assetId;
}
```

Add to `DigitalLibraryLoaded`:
```dart
final Map<int, List<CitationModel>> citations; // keyed by assetId
```

Handlers:
- `GenerateCitation`: POST `/v1/digital-assets/$assetId/citations` with `{ style }`, add to citations map
- `LoadCitations`: GET `/v1/digital-assets/$assetId/citations`, populate citations map

### 2C. CitationScreen (New)

**File**: `lib/features/digital_library/screens/citation_screen.dart`

- Takes `assetId` and `assetTitle` as constructor params
- 6 style buttons in a Wrap: APA, MLA, Chicago, Harvard, Vancouver, IEEE
- Tapping a style triggers `GenerateCitation(assetId, style)`
- Shows generated citation text in a Card
- "Copy" button (uses Clipboard API)
- "Share" button (uses share_plus)

### 2D. Router Update

Add under `/digital-library`:
```dart
GoRoute(
  path: ':id/citations',
  name: 'citation-generator',
  builder: (_, state) => CitationScreen(
    assetId: int.parse(state.pathParameters['id']!),
    assetTitle: state.extra as String? ?? '',
  ),
),
```

### 2E. DigitalAssetReaderScreen Link

**File**: `lib/features/digital_library/screens/digital_asset_reader_screen.dart`

Add a "Cite" button in the AppBar `PopupMenuButton` or as an action:
```dart
PopupMenuItem(
  value: 'cite',
  child: ListTile(
    leading: Icon(Icons.format_quote),
    title: Text('Generate Citation'),
    dense: true,
  ),
),
```

On select: `context.pushNamed('citation-generator', pathParameters: {'id': '${widget.assetId}'}, extra: _filename)`

### Phase 2 Tests Summary
| Test file | What it covers |
|---|---|
| `test/features/digital_library/models/citation_model_test.dart` | fromJson roundtrip |
| `test/features/digital_library/bloc/digital_library_bloc_test.dart` | GenerateCitation, LoadCitations events |
| `test/features/digital_library/screens/citation_screen_test.dart` | Renders style buttons, copy action |

---

## Phase 3: Deep Link Push Notifications (P0)

### 3A. Notification Deep Link Handler

**File**: `lib/core/services/push_notification_service.dart`

The existing `onNotificationTap` callback already receives `message.data['route']`. Enhance it:

```dart
// In _handleForegroundMessage, also call onNotificationTap for in-app routing:
FirebaseMessaging.onMessage.listen((message) {
  _handleForegroundMessage(message);
  // If app is in foreground, route directly
  final route = message.data['route'] as String?;
  if (route != null) {
    onNotificationTap?.call(route);
  }
});
```

### 3B. Notification Type → Route Mapper (New helper)

**File**: `lib/core/helpers/notification_route_helper.dart`

```dart
class NotificationRouteHelper {
  static String? resolveRoute(Map<String, dynamic> data) {
    final type = data['type'] as String?;
    final id = data['id'] as String?;
    
    switch (type) {
      case 'fine': return '/fines/$id';
      case 'overdue': return '/loans';
      case 'reservation': return '/reservations';
      case 'message': return '/messages/$id';
      case 'due_date': return '/loans';
      case 'assignment': return '/assignments/$id';
      case 'event': return '/events/$id';
      case 'announcement': return '/announcements/$id';
      case 'library': return '/digital-library';
      default: return data['route'] as String?;
    }
  }
}
```

### 3C. NotificationListScreen Tap Handler

**File**: `lib/features/notifications/screens/notification_list_screen.dart`

The current `ListTile` has no `onTap`. Add:

```dart
onTap: () {
  // Mark as read if unread
  if (!notification.isRead) {
    context.read<NotificationsBloc>().add(
      MarkNotificationRead(notification.id),
    );
  }
  // Navigate based on notification type/action_url
  final route = notification.actionUrl ?? NotificationRouteHelper.resolveRoute({
    'type': notification.type,
    'id': '${notification.entityId}',
  });
  if (route != null) {
    context.push(route);
  }
},
```

**Note**: This requires the NotificationModel to have `actionUrl` and `entityId` fields. Check existing model and add if missing.

### 3D. Main.dart Wiring

**File**: `lib/main.dart`

In app initialization, wire the notification tap callback to the router's navigator key:
```dart
PushNotificationService().onNotificationTap = (route) {
  if (route != null && rootNavigatorKey.currentState != null) {
    rootNavigatorKey.currentState!.pushNamed(route);
  }
};
```

### Phase 3 Tests Summary
| Test file | What it covers |
|---|---|
| `test/core/helpers/notification_route_helper_test.dart` | Route resolution for each notification type |

---

## Phase 4: P1 Quality Fixes

### 4A. Fix Dashboard Greeting (Gap #6)

**File**: `lib/features/dashboard/screens/dashboard_screen.dart`

Change `_greeting` method (line 33):
```dart
// BEFORE:
if (PermissionHelper.isStudent(user)) return '$timeGreeting, Student';
// AFTER:
if (PermissionHelper.isStudent(user)) return '$timeGreeting, ${user.name}';
if (PermissionHelper.isLecturer(user)) return '$timeGreeting, ${user.name}';
if (PermissionHelper.isStaff(user)) return '$timeGreeting, ${user.name}';
if (PermissionHelper.isAdmin(user)) return '$timeGreeting, ${user.name}';
return '$timeGreeting, ${user.name}';
```

Actually, the simplest fix: just use `user.name` for everyone:
```dart
String _greeting(UserModel user) {
  final hour = DateTime.now().hour;
  final timeGreeting = hour < 12 ? 'Good morning' : hour < 17 ? 'Good afternoon' : 'Good evening';
  return user.name.isNotEmpty ? '$timeGreeting, ${user.name}' : timeGreeting;
}
```

**Tests**: Update `test/features/dashboard/dashboard_screen_test.dart` to verify greeting uses user name.

### 4B. Book Detail Enhancements (Gap #7, #13)

**File**: `lib/features/books/screens/book_detail_screen.dart`

Changes:
1. **AppBar actions** — Add bookmark + share buttons:
```dart
appBar: AppBar(
  title: const Text('Book Details'),
  actions: [
    IconButton(
      icon: Icon(isBookmarked ? Icons.bookmark : Icons.bookmark_border),
      onPressed: () => context.read<BooksBloc>().add(ToggleBookmark(book.id)),
    ),
    IconButton(
      icon: const Icon(Icons.share),
      onPressed: () => share_plus.share('${book.title} by ${book.authors.join(", ")}'),
    ),
  ],
),
```

2. **Reviews section** — Below description, add embedded review list:
```dart
// After description section
if (book.averageRating != null) ...[
  _RatingSection(rating: book.averageRating!, reviewCount: book.reviewCount ?? 0),
],
// Link to full review list
TextButton(
  onPressed: () => context.pushNamed('book-reviews', pathParameters: {'id': '${book.id}'}, extra: book.title),
  child: const Text('See All Reviews'),
),
```

3. **Digital assets section** — If book has linked digital resources:
```dart
// After copies section
if (book.digitalAssets.isNotEmpty) ...[
  Text('Digital Resources', style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
  ...book.digitalAssets.map((asset) => ListTile(
    leading: Icon(asset.isPdf ? Icons.picture_as_pdf : Icons.insert_drive_file),
    title: Text(asset.title),
    trailing: const Icon(Icons.chevron_right),
    onTap: () => context.goNamed('digital-asset-reader', pathParameters: {'id': '${asset.id}'}),
  )),
],
```

4. **Rate action** — Add to the button row:
```dart
Row(
  children: [
    if (isAvailable) Expanded(child: FilledButton.icon(...Reserve...)),
    if (!isAvailable) Expanded(child: FilledButton.tonalIcon(...Unavailable...)),
    const SizedBox(width: 8),
    IconButton.filledTonal(
      onPressed: () => context.pushNamed('book-reviews', pathParameters: {'id': '${book.id}'}, extra: book.title),
      icon: const Icon(Icons.rate_review),
      tooltip: 'Rate & Review',
    ),
  ],
),
```

**Tests**: Widget test verifying bookmark/share buttons render.

### 4C. In-App PDF Viewer (Gap #8)

**File**: `lib/features/digital_library/screens/digital_asset_reader_screen.dart`

Add dependency to `pubspec.yaml`:
```yaml
syncfusion_flutter_pdfviewer: ^28.1.0
```

Replace the `url_launcher` based `_openAsset()` with an embedded PDF viewer when the file is PDF:

```dart
// If PDF and available locally or via URL, show Syncfusion viewer
if (_isPdf && (_isDownloaded || _fileUrl != null)) {
  // Show SyncfusionPdfViewer
  return SfPdfViewer.network(_fileUrl!)  // or SfPdfViewer.file(File(_localPath!))
}
```

For non-PDF files, keep the existing `url_launcher` fallback.

Keep the download/offline functionality as-is. The viewer replaces the "Open" button action.

### 4D. Scanner → Library Card Verification (Gap #9)

**File**: `lib/features/scanner/screens/scanner_screen.dart`

In `_handleScannedCode`, when code matches library card pattern:
```dart
if (code.toUpperCase().startsWith('LIB') || code.toUpperCase().startsWith('CARD')) {
  _verifyLibraryCard(code);
  return;
}
```

Add method:
```dart
Future<void> _verifyLibraryCard(String cardCode) async {
  setState(() => _isProcessing = true);
  try {
    final api = context.read<ApiClient>();
    final response = await api.get('/v1/library-cards/verify', queryParameters: {'card_number': cardCode});
    final data = response.data['data'] as Map<String, dynamic>?;
    if (mounted) {
      _showVerificationResult(cardCode, data);
    }
  } catch (e) {
    if (mounted) {
      _showResult('Verification Failed', 'Could not verify card: $e');
    }
  } finally {
    if (mounted) setState(() => _isProcessing = false);
  }
}
```

Show result dialog with card holder name, status, expiry.

### 4E. Message Forward/Reply (Gap #11 — Already Implemented)

**File**: `lib/features/messaging/screens/message_detail_screen.dart`

Verified: Forward button exists (line 98), Reply box exists (lines 214-261), `ForwardMessage` and `ReplyToMessage` events are wired. **No changes needed.**

### 4F. Subscription Cancel Confirmation (Gap #19)

**File**: `lib/features/subscriptions/screens/my_subscription_screen.dart`

The Cancel button already dispatches `CancelSubscription(sub.id)` (line 129). Add confirmation dialog:

```dart
onPressed: () async {
  final confirmed = await showDialog<bool>(
    context: context,
    builder: (ctx) => AlertDialog(
      title: const Text('Cancel Subscription'),
      content: const Text('Are you sure you want to cancel your subscription? This action cannot be undone.'),
      actions: [
        TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Keep')),
        FilledButton(
          style: FilledButton.styleFrom(backgroundColor: theme.colorScheme.error),
          onPressed: () => Navigator.pop(ctx, true),
          child: const Text('Cancel Subscription'),
        ),
      ],
    ),
  );
  if (confirmed == true) {
    context.read<SubscriptionsBloc>().add(CancelSubscription(sub.id));
  }
},
```

### 4G. Notification Preferences Save (Gap #20 — Already Implemented)

**File**: `lib/features/profile/screens/notification_preferences_screen.dart`

Verified: Save button exists (line 148), `_save()` method calls `api.put('/v1/profile', ...)` (lines 109-139). **No changes needed.**

### Phase 4 Tests Summary
| Test file | What it covers |
|---|---|
| `test/features/dashboard/dashboard_screen_test.dart` | Updated greeting test |
| `test/features/books/screens/book_detail_screen_test.dart` | Bookmark/share buttons render |
| `test/features/scanner/screens/scanner_screen_test.dart` | Library card verification flow |

---

## Phase 5: Fines Tabs + Digital Asset Search (P1)

### 5A. Fines Outstanding/Paid Tabs (Gap #15)

Already covered in Phase 1G (FinesScreen TabBar).

### 5B. Search Within Digital Assets (Gap #16)

**File**: `lib/features/digital_library/screens/digital_asset_list_screen.dart`

Add search bar at top of the list, above the category chips:

```dart
// In the CustomScrollView, add before recommendations:
SliverToBoxAdapter(
  child: Padding(
    padding: const EdgeInsets.fromLTRB(12, 12, 12, 0),
    child: TextField(
      decoration: InputDecoration(
        hintText: 'Search digital assets...',
        prefixIcon: const Icon(Icons.search),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16),
      ),
      onSubmitted: (query) {
        if (query.trim().isNotEmpty) {
          context.read<DigitalLibraryBloc>().add(
            LoadDigitalAssets(category: _selectedCategory, search: query.trim()),
          );
        }
      },
    ),
  ),
),
```

Requires adding `search` parameter to `LoadDigitalAssets` event and passing it as `queryParameters['search']` in the bloc.

### 5C. Reserved Books Tab in Loans (Gap #14)

**File**: `lib/features/loans/screens/active_loans_screen.dart`

Change `TabController(length: 2, ...)` to `TabController(length: 3, ...)`:
```dart
_tabController = TabController(length: 3, vsync: this);
// Tabs: Active, Reservations, History
```

Add third tab with reservation list (reuse `ReservationListScreen` content or embed inline).

Requires adding `LoadReservations` event dispatch and reservations data to `LoansLoaded` state, OR using a separate `ReservationsBloc` provided to the tab.

### Phase 5 Tests Summary
| Test file | What it covers |
|---|---|
| `test/features/digital_library/screens/digital_asset_list_screen_test.dart` | Search bar renders and triggers search |

---

## Phase 6: Remaining P2 Items (Nice to Have)

### 6A. Offline Cache Integration (Gap #17)

**Files**: 
- `lib/features/books/screens/book_list_screen.dart`
- `lib/features/digital_library/screens/digital_asset_list_screen.dart`

Wire `HiveCacheService` to cache API responses and show cached data when offline. This requires:
- Checking `ConnectivityService` state
- Storing last API response in Hive
- Showing cached data with "offline" indicator

### 6B. Global Search (Gap #18)

**New file**: `lib/features/search/screens/global_search_screen.dart`

A search overlay or screen that queries across books, digital assets, authors, and messages simultaneously.

---

## Dependency Graph

```
Phase 1 (Fines) ──────────────── No dependencies, standalone
Phase 2 (Citations) ──────────── Depends on Phase 1 router patterns only
Phase 3 (Deep Links) ──────────── Depends on notification model having action_url/entityId
Phase 4A (Dashboard) ──────────── Standalone, quick fix
Phase 4B (Book Detail) ────────── Standalone
Phase 4C (PDF Viewer) ──────────── Standalone, needs pubspec.yaml change
Phase 4D (Scanner) ─────────────── Standalone
Phase 4E (Messages) ────────────── Already done (no changes)
Phase 4F (Sub Cancel) ──────────── Standalone, small dialog
Phase 4G (Notif Prefs) ─────────── Already done (no changes)
Phase 5A (Fines Tabs) ──────────── Part of Phase 1
Phase 5B (Asset Search) ────────── Standalone
Phase 5C (Reservations Tab) ────── Standalone
```

## Implementation Order

1. **Phase 4A** — Dashboard greeting (5 min, quick win)
2. **Phase 4F** — Subscription cancel dialog (10 min, quick win)
3. **Phase 1** — Fines overhaul (largest phase, ~2-3 hours)
4. **Phase 3** — Deep link notifications (~30 min)
5. **Phase 2** — Citation generator (~1 hour)
6. **Phase 4B** — Book detail enhancements (~1 hour)
7. **Phase 4C** — PDF viewer (~30 min + pubspec change)
8. **Phase 4D** — Scanner verification (~30 min)
9. **Phase 5B** — Digital asset search (~30 min)
10. **Phase 5C** — Reservations tab (~30 min)

## Total Estimated Effort: ~7-8 hours
