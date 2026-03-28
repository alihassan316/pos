<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sale System')</title>
    @vite('resources/js/app.js')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
		svg.h-5{
			width:30px;
		}
        :root {
            --sidebar-width: 240px;
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --sidebar-bg: #1e1b4b;
            --sidebar-hover: #312e81;
            --sidebar-active: #4f46e5;
        }
        * { box-sizing: border-box; }
        body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; margin: 0; }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 99;
        }
        .sidebar-brand {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-brand .brand-icon {
            width: 36px; height: 36px;
            background: var(--primary);
            border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            color: #fff; font-size: 18px; margin-right: 10px;
        }
        .sidebar-brand span { color: #fff; font-weight: 700; font-size: 16px; }
        .sidebar-brand small { color: rgba(255,255,255,0.45); font-size: 11px; display: block; margin-top: 2px; }
        .sidebar-nav { padding: 16px 12px; flex: 1; }
        .nav-section-label {
            color: rgba(255,255,255,0.35);
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 8px 8px 4px;
        }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 10px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 2px;
            transition: all 0.15s;
        }
        .sidebar-nav a:hover { background: var(--sidebar-hover); color: #fff; }
        .sidebar-nav a.active { background: var(--sidebar-active); color: #fff; }
        .sidebar-nav a i { font-size: 16px; width: 20px; text-align: center; }
        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-footer a {
            display: flex; align-items: center; gap: 8px;
            color: rgba(255,255,255,0.5);
            text-decoration: none; font-size: 13px;
            padding: 8px 12px; border-radius: 8px;
            transition: all 0.15s;
        }
        .sidebar-footer a:hover { background: var(--sidebar-hover); color: #fff; }

        /* Main */
        .main-wrapper { margin-left: var(--sidebar-width); min-height: 100vh; display: flex; flex-direction: column; }
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 28px;
            height: 60px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-title { font-size: 16px; font-weight: 600; color: #1e293b; }
        .topbar-right { display: flex; align-items: center; gap: 16px; }
        .topbar-user {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: #475569;
        }
        .topbar-user .avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--primary);
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 600;
        }
        .page-content { padding: 28px; flex: 1; }

        /* Cards */
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.07); }
        .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; padding: 16px 20px; border-radius: 12px 12px 0 0 !important; }
        .card-header h5 { margin: 0; font-size: 15px; font-weight: 600; color: #1e293b; }

        /* Stat cards */
        .stat-card { border-radius: 12px; padding: 20px; color: #fff; position: relative; overflow: hidden; }
        .stat-card .stat-icon {
            width: 48px; height: 48px; border-radius: 10px;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; margin-bottom: 12px;
        }
        .stat-card .stat-value { font-size: 26px; font-weight: 700; line-height: 1; }
        .stat-card .stat-label { font-size: 13px; opacity: 0.85; margin-top: 4px; }
        .stat-card .stat-sub { font-size: 12px; opacity: 0.7; margin-top: 8px; }
        .stat-card::after {
            content: ''; position: absolute;
            width: 100px; height: 100px; border-radius: 50%;
            background: rgba(255,255,255,0.08);
            right: -20px; bottom: -20px;
        }

        /* Table */
        .table { font-size: 14px; }
        .table thead th { background: #f8fafc; color: #64748b; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; padding: 12px 16px; }
        .table tbody td { padding: 12px 16px; vertical-align: middle; color: #334155; border-bottom: 1px solid #f1f5f9; }
        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr:hover td { background: #f8fafc; }

        /* Badges */
        .badge { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px; }

        /* Buttons */
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        .btn-sm { font-size: 12px; padding: 5px 12px; border-radius: 6px; }

        /* Forms */
        .form-control, .form-select {
            border: 1px solid #e2e8f0; border-radius: 8px;
            font-size: 14px; padding: 9px 12px;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }
        .form-label { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }

        /* Alert */
        .alert { border: none; border-radius: 10px; font-size: 14px; }
        .alert-success { background: #f0fdf4; color: #166534; }

        /* Pagination */
        .pagination { gap: 4px; }
        .page-link { border-radius: 6px !important; border: 1px solid #e2e8f0; color: #475569; font-size: 13px; padding: 6px 12px; }
        .page-item.active .page-link { background: var(--primary); border-color: var(--primary); }
        
        @media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform .3s ease-in-out;
        position: fixed;
        z-index: 9999;
    }

    .sidebar.open {
        transform: translateX(0);
    }

    .main-wrapper {
        margin-left: 0 !important;
    }
}


    .sidebar-close-btn {
        position: absolute;
        right: 10px;
        top: 15px;
        background: transparent;
        border: none;
        color: #fff;
        font-size: 20px;
        display: none;
    }

    @media (max-width: 768px) {
        .sidebar-close-btn {
            display: block;
        }
    }

    @media (max-width: 576px) {
        .table td, .table th {
            padding: 8px 10px !important;
            font-size: 12px;
        }
        .table .badge {
            font-size: 10px;
            padding: 3px 8px;
        }
        .btn-sm {
            padding: 3px 6px !important;
        }
    }
    </style>
    @stack('styles')
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="d-flex align-items-center">
            <div class="brand-icon1">
            	<img src="{{asset('images/logor.png')}}" style="width:50px;" />
            </div>
            <div>
                <span>Asad Med Store</span>
                <small>Invoice System</small>
            </div>

            <button class="sidebar-close-btn d-md-none">
                <i class="bi bi-x-lg"></i>
            </button>
            
        </div>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section-label">Main</div>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>
        <a href="{{ route('summary') }}" class="{{ request()->routeIs('summary') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Summary
        </a>

        <div class="nav-section-label mt-2">Inventory</div>
        
		<a href="{{ route('purchases.index') }}" class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> Invoice Products
        </a>        
        
        <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> Stock Products
        </a>
        
         <li class="nav-item">
            <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <i class="bi bi-truck"></i> Suppliers
            </a>
        </li>

        <div class="nav-section-label mt-2">Sales</div>
        <a href="{{ route('sales.create') }}" class="{{ request()->routeIs('sales.create') ? 'active' : '' }}">
            <i class="bi bi-plus-circle"></i> New Sale (Ctrl + U)
        </a>
        <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.index') || request()->routeIs('sales.show') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> Sales History
        </a>
        
        <div class="nav-section-label mt-2">Settings</div>
        <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> Settings
        </a>
        
       
        
    </div>

    <div class="sidebar-footer">
        @auth
        <a href="{{ route('profile.edit') }}">
            <div class="avatar me-1">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            <div>
                <div style="color:#fff;font-size:13px;font-weight:500;">{{ Auth::user()->name }}</div>
                <div style="font-size:11px;">Settings</div>
            </div>
        </a>
        @endauth
    </div>
</div>

<div class="main-wrapper">
    <div class="topbar">

        <div class="d-flex align-items-center">
            <button id="sidebarToggle" class="btn btn-outline-secondary d-md-none me-2">
                <i class="bi bi-list"></i>
            </button>
            
            <div class="topbar-title">@yield('title', 'POS System')</div>
        </div>

        <div class="topbar-right">
            <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> New Sale (Ctrl + U)
            </a>
            @auth
            <div class="topbar-user">
                <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <span>{{ Auth::user()->name }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
            @endauth
        </div>
    </div>

    <div class="page-content">
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')

<script>
document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.querySelector(".sidebar");
    const toggle = document.getElementById("sidebarToggle");

    toggle.addEventListener("click", () => {
        sidebar.classList.toggle("open");
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.querySelector(".sidebar");
    const toggleBtn = document.querySelector(".mobile-menu-toggle");
    const closeBtn = document.querySelector(".sidebar-close-btn");

    if (toggleBtn) {
        toggleBtn.addEventListener("click", () => {
            sidebar.classList.toggle("open");
        });
    }
    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            sidebar.classList.remove("open");
        });
    }
});


document.addEventListener('keydown', function(e) {
    // Ignore if focus is in input, textarea, or contenteditable
    const target = e.target;
    if (['INPUT', 'TEXTAREA'].includes(target.tagName) || target.isContentEditable) return;

    // Ctrl + U → same tab
    if (e.ctrlKey && !e.shiftKey && e.key.toLowerCase() === 'u') {
        e.preventDefault();
        window.location.href = "{{ route('sales.create') }}";
    }

    // Ctrl + Shift + U → new tab
    if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'u') {
        e.preventDefault();
        window.open("{{ route('sales.create') }}", "_blank");
    }
	
	if (e.ctrlKey && !e.shiftKey && e.key.toLowerCase() === 'v') {
        e.preventDefault();
        window.location.href = "{{ route('dashboard') }}"; // make sure this route exists
    }
});

</script>

</body>
</html>
