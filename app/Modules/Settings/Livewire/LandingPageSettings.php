<?php

namespace App\Modules\Settings\Livewire;

use App\Modules\Settings\Services\SettingsService;
use Livewire\Component;
use Livewire\WithFileUploads;

class LandingPageSettings extends Component
{
    use WithFileUploads;

    public string $currentTab = 'hero';

    // Hero
    public array $hero = [];

    // Featured Books
    public array $featuredBooks = [];

    // Featured Digital Assets
    public array $featuredDigitalAssets = [];

    // Statistics
    public array $stats = [];

    // Newsletter
    public array $newsletter = [];

    // Mobile App
    public array $mobile = [];

    // Footer
    public array $footer = [];

    // SEO
    public array $seo = [];

    public $seo_og_image;

    // Contact
    public array $contact = [];

    public bool $saved = false;

    protected function rules(): array
    {
        return match ($this->currentTab) {
            'hero' => [
                'hero.hero_badge_text' => ['nullable', 'string', 'max:255'],
                'hero.hero_title' => ['nullable', 'string', 'max:255'],
                'hero.hero_subtitle' => ['nullable', 'string', 'max:500'],
                'hero.hero_description' => ['nullable', 'string', 'max:1000'],
                'hero.hero_quote' => ['nullable', 'string', 'max:500'],
                'hero.hero_primary_cta_text' => ['nullable', 'string', 'max:100'],
                'hero.hero_primary_cta_url' => ['nullable', 'string', 'max:500'],
                'hero.hero_secondary_cta_text' => ['nullable', 'string', 'max:100'],
                'hero.hero_secondary_cta_url' => ['nullable', 'string', 'max:500'],
                'hero.hero_visible' => ['boolean'],
            ],
            'stats' => [
                'stats.stats_resource_label' => ['nullable', 'string', 'max:100'],
                'stats.stats_resource_subtext' => ['nullable', 'string', 'max:200'],
                'stats.stats_member_label' => ['nullable', 'string', 'max:100'],
                'stats.stats_member_subtext' => ['nullable', 'string', 'max:200'],
                'stats.stats_borrow_label' => ['nullable', 'string', 'max:100'],
                'stats.stats_borrow_subtext' => ['nullable', 'string', 'max:200'],
                'stats.stats_visible' => ['boolean'],
            ],
            'newsletter' => [
                'newsletter.newsletter_heading' => ['nullable', 'string', 'max:255'],
                'newsletter.newsletter_description' => ['nullable', 'string', 'max:1000'],
                'newsletter.newsletter_placeholder' => ['nullable', 'string', 'max:255'],
                'newsletter.newsletter_button_text' => ['nullable', 'string', 'max:100'],
                'newsletter.newsletter_disclaimer' => ['nullable', 'string', 'max:500'],
                'newsletter.newsletter_visible' => ['boolean'],
            ],
            'mobile' => [
                'mobile.mobile_badge' => ['nullable', 'string', 'max:100'],
                'mobile.mobile_heading' => ['nullable', 'string', 'max:255'],
                'mobile.mobile_description' => ['nullable', 'string', 'max:1000'],
                'mobile.mobile_visible' => ['boolean'],
            ],
            'seo' => [
                'seo.seo_meta_title' => ['nullable', 'string', 'max:255'],
                'seo.seo_meta_description' => ['nullable', 'string', 'max:500'],
                'seo.seo_meta_keywords' => ['nullable', 'string', 'max:500'],
                'seo.seo_og_title' => ['nullable', 'string', 'max:255'],
                'seo.seo_og_description' => ['nullable', 'string', 'max:500'],
                'seo.seo_twitter_title' => ['nullable', 'string', 'max:255'],
                'seo.seo_twitter_description' => ['nullable', 'string', 'max:500'],
                'seo.seo_canonical_url' => ['nullable', 'string', 'max:500'],
                'seo.seo_robots' => ['nullable', 'string', 'max:100'],
            ],
            'contact' => [
                'contact.library_address' => ['nullable', 'string', 'max:500'],
                'contact.library_phone' => ['nullable', 'string', 'max:50'],
                'contact.library_email' => ['nullable', 'email', 'max:255'],
                'contact.opening_hours' => ['nullable', 'string', 'max:500'],
                'contact.contact_support_hours' => ['nullable', 'string', 'max:500'],
                'contact.contact_whatsapp' => ['nullable', 'string', 'max:50'],
            ],
            'featuredBooks' => [
                'featuredBooks.featured_books_badge' => ['nullable', 'string', 'max:100'],
                'featuredBooks.featured_books_heading' => ['nullable', 'string', 'max:255'],
                'featuredBooks.featured_books_description' => ['nullable', 'string', 'max:1000'],
                'featuredBooks.featured_books_visible' => ['boolean'],
            ],
            'featuredDigitalAssets' => [
                'featuredDigitalAssets.featured_digital_assets_badge' => ['nullable', 'string', 'max:100'],
                'featuredDigitalAssets.featured_digital_assets_heading' => ['nullable', 'string', 'max:255'],
                'featuredDigitalAssets.featured_digital_assets_description' => ['nullable', 'string', 'max:1000'],
                'featuredDigitalAssets.featured_digital_assets_visible' => ['boolean'],
            ],
            'footer' => [
                'footer.footer_copyright' => ['nullable', 'string', 'max:500'],
                'footer.footer_facebook_url' => ['nullable', 'string', 'max:500'],
                'footer.footer_twitter_url' => ['nullable', 'string', 'max:500'],
                'footer.footer_instagram_url' => ['nullable', 'string', 'max:500'],
                'footer.footer_youtube_url' => ['nullable', 'string', 'max:500'],
                'footer.footer_linkedin_url' => ['nullable', 'string', 'max:500'],
                'footer.footer_newsletter_visible' => ['boolean'],
            ],
            default => [],
        };
    }

