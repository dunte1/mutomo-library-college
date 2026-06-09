@section('title', 'Landing Page Settings')
<div>
    <x-header title="Landing Page Settings" subtitle="Manage all content displayed on the public landing page. No hardcoded content.">
        <x-slot:actions>
            <a href="{{ route('settings.index') }}" wire:navigate class="btn-outline btn-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                Back to Settings
            </a>
        </x-slot:actions>
    </x-header>

    {{-- Tabs --}}
    <div class="mb-6 overflow-x-auto scrollbar-thin" x-data="{ tab: @entangle('currentTab') }">
        <div class="flex gap-1 min-w-max p-1 bg-surface-100 dark:bg-surface-800 rounded-xl">
            <button @click="tab = 'hero'" :class="tab === 'hero' ? 'bg-white dark:bg-surface-700 shadow-sm' : 'hover:bg-surface-50 dark:hover:bg-surface-700/50'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors text-surface-600 dark:text-surface-300">Hero</button>
            <button @click="tab = 'features'" :class="tab === 'features' ? 'bg-white dark:bg-surface-700 shadow-sm' : 'hover:bg-surface-50 dark:hover:bg-surface-700/50'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors text-surface-600 dark:text-surface-300">Features</button>
            <button @click="tab = 'whychooseus'" :class="tab === 'whychooseus' ? 'bg-white dark:bg-surface-700 shadow-sm' : 'hover:bg-surface-50 dark:hover:bg-surface-700/50'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors text-surface-600 dark:text-surface-300">Why Choose Us</button>
            <button @click="tab = 'testimonials'" :class="tab === 'testimonials' ? 'bg-white dark:bg-surface-700 shadow-sm' : 'hover:bg-surface-50 dark:hover:bg-surface-700/50'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors text-surface-600 dark:text-surface-300">Testimonials</button>
            <button @click="tab = 'stats'" :class="tab === 'stats' ? 'bg-white dark:bg-surface-700 shadow-sm' : 'hover:bg-surface-50 dark:hover:bg-surface-700/50'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors text-surface-600 dark:text-surface-300">Statistics</button>
            <button @click="tab = 'newsletter'" :class="tab === 'newsletter' ? 'bg-white dark:bg-surface-700 shadow-sm' : 'hover:bg-surface-50 dark:hover:bg-surface-700/50'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors text-surface-600 dark:text-surface-300">Newsletter</button>
            <button @click="tab = 'featuredBooks'" :class="tab === 'featuredBooks' ? 'bg-white dark:bg-surface-700 shadow-sm' : 'hover:bg-surface-50 dark:hover:bg-surface-700/50'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors text-surface-600 dark:text-surface-300">Featured Books</button>
            <button @click="tab = 'featuredDigitalAssets'" :class="tab === 'featuredDigitalAssets' ? 'bg-white dark:bg-surface-700 shadow-sm' : 'hover:bg-surface-50 dark:hover:bg-surface-700/50'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors text-surface-600 dark:text-surface-300">Digital Assets</button>
            <button @click="tab = 'mobile'" :class="tab === 'mobile' ? 'bg-white dark:bg-surface-700 shadow-sm' : 'hover:bg-surface-50 dark:hover:bg-surface-700/50'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors text-surface-600 dark:text-surface-300">Mobile App</button>
            <button @click="tab = 'footer'" :class="tab === 'footer' ? 'bg-white dark:bg-surface-700 shadow-sm' : 'hover:bg-surface-50 dark:hover:bg-surface-700/50'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors text-surface-600 dark:text-surface-300">Footer</button>
            <button @click="tab = 'seo'" :class="tab === 'seo' ? 'bg-white dark:bg-surface-700 shadow-sm' : 'hover:bg-surface-50 dark:hover:bg-surface-700/50'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors text-surface-600 dark:text-surface-300">SEO</button>
            <button @click="tab = 'contact'" :class="tab === 'contact' ? 'bg-white dark:bg-surface-700 shadow-sm' : 'hover:bg-surface-50 dark:hover:bg-surface-700/50'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors text-surface-600 dark:text-surface-300">Contact</button>
        </div>
    </div>

    {{-- Hero Tab --}}
    @if ($currentTab === 'hero')
    <div class="card">
        <div class="card-body">
            <form wire:submit="save">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <span class="text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Hero Section Configuration</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="label">Badge Text</label>
                        <input type="text" wire:model="hero.hero_badge_text" class="input-field" placeholder="e.g. OLLMCHS Library Portal">
                        @error("hero.hero_badge_text") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Visibility</label>
                        <div class="flex items-center gap-3 mt-2">
                            <input type="checkbox" wire:model="hero.hero_visible" id="hero_visible" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500">
                            <label for="hero_visible" class="text-sm text-surface-600 dark:text-surface-300">Show hero section on landing page</label>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Title</label>
                        <input type="text" wire:model="hero.hero_title" class="input-field" placeholder="Leave blank to use site name">
                        @error("hero.hero_title") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Subtitle</label>
                        <input type="text" wire:model="hero.hero_subtitle" class="input-field" placeholder="e.g. Our Lady of Lourdes Mutomo College of Health Sciences">
                        @error("hero.hero_subtitle") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Description</label>
                        <textarea wire:model="hero.hero_description" class="input-field" rows="3" placeholder="Main description text"></textarea>
                        @error("hero.hero_description") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Quote / Motto</label>
                        <input type="text" wire:model="hero.hero_quote" class="input-field" placeholder="e.g. Empowering health sciences education...">
                        @error("hero.hero_quote") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="border-t border-surface-200 dark:border-surface-700 pt-6 mt-6">
                    <h3 class="text-base font-semibold text-surface-900 dark:text-white mb-1">Call-to-Action Buttons</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-4">Configure the primary and secondary buttons displayed in the hero section.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="label">Primary CTA Text</label>
                            <input type="text" wire:model="hero.hero_primary_cta_text" class="input-field" placeholder="e.g. Sign In to Library">
                        </div>
                        <div>
                            <label class="label">Primary CTA URL</label>
                            <input type="text" wire:model="hero.hero_primary_cta_url" class="input-field" placeholder="e.g. /login">
                        </div>
                        <div>
                            <label class="label">Secondary CTA Text</label>
                            <input type="text" wire:model="hero.hero_secondary_cta_text" class="input-field" placeholder="e.g. Create Account">
                        </div>
                        <div>
                            <label class="label">Secondary CTA URL</label>
                            <input type="text" wire:model="hero.hero_secondary_cta_url" class="input-field" placeholder="e.g. /register">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-surface-200 dark:border-surface-700 mt-6">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Save Hero Settings</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Features Tab --}}
    @elseif ($currentTab === 'features')
    <div class="card">
        <div class="card-body">
            <div class="text-center py-12">
                <div class="w-16 h-16 rounded-2xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-surface-900 dark:text-white mb-2">Features Management</h3>
                <p class="text-sm text-surface-500 dark:text-surface-400 max-w-md mx-auto mb-6">Manage landing page feature cards. Add, edit, reorder, and customize feature cards with icons, titles, and descriptions.</p>
                <a href="{{ route('settings.features') }}" wire:navigate class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                    </svg>
                    Manage Features
                </a>
            </div>
        </div>
    </div>

    {{-- Why Choose Us Tab --}}
    @elseif ($currentTab === 'whychooseus')
    <div class="card">
        <div class="card-body">
            <div class="text-center py-12">
                <div class="w-16 h-16 rounded-2xl bg-secondary-100 dark:bg-secondary-900/30 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-secondary-600 dark:text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-surface-900 dark:text-white mb-2">Why Choose Us Management</h3>
                <p class="text-sm text-surface-500 dark:text-surface-400 max-w-md mx-auto mb-6">Manage value proposition cards displayed on the landing page. Add, edit, reorder, and customize unlimited cards.</p>
                <a href="{{ route('settings.why-choose-us') }}" wire:navigate class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Manage Items
                </a>
            </div>
        </div>
    </div>

    {{-- Testimonials Tab --}}
    @elseif ($currentTab === 'testimonials')
    <div class="card">
        <div class="card-body">
            <div class="text-center py-12">
                <div class="w-16 h-16 rounded-2xl bg-accent-100 dark:bg-accent-900/30 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-accent-600 dark:text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-surface-900 dark:text-white mb-2">Testimonials Management</h3>
                <p class="text-sm text-surface-500 dark:text-surface-400 max-w-md mx-auto mb-6">Manage patron testimonials with approval workflow. Only approved testimonials are displayed on the landing page.</p>
                <a href="{{ route('settings.testimonials') }}" wire:navigate class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    Manage Testimonials
                </a>
            </div>
        </div>
    </div>

    {{-- Statistics Tab --}}
    @elseif ($currentTab === 'stats')
    <div class="card">
        <div class="card-body">
            <form wire:submit="save">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <span class="text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Statistics Section</span>
                </div>
                <p class="text-sm text-surface-500 dark:text-surface-400 mb-6">Customize labels for the stat counters. Values are automatically pulled from the database.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="label">Resources Label</label>
                        <input type="text" wire:model="stats.stats_resource_label" class="input-field">
                        @error("stats.stats_resource_label") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Resources Subtext</label>
                        <input type="text" wire:model="stats.stats_resource_subtext" class="input-field">
                        @error("stats.stats_resource_subtext") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Members Label</label>
                        <input type="text" wire:model="stats.stats_member_label" class="input-field">
                        @error("stats.stats_member_label") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Members Subtext</label>
                        <input type="text" wire:model="stats.stats_member_subtext" class="input-field">
                        @error("stats.stats_member_subtext") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Borrows Label</label>
                        <input type="text" wire:model="stats.stats_borrow_label" class="input-field">
                        @error("stats.stats_borrow_label") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Borrows Subtext</label>
                        <input type="text" wire:model="stats.stats_borrow_subtext" class="input-field">
                        @error("stats.stats_borrow_subtext") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" wire:model="stats.stats_visible" id="stats_visible" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500">
                            <label for="stats_visible" class="text-sm text-surface-600 dark:text-surface-300">Show statistics section</label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-surface-200 dark:border-surface-700 mt-6">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Save Statistics Settings</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Newsletter Tab --}}
    @elseif ($currentTab === 'newsletter')
    <div class="card">
        <div class="card-body">
            <form wire:submit="save">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <span class="text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Newsletter Section</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="label">Heading</label>
                        <input type="text" wire:model="newsletter.newsletter_heading" class="input-field" placeholder="e.g. Stay Connected">
                        @error("newsletter.newsletter_heading") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Description</label>
                        <textarea wire:model="newsletter.newsletter_description" class="input-field" rows="2"></textarea>
                        @error("newsletter.newsletter_description") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Placeholder Text</label>
                        <input type="text" wire:model="newsletter.newsletter_placeholder" class="input-field">
                        @error("newsletter.newsletter_placeholder") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Button Text</label>
                        <input type="text" wire:model="newsletter.newsletter_button_text" class="input-field">
                        @error("newsletter.newsletter_button_text") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Disclaimer</label>
                        <input type="text" wire:model="newsletter.newsletter_disclaimer" class="input-field">
                    </div>
                    <div class="md:col-span-2">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" wire:model="newsletter.newsletter_visible" id="newsletter_visible" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500">
                            <label for="newsletter_visible" class="text-sm text-surface-600 dark:text-surface-300">Show newsletter signup section</label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-surface-200 dark:border-surface-700 mt-6">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Save Newsletter Settings</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Featured Books Tab --}}
    @elseif ($currentTab === 'featuredBooks')
    <div class="card">
        <div class="card-body">
            <form wire:submit="save">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <span class="text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Featured Books Section</span>
                </div>
                <p class="text-sm text-surface-500 dark:text-surface-400 mb-6">Configure the featured books section shown on the landing page. Books marked as "featured" in the catalog are displayed automatically.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="label">Badge Text</label>
                        <input type="text" wire:model="featuredBooks.featured_books_badge" class="input-field" placeholder="e.g. Featured Books">
                        @error("featuredBooks.featured_books_badge") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Visibility</label>
                        <div class="flex items-center gap-3 mt-2">
                            <input type="checkbox" wire:model="featuredBooks.featured_books_visible" id="featured_books_visible" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500">
                            <label for="featured_books_visible" class="text-sm text-surface-600 dark:text-surface-300">Show featured books section</label>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Heading</label>
                        <input type="text" wire:model="featuredBooks.featured_books_heading" class="input-field">
                        @error("featuredBooks.featured_books_heading") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Description</label>
                        <textarea wire:model="featuredBooks.featured_books_description" class="input-field" rows="2"></textarea>
                        @error("featuredBooks.featured_books_description") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-surface-200 dark:border-surface-700 mt-6">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Save Featured Books Settings</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Featured Digital Assets Tab --}}
    @elseif ($currentTab === 'featuredDigitalAssets')
    <div class="card">
        <div class="card-body">
            <form wire:submit="save">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <span class="text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Featured Digital Assets Section</span>
                </div>
                <p class="text-sm text-surface-500 dark:text-surface-400 mb-6">Configure the digital assets section shown on the landing page. Digital assets marked as "featured" are displayed automatically.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="label">Badge Text</label>
                        <input type="text" wire:model="featuredDigitalAssets.featured_da_badge" class="input-field" placeholder="e.g. Digital Assets">
                        @error("featuredDigitalAssets.featured_da_badge") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Visibility</label>
                        <div class="flex items-center gap-3 mt-2">
                            <input type="checkbox" wire:model="featuredDigitalAssets.featured_da_visible" id="featured_da_visible" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500">
                            <label for="featured_da_visible" class="text-sm text-surface-600 dark:text-surface-300">Show featured digital assets section</label>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Heading</label>
                        <input type="text" wire:model="featuredDigitalAssets.featured_da_heading" class="input-field">
                        @error("featuredDigitalAssets.featured_da_heading") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Description</label>
                        <textarea wire:model="featuredDigitalAssets.featured_da_description" class="input-field" rows="2"></textarea>
                        @error("featuredDigitalAssets.featured_da_description") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Visibility</label>
                        <div class="flex items-center gap-3 mt-2">
                            <input type="checkbox" wire:model="featuredDigitalAssets.featured_digital_assets_visible" id="featured_digital_assets_visible" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500">
                            <label for="featured_digital_assets_visible" class="text-sm text-surface-600 dark:text-surface-300">Show digital assets section</label>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Heading</label>
                        <input type="text" wire:model="featuredDigitalAssets.featured_digital_assets_heading" class="input-field">
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Description</label>
                        <textarea wire:model="featuredDigitalAssets.featured_digital_assets_description" class="input-field" rows="2"></textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-surface-200 dark:border-surface-700 mt-6">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Save Digital Assets Settings</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Mobile App Tab --}}
    @elseif ($currentTab === 'mobile')
    <div class="card">
        <div class="card-body">
            <form wire:submit="save">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <span class="text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Mobile App Promo Section</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="label">Badge Text</label>
                        <input type="text" wire:model="mobile.mobile_badge" class="input-field" placeholder="e.g. Mobile Access">
                        @error("mobile.mobile_badge") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Visibility</label>
                        <div class="flex items-center gap-3 mt-2">
                            <input type="checkbox" wire:model="mobile.mobile_visible" id="mobile_visible" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500">
                            <label for="mobile_visible" class="text-sm text-surface-600 dark:text-surface-300">Show mobile app promo section</label>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Heading</label>
                        <input type="text" wire:model="mobile.mobile_heading" class="input-field">
                        @error("mobile.mobile_heading") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Description</label>
                        <textarea wire:model="mobile.mobile_description" class="input-field" rows="3"></textarea>
                        @error("mobile.mobile_description") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-surface-200 dark:border-surface-700 mt-6">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Save Mobile App Settings</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Footer Tab --}}
    @elseif ($currentTab === 'footer')
    <div class="card">
        <div class="card-body">
            <form wire:submit="save">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <span class="text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Footer Configuration</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="label">Copyright Text</label>
                        <input type="text" wire:model="footer.footer_copyright" class="input-field" placeholder="e.g. All rights reserved.">
                    </div>
                    <div>
                        <label class="label">Facebook URL</label>
                        <input type="url" wire:model="footer.footer_facebook_url" class="input-field" placeholder="https://facebook.com/...">
                    </div>
                    <div>
                        <label class="label">Twitter / X URL</label>
                        <input type="url" wire:model="footer.footer_twitter_url" class="input-field" placeholder="https://twitter.com/...">
                    </div>
                    <div>
                        <label class="label">Instagram URL</label>
                        <input type="url" wire:model="footer.footer_instagram_url" class="input-field" placeholder="https://instagram.com/...">
                    </div>
                    <div>
                        <label class="label">YouTube URL</label>
                        <input type="url" wire:model="footer.footer_youtube_url" class="input-field" placeholder="https://youtube.com/...">
                    </div>
                    <div>
                        <label class="label">LinkedIn URL</label>
                        <input type="url" wire:model="footer.footer_linkedin_url" class="input-field" placeholder="https://linkedin.com/...">
                    </div>
                    <div class="md:col-span-2">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" wire:model="footer.footer_newsletter_visible" id="footer_newsletter_visible" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500">
                            <label for="footer_newsletter_visible" class="text-sm text-surface-600 dark:text-surface-300">Show newsletter signup in footer</label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-surface-200 dark:border-surface-700 mt-6">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Save Footer Settings</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- SEO Tab --}}
    @elseif ($currentTab === 'seo')
    <div class="card">
        <div class="card-body">
            <form wire:submit="save">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <span class="text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">SEO &amp; Meta Tags</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="label">Meta Title</label>
                        <input type="text" wire:model="seo.seo_meta_title" class="input-field" placeholder="Leave blank to use site name">
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Meta Description</label>
                        <textarea wire:model="seo.seo_meta_description" class="input-field" rows="2"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Meta Keywords</label>
                        <input type="text" wire:model="seo.seo_meta_keywords" class="input-field" placeholder="library, books, OLLMCHS, education, Kenya">
                    </div>
                    <div>
                        <label class="label">Canonical URL</label>
                        <input type="url" wire:model="seo.seo_canonical_url" class="input-field" placeholder="https://library.ollmchs.edu/">
                    </div>
                    <div>
                        <label class="label">Robots</label>
                        <select wire:model="seo.seo_robots" class="input-field">
                            <option value="index,follow">index, follow</option>
                            <option value="noindex,follow">noindex, follow</option>
                            <option value="index,nofollow">index, nofollow</option>
                            <option value="noindex,nofollow">noindex, nofollow</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-surface-200 dark:border-surface-700 pt-6 mt-6">
                    <h3 class="text-base font-semibold text-surface-900 dark:text-white mb-1">Open Graph</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-4">Controls how the page appears when shared on Facebook, WhatsApp, and other platforms.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="label">OG Title</label>
                            <input type="text" wire:model="seo.seo_og_title" class="input-field" placeholder="Leave blank to use meta title">
                        </div>
                        <div class="md:col-span-2">
                            <label class="label">OG Description</label>
                            <textarea wire:model="seo.seo_og_description" class="input-field" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="border-t border-surface-200 dark:border-surface-700 pt-6 mt-6">
                    <h3 class="text-base font-semibold text-surface-900 dark:text-white mb-1">Twitter Cards</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-4">Controls how the page appears when shared on Twitter/X.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="label">Twitter Title</label>
                            <input type="text" wire:model="seo.seo_twitter_title" class="input-field" placeholder="Leave blank to use OG title">
                        </div>
                        <div class="md:col-span-2">
                            <label class="label">Twitter Description</label>
                            <textarea wire:model="seo.seo_twitter_description" class="input-field" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-surface-200 dark:border-surface-700 mt-6">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Save SEO Settings</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Contact Tab --}}
    @elseif ($currentTab === 'contact')
    <div class="card">
        <div class="card-body">
            <form wire:submit="save">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <span class="text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Contact &amp; Support Information</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="label">Library Address</label>
                        <textarea wire:model="contact.library_address" class="input-field" rows="2"></textarea>
                        @error("contact.library_address") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input type="text" wire:model="contact.library_phone" class="input-field">
                        @error("contact.library_phone") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input type="email" wire:model="contact.library_email" class="input-field">
                        @error("contact.library_email") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Opening Hours</label>
                        <input type="text" wire:model="contact.opening_hours" class="input-field" placeholder="e.g. Mon-Fri: 8:00 AM - 5:00 PM">
                        @error("contact.opening_hours") <p class="text-sm text-accent-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Support Hours</label>
                        <input type="text" wire:model="contact.contact_support_hours" class="input-field" placeholder="e.g. Mon-Fri: 8:00 AM - 5:00 PM">
                    </div>
                    <div>
                        <label class="label">WhatsApp Number</label>
                        <input type="text" wire:model="contact.contact_whatsapp" class="input-field" placeholder="e.g. +254712345678">
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-surface-200 dark:border-surface-700 mt-6">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Save Contact Settings</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
