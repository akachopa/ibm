@extends('layouts.app')

@section('title', 'Belajar BIM - BIM Nusantara')
@section('page_title', 'Belajar BIM')
@section('page_subtitle', 'Akses materi, modul, video, quiz, dan learning path terstruktur.')

@section('content')
  <!-- Hero Banner -->
  <section class="hero-banner rounded-2xl p-6 sm:p-8 text-white mb-6 flex flex-col lg:flex-row justify-between lg:items-center gap-6">
    <div class="max-w-lg">
      <div class="text-[11px] tracking-[0.2em] uppercase text-[#F4D38B] font-semibold mb-2">LEARNING PATH UNGGULAN</div>
      <h2 class="font-serif-title text-3xl sm:text-4xl font-semibold mb-2">Menjadi BIM Professional</h2>
      <p class="text-xs text-stone-100/90 leading-relaxed mb-4">
        {{ $program->description ?? 'Ikuti jalur pembelajaran terstruktur dari dasar hingga siap mengaplikasikan BIM di proyek nyata.' }}
      </p>
      <a href="{{ route('bim-lab') }}" class="inline-flex items-center gap-2 bg-[#C89D4C] hover:bg-[#b78d3f] text-stone-950 font-semibold text-xs px-4 py-2.5 rounded-lg transition">
        Lanjutkan Learning Path <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
      </a>
    </div>
    <div class="flex flex-col sm:flex-row sm:items-end gap-6 sm:gap-8">
      <div class="bg-black/45 backdrop-blur-sm rounded-xl p-4 space-y-2 text-xs border border-white/10">
        <div class="flex items-center gap-2"><span class="text-[#D8AE5E]">✓</span> {{ $modules->count() }} Modul Utama</div>
        <div class="flex items-center gap-2"><span class="text-[#D8AE5E]">✓</span> {{ $lessons->count() }}+ Video & Studi Kasus</div>
        <div class="flex items-center gap-2"><span class="text-[#D8AE5E]">✓</span> Quiz & Sertifikat</div>
        <div class="flex items-center gap-2"><span class="text-[#D8AE5E]">✓</span> Proyek Praktik</div>
      </div>
      <div class="max-w-[210px]">
        <p class="font-serif-title italic text-xs text-stone-100">"Bangun kompetensi hari ini untuk masa depan industri konstruksi yang lebih baik."</p>
        <div class="w-8 h-0.5 bg-[#D4A64E] mt-2"></div>
      </div>
    </div>
  </section>

  <!-- Main Grid -->
  <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-5">
    <!-- Left Column: Kurikulum Pembelajaran -->
    <div class="lg:col-span-5 bg-[#FAF8F4] border border-stone-200/80 rounded-2xl p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-base text-stone-900">Kurikulum Pembelajaran</h3>
        <button onclick="openModal('addModuleModal')" class="text-xs bg-[#6B532B] hover:bg-[#574220] text-white font-semibold px-3 py-1.5 rounded-lg transition">+ Modul Baru</button>
      </div>

      <div class="space-y-3">
        @php
          $modIcons = ['book-open', 'home', 'layers', 'git-merge', 'hard-hat', 'box'];
        @endphp
        @foreach($modules as $idx => $mod)
          @php
            $isActive = ($selectedModule && $selectedModule->id === $mod->id);
            $iconName = $modIcons[$idx % count($modIcons)];
          @endphp
          <div class="bg-white {{ $isActive ? 'border-2 border-[#D4B982] shadow-xs' : 'border border-stone-200' }} rounded-xl p-3.5 flex gap-3 items-start transition">
            <span class="w-6 h-6 rounded-full {{ $mod->progress_percent >= 100 ? 'bg-[#6E562F]' : 'bg-[#8A6729]' }} text-white text-xs font-bold flex items-center justify-center shrink-0">
              {{ $mod->sort_order }}
            </span>
            <a href="{{ route('belajar', ['module_id' => $mod->id]) }}" class="w-10 h-10 rounded-lg bg-[#F5EBD6] text-[#8A6729] flex items-center justify-center shrink-0">
              <i data-lucide="{{ $iconName }}" class="w-5 h-5"></i>
            </a>
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-start gap-2">
                <a href="{{ route('belajar', ['module_id' => $mod->id]) }}" class="text-xs font-bold text-stone-900 hover:text-[#8A6729] truncate">
                  {{ $mod->title }}
                </a>
                <div class="flex items-center gap-1 shrink-0">
                  @if($mod->progress_percent >= 100)
                    <span class="text-emerald-700 text-xs font-bold mr-1">✓</span>
                  @endif
                  <button onclick='openEditModuleModal(@json($mod))' class="text-stone-400 hover:text-stone-700 p-0.5" title="Edit Modul"><i data-lucide="edit-3" class="w-3.5 h-3.5"></i></button>
                  <form action="{{ route('belajar.modules.destroy', $mod->id) }}" method="POST" onsubmit="return confirm('Hapus modul {{ $mod->title }} beserta materinya?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-stone-400 hover:text-red-600 p-0.5" title="Hapus Modul"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                  </form>
                </div>
              </div>
              <p class="text-[11px] text-stone-500 line-clamp-1 mt-0.5">{{ $mod->description }}</p>
              <div class="flex items-center gap-2 mt-2">
                <div class="flex-1 h-1.5 bg-stone-200 rounded-full overflow-hidden">
                  <div class="h-full {{ $mod->progress_percent >= 100 ? 'bg-emerald-700' : 'bg-[#A87F32]' }}" style="width: {{ $mod->progress_percent }}%"></div>
                </div>
                <span class="text-[10px] font-bold {{ $mod->progress_percent >= 100 ? 'text-emerald-800' : 'text-stone-700' }}">{{ $mod->progress_percent }}%</span>
              </div>
              <div class="text-[10px] text-stone-400 mt-1">{{ $mod->total_lessons }} materi • {{ $mod->estimated_minutes }} menit</div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <!-- Right 7 Cols -->
    <div class="lg:col-span-7 space-y-5">
      <!-- Kursus Pilihan / Materi Terpilih -->
      <div class="bg-[#FAF8F4] border border-stone-200/80 rounded-2xl p-5">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-serif-title font-bold text-base text-stone-900">Kursus Pilihan</h3>
          <button onclick="openModal('addLessonModal')" class="text-xs bg-[#6B532B] hover:bg-[#574220] text-white font-semibold px-3 py-1.5 rounded-lg transition">+ Tambah Materi</button>
        </div>

        @if($featuredLesson)
          <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
            <div onclick="openModal('lessonPlayerModal')" class="md:col-span-6 relative rounded-xl overflow-hidden h-48 border border-stone-200 cursor-pointer group">
              <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />
              <span class="absolute top-3 left-3 bg-black/70 text-white text-[10px] px-2.5 py-1 rounded font-semibold uppercase">▶ {{ $featuredLesson->lesson_type }} KURSUS</span>
              <span class="absolute bottom-2 right-2 bg-black/80 text-white text-[10px] px-2 py-0.5 rounded">{{ $featuredLesson->estimated_minutes }}:00</span>
              <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-12 h-12 rounded-full bg-black/60 border border-white text-white flex items-center justify-center group-hover:bg-[#C89D4C] group-hover:text-stone-950 transition">▶</div>
              </div>
            </div>
            <div class="md:col-span-6">
              <span class="text-[10px] bg-[#F5EBD6] text-[#8A6729] font-bold px-2.5 py-1 rounded uppercase">
                MODUL {{ $featuredLesson->module_order }} • {{ strtoupper($featuredLesson->module_title) }}
              </span>
              <h4 class="font-serif-title text-xl font-bold text-stone-900 mt-2">{{ $featuredLesson->title }}</h4>
              <p class="text-xs text-stone-600 mt-1.5 leading-relaxed">
                {{ $featuredLesson->content ?: 'Pelajari alur kerja pemodelan arsitektur dari konsep hingga dokumentasi menggunakan Autodesk Revit.' }}
              </p>
              <div class="grid grid-cols-4 gap-2 my-4 text-[11px]">
                <div><div class="font-bold text-stone-800">Menengah</div><div class="text-stone-400 text-[10px]">Level</div></div>
                <div><div class="font-bold text-stone-800">{{ $featuredLesson->estimated_minutes }} menit</div><div class="text-stone-400 text-[10px]">Durasi</div></div>
                <div><div class="font-bold text-stone-800">{{ $lessons->count() }} materi</div><div class="text-stone-400 text-[10px]">Kurikulum</div></div>
                <div><div class="font-bold text-stone-800">{{ $featuredLesson->user_status === 'completed' ? 'Selesai ✓' : 'Aktif' }}</div><div class="text-stone-400 text-[10px]">Status</div></div>
              </div>
              <div class="flex items-center gap-2">
                <button onclick="openModal('lessonPlayerModal')" class="flex-1 bg-[#B89047] hover:bg-[#a37d38] text-stone-950 font-bold text-xs py-2.5 px-4 rounded-xl text-center transition">
                  ▶ Pelajari Materi
                </button>
                <form action="{{ route('belajar.lessons.toggle-progress', $featuredLesson->id) }}" method="POST">
                  @csrf
                  <button type="submit" class="p-2.5 rounded-xl border border-stone-200 {{ $featuredLesson->user_status === 'completed' ? 'bg-emerald-700 text-white' : 'bg-white text-stone-700' }}" title="Tandai Selesai / Belum Selesai">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                  </button>
                </form>
                <button onclick='openEditLessonModal(@json($featuredLesson))' class="p-2.5 rounded-xl border border-stone-200 bg-white text-stone-700" title="Edit Materi">
                  <i data-lucide="edit-3" class="w-4 h-4"></i>
                </button>
              </div>
            </div>
          </div>
        @endif
      </div>

      <!-- Materi Terbaru & Dokumen Pendukung -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <!-- Materi Terbaru -->
        <div class="bg-[#FAF8F4] border border-stone-200/80 rounded-2xl p-5">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-serif-title font-bold text-base text-stone-900">Daftar Materi ({{ $lessons->count() }})</h3>
            <button onclick="openModal('addLessonModal')" class="text-xs text-[#8A6729] font-semibold hover:underline">+ Materi</button>
          </div>
          <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
            @foreach($lessons as $les)
              <div class="flex items-center justify-between gap-2 pb-2 border-b border-stone-200/50 last:border-none">
                <a href="{{ route('belajar', ['module_id' => $les->training_module_id, 'lesson_id' => $les->id]) }}" class="min-w-0 flex-1 group">
                  <div class="text-xs font-bold text-stone-800 group-hover:text-[#8A6729] truncate">
                    {{ $les->title }}
                  </div>
                  <div class="text-[11px] text-stone-500 truncate">
                    Modul {{ $les->module_order }} - {{ $les->module_title }} • {{ $les->estimated_minutes }} menit
                  </div>
                </a>
                <div class="flex items-center gap-1 shrink-0">
                  <form action="{{ route('belajar.lessons.toggle-progress', $les->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="text-[10px] {{ $les->user_status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-[#F5EBD6] text-[#8A6729]' }} px-2 py-0.5 rounded font-semibold">
                      {{ $les->user_status === 'completed' ? 'Selesai ✓' : 'Baru' }}
                    </button>
                  </form>
                  <button onclick='openEditLessonModal(@json($les))' class="text-stone-400 hover:text-stone-700 p-0.5"><i data-lucide="edit-2" class="w-3.5 h-3.5"></i></button>
                  <form action="{{ route('belajar.lessons.destroy', $les->id) }}" method="POST" onsubmit="return confirm('Hapus materi ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-stone-400 hover:text-red-600 p-0.5"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                  </form>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <!-- Dokumen Pendukung -->
        <div class="bg-[#FAF8F4] border border-stone-200/80 rounded-2xl p-5">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-serif-title font-bold text-base text-stone-900">Dokumen Pendukung</h3>
            <button onclick="openModal('addFileModal')" class="text-xs text-[#8A6729] font-semibold hover:underline">+ Upload</button>
          </div>
          <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
            @foreach($files as $file)
              @php
                $badgeColor = $file->meta['badge_color'] ?? 'bg-red-600';
              @endphp
              <div class="flex items-center justify-between gap-2 pb-2 border-b border-stone-200/50 last:border-none">
                <div class="flex items-center gap-2.5 min-w-0">
                  <span class="px-2 py-1 rounded {{ $badgeColor }} text-white text-[10px] font-bold shrink-0">{{ $file->extension ?: 'DOC' }}</span>
                  <div class="min-w-0">
                    <div class="text-xs font-bold text-stone-800 truncate">{{ $file->original_name }}</div>
                    <div class="text-[10px] text-stone-500">{{ $file->meta['size_label'] ?? '4.5 MB' }} • {{ $file->meta['date_label'] ?? '25 Apr 2024' }}</div>
                  </div>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                  <a href="{{ route('belajar.files.download', $file->id) }}" class="p-1 text-stone-500 hover:text-[#8A6729]" title="Download"><i data-lucide="download" class="w-4 h-4"></i></a>
                  <button onclick='openEditFileModal(@json($file))' class="p-1 text-stone-400 hover:text-stone-700" title="Edit"><i data-lucide="edit-2" class="w-3.5 h-3.5"></i></button>
                  <form action="{{ route('belajar.files.destroy', $file->id) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1 text-stone-400 hover:text-red-600" title="Hapus"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                  </form>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Bottom Row: Quiz Hari Ini & Jadwal Kelas -->
  <section class="grid grid-cols-1 lg:grid-cols-12 gap-5">
    <!-- Quiz Hari Ini -->
    <div class="lg:col-span-5 bg-[#FAF8F4] border border-stone-200/80 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <h3 class="font-serif-title font-bold text-base text-stone-900">Quiz Hari Ini</h3>
          <button onclick="openModal('addQuestionModal')" class="text-[10px] bg-stone-200 hover:bg-stone-300 text-stone-700 px-2 py-0.5 rounded font-semibold">+ Soal</button>
        </div>
        <div class="text-xs font-bold text-stone-800">{{ $activeQuiz?->title ?? 'Quiz Modul 2 - Pemodelan Arsitektur' }}</div>
        <div class="text-[11px] text-stone-500 mb-3">
          {{ $activeQuiz?->questions->count() ?? 4 }} pertanyaan • {{ $activeQuiz?->time_limit_minutes ?? 15 }} menit • Nilai minimal {{ round($activeQuiz?->passing_score ?? 70) }}
        </div>
        <button onclick="openModal('quizModal')" class="bg-[#B89047] hover:bg-[#a37d38] text-stone-950 font-bold text-xs px-4 py-2 rounded-lg transition">
          Kerjakan Quiz Sekarang
        </button>
      </div>
      <div class="flex items-center gap-3">
        <div class="w-14 h-14 rounded-full border-4 border-emerald-700 flex items-center justify-center font-bold text-base text-stone-800">
          {{ round($activeQuiz?->last_attempt?->score ?? 85) }}
        </div>
        <div class="text-xs">
          <div class="font-bold text-stone-700">Nilai Terakhir</div>
          <div class="text-stone-400">
            {{ $activeQuiz?->last_attempt?->submitted_at ? \Carbon\Carbon::parse($activeQuiz->last_attempt->submitted_at)->format('d M Y') : '12 Apr 2024' }}
          </div>
        </div>
      </div>
    </div>

    <!-- Jadwal Kelas -->
    <div class="lg:col-span-7 bg-[#FAF8F4] border border-stone-200/80 rounded-2xl p-5">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-serif-title font-bold text-base text-stone-900">Jadwal Kelas</h3>
        <button onclick="openModal('addClassModal')" class="text-xs text-[#8A6729] font-semibold hover:underline">+ Tambah Jadwal</button>
      </div>
      <div class="space-y-2.5 text-xs">
        @foreach($classes as $idx => $cls)
          @php
            $badgeText = $cls->meta['badge'] ?? 'Terbuka';
            $dateLabel = $cls->meta['date_label'] ?? strtoupper(\Carbon\Carbon::parse($cls->start_date)->format('M d'));
          @endphp
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-stone-200/60 last:border-none">
            <div class="min-w-0">
              <span class="font-bold {{ $idx === 0 ? 'text-red-700' : 'text-stone-700' }} mr-2">{{ $dateLabel }}</span>
              <span class="font-bold text-stone-900">{{ $cls->name }}</span>
              <span class="text-stone-500 sm:ml-2 block sm:inline text-[11px]">{{ $cls->meta['time'] ?? '19:00 - 21:00 WIB' }} • {{ $cls->meta['platform'] ?? 'Online (Zoom)' }}</span>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
              <span class="{{ $badgeText === 'Akan Datang' ? 'bg-[#F5EBD6] text-[#8A6729]' : 'bg-emerald-100 text-emerald-800' }} px-2.5 py-0.5 rounded font-semibold text-[11px]">
                {{ $badgeText }}
              </span>
              <button onclick='openEditClassModal(@json($cls))' class="text-stone-400 hover:text-stone-700 p-0.5"><i data-lucide="edit-2" class="w-3.5 h-3.5"></i></button>
              <form action="{{ route('belajar.classes.destroy', $cls->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal kelas ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-stone-400 hover:text-red-600 p-0.5"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
              </form>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  <!-- MODAL: LESSON PLAYER -->
  @if($featuredLesson)
    <div id="lessonPlayerModal" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center p-4">
      <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-2xl w-full max-w-2xl p-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <span class="text-[10px] bg-[#F5EBD6] text-[#8A6729] font-bold px-2.5 py-0.5 rounded uppercase">{{ $featuredLesson->lesson_type }}</span>
            <h3 class="font-serif-title font-bold text-xl text-stone-900 mt-1">{{ $featuredLesson->title }}</h3>
          </div>
          <button onclick="closeModal('lessonPlayerModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <div class="rounded-xl overflow-hidden bg-stone-900 aspect-video mb-4 flex items-center justify-center relative">
          <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1000&q=80" class="w-full h-full object-cover opacity-50" />
          <div class="absolute inset-0 flex flex-col items-center justify-center text-white p-6 text-center">
            <div class="w-14 h-14 rounded-full bg-[#C89D4C] text-stone-950 flex items-center justify-center text-xl font-bold mb-3">▶</div>
            <div class="font-bold text-base">{{ $featuredLesson->title }}</div>
            <p class="text-xs text-stone-200 max-w-md mt-2">{{ $featuredLesson->content }}</p>
          </div>
        </div>
        <div class="flex items-center justify-between">
          <a href="{{ route('bim-lab') }}" class="text-xs text-[#8A6729] font-semibold hover:underline">Buka Praktik di BIM Lab →</a>
          <div class="flex gap-2">
            <button onclick="closeModal('lessonPlayerModal')" class="px-4 py-2 rounded-xl border border-stone-300 text-xs">Tutup</button>
            <form action="{{ route('belajar.lessons.toggle-progress', $featuredLesson->id) }}" method="POST">
              @csrf
              <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-700 text-white font-semibold text-xs">
                ✓ Tandai Materi Selesai
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- MODAL: KERJAKAN QUIZ -->
  @if($activeQuiz)
    <div id="quizModal" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center p-4">
      <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-2xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 border-b border-stone-200 pb-3">
          <div>
            <h3 class="font-serif-title font-bold text-xl text-stone-900">{{ $activeQuiz->title }}</h3>
            <p class="text-xs text-stone-500">Pilih jawaban yang paling tepat untuk setiap soal di bawah ini.</p>
          </div>
          <button onclick="closeModal('quizModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <form action="{{ route('belajar.quizzes.submit', $activeQuiz->id) }}" method="POST" class="space-y-4 text-xs">
          @csrf
          @foreach($activeQuiz->questions as $qIdx => $question)
            <div class="bg-white border border-stone-200 rounded-xl p-4">
              <div class="flex justify-between items-start gap-2 mb-2.5">
                <div class="font-bold text-stone-900">{{ $qIdx + 1 }}. {{ $question->question_text }}</div>
                <span class="text-[10px] bg-stone-100 text-stone-600 px-2 py-0.5 rounded shrink-0">{{ round($question->points) }} Poin</span>
              </div>
              <div class="space-y-2">
                @foreach($question->options as $opt)
                  <label class="flex items-center gap-2.5 p-2 rounded-lg border border-stone-200 hover:bg-[#FAF8F4] cursor-pointer">
                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $opt->id }}" required class="accent-[#8A6729]" />
                    <span class="text-stone-700">{{ $opt->option_text }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          @endforeach

          <div class="flex justify-end gap-2 pt-2">
            <button type="button" onclick="closeModal('quizModal')" class="px-4 py-2 rounded-xl border border-stone-300 text-stone-600">Batal</button>
            <button type="submit" class="px-5 py-2 rounded-xl bg-[#6B532B] text-white font-bold">Kumpulkan Jawaban Quiz</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: TAMBAH SOAL QUIZ -->
    <div id="addQuestionModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
      <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-serif-title font-bold text-lg text-stone-900">Tambah Soal Quiz</h3>
          <button onclick="closeModal('addQuestionModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <form action="{{ route('belajar.quizzes.questions.store', $activeQuiz->id) }}" method="POST" class="space-y-3 text-xs">
          @csrf
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Pertanyaan</label>
            <textarea name="question_text" rows="2" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Opsi 1</label>
              <input type="text" name="option_1" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-1.5" />
            </div>
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Opsi 2</label>
              <input type="text" name="option_2" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-1.5" />
            </div>
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Opsi 3</label>
              <input type="text" name="option_3" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-1.5" />
            </div>
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Opsi 4</label>
              <input type="text" name="option_4" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-1.5" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Jawaban Benar</label>
              <select name="correct_option" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
                <option value="1">Opsi 1</option>
                <option value="2">Opsi 2</option>
                <option value="3">Opsi 3</option>
                <option value="4">Opsi 4</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Bobot Poin</label>
              <input type="number" name="points" value="25" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
            </div>
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" onclick="closeModal('addQuestionModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Simpan Soal</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  <!-- MODAL: TAMBAH & EDIT MODUL -->
  <div id="addModuleModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-lg text-stone-900">Tambah Modul Kurikulum</h3>
        <button onclick="closeModal('addModuleModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
      </div>
      <form action="{{ route('belajar.modules.store') }}" method="POST" class="space-y-3 text-xs">
        @csrf
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Judul Modul</label>
          <input type="text" name="title" required placeholder="Contoh: Pemodelan MEP Lanjutan" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
        </div>
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Deskripsi</label>
          <textarea name="description" rows="2" placeholder="Deskripsi singkat modul..." class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Urutan Modul</label>
            <input type="number" name="sort_order" value="{{ $modules->count() + 1 }}" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Estimasi (Menit)</label>
            <input type="number" name="estimated_minutes" value="240" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" onclick="closeModal('addModuleModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
          <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Simpan Modul</button>
        </div>
      </form>
    </div>
  </div>

  <div id="editModuleModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-lg text-stone-900">Edit Modul Kurikulum</h3>
        <button onclick="closeModal('editModuleModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
      </div>
      <form id="editModuleForm" method="POST" class="space-y-3 text-xs">
        @csrf
        @method('PUT')
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Judul Modul</label>
          <input type="text" id="edit_mod_title" name="title" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
        </div>
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Deskripsi</label>
          <textarea id="edit_mod_desc" name="description" rows="2" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Urutan Modul</label>
            <input type="number" id="edit_mod_order" name="sort_order" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Estimasi (Menit)</label>
            <input type="number" id="edit_mod_mins" name="estimated_minutes" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" onclick="closeModal('editModuleModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
          <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Perbarui Modul</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: TAMBAH & EDIT MATERI -->
  <div id="addLessonModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-lg text-stone-900">Tambah Materi Pembelajaran</h3>
        <button onclick="closeModal('addLessonModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
      </div>
      <form action="{{ route('belajar.lessons.store') }}" method="POST" class="space-y-3 text-xs">
        @csrf
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Pilih Modul</label>
          <select name="training_module_id" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
            @foreach($modules as $m)
              <option value="{{ $m->id }}" {{ $selectedModule?->id === $m->id ? 'selected' : '' }}>Modul {{ $m->sort_order }} - {{ $m->title }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Judul Materi</label>
          <input type="text" name="title" required placeholder="Contoh: Pembuatan Curtain Wall & Mullion" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Tipe Materi</label>
            <select name="lesson_type" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
              <option value="video">Video</option>
              <option value="article">Artikel</option>
              <option value="lab">Lab Praktik</option>
              <option value="document">Dokumen</option>
              <option value="quiz">Quiz</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Durasi (Menit)</label>
            <input type="number" name="estimated_minutes" value="20" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
        </div>
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Ringkasan / Isi Materi</label>
          <textarea name="content" rows="3" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" onclick="closeModal('addLessonModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
          <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Simpan Materi</button>
        </div>
      </form>
    </div>
  </div>

  <div id="editLessonModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-lg text-stone-900">Edit Materi Pembelajaran</h3>
        <button onclick="closeModal('editLessonModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
      </div>
      <form id="editLessonForm" method="POST" class="space-y-3 text-xs">
        @csrf
        @method('PUT')
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Pilih Modul</label>
          <select id="edit_les_module" name="training_module_id" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
            @foreach($modules as $m)
              <option value="{{ $m->id }}">Modul {{ $m->sort_order }} - {{ $m->title }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Judul Materi</label>
          <input type="text" id="edit_les_title" name="title" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Tipe Materi</label>
            <select id="edit_les_type" name="lesson_type" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
              <option value="video">Video</option>
              <option value="article">Artikel</option>
              <option value="lab">Lab Praktik</option>
              <option value="document">Dokumen</option>
              <option value="quiz">Quiz</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Durasi (Menit)</label>
            <input type="number" id="edit_les_mins" name="estimated_minutes" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
        </div>
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Ringkasan / Isi Materi</label>
          <textarea id="edit_les_content" name="content" rows="3" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" onclick="closeModal('editLessonModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
          <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Perbarui Materi</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: TAMBAH & EDIT DOKUMEN -->
  <div id="addFileModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-lg text-stone-900">Tambah Dokumen Pendukung</h3>
        <button onclick="closeModal('addFileModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
      </div>
      <form action="{{ route('belajar.files.store') }}" method="POST" class="space-y-3 text-xs">
        @csrf
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Nama Dokumen / File</label>
          <input type="text" name="original_name" required placeholder="Contoh: Standar Modeling BIM SNI (PDF)" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Format / Ekstensi</label>
            <select name="extension" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
              <option value="PDF">PDF</option>
              <option value="RVT">RVT (Revit)</option>
              <option value="IFC">IFC</option>
              <option value="XLS">XLS (Excel)</option>
              <option value="DOC">DOC (Word)</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Ukuran File</label>
            <input type="text" name="size_label" value="8.4 MB" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" onclick="closeModal('addFileModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
          <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Simpan Dokumen</button>
        </div>
      </form>
    </div>
  </div>

  <div id="editFileModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-lg text-stone-900">Edit Dokumen Pendukung</h3>
        <button onclick="closeModal('editFileModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
      </div>
      <form id="editFileForm" method="POST" class="space-y-3 text-xs">
        @csrf
        @method('PUT')
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Nama Dokumen / File</label>
          <input type="text" id="edit_file_name" name="original_name" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Format / Ekstensi</label>
            <input type="text" id="edit_file_ext" name="extension" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Ukuran File</label>
            <input type="text" id="edit_file_size" name="size_label" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" onclick="closeModal('editFileModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
          <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Perbarui Dokumen</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: TAMBAH & EDIT JADWAL KELAS -->
  <div id="addClassModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-lg text-stone-900">Tambah Jadwal Kelas</h3>
        <button onclick="closeModal('addClassModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
      </div>
      <form action="{{ route('belajar.classes.store') }}" method="POST" class="space-y-3 text-xs">
        @csrf
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Nama Sesi / Kelas</label>
          <input type="text" name="name" required placeholder="Contoh: Live Review: Struktur Gedung 4 Lantai" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Tanggal</label>
            <input type="date" name="start_date" required value="{{ now()->addDays(5)->toDateString() }}" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Waktu</label>
            <input type="text" name="time_range" value="19:00 - 21:00 WIB" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Platform</label>
            <input type="text" name="platform" value="Online (Zoom)" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Status Badge</label>
            <select name="badge" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
              <option value="Akan Datang">Akan Datang</option>
              <option value="Terdaftar">Terdaftar</option>
              <option value="Terbuka">Terbuka</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" onclick="closeModal('addClassModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
          <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Simpan Jadwal</button>
        </div>
      </form>
    </div>
  </div>

  <div id="editClassModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif-title font-bold text-lg text-stone-900">Edit Jadwal Kelas</h3>
        <button onclick="closeModal('editClassModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
      </div>
      <form id="editClassForm" method="POST" class="space-y-3 text-xs">
        @csrf
        @method('PUT')
        <div>
          <label class="block font-semibold text-stone-700 mb-1">Nama Sesi / Kelas</label>
          <input type="text" id="edit_cls_name" name="name" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Tanggal</label>
            <input type="date" id="edit_cls_date" name="start_date" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Waktu</label>
            <input type="text" id="edit_cls_time" name="time_range" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Platform</label>
            <input type="text" id="edit_cls_platform" name="platform" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Status Badge</label>
            <select id="edit_cls_badge" name="badge" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
              <option value="Akan Datang">Akan Datang</option>
              <option value="Terdaftar">Terdaftar</option>
              <option value="Terbuka">Terbuka</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-3">
          <button type="button" onclick="closeModal('editClassModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
          <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Perbarui Jadwal</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  function openEditModuleModal(mod) {
    document.getElementById('editModuleForm').action = `/belajar/modules/${mod.id}`;
    document.getElementById('edit_mod_title').value = mod.title || '';
    document.getElementById('edit_mod_desc').value = mod.description || '';
    document.getElementById('edit_mod_order').value = mod.sort_order || 1;
    document.getElementById('edit_mod_mins').value = mod.estimated_minutes || 120;
    openModal('editModuleModal');
  }

  function openEditLessonModal(les) {
    document.getElementById('editLessonForm').action = `/belajar/lessons/${les.id}`;
    document.getElementById('edit_les_module').value = les.training_module_id;
    document.getElementById('edit_les_title').value = les.title || '';
    document.getElementById('edit_les_type').value = les.lesson_type || 'video';
    document.getElementById('edit_les_mins').value = les.estimated_minutes || 20;
    document.getElementById('edit_les_content').value = les.content || '';
    openModal('editLessonModal');
  }

  function openEditFileModal(file) {
    document.getElementById('editFileForm').action = `/belajar/files/${file.id}`;
    document.getElementById('edit_file_name').value = file.original_name || '';
    document.getElementById('edit_file_ext').value = file.extension || 'PDF';
    document.getElementById('edit_file_size').value = (file.meta && file.meta.size_label) ? file.meta.size_label : '5.0 MB';
    openModal('editFileModal');
  }

  function openEditClassModal(cls) {
    document.getElementById('editClassForm').action = `/belajar/classes/${cls.id}`;
    document.getElementById('edit_cls_name').value = cls.name || '';
    document.getElementById('edit_cls_date').value = cls.start_date || '';
    document.getElementById('edit_cls_time').value = (cls.meta && cls.meta.time) ? cls.meta.time : '19:00 - 21:00 WIB';
    document.getElementById('edit_cls_platform').value = (cls.meta && cls.meta.platform) ? cls.meta.platform : 'Online (Zoom)';
    document.getElementById('edit_cls_badge').value = (cls.meta && cls.meta.badge) ? cls.meta.badge : 'Terbuka';
    openModal('editClassModal');
  }
</script>
@endpush

