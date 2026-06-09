<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Privacy Policy - {{ config('app.name') }} Library Management System">

    <title>Privacy Policy | {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans bg-surface-50 dark:bg-surface-900 overflow-x-hidden">
    <div class="relative min-h-screen flex flex-col">

        <div class="h-1 bg-gradient-to-r from-primary-600 via-secondary-500 to-accent-500"></div>

        <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
            <div class="absolute -top-40 -left-40 w-[500px] h-[500px] rounded-full bg-primary-200/30 dark:bg-primary-800/20 blur-3xl animate-blob"></div>
            <div class="absolute top-60 -right-40 w-[600px] h-[600px] rounded-full bg-secondary-200/20 dark:bg-secondary-800/15 blur-3xl animate-blob-delayed"></div>
        </div>

        <nav class="sticky top-0 z-50 backdrop-blur-xl bg-white/70 dark:bg-surface-900/70 border-b border-surface-200/50 dark:border-surface-700/50">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-4 px-4 sm:px-6 lg:px-8 py-3">
                <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0 group">
                    <div class="relative w-9 h-9 sm:w-10 sm:h-10">
                        <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-primary-600 to-primary-500 shadow-soft group-hover:shadow-soft-lg transition-shadow duration-300"></div>
                        <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="relative w-full h-full flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    </div>
                    <span class="text-base sm:text-lg font-bold text-surface-900 dark:text-white">{{ config('app.name') }}</span>
                </a>

                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="{{ route('home') }}" wire:navigate class="btn-ghost btn-sm sm:btn text-xs sm:text-sm">Home</a>
                </div>
            </div>
        </nav>

        <main class="flex-1">
            <section class="relative px-4 sm:px-6 lg:px-8 py-12 sm:py-16 md:py-20">
                <div class="max-w-4xl mx-auto">
                    <div class="animate-fade-in-up mb-6">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300 border border-primary-200 dark:border-primary-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-pulse"></span>
                            Legal
                        </span>
                    </div>

                    <h1 class="animate-fade-in-up text-3xl sm:text-4xl md:text-5xl font-extrabold text-surface-900 dark:text-white mb-2">Privacy Policy</h1>
                    <p class="animate-fade-in-up-delayed text-surface-500 dark:text-surface-400 mb-8 sm:mb-10">Last updated: {{ date('F d, Y') }}</p>

                    <div class="animate-fade-in-up-delayed-2 space-y-8 text-sm sm:text-base text-surface-700 dark:text-surface-300 leading-relaxed">
                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">1. Introduction</h2>
                            <p class="mb-3">{{ config('app.name') }} ("we," "our," or "us") is committed to protecting the privacy of our users, including students, faculty, staff, and visitors to the OLLMCHS Library Management System. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our library management system and associated services.</p>
                            <p>By accessing or using the Library Management System, you agree to the collection and use of information in accordance with this policy. If you do not agree with the terms of this Privacy Policy, please do not access or use the system.</p>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">2. Information We Collect</h2>

                            <h3 class="font-semibold text-surface-900 dark:text-white mb-2">2.1 Personal Information</h3>
                            <p class="mb-3">We may collect personally identifiable information that you voluntarily provide to us when you register for an account or use the system, including:</p>
                            <ul class="list-disc pl-6 space-y-1.5 mb-4">
                                <li>Full name and identification number</li>
                                <li>Email address and institutional email</li>
                                <li>Phone number</li>
                                <li>Department, course, or program of study</li>
                                <li>Student or staff identification number</li>
                                <li>Profile photographs (if uploaded)</li>
                            </ul>

                            <h3 class="font-semibold text-surface-900 dark:text-white mb-2">2.2 Library Usage Data</h3>
                            <p class="mb-3">As part of normal library operations, we automatically collect the following information:</p>
                            <ul class="list-disc pl-6 space-y-1.5 mb-4">
                                <li>Books and materials borrowed, returned, or reserved</li>
                                <li>Digital resources accessed or downloaded</li>
                                <li>Fine history and payment records</li>
                                <li>Search queries and browsing activity within the catalog</li>
                                <li>Hold and reservation history</li>
                            </ul>

                            <h3 class="font-semibold text-surface-900 dark:text-white mb-2">2.3 Technical Data</h3>
                            <p class="mb-3">When you access the system, we automatically collect certain technical information:</p>
                            <ul class="list-disc pl-6 space-y-1.5">
                                <li>IP address and browser type</li>
                                <li>Device information (operating system, screen resolution)</li>
                                <li>Pages visited and time spent on each page</li>
                                <li>Referring URL and exit pages</li>
                                <li>Session duration and interaction patterns</li>
                            </ul>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">3. How We Use Your Information</h2>
                            <p class="mb-3">We use the collected information for the following purposes:</p>
                            <ul class="list-disc pl-6 space-y-1.5">
                                <li><strong>Library Operations:</strong> To manage borrowing, reservations, fines, and circulation of library materials</li>
                                <li><strong>Account Management:</strong> To create and maintain user accounts, process registrations, and authenticate users</li>
                                <li><strong>Communication:</strong> To send due-date reminders, overdue notices, fine notifications, and system announcements</li>
                                <li><strong>Analytics:</strong> To analyze usage patterns and improve library services and resource allocation</li>
                                <li><strong>Security:</strong> To protect the integrity of the system, detect unauthorized access, and prevent fraud</li>
                                <li><strong>Compliance:</strong> To comply with legal obligations and institutional policies</li>
                            </ul>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">4. Data Protection and Security</h2>
                            <p class="mb-3">We implement appropriate technical and organizational measures to protect your personal information:</p>
                            <ul class="list-disc pl-6 space-y-1.5 mb-4">
                                <li>All data transmitted between your browser and our servers is encrypted using TLS/SSL</li>
                                <li>Sensitive data such as passwords and financial information are encrypted at rest</li>
                                <li>Session data is encrypted by default</li>
                                <li>Access controls and permissions restrict data access to authorized personnel only</li>
                                <li>Regular security audits and vulnerability assessments are conducted</li>
                                <li>Rate limiting and brute-force protection mechanisms are in place</li>
                            </ul>
                            <p>Despite these measures, no method of electronic storage or transmission is 100% secure. We cannot guarantee absolute security of your data.</p>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">5. Data Retention</h2>
                            <p class="mb-3">We retain your personal information only for as long as necessary to fulfill the purposes described in this policy:</p>
                            <ul class="list-disc pl-6 space-y-1.5 mb-4">
                                <li>Active user accounts are retained for the duration of your affiliation with OLLMCHS</li>
                                <li>Borrowing records are retained in accordance with institutional record-keeping policies</li>
                                <li>Fine and payment records are retained for financial audit purposes as required by law</li>
                                <li>Inactive accounts may be archived or deleted after a period of inactivity</li>
                            </ul>
                            <p>Upon termination of your affiliation with OLLMCHS or upon your request, we will delete or anonymize your personal information, subject to legal retention requirements.</p>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">6. Sharing of Information</h2>
                            <p class="mb-3">We do not sell, trade, or rent your personal information to third parties. We may share your information only in the following circumstances:</p>
                            <ul class="list-disc pl-6 space-y-1.5 mb-4">
                                <li><strong>Within OLLMCHS:</strong> With authorized staff and faculty for legitimate educational and administrative purposes</li>
                                <li><strong>Service Providers:</strong> With trusted third-party service providers who assist us in operating the system (e.g., hosting, email delivery), subject to confidentiality agreements</li>
                                <li><strong>Legal Requirements:</strong> When required by law, court order, or governmental regulation</li>
                                <li><strong>Consent:</strong> With your explicit consent for specific purposes</li>
                            </ul>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">7. Cookies and Tracking</h2>
                            <p class="mb-3">We use essential cookies and similar technologies to:</p>
                            <ul class="list-disc pl-6 space-y-1.5 mb-4">
                                <li>Maintain your authenticated session throughout your visit</li>
                                <li>Remember your preferences and settings</li>
                                <li>Protect against cross-site request forgery (CSRF) attacks</li>
                                <li>Analyze system usage to improve performance</li>
                            </ul>
                            <p>We do not use third-party tracking cookies or behavioral advertising cookies. You may configure your browser to reject cookies, but this may affect the functionality of the system.</p>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">8. Your Rights</h2>
                            <p class="mb-3">You have the following rights regarding your personal information:</p>
                            <ul class="list-disc pl-6 space-y-1.5 mb-4">
                                <li><strong>Access:</strong> Request a copy of the personal information we hold about you</li>
                                <li><strong>Correction:</strong> Request correction of inaccurate or incomplete information</li>
                                <li><strong>Deletion:</strong> Request deletion of your personal information, subject to legal retention requirements</li>
                                <li><strong>Restriction:</strong> Request restriction of processing of your personal information</li>
                                <li><strong>Portability:</strong> Request transfer of your personal information in a structured, machine-readable format</li>
                                <li><strong>Objection:</strong> Object to processing of your personal information for certain purposes</li>
                            </ul>
                            <p>To exercise any of these rights, please contact us using the information provided in Section 10.</p>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">9. Changes to This Policy</h2>
                            <p>We may update this Privacy Policy from time to time. We will notify users of any material changes by posting the new policy on this page and updating the "Last updated" date. We encourage you to review this policy periodically.</p>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">10. Contact Information</h2>
                            <p class="mb-3">If you have any questions, concerns, or requests regarding this Privacy Policy, please contact us:</p>
                            <ul class="space-y-1.5">
                                <li><strong>Email:</strong> <a href="mailto:library@ollmchs.ac.ke" class="text-primary-600 dark:text-primary-400 hover:underline">library@ollmchs.ac.ke</a></li>
                                <li><strong>Address:</strong> Our Lady of Lourdes Mutomo College of Health Sciences, Mutomo, Kitui County, Kenya</li>
                                <li><strong>Phone:</strong> +254 (0) 123 456 789</li>
                            </ul>
                        </div>

                        <div class="flex items-center justify-center gap-4 pt-4">
                            <a href="{{ route('home') }}" class="btn-ghost text-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Home
                            </a>
                            <a href="{{ route('terms') }}" class="btn-outline btn-sm">Terms of Service</a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-surface-200 dark:border-surface-700 bg-white/50 dark:bg-surface-900/50 backdrop-blur-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-xs sm:text-sm text-surface-500 dark:text-surface-400">&copy; {{ date('Y') }} {{ config('app.name') }}. Mutomo Hospital College.</p>
                    <div class="flex items-center gap-4 sm:gap-6">
                        <a href="{{ route('privacy') }}" class="text-xs sm:text-sm text-surface-500 dark:text-surface-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Privacy Policy</a>
                        <a href="{{ route('terms') }}" class="text-xs sm:text-sm text-surface-500 dark:text-surface-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Terms of Service</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
