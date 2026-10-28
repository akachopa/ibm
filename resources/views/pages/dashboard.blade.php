@extends('layouts.app')

@section('title', 'Dashboard BIM - BIM Nusantara')
@section('page_title', 'Dashboard BIM')
@section('page_subtitle', 'Pantau progres belajar, project, dan capaian kompetensi Anda')

@section('content')
  <!-- Hero Banner -->
  <section class="hero-banner rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden shadow-sm mb-6 flex flex-col md:flex-row justify-between md:items-end gap-6 min-h-[210px]">
    <div class="max-w-xl">
      <div class="text-[11px] tracking-[0.2em] uppercase text-[#F4D38B] font-semibold mb-2">BELAJAR BIM, BANGUN MASA DEPAN</div>
      <h2 class="font-serif-title text-3xl sm:text-4xl font-semibold leading-tight mb-2">Dari Pengetahuan<br>Menuju Karya Nyata</h2>
      <p class="text-xs text-stone-100/90 leading-relaxed mb-4 max-w-md">
        Kembangkan kompetensi BIM Anda melalui pelatihan terstruktur, praktik langsung, dan project nyata di industri konstruksi.
      </p>
      <a href="{{ route('belajar') }}" class="inline-flex items-center gap-2 bg-[#C89D4C] hover:bg-[#b78d3f] text-stone-950 font-semibold text-xs px-4 py-2.5 rounded-lg transition">
        Lanjutkan Belajar <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
      </a>
    </div>
    <div class="md:text-right max-w-xs">
      <p class="font-serif-title text-sm italic text-white/95">"Bangunan yang lebih baik<br>dimulai dari talenta yang lebih baik."</p>
      <div class="w-8 h-0.5 bg-[#D4A64E] mt-2 md:ml-auto"></div>
    </div>
  </section>

  <!-- 4 KPI Cards -->
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-[#FAF8F4] border border-stone-200/80 rounded-2xl p-4">
      <div class="flex items-start justify-between">
        <div class="flex gap-3">
          <div class="w-11 h-11 rounded-xl bg-[#EFE9DC] text-[#7A5D29] flex items-center justify-center shrink-0">
            <i data-lucide="book-open" class="w-5 h-5"></i>
          </div>
          <div>
            <div class="text-xs text-stone-600 font-medium">Progress Pelatihan</div>
            <div class="text-3xl font-bold text-stone-900 mt-0.5">{{ $overallTrainingProgress }}%</div>
          </div>
        </div>
        <span class="text-[11px] bg-emerald-100 text-emerald-800 font-semibold px-2 py-0.5 rounded-full mt-4">↑ 12%</span>
      </div>
      <div class="w-full h-2 bg-stone-200 rounded-full mt-3 overflow-hidden">
        <div class="h-full bg-[#A87F32] rounded-full" style="width: {{ $overallTrainingProgress }}%"></div>
      </div>
      <div class="text-[11px] text-stone-500 mt-2">{{ $completedModulesCount }} dari {{ $totalModulesCount }} modul selesai</div>
    </div>

    <div class="bg-[#FAF8F4] border border-stone-200/80 rounded-2xl p-4">
      <div class="flex items-start justify-between">
        <div class="flex gap-3">
          <div class="w-11 h-11 rounded-xl bg-[#EAE8DE] text-[#5B6049] flex items-center justify-center shrink-0">
            <i data-lucide="folder" class="w-5 h-5"></i>
          </div>
          <div>
            <div class="text-xs text-stone-600 font-medium">Project Aktif</div>
            <div class="text-3xl font-bold text-stone-900 mt-0.5">{{ $projects->count() }}</div>
          </div>
        </div>
        <span class="text-[11px] bg-blue-100 text-blue-800 font-medium px-2.5 py-1 rounded-lg mt-3">Sedang dikerjakan</span>
      </div>
      <div class="text-[11px] text-stone-500 mt-6">{{ $activeProjectsCount }} project dalam pengerjaan</div>
    </div>

    <div class="bg-[#FAF8F4] border border-stone-200/80 rounded-2xl p-4">
      <div class="flex items-start justify-between">
        <div class="flex gap-3">
          <div class="w-11 h-11 rounded-xl bg-[#EAE8DE] text-[#6E5C38] flex items-center justify-center shrink-0">
            <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
          </div>
          <div>
            <div class="text-xs text-stone-600 font-medium">Nilai Rata-rata</div>
            <div class="text-3xl font-bold text-stone-900 mt-0.5">{{ $avgScore }}</div>
          </div>
        </div>
        <span class="text-[11px] bg-emerald-100 text-emerald-800 font-semibold px-2 py-0.5 rounded-full mt-4">↑ 6%</span>
      </div>
      <div class="text-[11px] text-stone-500 mt-6">Dari {{ $assessmentsCount }} penilaian tugas</div>
    </div>

    <div class="bg-[#FAF8F4] border border-stone-200/80 rounded-2xl p-4">
      <div class="flex items-start justify-between">
        <div class="flex gap-3">
          <div class="w-11 h-11 rounded-xl bg-[#EFE9DC] text-[#7A5D29] flex items-center justify-center shrink-0">
            <i data-lucide="award" class="w-5 h-5"></i>
          </div>
          <div>
            <div class="text-xs text-stone-600 font-medium">Sertifikat</div>
            <div class="text-3xl font-bold text-stone-900 mt-0.5">{{ $certificatesCount }}</div>
          </div>
        </div>
        <span class="text-[11px] bg-[#F3E9D2] text-[#8B6827] font-medium px-2.5 py-1 rounded-lg mt-3">Terverifikasi</span>
      </div>
      <div class="text-[11px] text-stone-500 mt-6">Dari 7 jalur kompetensi</div>
    </div>
  </section>

  <!-- Middle 3 Columns -->
  <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-6">
    <!-- Jalur Pembelajaran Anda -->
    <div class="lg:col-span-4 bg-[#FAF8F4] border border-stone-200/80 rounded-2xl p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-base text-stone-900">Jalur Pembelajaran Anda</h3>
        <a href="{{ route('belajar') }}" class="text-xs text-[#8A6729] font-medium flex items-center gap-1 hover:underline">Lihat Semua <i data-lucide="arrow-right" class="w-3 h-3"></i></a>
      </div>
      <div class="space-y-5 relative">
        @foreach($modules->take(4) as $mod)
          <a href="{{ route('belajar', ['module_id' => $mod->id]) }}" class="flex items-start justify-between gap-3 group">
            <div class="flex items-start gap-3">
              @if($mod->progress_percent >= 100)
                <div class="w-5 h-5 rounded-full bg-emerald-700 text-white flex items-center justify-center mt-0.5 shrink-0">
                  <i data-lucide="check" class="w-3 h-3"></i>
                </div>
              @elseif($mod->progress_percent > 0)
                <div class="w-5 h-5 rounded-full border-2 border-[#8A6729] flex items-center justify-center mt-0.5 shrink-0">
                  <div class="w-2 h-2 rounded-full bg-[#8A6729]"></div>
                </div>
              @else
                <div class="w-5 h-5 rounded-full border-2 border-stone-300 mt-0.5 shrink-0"></div>
              @endif
              <div>
                <div class="text-xs font-bold text-stone-900 group-hover:text-[#8A6729] transition">{{ $mod->title }}</div>
                @if($mod->progress_percent >= 100)
                  <div class="text-[11px] text-emerald-700 font-medium mt-0.5">Selesai • <span class="text-stone-500 font-normal">{{ $mod->completed_lessons }}/{{ $mod->total_lessons }} materi</span></div>
                @elseif($mod->progress_percent > 0)
                  <div class="text-[11px] text-stone-600 mt-0.5">Sedang berlangsung • {{ $mod->completed_lessons }}/{{ $mod->total_lessons }} materi</div>
                @else
                  <div class="text-[11px] text-stone-500 mt-0.5">Belum dimulai • 0/{{ $mod->total_lessons }} materi</div>
                @endif
              </div>
            </div>
            <div class="text-right w-20 shrink-0">
              <div class="text-[11px] font-bold {{ $mod->progress_percent >= 100 ? 'text-emerald-800' : 'text-stone-700' }}">{{ $mod->progress_percent }}%</div>
              <div class="w-full h-1.5 bg-stone-200 rounded-full mt-1 overflow-hidden">
                <div class="h-full {{ $mod->progress_percent >= 100 ? 'bg-emerald-700' : 'bg-[#8A6729]' }}" style="width: {{ $mod->progress_percent }}%"></div>
              </div>
            </div>
          </a>
        @endforeach
      </div>
    </div>

    <!-- Tugas Mendatang -->
    <div class="lg:col-span-4 bg-[#FAF8F4] border border-stone-200/80 rounded-2xl p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-base text-stone-900">Tugas Mendatang</h3>
        <div class="flex items-center gap-2">
          <button onclick="openModal('addAssignmentModal')" class="text-[11px] bg-[#F5EBD6] text-[#7A591E] font-semibold px-2.5 py-1 rounded-lg hover:bg-[#ebdcc0] transition">+ Tugas</button>
          <a href="{{ route('project', ['tab' => 'submissions']) }}" class="text-xs text-[#8A6729] font-medium flex items-center gap-1 hover:underline">Semua <i data-lucide="arrow-right" class="w-3 h-3"></i></a>
        </div>
      </div>
      <div class="space-y-3.5">
        @forelse($assignments as $idx => $task)
          <div class="flex items-center justify-between {{ ! $loop->last ? 'pb-3 border-b border-stone-200/60' : '' }}">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-10 h-11 rounded-lg {{ $idx === 0 ? 'bg-red-50 border border-red-200/60 text-red-800' : 'bg-stone-100 border border-stone-200 text-stone-800' }} flex flex-col items-center justify-center shrink-0">
                <span class="text-[9px] font-bold uppercase">{{ $task->month_short }}</span>
                <span class="text-sm font-bold leading-none">{{ $task->day_num }}</span>
              </div>
              <div class="min-w-0">
                <div class="text-xs font-bold text-stone-800 truncate">{{ $task->title }}</div>
                <div class="text-[11px] text-stone-500 truncate">{{ $task->description }}</div>
              </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
              <span class="text-[10px] {{ $idx === 0 ? 'bg-red-100 text-red-700 font-semibold' : ($idx === 1 ? 'bg-[#F5EBD6] text-[#8B6827] font-semibold' : 'bg-stone-200/70 text-stone-700 font-medium') }} px-2.5 py-1 rounded-md">
                {{ $task->days_left }} hari lagi
              </span>
              <form action="{{ route('project.assignments.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Hapus tugas ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-stone-400 hover:text-red-600 p-1"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
              </form>
            </div>
          </div>
        @empty
          <div class="text-xs text-stone-400 py-4 text-center">Belum ada tugas mendatang.</div>
        @endforelse
      </div>
    </div>

    <!-- Project Unggulan -->
    <div class="lg:col-span-4 bg-[#FAF8F4] border border-stone-200/80 rounded-2xl p-5">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-serif-title font-bold text-base text-stone-900">Project Unggulan</h3>
        <a href="{{ route('project') }}" class="text-xs text-[#8A6729] font-medium flex items-center gap-1 hover:underline">Lihat Project <i data-lucide="arrow-right" class="w-3 h-3"></i></a>
      </div>
      @if($featuredProject)
        @php
          $fpMeta = json_decode($featuredProject->settings ?? '{}', true) ?: [];
        @endphp
        <div class="relative rounded-xl overflow-hidden h-36 border border-stone-200">
          <img src="{{ $featuredProject->thumbnail_url ?: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80' }}" class="w-full h-full object-cover" alt="Project" />
          <a href="{{ route('bim-studio', ['project_id' => $featuredProject->id]) }}" class="absolute right-2 top-2 bg-white/95 rounded-lg p-1.5 flex flex-col gap-1.5 shadow hover:bg-white transition" title="Buka di BIM Studio">
            <i data-lucide="box" class="w-3.5 h-3.5 text-stone-700"></i>
            <i data-lucide="layers" class="w-3.5 h-3.5 text-stone-700"></i>
            <i data-lucide="maximize-2" class="w-3.5 h-3.5 text-stone-700"></i>
          </a>
        </div>
        <div class="flex items-start justify-between mt-3">
          <div>
            <h4 class="font-bold text-sm text-stone-900">{{ $featuredProject->title }}</h4>
            <p class="text-[11px] text-stone-500">Project {{ ucfirst($featuredProject->category) }} • Pemodelan Arsitektur</p>
          </div>
          <span class="text-[10px] bg-blue-100 text-blue-800 font-semibold px-2.5 py-1 rounded-full">
            {{ ucfirst(str_replace('_', ' ', $featuredProject->status)) }}
          </span>
        </div>
        <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-stone-200/70 text-center">
          <div>
            <div class="text-xs font-bold text-stone-900">{{ round($featuredProject->progress_percent) }}%</div>
            <div class="text-[10px] text-stone-500">Progres Model</div>
          </div>
          <div>
            <div class="text-xs font-bold text-stone-900">{{ $fpMeta['floors'] ?? 12 }}</div>
            <div class="text-[10px] text-stone-500">Jumlah Lantai</div>
          </div>
          <div>
            <div class="text-xs font-bold text-stone-900">{{ number_format($fpMeta['area_m2'] ?? 2450, 0, ',', '.') }} m²</div>
            <div class="text-[10px] text-stone-500">Luas Bangunan</div>
          </div>
        </div>
      @endif
    </div>
  </section>

  <!-- Bottom 3 Columns -->
  <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-5">
    <!-- Aktivitas Terakhir -->
    <div class="lg:col-span-4 bg-[#FAF8F4] border border-stone-200/80 rounded-2xl p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-base text-stone-900">Aktivitas Terakhir</h3>
        <a href="{{ route('project') }}" class="text-xs text-[#8A6729] font-medium hover:underline">Lihat Semua →</a>
      </div>
      <div class="space-y-3.5">
        @foreach($activities as $act)
          @php
            $m = $act->meta;
            $badgeType = $m['badge_type'] ?? 'emerald';
            $badgeClass = match($badgeType) {
              'blue' => 'bg-blue-100 text-blue-800 font-bold',
              'gold' => 'bg-[#F5EBD6] text-[#8A6729] font-semibold',
              default => 'bg-emerald-100 text-emerald-800 font-semibold',
            };
          @endphp
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-8 h-8 rounded-lg {{ $badgeType === 'gold' ? 'bg-[#F5EBD6] text-[#8A6729]' : 'bg-stone-200/70 text-stone-700' }} flex items-center justify-center shrink-0">
                <i data-lucide="{{ $m['icon'] ?? 'check-circle-2' }}" class="w-4 h-4"></i>
              </div>
              <div class="min-w-0">
                <div class="text-xs font-semibold text-stone-800 truncate">{{ $m['title'] ?? $act->action }}</div>
                <div class="text-[11px] text-stone-500">{{ $m['time_label'] ?? \Carbon\Carbon::parse($act->created_at)->diffForHumans() }}</div>
              </div>
            </div>
            <span class="text-[10px] {{ $badgeClass }} px-2.5 py-1 rounded-full shrink-0">{{ $m['badge'] ?? 'Selesai' }}</span>
          </div>
        @endforeach
      </div>
    </div>

    <!-- Status Pengumpulan Project -->
    <div class="lg:col-span-4 bg-[#FAF8F4] border border-stone-200/80 rounded-2xl p-5 flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-serif-title font-bold text-base text-stone-900">Status Pengumpulan Project</h3>
          <a href="{{ route('project', ['tab' => 'submissions']) }}" class="text-xs text-[#8A6729] font-medium hover:underline">Lihat Semua →</a>
        </div>
        <div class="flex items-center gap-3 mb-4">
          <img src="{{ $submissionProject?->thumbnail_url ?: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=200&q=80' }}" class="w-16 h-12 rounded-lg object-cover" />
          <div>
            <div class="text-xs font-bold text-stone-900">{{ $submissionProject?->title ?? 'Project 01 – Rumah Tinggal 2 Lantai' }}</div>
            <div class="text-[11px] text-stone-500">Pemodelan Arsitektur ({{ $submissionProject?->code ?? 'PRJ-001' }})</div>
          </div>
        </div>
        <!-- Stepper -->
        <div class="grid grid-cols-4 text-center my-4 relative">
          <div>
            <div class="w-5 h-5 mx-auto rounded-full bg-emerald-700 text-white flex items-center justify-center text-[10px]">✓</div>
            <div class="text-[10px] text-stone-700 mt-1">Upload<br>Model</div>
          </div>
          <div>
            <div class="w-5 h-5 mx-auto rounded-full bg-emerald-700 text-white flex items-center justify-center text-[10px]">✓</div>
            <div class="text-[10px] text-stone-700 mt-1">Gambar<br>Kerja</div>
          </div>
          <div>
            <div class="w-5 h-5 mx-auto rounded-full bg-emerald-700 text-white flex items-center justify-center text-[10px]">✓</div>
            <div class="text-[10px] text-stone-700 mt-1">Dokumentasi</div>
          </div>
          <div>
            <div class="w-5 h-5 mx-auto rounded-full {{ ($latestSubmission?->status === 'approved' || $latestSubmission?->status === 'final') ? 'bg-emerald-700 text-white' : 'bg-stone-300 text-stone-600' }} flex items-center justify-center text-[10px]">
              {{ ($latestSubmission?->status === 'approved' || $latestSubmission?->status === 'final') ? '✓' : '•' }}
            </div>
            <div class="text-[10px] text-stone-500 mt-1">Review<br>Mentor</div>
          </div>
        </div>
      </div>
      <div class="bg-[#F3E9D2]/60 border border-[#E5D3AC] rounded-xl p-3 flex items-center justify-between gap-2">
        <div>
          <div class="text-xs font-bold text-stone-800">
            {{ $latestSubmission ? 'Status: ' . ucfirst(str_replace('_', ' ', $latestSubmission->status)) : 'Menunggu Review Mentor' }}
          </div>
          <div class="text-[10px] text-stone-500">
            Dikumpulkan {{ $latestSubmission?->submitted_at ? \Carbon\Carbon::parse($latestSubmission->submitted_at)->translatedFormat('d M Y, H:i') : 'Baru saja' }}
          </div>
        </div>
        <a href="{{ route('project', ['project_id' => $submissionProject?->id, 'tab' => 'submissions']) }}" class="bg-[#4E3E24] hover:bg-[#3c2f1b] text-white text-xs px-3 py-1.5 rounded-lg font-medium shrink-0 transition">Lihat Detail</a>
      </div>
    </div>

    <!-- Ringkasan Kompetensi -->
    <div class="lg:col-span-4 bg-[#FAF8F4] border border-stone-200/80 rounded-2xl p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-base text-stone-900">Ringkasan Kompetensi</h3>
        <a href="{{ route('kompetensi') }}" class="text-xs text-[#8A6729] font-medium hover:underline">Lihat Semua →</a>
      </div>
      <div class="space-y-4">
        @foreach($competencies as $idx => $comp)
          @php
            $barColor = $idx % 2 === 0 ? 'bg-[#A87F32]' : 'bg-[#586F50]';
            $pct = (int) round($comp->score);
          @endphp
          <div class="flex items-center justify-between gap-3">
            <span class="text-xs font-medium text-stone-800 w-40 truncate">{{ $comp->name }}</span>
            <div class="flex-1 h-2 bg-stone-200 rounded-full overflow-hidden">
              <div class="h-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
            </div>
            <span class="text-xs font-bold text-stone-800 w-9 text-right">{{ $pct }}%</span>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <!-- Footer Mini Cards -->
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <a href="{{ route('belajar') }}" class="bg-[#FAF8F4] hover:border-[#C89D4C] border border-stone-200/80 rounded-xl p-3.5 flex items-center justify-between transition">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-[#F5EBD6] text-[#8A6729] flex items-center justify-center shrink-0"><i data-lucide="target" class="w-5 h-5"></i></div>
        <div>
          <div class="text-xs font-bold text-[#8A6729]">Tantangan Hari Ini</div>
          <div class="text-[11px] text-stone-600">Selesaikan 1 materi video dan kerjakan Quiz.</div>
        </div>
      </div>
      <i data-lucide="chevron-right" class="w-4 h-4 text-stone-400 shrink-0"></i>
    </a>

    <a href="{{ route('project', ['tab' => 'submissions']) }}" class="bg-[#FAF8F4] hover:border-[#C89D4C] border border-stone-200/80 rounded-xl p-3.5 flex items-center justify-between transition">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-red-50 text-red-700 flex items-center justify-center shrink-0"><i data-lucide="calendar" class="w-5 h-5"></i></div>
        <div>
          <div class="text-xs font-bold text-stone-800">Deadline Terdekat</div>
          <div class="text-[11px] text-stone-600 truncate max-w-[160px]">{{ $assignments->first()?->title ?? 'Upload Model Arsitektur' }}</div>
          <div class="text-[11px] text-red-700 font-bold">{{ $assignments->first()?->days_left ?? 3 }} hari lagi</div>
        </div>
      </div>
      <i data-lucide="chevron-right" class="w-4 h-4 text-stone-400 shrink-0"></i>
    </a>

    <a href="{{ route('project', ['tab' => 'issues']) }}" class="bg-[#FAF8F4] hover:border-[#C89D4C] border border-stone-200/80 rounded-xl p-3.5 flex items-center justify-between transition">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-red-50 text-red-700 flex items-center justify-center shrink-0"><i data-lucide="alert-triangle" class="w-5 h-5"></i></div>
        <div>
          <div class="text-xs font-bold text-stone-800">Issue Terbuka</div>
          <div class="text-xs text-red-700 font-bold">{{ $openIssuesCount }} issue</div>
          <div class="text-[11px] text-stone-500">Perlu perhatian pada project Anda</div>
        </div>
      </div>
      <i data-lucide="chevron-right" class="w-4 h-4 text-stone-400 shrink-0"></i>
    </a>

    <a href="{{ route('belajar') }}" class="bg-[#FAF8F4] hover:border-[#C89D4C] border border-stone-200/80 rounded-xl p-3.5 flex items-center justify-between transition">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-[#F5EBD6] text-[#8A6729] flex items-center justify-center shrink-0"><i data-lucide="trophy" class="w-5 h-5"></i></div>
        <div>
          <div class="text-xs font-bold text-stone-800">Skor Terakhir</div>
          <div class="text-base font-bold text-stone-900">{{ round($latestQuizAttempt?->score ?? 92) }}</div>
          <div class="text-[11px] text-stone-500 truncate max-w-[160px]">{{ $latestQuizAttempt?->quiz_title ?? 'Kuis Modul 1 – Pengenalan BIM' }}</div>
        </div>
      </div>
      <i data-lucide="chevron-right" class="w-4 h-4 text-stone-400 shrink-0"></i>
    </a>
  </section>

  <!-- MODAL: TAMBAH TUGAS MENDATANG -->
  <div id="addAssignmentModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-lg text-stone-900">Tambah Tugas Baru</h3>
        <button onclick="closeModal('addAssignmentModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
      </div>
      <form action="{{ route('project.assignments.store') }}" method="POST" class="space-y-3 text-xs">
        @csrf
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Judul Tugas</label>
          <input type="text" name="title" required placeholder="Contoh: Upload Model Struktur Lantai 2" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
        </div>
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Modul / Deskripsi</label>
          <input type="text" name="description" placeholder="Pemodelan Arsitektur dengan Revit" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Tenggat Waktu (Deadline)</label>
            <input type="date" name="due_at" required value="{{ now()->addDays(4)->toDateString() }}" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Nilai Maksimal</label>
            <input type="number" name="max_score" value="100" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" onclick="closeModal('addAssignmentModal')" class="px-4 py-2 rounded-xl border border-stone-300 text-stone-600">Batal</button>
          <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Simpan Tugas</button>
        </div>
      </form>
    </div>
  </div>
@endsection

