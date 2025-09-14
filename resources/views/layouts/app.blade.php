<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0284c7">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Travel Order System') }} @yield('title')</title>
    <meta name="description" content="Modern travel order management system for efficient business travel planning and approval workflow.">

    <!-- Preconnect to external domains -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">

    <!-- Preload critical fonts -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap"></noscript>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">

    <!-- Additional meta tags -->
    <meta name="format-detection" content="telephone=no">
    <meta name="msapplication-tap-highlight" content="no">
    
    @stack('styles')
</head>
<body class="d-flex flex-column h-100">
    <div id="app" class="d-flex flex-column h-100">
        <!-- Modern Navigation -->
        <nav class="navbar navbar-expand-lg navbar-modern sticky-top">
            <div class="container-fluid">
                <!-- Brand -->
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                    <i class="fas fa-bus me-2 text-primary-600"></i>
                    <span class="fw-bold">{{ config('app.name', 'Travel Order System') }}</span>
                </a>

                <!-- Mobile Menu Toggle -->
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Desktop Navigation -->
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Navigation -->
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('travel-orders.*') ? 'active' : '' }}" href="{{ route('travel-orders.index') }}">
                                    <i class="fas fa-suitcase-rolling"></i>
                                    <span>Travel Orders</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}" href="{{ route('approvals.index') }}">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Approvals</span>
                                    @if(isset($pendingApprovalCount) && $pendingApprovalCount > 0)
                                        <span class="badge bg-warning rounded-pill ms-1">{{ $pendingApprovalCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('workflows.*') ? 'active' : '' }}" href="{{ route('workflows.index') }}">
                                    <i class="fas fa-sitemap"></i>
                                    <span>Workflows</span>
                                </a>
                            </li>
                            @can('viewAny', App\Models\TravelOrder::class)
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.travel-orders.index') }}">
                                        <i class="fas fa-cogs"></i>
                                        <span>Admin</span>
                                    </a>
                                </li>
                            @endcan
                        @endauth
                    </ul>

                    <!-- Right Side Navigation -->
                    <ul class="navbar-nav">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">
                                        <i class="fas fa-sign-in-alt me-1"></i>{{ __('Login') }}
                                    </a>
                                </li>
                            @endif
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="btn btn-primary btn-sm ms-2" href="{{ route('register') }}">
                                        <i class="fas fa-user-plus me-1"></i>{{ __('Register') }}
                                    </a>
                                </li>
                            @endif
                        @else
                            <!-- Notifications -->
                            <li class="nav-item dropdown me-3">
                                <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-bell"></i>
                                    @if(isset($pendingApprovalCount) && $pendingApprovalCount > 0)
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            {{ $pendingApprovalCount }}
                                            <span class="visually-hidden">pending approvals</span>
                                        </span>
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><h6 class="dropdown-header">Recent Notifications</h6></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#">Travel order approved</a></li>
                                    <li><a class="dropdown-item" href="#">New approval required</a></li>
                                    <li><a class="dropdown-item" href="#">Document uploaded</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-center" href="#">View all notifications</a></li>
                                </ul>
                            </li>

                            <!-- User Profile -->
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <div class="user-avatar me-2">
                                        <i class="fas fa-user-circle fa-lg"></i>
                                    </div>
                                    <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end glass">
                                    <div class="dropdown-header">
                                        <strong>{{ Auth::user()->name }}</strong>
                                        <br><small class="text-muted">{{ Auth::user()->email }}</small>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-user me-2"></i>Profile
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-cog me-2"></i>Settings
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i>{{ __('Logout') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Mobile Offcanvas Navigation -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel">
            <div class="offcanvas-header glass">
                <h5 class="offcanvas-title" id="mobileNavLabel">
                    <i class="fas fa-bus me-2 text-primary"></i>
                    {{ config('app.name', 'Travel Order System') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                @auth
                    <div class="user-info mb-4 p-3 glass rounded">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-circle fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                                <small class="text-muted">{{ Auth::user()->email }}</small>
                            </div>
                        </div>
                    </div>
                @endauth
                
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('travel-orders.*') ? 'active' : '' }}" href="{{ route('travel-orders.index') }}">
                                <i class="fas fa-suitcase-rolling"></i> Travel Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}" href="{{ route('approvals.index') }}">
                                <i class="fas fa-check-circle"></i> Approvals
                                @if(isset($pendingApprovalCount) && $pendingApprovalCount > 0)
                                    <span class="badge bg-warning rounded-pill ms-1">{{ $pendingApprovalCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('workflows.*') ? 'active' : '' }}" href="{{ route('workflows.index') }}">
                                <i class="fas fa-sitemap"></i> Workflows
                            </a>
                        </li>
                        @can('viewAny', App\Models\TravelOrder::class)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.travel-orders.index') }}">
                                    <i class="fas fa-cogs"></i> Admin
                                </a>
                            </li>
                        @endcan
                        <hr>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-user"></i> Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                            <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-grow-1 py-4">
            <!-- Flash Messages -->
            <div class="container-fluid">
                @if (session('success'))
                    <div class="alert alert-modern alert-success alert-dismissible fade show animate-slide-up" role="alert">
                        <i class="fas fa-check-circle alert-icon"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-modern alert-danger alert-dismissible fade show animate-slide-up" role="alert">
                        <i class="fas fa-exclamation-circle alert-icon"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="alert alert-modern alert-warning alert-dismissible fade show animate-slide-up" role="alert">
                        <i class="fas fa-exclamation-triangle alert-icon"></i>
                        <div>{{ session('warning') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-modern alert-info alert-dismissible fade show animate-slide-up" role="alert">
                        <i class="fas fa-info-circle alert-icon"></i>
                        <div>{{ session('info') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-modern alert-danger alert-dismissible fade show animate-slide-up" role="alert">
                        <i class="fas fa-exclamation-triangle alert-icon"></i>
                        <div>
                            <h6 class="mb-2">Please fix the following errors:</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>
            
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="mt-auto py-4 bg-light border-top">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="text-muted mb-0">
                            &copy; {{ date('Y') }} {{ config('app.name', 'Travel Order System') }}. All rights reserved.
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted">
                            Version 2.0 | Built with ❤️ for modern travel management
                        </small>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    <!-- Additional JavaScript for enhanced interactivity -->
    <script>
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Add loading state to buttons on form submit
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
                    if (submitButton) {
                        submitButton.classList.add('loading');
                        submitButton.disabled = true;
                    }
                });
            });
        });

        // Smooth scrolling for anchor links
        document.addEventListener('DOMContentLoaded', function() {
            const anchorLinks = document.querySelectorAll('a[href^="#"]');
            anchorLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href !== '#' && href !== '#!') {
                        const target = document.querySelector(href);
                        if (target) {
                            e.preventDefault();
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    }
                });
            });
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            const mobileNav = document.getElementById('mobileNav');
            const navToggler = document.querySelector('[data-bs-target="#mobileNav"]');
            
            if (mobileNav && !mobileNav.contains(e.target) && !navToggler.contains(e.target)) {
                const offcanvas = bootstrap.Offcanvas.getInstance(mobileNav);
                if (offcanvas) {
                    offcanvas.hide();
                }
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
