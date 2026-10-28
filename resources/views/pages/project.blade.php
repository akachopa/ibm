@extends('layouts.app')

@section('title', 'Project - BIM Nusantara')
@section('page_title', 'Project')
@section('page_subtitle', 'Kelola simulasi proyek, submission, review, dan issue.')

@section('content')
  <!-- Banner -->
  <section class="hero-banner rounded-2xl p-6 sm:p-8 text-white mb-5 flex flex-col sm:flex-row justify-between sm:items-end gap-4">
    <div>
      <div class="text-[11px] tracking-[0.2em] uppercase text-[#F4D38B] font-semibold mb-1">KELOLA PROJECT, BANGUN PORTOFOLIO</div>
      <h2 class="font-serif-title text-3xl sm:text-4xl font-semibold">Project</h2>
      <p class="text-xs text-stone-100/90 mt-1">Kelola simulasi proyek, submission, review, dan issue.</p>
    </div>
    <div class="sm:text-right">
      <p class="font-serif-title italic text-sm">"Dari pembelajaran<br>menuju karya nyata."</p>
      <div class="w-8 h-0.5 bg-[#D4A64E] mt-2 sm:ml-auto"></div>
    </div>
  </section>

  <!-- Tabs Bar -->
  @php
    $tabs = [
      'overview' => '📁 Proyek Aktif',
      'stages' => '📋 Tahap Project',
      'submissions' => '📄 Tugas & Submission',
      'reviews' => '👤 Review',
      'issues' => '⚠ Issue (' . $issues->whereIn('status', ['open', 'in_progress'])->count() . ')',
      'score' => '🏆 Final Score',
    ];
  @endphp
  <div class="flex flex-wrap items-center justify-between gap-3 bg-[#FAF8F4] border border-stone-200 rounded-xl px-4 py-2.5 mb-5 text-xs">
    <div class="flex flex-wrap items-center gap-4 sm:gap-6">
      @foreach($tabs as $tabKey => $tabLabel)
        <a href="{{ route('project', ['project_id' => $activeProject?->id, 'tab' => $tabKey]) }}"
           class="{{ $activeTab === $tabKey ? 'font-bold text-[#7A591E] border-b-2 border-[#7A591E] pb-1' : 'text-stone-600 hover:text-stone-900' }} transition">
          {{ $tabLabel }}
        </a>
      @endforeach
    </div>
    <button onclick="openModal('addProjectModal')" class="bg-[#6B532B] hover:bg-[#574220] text-white font-semibold px-4 py-2 rounded-lg transition">
      + Buat Project Baru
    </button>
  </div>

  <!-- Main Split -->
  <section class="grid grid-cols-1 lg:grid-cols-12 gap-5">
    <!-- Left: Daftar Project Aktif -->
    <div class="lg:col-span-6 space-y-4">
      @foreach($projects as $p)
        @php
          $isSelected = ($activeProject && $activeProject->id === $p->id);
          $statusBadge = match($p->status) {
            'approved', 'completed' => ['bg-emerald-100 text-emerald-800', '✓ Approved'],
            'revision_required' => ['bg-red-100 text-red-700', '● Revision Required'],
            'under_review' => ['bg-[#F5EBD6] text-[#8A6729]', '● Under Review'],
            default => ['bg-blue-100 text-blue-800', '● ' . ucfirst($p->status)],
          };
          $tasksDone = $p->meta['tasks_done'] ?? 12;
          $tasksTotal = $p->meta['tasks_total'] ?? 18;
          $teamSize = $p->meta['team_size'] ?? $p->member_count;
          $bType = $p->meta['building_type'] ?? ucfirst($p->category);
        @endphp
        <div class="bg-[#FAF8F4] {{ $isSelected ? 'border-2 border-[#C7A35B] shadow-sm' : 'border border-stone-200 hover:border-stone-300' }} rounded-2xl p-4 flex flex-col sm:flex-row gap-4 transition">
          <a href="{{ route('project', ['project_id' => $p->id, 'tab' => $activeTab]) }}" class="w-full sm:w-36 h-32 rounded-xl overflow-hidden shrink-0 block">
            <img src="{{ $p->thumbnail_url ?: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=400&q=80' }}" class="w-full h-full object-cover" />
          </a>
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-start gap-2">
              <a href="{{ route('project', ['project_id' => $p->id, 'tab' => $activeTab]) }}" class="font-serif-title font-bold text-base text-stone-900 hover:text-[#8A6729] truncate">
                {{ $p->title }}
              </a>
              <span class="{{ $statusBadge[0] }} text-[10px] font-bold px-2.5 py-1 rounded-full shrink-0">{{ $statusBadge[1] }}</span>
            </div>
            <div class="flex flex-wrap gap-2 my-1.5 text-[10px]">
              <span class="bg-stone-200 px-2 py-0.5 rounded">{{ $bType }}</span>
              <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded font-semibold">R {{ $p->meta['software'] ?? 'Revit' }}</span>
              <span class="bg-stone-100 text-stone-600 px-2 py-0.5 rounded">{{ $p->code }}</span>
            </div>
            <p class="text-[11px] text-stone-600 line-clamp-2">{{ $p->description }}</p>
            <div class="flex items-center gap-2 mt-2">
              <div class="flex-1 h-2 bg-stone-200 rounded-full overflow-hidden">
                <div class="h-full bg-[#A87F32]" style="width: {{ round($p->progress_percent) }}%"></div>
              </div>
              <span class="text-xs font-bold">{{ round($p->progress_percent) }}%</span>
            </div>
            <div class="flex flex-wrap justify-between items-center gap-2 text-[11px] text-stone-500 mt-2">
              <span>📅 Deadline: <b class="text-red-700">{{ $p->deadline ? \Carbon\Carbon::parse($p->deadline)->format('d M Y') : '28 Apr 2024' }}</b></span>
              <span>📄 <b>{{ $tasksDone }} / {{ $tasksTotal }}</b> tugas</span>
              <span>👥 Tim ({{ $teamSize }})</span>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <!-- Right: Detail Project Terpilih -->
    <div class="lg:col-span-6 bg-[#FAF8F4] border border-stone-200 rounded-2xl p-5 space-y-4 h-fit">
      @if($activeProject)
        @php
          $apMeta = $activeProject->meta ?? [];
          $tasksDone = $apMeta['tasks_done'] ?? 12;
          $tasksTotal = $apMeta['tasks_total'] ?? 18;
          $tasksLeft = max(0, $tasksTotal - $tasksDone);
          $stages = $apMeta['stages'] ?? [
            ['name' => 'Konsep & Studi Awal', 'progress' => 100],
            ['name' => 'Pemodelan Arsitektur', 'progress' => 100],
            ['name' => 'Koordinasi Struktur', 'progress' => 70],
            ['name' => 'Koordinasi MEP', 'progress' => 0],
            ['name' => 'Finalisasi & Submission', 'progress' => 0],
          ];
        @endphp

        <div class="flex flex-wrap justify-between items-start gap-2">
          <div>
            <h3 class="font-serif-title text-xl font-bold text-stone-900">{{ $activeProject->title }}</h3>
            <div class="text-[11px] text-stone-500 mt-0.5">
              {{ $apMeta['building_type'] ?? 'Residential' }} • Revit • Dibuat {{ \Carbon\Carbon::parse($activeProject->start_date ?? $activeProject->created_at)->format('d M Y') }} | ID: <b>{{ $activeProject->code }}</b>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button onclick="openModal('editProjectModal')" class="text-xs bg-white border border-stone-300 px-3 py-1 rounded-lg hover:bg-stone-50 font-medium">✎ Edit Project</button>
            @if($projects->count() > 1)
              <form action="{{ route('project.projects.destroy', $activeProject->id) }}" method="POST" onsubmit="return confirm('Hapus project {{ $activeProject->title }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs bg-red-50 border border-red-200 text-red-700 px-2.5 py-1 rounded-lg hover:bg-red-100">Hapus</button>
              </form>
            @endif
          </div>
        </div>

        <!-- KPI Row -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-white p-3.5 rounded-xl border border-stone-200">
          <div><div class="text-[11px] text-stone-500">Progress Project</div><div class="text-xl font-bold">{{ round($activeProject->progress_percent) }}%</div></div>
          <div><div class="text-sm font-bold">{{ $tasksDone }}</div><div class="text-[10px] text-stone-500">Tugas Selesai</div></div>
          <div><div class="text-sm font-bold">{{ $tasksLeft }}</div><div class="text-[10px] text-stone-500">Tugas Tersisa</div></div>
          <div><div class="text-sm font-bold text-red-700">{{ $activeProject->deadline ? \Carbon\Carbon::parse($activeProject->deadline)->format('d M Y') : '28 Apr 2024' }}</div><div class="text-[10px] text-stone-500">Deadline Project</div></div>
        </div>

        <!-- Tahap Project & Preview Model 3D -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-white p-4 rounded-xl border border-stone-200 space-y-2.5 text-xs">
            <div class="flex items-center justify-between">
              <span class="font-bold text-stone-900">Tahap Project</span>
              <button onclick="openModal('editProjectModal')" class="text-[10px] text-[#8A6729] font-semibold hover:underline">Update Tahap</button>
            </div>
            @foreach($stages as $st)
              @php
                $stPct = (int) ($st['progress'] ?? 0);
              @endphp
              <div class="flex justify-between items-center {{ $stPct === 0 ? 'text-stone-400' : '' }}">
                <span>
                  {{ $stPct >= 100 ? '✓' : ($stPct > 0 ? '◉' : '○') }}
                  {{ $st['name'] }}
                </span>
                <b class="{{ $stPct >= 100 ? 'text-emerald-700' : ($stPct > 0 ? 'text-[#8A6729]' : 'text-stone-400') }}">{{ $stPct }}%</b>
              </div>
            @endforeach
          </div>

          <div class="bg-white p-4 rounded-xl border border-stone-200">
            <div class="flex justify-between items-center mb-2">
              <span class="text-xs font-bold">Preview Model 3D ({{ $activeProject->element_count }} Elemen)</span>
              <a href="{{ route('bim-studio', ['project_id' => $activeProject->id]) }}" class="text-[10px] bg-[#F5EBD6] hover:bg-[#e9d8b4] text-[#7A591E] px-2.5 py-1 rounded font-semibold transition">
                Buka di BIM Studio →
              </a>
            </div>
            <img src="{{ $activeProject->thumbnail_url ?: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=600&q=80' }}" class="w-full h-32 rounded-lg object-cover" />
          </div>
        </div>

        <!-- Ringkasan Issue & Review Terbaru -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
          <!-- Issues Box -->
          <div class="bg-white p-4 rounded-xl border border-stone-200 space-y-2.5">
            <div class="flex items-center justify-between">
              <span class="font-bold text-stone-900">Ringkasan Issue ({{ $issues->whereIn('status', ['open', 'in_progress'])->count() }} Terbuka)</span>
              <button onclick="openModal('addIssueModal')" class="text-[10px] bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded font-semibold">+ Lapor Issue</button>
            </div>
            <div class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
              @forelse($issues as $iss)
                @php
                  $sevColor = match($iss->severity) {
                    'critical' => 'text-red-700',
                    'major' => 'text-amber-700',
                    default => 'text-stone-600',
                  };
                @endphp
                <div class="p-2.5 rounded-lg bg-[#FAF8F4] border border-stone-200/80 space-y-1">
                  <div class="flex items-start justify-between gap-2">
                    <div class="text-[11px] font-bold text-stone-900">⚠️ {{ $iss->title }}</div>
                    <div class="flex items-center gap-1 shrink-0">
                      <button onclick='openEditIssueModal(@json($iss))' class="text-stone-400 hover:text-stone-700" title="Edit Issue"><i data-lucide="edit-2" class="w-3 h-3"></i></button>
                      <form action="{{ route('project.issues.destroy', $iss->id) }}" method="POST" onsubmit="return confirm('Hapus issue ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-stone-400 hover:text-red-600"><i data-lucide="trash-2" class="w-3 h-3"></i></button>
                      </form>
                    </div>
                  </div>
                  <p class="text-[10px] text-stone-600">{{ $iss->description }}</p>
                  <div class="flex items-center justify-between text-[10px]">
                    <span class="{{ $sevColor }} font-semibold uppercase">{{ $iss->severity }} • {{ $iss->camera['discipline'] ?? 'Arsitektur' }}</span>
                    <span class="px-2 py-0.5 rounded-full {{ $iss->status === 'resolved' || $iss->status === 'closed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }} font-semibold">
                      {{ $iss->status }}
                    </span>
                  </div>
                  @if($iss->comments->count() > 0)
                    <div class="pt-1 mt-1 border-t border-stone-200/60 space-y-1">
                      @foreach($iss->comments as $cmt)
                        <div class="text-[10px] text-stone-500">💬 <b>{{ $cmt->user_name }}:</b> {{ $cmt->comment }}</div>
                      @endforeach
                    </div>
                  @endif
                </div>
              @empty
                <div class="text-stone-400 text-[11px] py-3 text-center">Tidak ada issue pada project ini.</div>
              @endforelse
            </div>
          </div>

          <!-- Reviews Box -->
          <div class="bg-white p-4 rounded-xl border border-stone-200 space-y-2.5">
            <div class="flex items-center justify-between">
              <span class="font-bold text-stone-900">Review Terbaru ({{ $reviews->count() }})</span>
              <button onclick="openModal('addReviewModal')" class="text-[10px] bg-[#F5EBD6] text-[#7A591E] px-2 py-0.5 rounded font-semibold">+ Review</button>
            </div>
            <div class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
              @forelse($reviews as $rev)
                <div class="p-2.5 rounded-lg bg-[#FAF8F4] border border-stone-200/80 space-y-1">
                  <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase text-[#8A6729]">{{ $rev->review_type }} • Skor: {{ round($rev->score) }}</span>
                    <form action="{{ route('project.reviews.destroy', $rev->id) }}" method="POST" onsubmit="return confirm('Hapus review ini?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="text-stone-400 hover:text-red-600"><i data-lucide="trash-2" class="w-3 h-3"></i></button>
                    </form>
                  </div>
                  <div class="text-[11px] text-stone-700 leading-relaxed">{{ $rev->notes }}</div>
                </div>
              @empty
                <div class="text-stone-400 text-[11px] py-3 text-center">Belum ada review pada project ini.</div>
              @endforelse
            </div>
          </div>
        </div>

        <!-- Submissions & Final Score Section -->
        <div class="bg-white p-4 rounded-xl border border-stone-200 space-y-2.5 text-xs">
          <div class="flex items-center justify-between">
            <span class="font-bold text-stone-900">Daftar Pengumpulan (Submissions) & Nilai Project</span>
            <button onclick="openModal('addSubmissionModal')" class="text-[10px] bg-[#6B532B] text-white px-2.5 py-1 rounded-lg font-semibold">+ Submit Tugas</button>
          </div>
          <div class="space-y-2">
            @forelse($submissions as $sub)
              <div class="flex flex-wrap items-center justify-between gap-2 p-2.5 rounded-lg bg-[#FAF8F4] border border-stone-200">
                <div>
                  <div class="font-bold text-stone-900">{{ $sub->assignment_title ?: ('Submission Model ' . $activeProject->title) }}</div>
                  <div class="text-[11px] text-stone-500">{{ $sub->feedback ?: 'Menunggu evaluasi mentor' }}</div>
                </div>
                <div class="flex items-center gap-2">
                  @if($sub->final_score)
                    <span class="bg-blue-100 text-blue-800 font-bold px-2.5 py-0.5 rounded-full text-[11px]">Skor: {{ round($sub->final_score) }}</span>
                  @endif
                  <span class="bg-[#F5EBD6] text-[#8A6729] font-semibold px-2.5 py-0.5 rounded-full text-[10px] uppercase">{{ str_replace('_', ' ', $sub->status) }}</span>
                  <form action="{{ route('project.submissions.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Hapus submission ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-stone-400 hover:text-red-600"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                  </form>
                </div>
              </div>
            @empty
              <div class="text-stone-400 text-[11px] py-2 text-center">Belum ada submission untuk project ini.</div>
            @endforelse
          </div>
        </div>
      @endif
    </div>
  </section>

  <!-- MODAL: BUAT PROJECT BARU -->
  <div id="addProjectModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-lg text-stone-900">Buat Project BIM Baru</h3>
        <button onclick="closeModal('addProjectModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
      </div>
      <form action="{{ route('project.projects.store') }}" method="POST" class="space-y-3 text-xs">
        @csrf
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Judul Project</label>
          <input type="text" name="title" required placeholder="Contoh: Rumah Sakit Pratama 3 Lantai" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Kategori</label>
            <select name="category" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
              <option value="training">Training</option>
              <option value="simulation">Simulation</option>
              <option value="portfolio">Portfolio</option>
              <option value="real_project">Real Project</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Tipe Bangunan</label>
            <input type="text" name="building_type" value="Commercial" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
        </div>
        <div class="grid grid-cols-3 gap-2">
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Status</label>
            <select name="status" class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2">
              <option value="active">Active</option>
              <option value="draft">Draft</option>
              <option value="under_review">Under Review</option>
              <option value="approved">Approved</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Unit</label>
            <select name="unit" class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2">
              <option value="mm">mm</option>
              <option value="m">m</option>
              <option value="cm">cm</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Deadline</label>
            <input type="date" name="deadline" value="{{ now()->addDays(21)->toDateString() }}" class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2" />
          </div>
        </div>
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Deskripsi Project</label>
          <textarea name="description" rows="2" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" onclick="closeModal('addProjectModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
          <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Simpan Project</button>
        </div>
      </form>
    </div>
  </div>

  @if($activeProject)
    <!-- MODAL: EDIT PROJECT & TAHAP -->
    <div id="editProjectModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
      <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-serif-title font-bold text-lg text-stone-900">Edit Project: {{ $activeProject->title }}</h3>
          <button onclick="closeModal('editProjectModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <form action="{{ route('project.projects.update', $activeProject->id) }}" method="POST" class="space-y-3 text-xs">
          @csrf
          @method('PUT')
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Judul Project</label>
            <input type="text" name="title" required value="{{ $activeProject->title }}" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div class="grid grid-cols-3 gap-2">
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Kategori</label>
              <select name="category" class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2">
                @foreach(['training', 'simulation', 'portfolio', 'real_project'] as $cat)
                  <option value="{{ $cat }}" {{ $activeProject->category === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Status</label>
              <select name="status" class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2">
                @foreach(['draft', 'active', 'under_review', 'revision_required', 'approved', 'completed'] as $st)
                  <option value="{{ $st }}" {{ $activeProject->status === $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Progress (%)</label>
              <input type="number" name="progress_percent" value="{{ round($activeProject->progress_percent) }}" min="0" max="100" class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2" />
            </div>
          </div>
          <div class="grid grid-cols-3 gap-2">
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Unit</label>
              <select name="unit" class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2">
                <option value="mm" {{ $activeProject->unit === 'mm' ? 'selected' : '' }}>mm</option>
                <option value="m" {{ $activeProject->unit === 'm' ? 'selected' : '' }}>m</option>
                <option value="cm" {{ $activeProject->unit === 'cm' ? 'selected' : '' }}>cm</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Tugas Selesai</label>
              <input type="number" name="tasks_done" value="{{ $activeProject->meta['tasks_done'] ?? 12 }}" class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2" />
            </div>
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Total Tugas</label>
              <input type="number" name="tasks_total" value="{{ $activeProject->meta['tasks_total'] ?? 18 }}" class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2" />
            </div>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Deskripsi</label>
            <textarea name="description" rows="2" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">{{ $activeProject->description }}</textarea>
          </div>
          <div class="border-t border-stone-200 pt-2">
            <label class="block font-bold text-stone-800 mb-2">Progres Tahap Project (%)</label>
            <div class="space-y-2">
              @foreach($stages as $sIdx => $st)
                <div class="flex items-center justify-between gap-2">
                  <span>{{ $st['name'] }}</span>
                  <input type="number" name="stage_progress[{{ $sIdx }}]" value="{{ $st['progress'] }}" min="0" max="100" class="w-20 bg-white border border-stone-300 rounded-lg px-2 py-1 text-right" />
                </div>
              @endforeach
            </div>
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" onclick="closeModal('editProjectModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: TAMBAH & EDIT ISSUE -->
    <div id="addIssueModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
      <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-serif-title font-bold text-lg text-stone-900">Laporkan Issue BIM Baru</h3>
          <button onclick="closeModal('addIssueModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <form action="{{ route('project.issues.store') }}" method="POST" class="space-y-3 text-xs">
          @csrf
          <input type="hidden" name="project_id" value="{{ $activeProject->id }}" />
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Judul Issue / Clash</label>
            <input type="text" name="title" required placeholder="Contoh: Clash pipa plumbing dengan balok B1" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Tingkat Severity</label>
              <select name="severity" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
                <option value="critical">Critical (High)</option>
                <option value="major">Major (Medium)</option>
                <option value="minor">Minor (Low)</option>
                <option value="info">Info</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Disiplin</label>
              <select name="discipline" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
                <option value="Arsitektur">Arsitektur</option>
                <option value="Struktur">Struktur</option>
                <option value="MEP">MEP</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Deskripsi & Rekomendasi Perbaikan</label>
            <textarea name="description" rows="2" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea>
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" onclick="closeModal('addIssueModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-red-700 text-white font-semibold">Simpan Issue</button>
          </div>
        </form>
      </div>
    </div>

    <div id="editIssueModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
      <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-serif-title font-bold text-lg text-stone-900">Update Status & Detail Issue</h3>
          <button onclick="closeModal('editIssueModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <form id="editIssueForm" method="POST" class="space-y-3 text-xs">
          @csrf
          @method('PUT')
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Judul Issue</label>
            <input type="text" id="edit_iss_title" name="title" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Severity</label>
              <select id="edit_iss_sev" name="severity" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
                <option value="critical">Critical</option>
                <option value="major">Major</option>
                <option value="minor">Minor</option>
                <option value="info">Info</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Status</label>
              <select id="edit_iss_status" name="status" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Deskripsi</label>
            <textarea id="edit_iss_desc" name="description" rows="2" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea>
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" onclick="closeModal('editIssueModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Perbarui Issue</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: TAMBAH REVIEW -->
    <div id="addReviewModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
      <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-serif-title font-bold text-lg text-stone-900">Tambah Review Mentor</h3>
          <button onclick="closeModal('addReviewModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <form action="{{ route('project.reviews.store') }}" method="POST" class="space-y-3 text-xs">
          @csrf
          <input type="hidden" name="project_id" value="{{ $activeProject->id }}" />
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Tipe Review</label>
              <select name="review_type" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
                <option value="technical">Technical Review</option>
                <option value="assessment">Assessment Review</option>
                <option value="final">Final Review</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Skor Evaluasi (0-100)</label>
              <input type="number" name="score" value="90" min="0" max="100" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
            </div>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Catatan Feedback Mentor</label>
            <textarea name="notes" rows="3" required placeholder="Budi Santoso (Trainer): Model sudah sesuai dengan standar LOD 300..." class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea>
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" onclick="closeModal('addReviewModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Simpan Review</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: TAMBAH SUBMISSION -->
    <div id="addSubmissionModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
      <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-serif-title font-bold text-lg text-stone-900">Kumpulkan Submission Project</h3>
          <button onclick="closeModal('addSubmissionModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <form action="{{ route('project.submissions.store') }}" method="POST" class="space-y-3 text-xs">
          @csrf
          <input type="hidden" name="project_id" value="{{ $activeProject->id }}" />
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Pilih Tugas Terkait</label>
            <select name="assignment_id" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
              @foreach($assignments as $asg)
                <option value="{{ $asg->id }}">{{ $asg->title }}</option>
              @endforeach
            </select>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Status</label>
              <select name="status" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
                <option value="submitted">Submitted</option>
                <option value="under_review">Under Review</option>
                <option value="approved">Approved</option>
                <option value="final">Final</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Skor (0-100)</label>
              <input type="number" name="trainer_score" value="90" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
            </div>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Catatan Pengumpulan</label>
            <textarea name="feedback" rows="2" placeholder="Model arsitektur, gambar kerja, dan jadwal kuantitas telah dilampirkan." class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea>
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" onclick="closeModal('addSubmissionModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Kirim Submission</button>
          </div>
        </form>
      </div>
    </div>
  @endif
@endsection

@push('scripts')
<script>
  function openEditIssueModal(iss) {
    document.getElementById('editIssueForm').action = `/project/issues/${iss.id}`;
    document.getElementById('edit_iss_title').value = iss.title || '';
    document.getElementById('edit_iss_sev').value = iss.severity || 'minor';
    document.getElementById('edit_iss_status').value = iss.status || 'open';
    document.getElementById('edit_iss_desc').value = iss.description || '';
    openModal('editIssueModal');
  }
</script>
@endpush

