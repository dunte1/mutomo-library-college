<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Terms of Service - {{ config('app.name') }} Library Management System">

    <title>Terms of Service | {{ config('app.name') }}</title>

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

                    <h1 class="animate-fade-in-up text-3xl sm:text-4xl md:text-5xl font-extrabold text-surface-900 dark:text-white mb-2">Terms of Service</h1>
                    <p class="animate-fade-in-up-delayed text-surface-500 dark:text-surface-400 mb-8 sm:mb-10">Last updated: {{ date('F d, Y') }}</p>

                    <div class="animate-fade-in-up-delayed-2 space-y-8 text-sm sm:text-base text-surface-700 dark:text-surface-300 leading-relaxed">
                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">1. Acceptance of Terms</h2>
                            <p class="mb-3">By accessing or using the {{ config('app.name') }} Library Management System ("the System"), you agree to be bound by these Terms of Service ("Terms"). If you do not agree to these Terms, you may not access or use the System.</p>
                            <p>These Terms constitute a legally binding agreement between you ("User," "you," or "your") and Our Lady of Lourdes Mutomo College of Health Sciences (OLLMCHS).</p>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">2. Description of Service</h2>
                            <p class="mb-3">The System provides the following services to authorized users:</p>
                            <ul class="list-disc pl-6 space-y-1.5 mb-4">
                                <li>Library catalog search and browsing</li>
                                <li>Borrowing, returning, and renewing library materials</li>
                                <li>Reserving books and other materials</li>
                                <li>Accessing digital resources, including eBooks, journals, and multimedia</li>
                                <li>Managing library fines and fees</li>
                                <li>Receiving notifications and alerts regarding library activities</li>
                                <li>Generating reports and analytics</li>
                            </ul>
                            <p>The System is provided for educational and research purposes within OLLMCHS.</p>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">3. Eligibility and Registration</h2>
                            <p class="mb-3">To use the System, you must:</p>
                            <ul class="list-disc pl-6 space-y-1.5 mb-4">
                                <li>Be a currently enrolled student, faculty member, or staff of OLLMCHS</li>
                                <li>Be at least 18 years of age or have the consent of a parent or guardian</li>
                                <li>Provide accurate, current, and complete registration information</li>
                                <li>Maintain and update your registration information as needed</li>
                            </ul>
                            <p>You are responsible for safeguarding your account credentials and for all activities that occur under your account. Notify us immediately of any unauthorized use of your account.</p>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">4. User Responsibilities</h2>
                            <p class="mb-3">As a user of the System, you agree to:</p>
                            <ul class="list-disc pl-6 space-y-1.5 mb-4">
                                <li>Comply with all OLLMCHS library policies and procedures</li>
                                <li>Return borrowed materials by the due date</li>
                                <li>Pay any fines or fees assessed to your account</li>
                                <li>Use library materials responsibly and respect the rights of other users</li>
                                <li>Report lost or damaged materials promptly</li>
                                <li>Not share your account credentials with others</li>
                                <li>Not use the System for any unlawful purpose</li>
                            </ul>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">5. Acceptable Use</h2>
                            <p class="mb-3">You agree not to engage in any of the following prohibited activities:</p>
                            <ul class="list-disc pl-6 space-y-1.5 mb-4">
                                <li>Attempting to access, modify, or delete another user's data without authorization</li>
                                <li>Attempting to circumvent security measures, authentication protocols, or access controls</li>
                                <li>Introducing malware, viruses, or other harmful code into the System</li>
                                <li>Conducting any automated or systematic data collection without prior written consent</li>
                                <li>Using the System for commercial purposes without authorization</li>
                                <li>Uploading or sharing copyrighted materials without the right to do so</li>
                                <li>Engaging in any activity that disrupts or interferes with the System's operation</li>
                            </ul>
                            <p>Violation of these provisions may result in immediate suspension or termination of your account and may be referred to appropriate institutional authorities.</p>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">6. Borrowing Rules and Fines</h2>
                            <p class="mb-3">Borrowing privileges are subject to the following general rules:</p>
                            <ul class="list-disc pl-6 space-y-1.5 mb-4">
                                <li>Loan periods vary by material type and user category</li>
                                <li>Materials may be renewed subject to availability and renewal limits</li>
                                <li>Overdue materials accrue fines at rates determined by the Library</li>
                                <li>Lost or damaged materials will result in replacement costs and processing fees</li>
                                <li>Excessive fines or overdue items may result in suspension of borrowing privileges</li>
                                <li>The Library reserves the right to recall materials at any time</li>
                            </ul>
                            <p>Detailed circulation policies are available in the Library and may be updated from time to time.</p>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">7. Digital Resources</h2>
                            <p class="mb-3">Access to digital resources is governed by additional terms:</p>
                            <ul class="list-disc pl-6 space-y-1.5 mb-4">
                                <li>Digital resources are licensed for educational use only</li>
                                <li>Systematic downloading of content is prohibited</li>
                                <li>Users must respect copyright and intellectual property rights</li>
                                <li>Sharing of access credentials or downloaded content with unauthorized users is prohibited</li>
                                <li>Usage may be subject to publisher-imposed restrictions</li>
                            </ul>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">8. Intellectual Property</h2>
                            <p class="mb-3">The System and its original content, features, and functionality are owned by OLLMCHS and are protected by Kenyan and international copyright, trademark, and other intellectual property laws.</p>
                            <p>Library catalog metadata and user-generated content within the System may be subject to separate licensing terms as indicated.</p>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">9. Limitation of Liability</h2>
                            <p class="mb-3">To the fullest extent permitted by applicable law:</p>
                            <ul class="list-disc pl-6 space-y-1.5 mb-4">
                                <li>The System is provided "as is" and "as available" without warranties of any kind</li>
                                <li>OLLMCHS shall not be liable for any indirect, incidental, special, or consequential damages</li>
                                <li>OLLMCHS does not guarantee uninterrupted or error-free operation of the System</li>
                                <li>OLLMCHS is not responsible for the content of third-party resources accessed through the System</li>
                            </ul>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">10. Termination</h2>
                            <p class="mb-3">We reserve the right to suspend or terminate your access to the System at any time, without prior notice, for:</p>
                            <ul class="list-disc pl-6 space-y-1.5 mb-4">
                                <li>Violation of these Terms</li>
                                <li>Violation of library policies or institutional rules</li>
                                <li>Unauthorized access or security breaches</li>
                                <li>Extended inactivity of your account</li>
                                <li>Upon termination of your affiliation with OLLMCHS</li>
                            </ul>
                            <p>Upon termination, your borrowing privileges will cease, and you remain responsible for any outstanding fines or unreturned materials.</p>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">11. Changes to Terms</h2>
                            <p>We reserve the right to modify these Terms at any time. Changes will be effective immediately upon posting. Continued use of the System after changes constitutes acceptance of the updated Terms. We will make reasonable efforts to notify users of material changes.</p>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">12. Governing Law</h2>
                            <p class="mb-3">These Terms shall be governed by and construed in accordance with the laws of the Republic of Kenya. Any disputes arising under these Terms shall be subject to the exclusive jurisdiction of the courts of Kenya.</p>
                        </div>

                        <div class="card p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-white mb-4">13. Contact Information</h2>
                            <p class="mb-3">For questions, concerns, or inquiries regarding these Terms, please contact:</p>
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
                            <a href="{{ route('privacy') }}" class="btn-outline btn-sm">Privacy Policy</a>
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
