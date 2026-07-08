<?php

use App\Livewire\Verify\DocumentLookup;
use App\Modules\Auth\Livewire\TwoFactorVerify;
use App\Modules\Catalog\Models\Book;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\Members\Models\Member;
use App\Modules\Settings\Models\Feature;
use App\Modules\Settings\Models\Testimonial;
use App\Modules\Settings\Models\WhyChooseUs;
use App\Modules\Settings\Services\SettingsService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->intended(route('dashboard', absolute: false));
    }

    try {
        $stats = [
            'resources' => Book::count(),
            'members' => Member::count(),
            'borrowsToday' => BorrowRecord::whereDate('borrowed_at', today())->count(),
        ];
    } catch (Throwable) {
        $stats = ['resources' => 0, 'members' => 0, 'borrowsToday' => 0];
    }

    try {
        $service = app(SettingsService::class);
        $display = $service->getDisplaySettings();
        $footer = $service->getFooterSettings();
        $landing = [
            'hero_badge_text' => $service->cached('hero_badge_text', 'OLLMCHS Library Portal'),
            'hero_title' => $service->cached('hero_title', ''),
            'hero_subtitle' => $service->cached('hero_subtitle', 'Our Lady of Lourdes Mutomo College of Health Sciences'),
            'hero_description' => $service->cached('hero_description', 'A modern, enterprise-grade library management system designed for healthcare education excellence.'),
            'hero_quote' => $service->cached('hero_quote', 'Empowering health sciences education through seamless access to knowledge'),
            'hero_primary_cta_text' => $service->cached('hero_primary_cta_text', 'Sign In to Library'),
            'hero_primary_cta_url' => $service->cached('hero_primary_cta_url', '/login'),
            'hero_secondary_cta_text' => $service->cached('hero_secondary_cta_text', 'Create Account'),
            'hero_secondary_cta_url' => $service->cached('hero_secondary_cta_url', '/register'),
            'hero_visible' => (bool) $service->cached('hero_visible', true),

            'seo_meta_title' => $service->cached('seo_meta_title', ''),
            'seo_meta_description' => $service->cached('seo_meta_description', ''),
            'seo_meta_keywords' => $service->cached('seo_meta_keywords', ''),
            'seo_og_title' => $service->cached('seo_og_title', ''),
            'seo_og_description' => $service->cached('seo_og_description', ''),
            'seo_twitter_title' => $service->cached('seo_twitter_title', ''),
            'seo_twitter_description' => $service->cached('seo_twitter_description', ''),
            'seo_canonical_url' => $service->cached('seo_canonical_url', ''),
            'seo_robots' => $service->cached('seo_robots', 'index,follow'),

            'contact_support_hours' => $service->cached('contact_support_hours', ''),
            'contact_whatsapp' => $service->cached('contact_whatsapp', ''),

            'newsletter_heading' => $service->cached('newsletter_heading', 'Stay Connected'),
            'newsletter_description' => $service->cached('newsletter_description', 'Subscribe to receive library updates, new arrivals, and health sciences research alerts.'),
            'newsletter_disclaimer' => $service->cached('newsletter_disclaimer', 'No spam. Unsubscribe anytime.'),
            'newsletter_visible' => (bool) $service->cached('newsletter_visible', true),

            'featured_books_badge' => $service->cached('featured_books_badge', 'Featured Books'),
            'featured_books_heading' => $service->cached('featured_books_heading', 'Explore Our Collection'),
            'featured_books_description' => $service->cached('featured_books_description', 'Handpicked titles from our catalog to support health sciences education and research.'),
            'featured_books_visible' => (bool) $service->cached('featured_books_visible', true),
            'featured_digital_assets_badge' => $service->cached('featured_digital_assets_badge', 'Digital Library'),
            'featured_digital_assets_heading' => $service->cached('featured_digital_assets_heading', 'Digital Assets & Resources'),
            'featured_digital_assets_description' => $service->cached('featured_digital_assets_description', 'Access e-books, journals, lecture notes, and research papers from our digital collection.'),
            'featured_digital_assets_visible' => (bool) $service->cached('featured_digital_assets_visible', true),

            'stats_resource_label' => $service->cached('stats_resource_label', 'Medical Resources'),
            'stats_resource_subtext' => $service->cached('stats_resource_subtext', 'Books, journals & digital media'),
            'stats_member_label' => $service->cached('stats_member_label', 'Active Members'),
            'stats_member_subtext' => $service->cached('stats_member_subtext', 'Students, faculty & staff'),
            'stats_borrow_label' => $service->cached('stats_borrow_label', 'Borrows Today'),
            'stats_borrow_subtext' => $service->cached('stats_borrow_subtext', 'Transactions & circulation'),
            'stats_visible' => (bool) $service->cached('stats_visible', true),

            'mobile_badge' => $service->cached('mobile_badge', 'Mobile Access'),
            'mobile_heading' => $service->cached('mobile_heading', 'Your Library in Your Pocket'),
            'mobile_description' => $service->cached('mobile_description', 'Access the full library from any device. Our responsive platform works seamlessly on phones, tablets, and desktops — no app download required.'),
            'mobile_visible' => (bool) $service->cached('mobile_visible', true),
        ];

        $features = Feature::active()->ordered()->get();
        $whyChooseUs = WhyChooseUs::active()->ordered()->get();
        $testimonials = Testimonial::approved()->active()->ordered()->get();

        $featuredBooks = Book::active()->featured()->with(['authors', 'category'])->take(6)->get();
        $featuredDigitalAssets = DigitalAsset::active()->featured()->take(6)->get();
    } catch (Throwable) {
        $display = ['site_name' => config('app.name'), 'site_description' => '', 'library_address' => '', 'library_phone' => '', 'library_email' => '', 'opening_hours' => ''];
        $footer = ['footer_copyright' => 'All rights reserved.', 'footer_facebook_url' => '', 'footer_twitter_url' => '', 'footer_instagram_url' => '', 'footer_youtube_url' => '', 'footer_linkedin_url' => '', 'footer_newsletter_visible' => true];
        $landing = [
            'hero_badge_text' => 'OLLMCHS Library Portal',
            'hero_title' => '',
            'hero_subtitle' => 'Our Lady of Lourdes Mutomo College of Health Sciences',
            'hero_description' => 'A modern, enterprise-grade library management system designed for healthcare education excellence.',
            'hero_quote' => 'Empowering health sciences education through seamless access to knowledge',
            'hero_primary_cta_text' => 'Sign In to Library',
            'hero_primary_cta_url' => '/login',
            'hero_secondary_cta_text' => 'Create Account',
            'hero_secondary_cta_url' => '/register',
            'hero_visible' => true,

            'seo_meta_title' => '',
            'seo_meta_description' => '',
            'seo_meta_keywords' => '',
            'seo_og_title' => '',
            'seo_og_description' => '',
            'seo_twitter_title' => '',
            'seo_twitter_description' => '',
            'seo_canonical_url' => '',
            'seo_robots' => 'index,follow',

            'contact_support_hours' => '',
            'contact_whatsapp' => '',

            'newsletter_heading' => 'Stay Connected',
            'newsletter_description' => 'Subscribe to receive library updates, new arrivals, and health sciences research alerts.',
            'newsletter_disclaimer' => 'No spam. Unsubscribe anytime.',
            'newsletter_visible' => true,

            'featured_books_badge' => 'Featured Books',
            'featured_books_heading' => 'Explore Our Collection',
            'featured_books_description' => 'Handpicked titles from our catalog to support health sciences education and research.',
            'featured_books_visible' => true,
            'featured_digital_assets_badge' => 'Digital Library',
            'featured_digital_assets_heading' => 'Digital Assets & Resources',
            'featured_digital_assets_description' => 'Access e-books, journals, lecture notes, and research papers from our digital collection.',
            'featured_digital_assets_visible' => true,

            'stats_resource_label' => 'Medical Resources',
            'stats_resource_subtext' => 'Books, journals & digital media',
            'stats_member_label' => 'Active Members',
            'stats_member_subtext' => 'Students, faculty & staff',
            'stats_borrow_label' => 'Borrows Today',
            'stats_borrow_subtext' => 'Transactions & circulation',
            'stats_visible' => true,

            'mobile_badge' => 'Mobile Access',
            'mobile_heading' => 'Your Library in Your Pocket',
            'mobile_description' => 'Access the full library from any device. Our responsive platform works seamlessly on phones, tablets, and desktops — no app download required.',
            'mobile_visible' => true,
        ];
        $features = collect();
        $whyChooseUs = collect();
        $testimonials = collect();
        $featuredBooks = collect();
        $featuredDigitalAssets = collect();
    }

    return view('welcome', compact('stats', 'display', 'footer', 'landing', 'features', 'whyChooseUs', 'testimonials', 'featuredBooks', 'featuredDigitalAssets'));
})->name('home');

Route::view('/privacy', 'legal.privacy')->name('privacy');
Route::view('/terms', 'legal.terms')->name('terms');

Route::get('/verify/document/{id?}', DocumentLookup::class)->name('verify.document');

Route::middleware(['auth'])->group(function () {
    Route::get('/two-factor/verify', TwoFactorVerify::class)->name('two-factor.verify');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/pending-approval', fn () => view('pending-approval'))->name('pending.approval');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard')->middleware('permission:view-dashboard');
    Route::get('/profile', fn () => view('profile'))->name('profile');
});

Route::post('/logout', function () {
    $user = auth()->user();
    if ($user) {
        $user->setRememberToken(null);
        $user->save();
    }
    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect('/');
})->name('logout')->middleware('auth');
