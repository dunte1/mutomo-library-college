<?php

use App\Modules\API\Controllers\AnnouncementController;
use App\Modules\API\Controllers\AuthorController;
use App\Modules\API\Controllers\BookController;
use App\Modules\API\Controllers\AuthController;
use App\Modules\API\Controllers\CategoryController;
use App\Modules\API\Controllers\CirculationController;
use App\Modules\API\Controllers\DashboardController;
use App\Modules\API\Controllers\DigitalAssetController;
use App\Modules\API\Controllers\DigitalCategoryController;
use App\Modules\API\Controllers\EventController;
use App\Modules\API\Controllers\LibraryCardController;
use App\Modules\API\Controllers\MessageController;
use App\Modules\API\Controllers\MpesaCallbackController;
use App\Modules\API\Controllers\MpesaValidationController;
use App\Modules\API\Controllers\NotificationController;
use App\Modules\API\Controllers\ProfileController;
use App\Modules\API\Controllers\PublisherController;
use App\Modules\API\Controllers\BulletinController;
use App\Modules\API\Controllers\PaymentController;
use App\Modules\API\Controllers\PushNotificationController;
use App\Modules\API\Controllers\ReadingHistoryController;
use App\Modules\API\Controllers\RecommendationController;
use App\Modules\API\Controllers\ReservationController;
use App\Modules\API\Controllers\ReviewController;
use App\Modules\API\Controllers\AssignmentController;
use App\Modules\API\Controllers\TeacherAssignmentController;
use App\Modules\API\Controllers\SubscriptionPlanController;
use App\Modules\API\Controllers\SubscriptionController;
use App\Modules\API\Controllers\StripeWebhookController;
use App\Modules\API\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API Routes - v1
|--------------------------------------------------------------------------
|
| All mobile endpoints are prefixed with /api/v1
| Rate limits are defined in ApiServiceProvider
|
*/

