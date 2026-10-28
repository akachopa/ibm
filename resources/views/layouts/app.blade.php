<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', 'BIM Nusantara - Learn • Model • Build')</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F4F1EA; color: #232321; }
    .font-serif-title { font-family: 'Playfair Display', serif; }
    .sidebar-bg {
      background: linear-gradient(180deg, #252620 0%, #252620 55%, rgba(28,29,24,0.88) 75%, rgba(22,23,19,0.95) 100%),
                  url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80') bottom center / cover no-repeat;
    }
    .nav-active {
      background: linear-gradient(90deg, #6E5831 0%, #836A3D 100%);
      border-left: 4px solid #D9AB55;
      color: #FFFFFF;
    }
    .hero-banner {
      background: linear-gradient(90deg, rgba(124,113,97,0.94) 0%, rgba(105,94,78,0.76) 48%, rgba(24,22,18,0.68) 100%),
                  url('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1600&q=80') center / cover no-repeat;
    }
    .profile-banner {
      background: linear-gradient(90deg, #EFECE6 0%, #EAE4D7 52%, rgba(35,33,28,0.78) 100%),
                  url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1400&q=80') right center / cover no-repeat;
    }
    @media print {
      .no-print { display: none !important; }
    }
  </style>
  @stack('styles')
</head>
<body class="min-h-screen flex flex-col lg:flex-row overflow-x-hidden">

  <!-- MOBILE TOPBAR -->
  <div class="lg:hidden bg-[#252620] text-white px-4 py-3 flex items-center justify-between border-b border-white/10 sticky top-0 z-40 no-print">
    <div class="flex items-center gap-2.5">
      <div class="w-8 h-8 rounded bg-[#332B1E] border border-[#C69C49]/50 flex items-center justify-center text-[#D8AE5E]">
        <i data-lucide="building-2" class="w-5 h-5"></i>
      </div>
      <div>
        <div class="text-white font-bold tracking-wider text-xs">BIM NUSANTARA</div>
        <div class="text-[8px] tracking-[0.2em] text-stone-400">LEARN • MODEL • BUILD</div>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <button onclick="openSearchModal()" class="p-2 rounded-lg bg-white/10 text-stone-200">
        <i data-lucide="search" class="w-4 h-4"></i>
      </button>
      <button onclick="toggleMobileSidebar()" class="p-2 rounded-lg bg-white/10 text-stone-200">
        <i data-lucide="menu" class="w-5 h-5"></i>
      </button>
    </div>
  </div>

  <!-- MOBILE SIDEBAR BACKDROP -->
  <div id="sidebarBackdrop" onclick="toggleMobileSidebar()" class="fixed inset-0 bg-black/60 z-40 hidden lg:hidden no-print"></div>

  <!-- SIDEBAR -->
  <aside id="appSidebar" class="fixed lg:static inset-y-0 left-0 z-50 w-64 sidebar-bg text-stone-300 flex flex-col justify-between shrink-0 min-h-screen select-none transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out no-print">
    <div>
      <!-- Logo -->
      <div class="px-6 py-6 flex items-center justify-between border-b border-white/10">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
          <div class="w-10 h-10 rounded bg-[#332B1E] border border-[#C69C49]/50 flex items-center justify-center text-[#D8AE5E]">
            <i data-lucide="building-2" class="w-6 h-6"></i>
          </div>
          <div>
            <div class="text-white font-bold tracking-wider text-sm">BIM NUSANTARA</div>
            <div class="text-[9px] tracking-[0.22em] text-stone-400">LEARN • MODEL • BUILD</div>
          </div>
        </a>
        <button onclick="toggleMobileSidebar()" class="lg:hidden text-stone-400 hover:text-white">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <!-- Menu -->
      @php
        $currentRoute = Route::currentRouteName();
      @endphp
      <nav class="mt-5 px-3 space-y-1.5 text-sm">
        <a href="{{ route('dashboard') }}" class="{{ in_array($currentRoute, ['home', 'dashboard']) ? 'nav-active font-medium' : 'hover:bg-white/5' }} flex items-center gap-3 px-4 py-3 rounded-lg transition">
          <i data-lucide="home" class="w-4 h-4"></i> Dashboard
        </a>
        <a href="{{ route('belajar') }}" class="{{ $currentRoute === 'belajar' ? 'nav-active font-medium' : 'hover:bg-white/5' }} flex items-center gap-3 px-4 py-3 rounded-lg transition">
          <i data-lucide="book-open" class="w-4 h-4"></i> Belajar
        </a>
        <a href="{{ route('bim-lab') }}" class="{{ $currentRoute === 'bim-lab' ? 'nav-active font-medium' : 'hover:bg-white/5' }} flex items-center gap-3 px-4 py-3 rounded-lg transition">
          <i data-lucide="box" class="w-4 h-4"></i> BIM Lab
        </a>
        <a href="{{ route('bim-studio') }}" class="{{ $currentRoute === 'bim-studio' ? 'nav-active font-medium' : 'hover:bg-white/5' }} flex items-center gap-3 px-4 py-3 rounded-lg transition">
          <i data-lucide="monitor" class="w-4 h-4"></i> BIM Studio
        </a>
        <a href="{{ route('project') }}" class="{{ $currentRoute === 'project' ? 'nav-active font-medium' : 'hover:bg-white/5' }} flex items-center gap-3 px-4 py-3 rounded-lg transition">
          <i data-lucide="folder" class="w-4 h-4"></i> Project
        </a>
        <a href="{{ route('kompetensi') }}" class="{{ $currentRoute === 'kompetensi' ? 'nav-active font-medium' : 'hover:bg-white/5' }} flex items-center gap-3 px-4 py-3 rounded-lg transition">
          <i data-lucide="bar-chart-3" class="w-4 h-4"></i> Kompetensi
        </a>
        <a href="{{ route('profil') }}" class="{{ $currentRoute === 'profil' ? 'nav-active font-medium' : 'hover:bg-white/5' }} flex items-center gap-3 px-4 py-3 rounded-lg transition">
          <i data-lucide="award" class="w-4 h-4"></i> Profil & Sertifikat
        </a>
      </nav>
    </div>

    <!-- Bottom Quote -->
    <div class="p-6">
      <p class="font-serif-title text-white/90 text-base leading-snug">
        Membangun<br>Kompetensi BIM<br>untuk Indonesia<br>yang Lebih Baik
      </p>
      <div class="w-8 h-0.5 bg-[#D4A64E] mt-3"></div>
    </div>
  </aside>

  <!-- MAIN CONTENT WRAPPER -->
  <main class="flex-1 p-4 sm:p-6 lg:p-7 overflow-y-auto min-w-0">
    <!-- Top Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 no-print">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-stone-900">@yield('page_title', 'Dashboard BIM')</h1>
        <p class="text-xs text-stone-500 mt-0.5">@yield('page_subtitle', 'Pantau progres belajar, project, dan capaian kompetensi Anda')</p>
      </div>

      <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
        <!-- Global Search Trigger -->
        <div onclick="openSearchModal()" class="flex items-center bg-white border border-stone-200 rounded-xl px-3.5 py-2 w-full sm:w-64 lg:w-72 shadow-sm cursor-pointer hover:border-[#C89D4C] transition">
          <i data-lucide="search" class="w-4 h-4 text-stone-400 mr-2 shrink-0"></i>
          <input type="text" readonly placeholder="Cari materi, project, atau topik..." class="text-xs w-full outline-none bg-transparent text-stone-700 cursor-pointer" />
          <span class="text-[10px] bg-stone-100 text-stone-500 px-1.5 py-0.5 rounded border border-stone-200 shrink-0">Ctrl K</span>
        </div>

        <!-- Organization Badge -->
        <a href="{{ route('profil') }}" class="hidden sm:flex items-center gap-2 bg-[#EFECE4] hover:bg-[#e5e0d4] border border-stone-300/70 rounded-xl px-3.5 py-2 text-xs font-medium text-stone-700 transition">
          <i data-lucide="building" class="w-4 h-4 text-stone-600"></i>
          <span class="truncate max-w-[170px]">{{ $currentOrg->name ?? 'PT. Arsitek Muda Indonesia' }}</span>
          <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
        </a>

        <!-- Notifications Dropdown -->
        <div class="relative">
          <button onclick="toggleDropdown('notifDropdown')" class="relative p-2 rounded-xl bg-white border border-stone-200 hover:bg-stone-50 transition">
            <i data-lucide="bell" class="w-4 h-4 text-stone-700"></i>
            @if(($unreadNotificationsCount ?? 0) > 0)
              <span class="w-2 h-2 rounded-full bg-red-500 absolute top-1.5 right-1.5"></span>
            @endif
          </button>

          <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white border border-stone-200 rounded-2xl shadow-xl z-50 p-4">
            <div class="flex items-center justify-between pb-2.5 border-b border-stone-100">
              <span class="text-xs font-bold text-stone-900">Notifikasi ({{ $unreadNotificationsCount ?? 0 }} belum dibaca)</span>
              <form action="{{ route('notifications.mark-read') }}" method="POST">
                @csrf
                <button type="submit" class="text-[11px] text-[#8A6729] hover:underline font-medium">Tandai Dibaca</button>
              </form>
            </div>
            <div class="divide-y divide-stone-100 max-h-64 overflow-y-auto mt-1">
              @forelse($notifications ?? [] as $notif)
                <div class="py-2.5 flex items-start gap-2.5 {{ is_null($notif->read_at) ? 'bg-[#FAF8F4] -mx-2 px-2 rounded-lg' : '' }}">
                  <div class="w-7 h-7 rounded-lg bg-[#F5EBD6] text-[#8A6729] flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="bell" class="w-3.5 h-3.5"></i>
                  </div>
                  <div>
                    <div class="text-xs font-bold text-stone-800">{{ $notif->title }}</div>
                    <div class="text-[11px] text-stone-500 leading-snug mt-0.5">{{ $notif->message }}</div>
                  </div>
                </div>
              @empty
                <div class="py-4 text-center text-xs text-stone-400">Tidak ada notifikasi.</div>
              @endforelse
            </div>
          </div>
        </div>

        <!-- User Switcher Dropdown -->
        <div class="relative">
          <button onclick="toggleDropdown('userDropdown')" class="flex items-center gap-2.5 pl-1 text-left hover:opacity-90 transition">
            <div class="w-9 h-9 rounded-full bg-[#2C2D26] text-white flex items-center justify-center text-xs font-bold shrink-0">
              {{ $currentUserInitials ?? 'AD' }}
            </div>
            <div class="hidden sm:block">
              <div class="text-xs font-bold text-stone-800 flex items-center gap-1">
                {{ $currentUser->name ?? 'Andi Dwiputra' }}
                <i data-lucide="chevron-down" class="w-3 h-3"></i>
              </div>
              <div class="text-[11px] text-stone-500">{{ $currentOrgUser->role_name ?? ($currentUserPrefs['role_label'] ?? 'Peserta') }}</div>
            </div>
          </button>

          <div id="userDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white border border-stone-200 rounded-2xl shadow-xl z-50 p-3">
            <div class="px-2 py-1.5 border-b border-stone-100 mb-2">
              <div class="text-xs font-bold text-stone-900">{{ $currentUser->name }}</div>
              <div class="text-[11px] text-stone-500 truncate">{{ $currentUser->email }}</div>
            </div>
            <div class="text-[10px] font-bold uppercase tracking-wider text-stone-400 px-2 mb-1">Ganti Akun Demo</div>
            <div class="space-y-1">
              @foreach($allUsers ?? [] as $u)
                <form action="{{ route('switch-user') }}" method="POST">
                  @csrf
                  <input type="hidden" name="user_id" value="{{ $u->id }}" />
                  <button type="submit" class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs flex items-center justify-between {{ $u->id === $currentUser->id ? 'bg-[#F5EBD6] text-[#7A591E] font-bold' : 'hover:bg-stone-100 text-stone-700' }}">
                    <span>{{ $u->name }}</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-stone-200/70 text-stone-600">{{ $u->role_name ?? 'Member' }}</span>
                  </button>
                </form>
              @endforeach
            </div>
            <div class="border-t border-stone-100 mt-2 pt-2">
              <a href="{{ route('profil') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-stone-700 hover:bg-stone-100">
                <i data-lucide="user" class="w-3.5 h-3.5"></i> Pengaturan Profil & Sertifikat
              </a>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Flash Alerts -->
    @if(session('success'))
      <div id="flashToast" class="mb-5 bg-emerald-50 border border-emerald-300 text-emerald-900 px-4 py-3 rounded-xl flex items-center justify-between text-xs shadow-sm no-print">
        <div class="flex items-center gap-2.5">
          <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-700 shrink-0"></i>
          <span class="font-medium">{{ session('success') }}</span>
        </div>
        <button onclick="document.getElementById('flashToast').remove()" class="text-emerald-700 hover:text-emerald-950">
          <i data-lucide="x" class="w-4 h-4"></i>
        </button>
      </div>
    @endif

    @if($errors->any())
      <div id="errorToast" class="mb-5 bg-red-50 border border-red-300 text-red-900 px-4 py-3 rounded-xl flex items-center justify-between text-xs shadow-sm no-print">
        <div class="flex items-center gap-2.5">
          <i data-lucide="alert-triangle" class="w-4 h-4 text-red-700 shrink-0"></i>
          <div>
            @foreach($errors->all() as $err)
              <div>{{ $err }}</div>
            @endforeach
          </div>
        </div>
        <button onclick="document.getElementById('errorToast').remove()" class="text-red-700 hover:text-red-950">
          <i data-lucide="x" class="w-4 h-4"></i>
        </button>
      </div>
    @endif

    <!-- Page Body -->
    @yield('content')
  </main>

  <!-- GLOBAL SEARCH MODAL (Ctrl+K) -->
  <div id="searchModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 hidden items-start justify-center pt-20 p-4 no-print">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden">
      <div class="p-3.5 border-b border-stone-200 flex items-center gap-3 bg-white">
        <i data-lucide="search" class="w-4 h-4 text-[#8A6729]"></i>
        <input id="globalSearchInput" type="text" placeholder="Ketik nama materi, project, elemen BIM, kompetensi, atau sertifikat..." class="w-full text-xs outline-none bg-transparent text-stone-800" oninput="performGlobalSearch(this.value)" />
        <button onclick="closeSearchModal()" class="text-[10px] bg-stone-100 text-stone-500 px-2 py-1 rounded border">ESC</button>
      </div>
      <div id="globalSearchResults" class="p-4 max-h-80 overflow-y-auto space-y-2 text-xs">
        <div class="text-stone-400 text-center py-4">Ketik kata kunci untuk mencari di seluruh modul BIM Nusantara...</div>
      </div>
    </div>
  </div>

  <script>
    lucide.createIcons();

    function toggleMobileSidebar() {
      const sb = document.getElementById('appSidebar');
      const bd = document.getElementById('sidebarBackdrop');
      sb.classList.toggle('-translate-x-full');
      bd.classList.toggle('hidden');
    }

    function toggleDropdown(id) {
      const el = document.getElementById(id);
      if (!el) return;
      ['notifDropdown', 'userDropdown'].forEach(other => {
        if (other !== id) {
          const o = document.getElementById(other);
          if (o) o.classList.add('hidden');
        }
      });
      el.classList.toggle('hidden');
    }

    function openModal(id) {
      const el = document.getElementById(id);
      if (el) {
        el.classList.remove('hidden');
        el.classList.add('flex');
      }
    }

    function closeModal(id) {
      const el = document.getElementById(id);
      if (el) {
        el.classList.add('hidden');
        el.classList.remove('flex');
      }
    }

    function openSearchModal() {
      openModal('searchModal');
      setTimeout(() => document.getElementById('globalSearchInput')?.focus(), 50);
    }

    function closeSearchModal() {
      closeModal('searchModal');
    }

    document.addEventListener('keydown', function(e) {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        openSearchModal();
      } else if (e.key === 'Escape') {
        closeSearchModal();
      }
    });

    let searchTimer = null;
    function performGlobalSearch(q) {
      clearTimeout(searchTimer);
      const container = document.getElementById('globalSearchResults');
      if (!q || q.trim().length < 1) {
        container.innerHTML = '<div class="text-stone-400 text-center py-4">Ketik kata kunci untuk mencari di seluruh modul BIM Nusantara...</div>';
        return;
      }
      searchTimer = setTimeout(() => {
        fetch(`{{ route('search') }}?q=${encodeURIComponent(q)}`)
          .then(r => r.json())
          .then(data => {
            if (!data.results || data.results.length === 0) {
              container.innerHTML = '<div class="text-stone-500 text-center py-4">Tidak ditemukan hasil untuk "' + q + '".</div>';
              return;
            }
            container.innerHTML = data.results.map(item => `
              <a href="${item.url}" class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-stone-200 hover:border-[#C89D4C] transition">
                <div>
                  <div class="font-bold text-stone-900">${item.title}</div>
                  <div class="text-[11px] text-stone-500">${item.subtitle}</div>
                </div>
                <span class="text-[10px] bg-[#F5EBD6] text-[#8A6729] font-semibold px-2 py-0.5 rounded">${item.category}</span>
              </a>
            `).join('');
          });
      }, 180);
    }
  </script>
  @stack('scripts')
</body>
</html>