    protected function messages(): array
    {
        return [
            'hero.*.max' => 'The :attribute field must not exceed :max characters.',
            'stats.*.max' => 'The :attribute field must not exceed :max characters.',
            'newsletter.*.max' => 'The :attribute field must not exceed :max characters.',
            'mobile.*.max' => 'The :attribute field must not exceed :max characters.',
            'seo.*.max' => 'The :attribute field must not exceed :max characters.',
            'contact.*.max' => 'The :attribute field must not exceed :max characters.',
            'featuredBooks.*.max' => 'The :attribute field must not exceed :max characters.',
            'featuredDigitalAssets.*.max' => 'The :attribute field must not exceed :max characters.',
            'footer.*.max' => 'The :attribute field must not exceed :max characters.',
        ];
    }

    public function mount(): void
    {
        $service = app(SettingsService::class);

        $this->hero = [
            'hero_badge_text' => $service->cached('hero_badge_text', 'OLLMCHS Library Portal'),
            'hero_title' => $service->cached('hero_title', ''),
            'hero_subtitle' => $service->cached('hero_subtitle', 'Our Lady of Lourdes Mutomo College of Health Sciences'),
            'hero_description' => $service->cached('hero_description', 'A modern, enterprise-grade library management system designed for healthcare education excellence.'),
            'hero_quote' => $service->cached('hero_quote', '"Empowering health sciences education through seamless access to knowledge"'),
            'hero_primary_cta_text' => $service->cached('hero_primary_cta_text', 'Sign In to Library'),
            'hero_primary_cta_url' => $service->cached('hero_primary_cta_url', '/login'),
            'hero_secondary_cta_text' => $service->cached('hero_secondary_cta_text', 'Create Account'),
            'hero_secondary_cta_url' => $service->cached('hero_secondary_cta_url', '/register'),
            'hero_visible' => (bool) $service->cached('hero_visible', true),
        ];

        $this->stats = [
            'stats_resource_label' => $service->cached('stats_resource_label', 'Medical Resources'),
            'stats_resource_subtext' => $service->cached('stats_resource_subtext', 'Books, journals & digital media'),
            'stats_member_label' => $service->cached('stats_member_label', 'Active Members'),
            'stats_member_subtext' => $service->cached('stats_member_subtext', 'Students, faculty & staff'),
            'stats_borrow_label' => $service->cached('stats_borrow_label', 'Borrows Today'),
            'stats_borrow_subtext' => $service->cached('stats_borrow_subtext', 'Transactions & circulation'),
            'stats_visible' => (bool) $service->cached('stats_visible', true),
        ];

        $this->newsletter = [
            'newsletter_heading' => $service->cached('newsletter_heading', 'Stay Connected'),
            'newsletter_description' => $service->cached('newsletter_description', 'Subscribe to receive library updates, new arrivals, and health sciences research alerts.'),
            'newsletter_placeholder' => $service->cached('newsletter_placeholder', 'Enter your email address'),
            'newsletter_button_text' => $service->cached('newsletter_button_text', 'Subscribe'),
            'newsletter_disclaimer' => $service->cached('newsletter_disclaimer', 'No spam. Unsubscribe anytime.'),
            'newsletter_visible' => (bool) $service->cached('newsletter_visible', true),
        ];

        $this->featuredBooks = [
            'featured_books_badge' => $service->cached('featured_books_badge', 'Featured Books'),
            'featured_books_heading' => $service->cached('featured_books_heading', 'Explore Our Collection'),
            'featured_books_description' => $service->cached('featured_books_description', 'Handpicked titles from our catalog to support health sciences education and research.'),
            'featured_books_visible' => (bool) $service->cached('featured_books_visible', true),
        ];

        $this->featuredDigitalAssets = [
            'featured_digital_assets_badge' => $service->cached('featured_digital_assets_badge', 'Digital Library'),
            'featured_digital_assets_heading' => $service->cached('featured_digital_assets_heading', 'Digital Assets & Resources'),
            'featured_digital_assets_description' => $service->cached('featured_digital_assets_description', 'Access e-books, journals, lecture notes, and research papers from our digital collection.'),
            'featured_digital_assets_visible' => (bool) $service->cached('featured_digital_assets_visible', true),
        ];

        $this->mobile = [
            'mobile_badge' => $service->cached('mobile_badge', 'Mobile Access'),
            'mobile_heading' => $service->cached('mobile_heading', 'Your Library in Your Pocket'),
            'mobile_description' => $service->cached('mobile_description', 'Access the full library from any device. Our responsive platform works seamlessly on phones, tablets, and desktops — no app download required.'),
            'mobile_visible' => (bool) $service->cached('mobile_visible', true),
        ];

        $this->seo = [
            'seo_meta_title' => $service->cached('seo_meta_title', ''),
            'seo_meta_description' => $service->cached('seo_meta_description', ''),
            'seo_meta_keywords' => $service->cached('seo_meta_keywords', ''),
            'seo_og_title' => $service->cached('seo_og_title', ''),
            'seo_og_description' => $service->cached('seo_og_description', ''),
            'seo_twitter_title' => $service->cached('seo_twitter_title', ''),
            'seo_twitter_description' => $service->cached('seo_twitter_description', ''),
            'seo_canonical_url' => $service->cached('seo_canonical_url', ''),
            'seo_robots' => $service->cached('seo_robots', 'index,follow'),
        ];

        $display = $service->getDisplaySettings();
        $footerSettings = $service->getFooterSettings();

        $this->contact = [
            'library_address' => $display['library_address'],
            'library_phone' => $display['library_phone'],
            'library_email' => $display['library_email'],
            'opening_hours' => $display['opening_hours'],
            'contact_support_hours' => $service->cached('contact_support_hours', ''),
            'contact_whatsapp' => $service->cached('contact_whatsapp', ''),
        ];

        $this->footer = [
            'footer_copyright' => $footerSettings['footer_copyright'],
            'footer_facebook_url' => $footerSettings['footer_facebook_url'],
            'footer_twitter_url' => $footerSettings['footer_twitter_url'],
            'footer_instagram_url' => $service->cached('footer_instagram_url', ''),
            'footer_youtube_url' => $service->cached('footer_youtube_url', ''),
            'footer_linkedin_url' => $service->cached('footer_linkedin_url', ''),
            'footer_newsletter_visible' => (bool) $service->cached('footer_newsletter_visible', true),
        ];
    }

    public function save(): void
    {
        $this->validate();

        $service = app(SettingsService::class);

        match ($this->currentTab) {
            'hero' => $service->updateSettings('landing_hero', $this->hero),
            'stats' => $service->updateSettings('landing_stats', $this->stats),
            'newsletter' => $service->updateSettings('landing_newsletter', $this->newsletter),
            'mobile' => $service->updateSettings('landing_mobile', $this->mobile),
            'seo' => $service->updateSettings('landing_seo', $this->seo),
            'featuredBooks' => $service->updateSettings('landing_featured_books', $this->featuredBooks),
            'featuredDigitalAssets' => $service->updateSettings('landing_featured_digital_assets', $this->featuredDigitalAssets),
            'contact' => $service->updateSettings('landing_contact', $this->contact),
            'footer' => $service->updateSettings('landing_footer', $this->footer),
            default => null,
        };

        $this->saved = true;
        session()->flash('success', 'Landing page settings saved successfully.');
    }

    public function setTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->saved = false;
    }

    public function render()
    {
        return view('settings::livewire.landing-page-settings');
    }
}