Route::name('api.v1.')->prefix('v1')->group(function () {
    // =========================================================================
    // PUBLIC ENDPOINTS
    // =========================================================================

    // Authentication
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->name('auth.login')
        ->middleware('throttle:6,1');

    Route::post('/auth/register', [AuthController::class, 'register'])
        ->name('auth.register')
        ->middleware('throttle:6,1');

    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])
        ->name('auth.forgot-password')
        ->middleware('throttle:6,1');

    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])
        ->name('auth.reset-password')
        ->middleware('throttle:6,1');

    Route::post('/auth/2fa/verify', [TwoFactorController::class, 'verify'])
        ->name('auth.2fa.verify')
        ->middleware('throttle:10,1');

    Route::post('/auth/2fa/verify-recovery', [TwoFactorController::class, 'verifyRecovery'])
        ->name('auth.2fa.verify-recovery')
        ->middleware('throttle:5,1');

    // Webhooks
    Route::post('/mpesa/validation', MpesaValidationController::class)
        ->name('mpesa.validation')
        ->middleware('throttle:10,1');

    Route::post('/mpesa/callback', MpesaCallbackController::class)
        ->name('mpesa.callback')
        ->middleware('throttle:10,1');

    Route::post('/stripe/webhook', StripeWebhookController::class)
        ->name('stripe.webhook')
        ->middleware('throttle:30,1');

    // Book search (public, throttled)
    Route::get('/books/search', [BookController::class, 'search'])
        ->name('books.search')
        ->middleware('throttle:30,1');

    // Push notification VAPID key (public)
    Route::get('/push/vapid-key', [PushNotificationController::class, 'vapidKey'])
        ->name('push.vapid-key')
        ->middleware('throttle:30,1');

    // =========================================================================
    // AUTHENTICATED ENDPOINTS
    // =========================================================================

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

        // ---- Authentication & Session ----

        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/auth/user', [AuthController::class, 'user'])->name('auth.user');
        Route::post('/auth/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
        Route::post('/auth/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');
        Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail'])->name('auth.verify-email');
        Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification'])->name('auth.resend-verification');

        // ---- Two Factor Authentication ----

        Route::post('/auth/2fa/enable', [TwoFactorController::class, 'enable'])->name('auth.2fa.enable');
        Route::post('/auth/2fa/verify-setup', [TwoFactorController::class, 'verifySetup'])->name('auth.2fa.verify-setup');
        Route::post('/auth/2fa/disable', [TwoFactorController::class, 'disable'])->name('auth.2fa.disable');

        // ---- Profile ----

        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');

        // ---- Dashboard ----

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('permission:view-dashboard');

        // ---- Catalog ----

        Route::get('/books', [BookController::class, 'index'])
            ->name('books.index')
            ->middleware('permission:view-books');

        Route::get('/books/{book}', [BookController::class, 'show'])
            ->name('books.show')
            ->middleware('permission:view-books');

        Route::get('/categories', [CategoryController::class, 'index'])
            ->name('categories.index')
            ->middleware('permission:view-books');

        Route::get('/categories/{id}', [CategoryController::class, 'show'])
            ->name('categories.show')
            ->middleware('permission:view-books');

        Route::get('/authors', [AuthorController::class, 'index'])
            ->name('authors.index')
            ->middleware('permission:view-books');

        Route::get('/authors/{id}', [AuthorController::class, 'show'])
            ->name('authors.show')
            ->middleware('permission:view-books');

        Route::get('/publishers', [PublisherController::class, 'index'])
            ->name('publishers.index')
            ->middleware('permission:view-books');

        Route::get('/publishers/{id}', [PublisherController::class, 'show'])
            ->name('publishers.show')
            ->middleware('permission:view-books');

        // ---- Circulation / Loans ----
        // NOTE: Static routes MUST be defined before parameterized {id} routes

        Route::get('/loans/active', [CirculationController::class, 'activeBorrows'])
            ->name('loans.active');

        Route::get('/loans/history', [CirculationController::class, 'history'])
            ->name('loans.history');

        Route::get('/loans/overdue', [CirculationController::class, 'overdue'])
            ->name('loans.overdue');

        Route::post('/loans/issue', [CirculationController::class, 'issue'])
            ->name('loans.issue')
            ->middleware('permission:borrow-books');

        Route::post('/loans/return', [CirculationController::class, 'returnBook'])
            ->name('loans.return')
            ->middleware('permission:return-books');

        Route::get('/loans/{id}', [CirculationController::class, 'show'])
            ->name('loans.show');

        Route::post('/loans/{id}/renew', [CirculationController::class, 'renew'])
            ->name('loans.renew')
            ->middleware('permission:renew-books');

        // ---- Reservations ----

        Route::get('/reservations', [ReservationController::class, 'index'])
            ->name('reservations.index');

        Route::post('/reservations', [ReservationController::class, 'store'])
            ->name('reservations.store')
            ->middleware('permission:manage-reservations');

        Route::delete('/reservations/{id}', [ReservationController::class, 'destroy'])
            ->name('reservations.destroy');

        // ---- Fines ----

        Route::get('/fines', [CirculationController::class, 'myFines'])
            ->name('fines.index');

        Route::post('/fines/{id}/pay', [CirculationController::class, 'payFine'])
            ->name('fines.pay');

        // ---- Library Card ----

        Route::get('/library-card', [LibraryCardController::class, 'show'])
            ->name('library-card.show')
            ->middleware('permission:view-library-cards');

        Route::get('/library-card/qr-code', [LibraryCardController::class, 'qrCode'])
            ->name('library-card.qr-code')
            ->middleware('permission:view-library-cards');

        Route::get('/library-card/barcode', [LibraryCardController::class, 'barcode'])
            ->name('library-card.barcode')
            ->middleware('permission:view-library-cards');

        Route::get('/library-card/pdf', [LibraryCardController::class, 'pdf'])
            ->name('library-card.pdf')
            ->middleware('permission:view-library-cards');

        // ---- Digital Library ----

        Route::get('/digital-assets', [DigitalAssetController::class, 'index'])
            ->name('digital-assets.index')
            ->middleware('permission:view-digital-assets');

        Route::get('/digital-assets/{asset}', [DigitalAssetController::class, 'show'])
            ->name('digital-assets.show')
            ->middleware('permission:view-digital-assets');

        Route::get('/digital-assets/{asset}/download', [DigitalAssetController::class, 'download'])
            ->name('digital-assets.download')
            ->middleware('permission:download-digital-assets');

        Route::get('/digital-categories', [DigitalCategoryController::class, 'index'])
            ->name('digital-categories.index')
            ->middleware('permission:view-digital-assets');

        Route::get('/reading-history', [ReadingHistoryController::class, 'index'])
            ->name('reading-history.index');

        Route::put('/reading-history/{assetId}', [ReadingHistoryController::class, 'update'])
            ->name('reading-history.update');

        Route::get('/recommendations', [RecommendationController::class, 'index'])
            ->name('recommendations.index')
            ->middleware('permission:view-recommendations');

        // ---- Messaging ----

        Route::get('/messages/inbox', [MessageController::class, 'inbox'])
            ->name('messages.inbox.alias')
            ->middleware('permission:view-messages');

        Route::get('/messages', [MessageController::class, 'inbox'])
            ->name('messages.inbox')
            ->middleware('permission:view-messages');

        Route::get('/messages/sent', [MessageController::class, 'sent'])
            ->name('messages.sent')
            ->middleware('permission:view-messages');

        Route::get('/messages/{id}', [MessageController::class, 'show'])
            ->name('messages.show')
            ->middleware('permission:view-messages');

        Route::post('/messages/send', [MessageController::class, 'store'])
            ->name('messages.send')
            ->middleware('permission:send-messages');

        Route::post('/messages', [MessageController::class, 'store'])
            ->name('messages.store')
            ->middleware('permission:send-messages');

        Route::post('/messages/{id}/reply', [MessageController::class, 'reply'])
            ->name('messages.reply')
            ->middleware('permission:reply-messages');

        Route::delete('/messages/{id}', [MessageController::class, 'destroy'])
            ->name('messages.destroy');

        // ---- Notifications ----

        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('notifications.index');

        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.read');

        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.read-all');

        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
            ->name('notifications.unread-count');

        // ---- Announcements & Events ----

        Route::get('/announcements', [AnnouncementController::class, 'index'])
            ->name('announcements.index');

        Route::get('/announcements/{id}', [AnnouncementController::class, 'show'])
            ->name('announcements.show');

        Route::get('/events', [EventController::class, 'index'])
            ->name('events.index');

        Route::get('/events/{id}', [EventController::class, 'show'])
            ->name('events.show');

        // ---- Assignments ----

        Route::get('/assignments', [AssignmentController::class, 'index'])
            ->name('assignments.index');

        Route::get('/assignments/{id}', [AssignmentController::class, 'show'])
            ->name('assignments.show');

        Route::post('/assignments/{id}/submit', [AssignmentController::class, 'submit'])
            ->name('assignments.submit');

        // ---- Teacher Assignments ----

        Route::prefix('teacher/assignments')->name('teacher.assignments.')->group(function () {
            Route::get('/', [TeacherAssignmentController::class, 'index'])
                ->name('index')
                ->middleware('permission:create-assignments');
            Route::post('/', [TeacherAssignmentController::class, 'store'])
                ->name('store')
                ->middleware('permission:create-assignments');
            Route::get('/{id}', [TeacherAssignmentController::class, 'show'])
                ->name('show')
                ->middleware('permission:create-assignments');
            Route::put('/{id}', [TeacherAssignmentController::class, 'update'])
                ->name('update')
                ->middleware('permission:create-assignments');
            Route::delete('/{id}', [TeacherAssignmentController::class, 'destroy'])
                ->name('destroy')
                ->middleware('permission:create-assignments');
            Route::get('/{id}/progress', [TeacherAssignmentController::class, 'progress'])
                ->name('progress')
                ->middleware('permission:create-assignments');
        });

        // ---- Programs & Departments ----

        Route::get('/programs', function () {
            $svc = app(\App\Modules\API\Services\ApiResponseService::class);
            return $svc->success(
                \App\Models\Program::active()->orderBy('name')->get(['id', 'name', 'code', 'department_id'])
            );
        })->name('programs.index');

        Route::get('/departments', function () {
            $svc = app(\App\Modules\API\Services\ApiResponseService::class);
            return $svc->success(
                \App\Models\Department::active()->orderBy('name')->get(['id', 'name', 'code'])
            );
        })->name('departments.index');

        Route::get('/students', function () {
            $data = request()->validate([
                'program_id' => 'sometimes|integer|exists:programs,id',
                'department_id' => 'sometimes|integer|exists:departments,id',
                'search' => 'sometimes|string|max:255',
            ]);
            $students = \App\Models\User::byRole('student')->active()
                ->when($data['program_id'] ?? null, fn ($q) => $q->where('program_id', $data['program_id']))
                ->when($data['department_id'] ?? null, fn ($q) => $q->where('department_id', $data['department_id']))
                ->when($data['search'] ?? null, fn ($q) => $q->where('name', 'like', '%'.$data['search'].'%'))
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'program_id', 'department_id']);
            $svc = app(\App\Modules\API\Services\ApiResponseService::class);
            return $svc->success($students);
        })->name('students.index');

        // ---- Subscriptions ----

        Route::get('/subscription-plans', [SubscriptionPlanController::class, 'index'])
            ->name('subscription-plans.index');

        Route::get('/subscriptions/my', [SubscriptionController::class, 'my'])
            ->name('subscriptions.my');

        Route::post('/subscriptions', [SubscriptionController::class, 'store'])
            ->name('subscriptions.store');

        Route::post('/subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel'])
            ->name('subscriptions.cancel');

        // ---- Payments ----

        Route::get('/payments', [PaymentController::class, 'index'])
            ->name('payments.index');

        Route::get('/payments/{id}', [PaymentController::class, 'show'])
            ->name('payments.show');

        // ---- Bulletins ----

        Route::get('/bulletins', [BulletinController::class, 'index'])
            ->name('bulletins.index');

        Route::get('/bulletins/{id}', [BulletinController::class, 'show'])
            ->name('bulletins.show');

        // ---- Book Reviews & Ratings ----

        Route::get('/books/{bookId}/reviews', [ReviewController::class, 'index'])
            ->name('books.reviews.index');

        Route::post('/books/reviews', [ReviewController::class, 'store'])
            ->name('books.reviews.store');

        Route::get('/books/reviews/{id}', [ReviewController::class, 'show'])
            ->name('books.reviews.show');

        Route::get('/my-reviews', [ReviewController::class, 'my'])
            ->name('my-reviews');

        // ---- Push Notifications ----

        Route::post('/push/subscribe', [PushNotificationController::class, 'subscribe'])->name('push.subscribe');
        Route::post('/push/unsubscribe', [PushNotificationController::class, 'unsubscribe'])->name('push.unsubscribe');
        Route::post('/push/unsubscribe-all', [PushNotificationController::class, 'unsubscribeAll'])->name('push.unsubscribe-all');
        Route::get('/push/subscriptions', [PushNotificationController::class, 'subscriptions'])->name('push.subscriptions');
        Route::get('/push/preferences', [PushNotificationController::class, 'preferences'])->name('push.preferences');
    });
});
