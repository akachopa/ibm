@extends('layouts.app')

@section('title', 'BIM Lab - BIM Nusantara')
@section('page_title', 'BIM Lab')
@section('page_subtitle', 'Ikuti praktik langkah demi langkah untuk membangun kemampuan BIM.')

@section('content')
  <!-- Lab Selector & CRUD Actions Bar -->
  <div class="flex flex-wrap items-center justify-between gap-3 mb-4 bg-[#FAF8F4] border border-stone-200 rounded-xl px-4 py-2.5 text-xs">
    <div class="flex flex-wrap items-center gap-2">
      <span class="font-semibold text-stone-600">Pilih Modul Lab:</span>
      @foreach($labs as $l)
        <a href="{{ route('bim-lab', ['lab_id' => $l->id]) }}" class="px-3 py-1.5 rounded-lg font-medium transition {{ $activeLab && $activeLab->id === $l->id ? 'bg-[#6B532B] text-white' : 'bg-white border border-stone-200 text-stone-700 hover:border-[#C89D4C]' }}">
          {{ $l->title }}
        </a>
      @endforeach
    </div>
    <div class="flex items-center gap-2">
      @if($activeLab)
        <button onclick='openEditLabModal(@json($activeLab))' class="px-3 py-1.5 rounded-lg border border-stone-300 bg-white text-stone-700 hover:bg-stone-50 font-medium">✎ Edit Lab</button>
        @if($labs->count() > 1)
          <form action="{{ route('bim-lab.labs.destroy', $activeLab->id) }}" method="POST" onsubmit="return confirm('Hapus BIM Lab ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100">Hapus</button>
          </form>
        @endif
      @endif
      <button onclick="openModal('addLabModal')" class="px-3.5 py-1.5 rounded-lg bg-[#6B532B] text-white font-semibold">+ Lab Baru</button>
    </div>
  </div>

  <!-- Banner -->
  <section class="hero-banner rounded-2xl p-6 sm:p-7 text-white mb-6 flex flex-col lg:flex-row justify-between lg:items-end gap-6">
    <div>
      <div class="text-[11px] tracking-[0.2em] uppercase text-[#F4D38B] font-semibold mb-1.5">PRAKTIK BIM TERPANDU</div>
      <h2 class="font-serif-title text-2xl sm:text-3xl font-semibold leading-snug">{{ $activeLab?->title ?? 'Architecture Modeling - Rumah Tinggal 2 Lantai' }}</h2>
      <p class="text-xs text-stone-100/90 mt-2 max-w-lg">
        {{ $activeLab?->description ?? 'Pelajari alur lengkap pemodelan arsitektur rumah tinggal 2 lantai dari persiapan project hingga menghasilkan model siap presentasi.' }}
      </p>
      <div class="flex flex-wrap gap-2 mt-4 text-[11px]">
        <span class="bg-black/45 px-3 py-1 rounded-full border border-white/15">Revit Architecture</span>
        <span class="bg-black/45 px-3 py-1 rounded-full border border-white/15">Level: {{ ucfirst($activeLab?->difficulty ?? 'Pemula') }}</span>
        <span class="bg-black/45 px-3 py-1 rounded-full border border-white/15">{{ $totalSteps }} Langkah</span>
        <span class="bg-black/45 px-3 py-1 rounded-full border border-white/15">± {{ round(($activeLab?->estimated_minutes ?? 240) / 60, 1) }} Jam</span>
      </div>
    </div>
    <div class="bg-black/60 backdrop-blur-md p-4 rounded-xl border border-white/15 w-full lg:w-72 shrink-0">
      <div class="flex justify-between text-xs mb-1">
        <span>Progress Lab</span>
        <span class="text-stone-300">{{ $completedSteps }} dari {{ $totalSteps }} langkah selesai</span>
      </div>
      <div class="flex items-center gap-3">
        <span class="text-2xl font-bold">{{ $labProgressPercent }}%</span>
        <div class="flex-1 h-2 bg-stone-600 rounded-full overflow-hidden">
          <div class="h-full bg-[#D8AE5E]" style="width: {{ $labProgressPercent }}%"></div>
        </div>
      </div>
    </div>
  </section>

  <!-- 4 Info Cards -->
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-[#FAF8F4] border border-stone-200 rounded-2xl p-4">
      <div class="text-[11px] text-stone-500">Target Kompetensi</div>
      <div class="text-sm font-bold text-stone-900 mt-0.5">Pemodelan Arsitektur Dasar</div>
      <p class="text-[11px] text-stone-500 mt-1">Mampu membuat model rumah tinggal 2 lantai lengkap dengan elemen utama.</p>
    </div>
    <div class="bg-[#FAF8F4] border border-stone-200 rounded-2xl p-4">
      <div class="text-[11px] text-stone-500">Estimasi Waktu</div>
      <div class="text-lg font-bold text-stone-900 mt-0.5">{{ round(($activeLab?->estimated_minutes ?? 240) / 60, 1) }} Jam</div>
      <p class="text-[11px] text-stone-500 mt-1">Durasi total seluruh praktik termasuk latihan mandiri.</p>
    </div>
    <div class="bg-[#FAF8F4] border border-stone-200 rounded-2xl p-4">
      <div class="text-[11px] text-stone-500">Level</div>
      <div class="text-lg font-bold text-stone-900 mt-0.5">{{ ucfirst($activeLab?->difficulty ?? 'Pemula') }}</div>
      <p class="text-[11px] text-stone-500 mt-1">Cocok untuk pengguna baru maupun menengah Revit Architecture.</p>
    </div>
    <div class="bg-[#FAF8F4] border border-stone-200 rounded-2xl p-4 flex flex-col justify-between">
      <div>
        <div class="text-[11px] text-stone-500">File Latihan</div>
        <div class="text-sm font-bold text-stone-900 truncate">{{ $starterFile?->stored_name ?? 'RT_2Lantai_Start.rvt' }}</div>
        <div class="text-[11px] text-stone-400">245 MB</div>
      </div>
      @if($starterFile)
        <a href="{{ route('belajar.files.download', $starterFile->id) }}" class="mt-2 bg-[#F2E5C7] hover:bg-[#e6d5ad] text-[#6B4F1D] font-bold text-xs py-1.5 rounded-lg text-center block transition">↓ Download File</a>
      @endif
    </div>
  </section>

  <!-- Main Stepper + Content -->
  <section class="grid grid-cols-1 lg:grid-cols-12 gap-5">
    <!-- Left Steps -->
    <div class="lg:col-span-4 bg-[#FAF8F4] border border-stone-200 rounded-2xl p-4 space-y-2.5 h-fit">
      <div class="flex items-center justify-between pb-2 border-b border-stone-200">
        <span class="font-serif-title font-bold text-sm text-stone-900">Tahapan Praktik ({{ $totalSteps }})</span>
        @if($activeLab)
          <button onclick="openModal('addStepModal')" class="text-xs bg-[#6B532B] text-white font-semibold px-2.5 py-1 rounded-lg">+ Langkah</button>
        @endif
      </div>

      @foreach($instructions as $idx => $st)
        @php
          $isCurrent = ($idx === $activeStepIndex);
          $isDone = !empty($st['completed']);
        @endphp
        <a href="{{ route('bim-lab', ['lab_id' => $activeLab?->id, 'step' => $idx]) }}" class="{{ $isCurrent ? 'bg-[#F5EBD6]/80 border border-[#D7BE8E]' : 'hover:bg-stone-100 border border-transparent border-b-stone-200/70' }} rounded-xl p-3.5 flex items-start justify-between gap-2 transition block">
          <div class="flex gap-3">
            <span class="w-6 h-6 rounded-full {{ $isCurrent ? 'bg-[#6B4F1D] text-white' : ($isDone ? 'bg-emerald-700 text-white' : 'bg-stone-200 text-stone-700') }} text-xs font-bold flex items-center justify-center shrink-0">
              {{ $idx + 1 }}
            </span>
            <div>
              <div class="text-xs font-bold text-stone-900">{{ $st['title'] ?? ('Langkah ' . ($idx + 1)) }}</div>
              <div class="text-[11px] text-stone-600 mt-0.5">{{ $st['summary'] ?? '' }}</div>
            </div>
          </div>
          @if($isDone)
            <span class="text-emerald-700 font-bold shrink-0">✓</span>
          @endif
        </a>
      @endforeach
    </div>

    <!-- Right Detail -->
    <div class="lg:col-span-8 bg-[#FAF8F4] border border-stone-200 rounded-2xl p-5 sm:p-6">
      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-4">
        <div>
          <div class="text-xs text-[#8A6729] font-semibold">Langkah {{ $activeStepIndex + 1 }} dari {{ max(1, $totalSteps) }}</div>
          <h3 class="font-serif-title text-2xl font-bold text-stone-900">{{ $activeStep['title'] ?? 'Persiapan Project' }}</h3>
          <p class="text-xs text-stone-600 mt-1">{{ $activeStep['description'] ?? '' }}</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          @if($activeLab)
            <form action="{{ route('bim-lab.steps.toggle-complete', ['id' => $activeLab->id, 'stepIndex' => $activeStepIndex]) }}" method="POST">
              @csrf
              <button type="submit" class="{{ !empty($activeStep['completed']) ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-stone-200 text-stone-700 hover:bg-emerald-100 hover:text-emerald-800' }} text-xs font-bold px-3 py-1.5 rounded-full transition">
                {{ !empty($activeStep['completed']) ? 'Selesai ✓' : '○ Tandai Selesai' }}
              </button>
            </form>
            <button onclick="openModal('editStepModal')" class="p-1.5 rounded-lg border border-stone-300 bg-white text-stone-600 hover:text-stone-900" title="Edit Langkah">
              <i data-lucide="edit-3" class="w-4 h-4"></i>
            </button>
            @if($totalSteps > 1)
              <form action="{{ route('bim-lab.steps.destroy', ['id' => $activeLab->id, 'stepIndex' => $activeStepIndex]) }}" method="POST" onsubmit="return confirm('Hapus langkah ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-1.5 rounded-lg border border-stone-300 bg-white text-stone-500 hover:text-red-600" title="Hapus Langkah">
                  <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
              </form>
            @endif
          @endif
        </div>
      </div>

      <!-- Revit Mock Viewport -->
      <div class="rounded-xl overflow-hidden border border-stone-300 bg-white mb-5">
        <div class="bg-stone-100 px-3 py-1.5 border-b border-stone-300 text-[11px] font-semibold text-stone-700 flex flex-wrap justify-between gap-2">
          <span>{{ $activeStep['viewport_title'] ?? 'Autodesk Revit - RT_2Lantai - 3D View: {3D}' }}</span>
          <span>{{ $activeStep['viewport_levels'] ?? 'Atap +7.20 | Lantai 2 +3.60 | Lantai 1 +0.00' }}</span>
        </div>
        <div class="relative">
          <img src="{{ $activeStep['image'] ?? 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80' }}" class="w-full h-64 object-cover" />
          <a href="{{ route('bim-studio', ['project_id' => $activeLab?->template_project_id]) }}" class="absolute bottom-3 right-3 bg-[#252620]/90 hover:bg-[#252620] text-[#F4D38B] text-xs font-semibold px-3.5 py-2 rounded-xl border border-[#C89D4C]/40 flex items-center gap-2 shadow-lg transition">
            <i data-lucide="monitor" class="w-4 h-4"></i> Praktikkan di BIM Studio →
          </a>
        </div>
      </div>

      <!-- 3 Bottom Boxes -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-stone-200 rounded-xl p-4">
          <h4 class="text-xs font-bold text-stone-900 mb-2.5">Instruksi Langkah demi Langkah</h4>
          <ol class="space-y-2 text-[11px] text-stone-700 list-decimal pl-4">
            @foreach($activeStep['steps_list'] ?? [] as $instItem)
              <li>{{ $instItem }}</li>
            @endforeach
          </ol>
        </div>

        <div class="bg-white border border-stone-200 rounded-xl p-4">
          <h4 class="text-xs font-bold text-stone-900 mb-2.5">Checklist Penyelesaian</h4>
          <div class="space-y-2 text-[11px]">
            @foreach($activeStep['checklist'] ?? [] as $cIdx => $chk)
              @if($activeLab)
                <form action="{{ route('bim-lab.steps.toggle-checklist', ['id' => $activeLab->id, 'stepIndex' => $activeStepIndex]) }}" method="POST">
                  @csrf
                  <input type="hidden" name="check_index" value="{{ $cIdx }}" />
                  <label class="flex items-start gap-2 cursor-pointer hover:text-stone-950">
                    <input type="checkbox" onchange="this.form.submit()" {{ !empty($chk['done']) ? 'checked' : '' }} class="accent-emerald-700 mt-0.5" />
                    <span class="{{ !empty($chk['done']) ? 'line-through text-stone-400' : 'text-stone-700' }}">{{ $chk['label'] ?? '' }}</span>
                  </label>
                </form>
              @endif
            @endforeach
          </div>
        </div>

        <div class="bg-[#FDF6E7] border border-[#E6D0A3] rounded-xl p-4">
          <h4 class="text-xs font-bold text-[#7A591E] mb-2.5">💡 Tips & Catatan</h4>
          <ul class="space-y-1.5 text-[11px] text-stone-700 list-disc pl-4">
            @foreach($activeStep['tips'] ?? [] as $tipItem)
              <li>{{ $tipItem }}</li>
            @endforeach
          </ul>
        </div>
      </div>

      <!-- Prev / Next Navigation -->
      <div class="flex flex-wrap justify-between items-center gap-3 mt-5 pt-4 border-t border-stone-200/70">
        <div class="text-[11px] text-stone-500">
          Rule Challenge Aktif: <b>{{ $rules->count() }} Kriteria Standar BIM</b>
        </div>
        <div class="flex gap-3">
          @if($activeStepIndex > 0)
            <a href="{{ route('bim-lab', ['lab_id' => $activeLab?->id, 'step' => $activeStepIndex - 1]) }}" class="px-4 py-2 rounded-lg border border-stone-300 bg-white text-xs text-stone-700 hover:bg-stone-50 font-medium">
              ‹ Sebelumnya
            </a>
          @else
            <button disabled class="px-4 py-2 rounded-lg border border-stone-200 text-xs text-stone-400 cursor-not-allowed">‹ Sebelumnya</button>
          @endif

          @if($activeStepIndex + 1 < $totalSteps)
            <a href="{{ route('bim-lab', ['lab_id' => $activeLab?->id, 'step' => $activeStepIndex + 1]) }}" class="px-5 py-2 rounded-lg bg-[#C59B49] hover:bg-[#b3893b] text-stone-950 font-bold text-xs transition">
              Lanjut ke Langkah {{ $activeStepIndex + 2 }} →
            </a>
          @else
            <a href="{{ route('bim-studio', ['project_id' => $activeLab?->template_project_id]) }}" class="px-5 py-2 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs transition">
              Buka BIM Studio & Validasi →
            </a>
          @endif
        </div>
      </div>
    </div>
  </section>

  <!-- MODAL: TAMBAH & EDIT LAB -->
  <div id="addLabModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-lg text-stone-900">Buat BIM Lab Baru</h3>
        <button onclick="closeModal('addLabModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
      </div>
      <form action="{{ route('bim-lab.labs.store') }}" method="POST" class="space-y-3 text-xs">
        @csrf
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Judul BIM Lab</label>
          <input type="text" name="title" required placeholder="Contoh: Structure Modeling - Gedung 4 Lantai" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
        </div>
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Deskripsi</label>
          <textarea name="description" rows="2" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Tingkat Kesulitan</label>
            <select name="difficulty" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
              <option value="beginner">Pemula (Beginner)</option>
              <option value="intermediate">Menengah (Intermediate)</option>
              <option value="advanced">Lanjutan (Advanced)</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Estimasi (Menit)</label>
            <input type="number" name="estimated_minutes" value="240" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" onclick="closeModal('addLabModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
          <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Simpan Lab</button>
        </div>
      </form>
    </div>
  </div>

  <div id="editLabModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-lg text-stone-900">Edit BIM Lab</h3>
        <button onclick="closeModal('editLabModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
      </div>
      <form id="editLabForm" method="POST" class="space-y-3 text-xs">
        @csrf
        @method('PUT')
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Judul BIM Lab</label>
          <input type="text" id="edit_lab_title" name="title" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
        </div>
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Deskripsi</label>
          <textarea id="edit_lab_desc" name="description" rows="2" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Tingkat Kesulitan</label>
            <select id="edit_lab_diff" name="difficulty" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
              <option value="beginner">Pemula (Beginner)</option>
              <option value="intermediate">Menengah (Intermediate)</option>
              <option value="advanced">Lanjutan (Advanced)</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Estimasi (Menit)</label>
            <input type="number" id="edit_lab_mins" name="estimated_minutes" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" onclick="closeModal('editLabModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
          <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Perbarui Lab</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: TAMBAH & EDIT LANGKAH PRAKTIK -->
  @if($activeLab)
    <div id="addStepModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
      <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-serif-title font-bold text-lg text-stone-900">Tambah Langkah Praktik Baru</h3>
          <button onclick="closeModal('addStepModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <form action="{{ route('bim-lab.steps.store', $activeLab->id) }}" method="POST" class="space-y-3 text-xs">
          @csrf
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Judul Langkah</label>
            <input type="text" name="title" required placeholder="Contoh: Pemodelan Tangga & Railing" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Ringkasan Singkat</label>
            <input type="text" name="summary" placeholder="Menghubungkan sirkulasi vertikal Lantai 1 ke Lantai 2" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Deskripsi Lengkap</label>
            <textarea name="description" rows="2" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Daftar Instruksi (Satu per baris)</label>
            <textarea name="steps_raw" rows="3" placeholder="Buka Floor Plan Level 1&#10;Pilih tool Stair pada tab Circulation&#10;Atur jumlah anak tangga dan tinggi riser" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Checklist Penyelesaian (Satu per baris)</label>
            <textarea name="checklist_raw" rows="2" placeholder="Tangga terhubung ke Level 2&#10;Railing terpasang di kedua sisi" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea>
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" onclick="closeModal('addStepModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Simpan Langkah</button>
          </div>
        </form>
      </div>
    </div>

    <div id="editStepModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
      <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-serif-title font-bold text-lg text-stone-900">Edit Langkah {{ $activeStepIndex + 1 }}</h3>
          <button onclick="closeModal('editStepModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <form action="{{ route('bim-lab.steps.update', ['id' => $activeLab->id, 'stepIndex' => $activeStepIndex]) }}" method="POST" class="space-y-3 text-xs">
          @csrf
          @method('PUT')
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Judul Langkah</label>
            <input type="text" name="title" required value="{{ $activeStep['title'] ?? '' }}" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Ringkasan Singkat</label>
            <input type="text" name="summary" value="{{ $activeStep['summary'] ?? '' }}" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Deskripsi Lengkap</label>
            <textarea name="description" rows="2" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">{{ $activeStep['description'] ?? '' }}</textarea>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Daftar Instruksi (Satu per baris)</label>
            <textarea name="steps_raw" rows="3" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">{{ implode("\n", $activeStep['steps_list'] ?? []) }}</textarea>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Checklist Penyelesaian (Satu per baris)</label>
            <textarea name="checklist_raw" rows="3" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">{{ implode("\n", array_map(fn($c) => $c['label'] ?? '', $activeStep['checklist'] ?? [])) }}</textarea>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Tips & Catatan (Satu per baris)</label>
            <textarea name="tips_raw" rows="2" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">{{ implode("\n", $activeStep['tips'] ?? []) }}</textarea>
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" onclick="closeModal('editStepModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Perbarui Langkah</button>
          </div>
        </form>
      </div>
    </div>
  @endif
@endsection

@push('scripts')
<script>
  function openEditLabModal(lab) {
    document.getElementById('editLabForm').action = `/bim-lab/labs/${lab.id}`;
    document.getElementById('edit_lab_title').value = lab.title || '';
    document.getElementById('edit_lab_desc').value = lab.description || '';
    document.getElementById('edit_lab_diff').value = lab.difficulty || 'beginner';
    document.getElementById('edit_lab_mins').value = lab.estimated_minutes || 240;
    openModal('editLabModal');
  }
</script>
@endpush

