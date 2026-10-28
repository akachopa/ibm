@extends('layouts.app')

@section('title', 'Kompetensi - BIM Nusantara')
@section('page_title', 'Kompetensi')
@section('page_subtitle', 'Pantau skill matrix, hasil assessment, dan perkembangan kompetensi.')

@section('content')
  <section class="hero-banner rounded-2xl p-6 sm:p-8 text-white mb-6 flex flex-col sm:flex-row justify-between sm:items-end gap-4">
    <div>
      <div class="text-[11px] tracking-[0.2em] uppercase text-[#F4D38B] font-semibold mb-1.5">KOMPETENSI MEMBANGUN MASA DEPAN</div>
      <h2 class="font-serif-title text-3xl sm:text-4xl font-semibold leading-tight">Tingkatkan Kompetensi<br>Wujudkan Karya Nyata</h2>
      <p class="text-xs text-stone-100/90 mt-2 max-w-md">Kuasai skill BIM secara terstruktur dengan jalur pembelajaran berbasis industri dan standar global.</p>
      <a href="{{ route('belajar') }}" class="inline-block mt-4 bg-[#C89D4C] hover:bg-[#b78d3f] text-stone-950 font-semibold text-xs px-4 py-2.5 rounded-lg transition">Lanjutkan Pembelajaran →</a>
    </div>
    <div class="sm:text-right">
      <p class="font-serif-title italic text-sm">"Kompetensi hari ini,<br>untuk bangunan yang lebih baik esok."</p>
      <div class="w-8 h-0.5 bg-[#D4A64E] mt-2 sm:ml-auto"></div>
    </div>
  </section>

  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-[#FAF8F4] border border-stone-200 rounded-2xl p-4">
      <div class="text-xs text-stone-600">Skor Kompetensi Keseluruhan</div>
      <div class="mt-1"><span class="text-3xl font-bold">{{ $overallScore }}</span> <span class="text-xs text-stone-500">/ 100</span></div>
      <div class="w-full h-2 bg-stone-200 rounded-full mt-3 overflow-hidden"><div class="h-full bg-[#A87F32]" style="width: {{ $overallScore }}%"></div></div>
    </div>
    <div class="bg-[#FAF8F4] border border-stone-200 rounded-2xl p-4">
      <div class="text-xs text-stone-600">Level Saat Ini</div>
      <div class="flex items-center gap-2 mt-1">
        <span class="text-xl font-bold">{{ $currentLevelName }}</span>
        <span class="text-[10px] bg-[#F5EBD6] text-[#8A6729] px-2 py-0.5 rounded font-semibold">Level {{ $avgLevel }} dari 5</span>
      </div>
      <div class="text-[11px] text-stone-500 mt-2">Terus tingkatkan skill untuk mencapai level berikutnya</div>
    </div>
    <div class="bg-[#FAF8F4] border border-stone-200 rounded-2xl p-4">
      <div class="text-xs text-stone-600">Area Kompetensi</div>
      <div class="text-2xl font-bold mt-1">{{ $allCompetencies->count() }} <span class="text-xs font-normal text-stone-500">area terdaftar</span></div>
      <div class="text-[11px] text-stone-500 mt-2">{{ $allCompetencies->where('user_status', 'achieved')->count() }} area tercapai</div>
    </div>
    <div class="bg-[#FAF8F4] border border-stone-200 rounded-2xl p-4">
      <div class="text-xs text-stone-600">Sertifikasi Aktif</div>
      <div class="text-2xl font-bold mt-1">{{ $certificatesCount }} <span class="text-xs font-normal text-stone-500">sertifikat</span></div>
      <a href="{{ route('profil') }}" class="text-[11px] text-[#8A6729] font-medium hover:underline mt-2 inline-block">Lihat sertifikat →</a>
    </div>
  </section>

  <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-6">
    <div class="lg:col-span-8 bg-[#FAF8F4] border border-stone-200 rounded-2xl p-5">
      <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
        <div>
          <h3 class="font-serif-title font-bold text-base">Skill Matrix Kompetensi</h3>
          <p class="text-[11px] text-stone-500">Pantau perkembangan setiap area kompetensi BIM Anda.</p>
        </div>
        <div class="flex items-center gap-2">
          <form method="GET" action="{{ route('kompetensi') }}">
            <select name="level" onchange="this.form.submit()" class="border rounded-lg px-3 py-1.5 text-xs bg-white">
              @foreach(['all' => 'Semua Level', 'basic' => 'Basic', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'] as $k => $v)
                <option value="{{ $k }}" {{ $levelFilter === $k ? 'selected' : '' }}>{{ $v }}</option>
              @endforeach
            </select>
          </form>
          <button onclick="openModal('addCompModal')" class="bg-[#6B532B] text-white text-xs font-semibold px-3 py-1.5 rounded-lg">+ Kompetensi</button>
        </div>
      </div>

      <div class="space-y-1 text-xs">
        @forelse($competencies as $c)
          @php
            $badge = match($c->level_label) {
              'Advanced' => 'bg-emerald-100 text-emerald-800',
              'Intermediate' => 'bg-[#F5EBD6] text-[#8A6729]',
              default => 'bg-blue-100 text-blue-800',
            };
            $bar = $c->level_label === 'Advanced' ? 'bg-emerald-700' : 'bg-[#A87F32]';
          @endphp
          <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 sm:gap-3 py-2 border-b border-stone-200/70">
            <div class="w-full sm:w-1/3 font-semibold">{{ $c->name }}<div class="text-[10px] text-stone-400 font-normal">{{ $c->code }}</div></div>
            <div><span class="{{ $badge }} px-2.5 py-0.5 rounded-full text-[11px]">{{ $c->level_label }}</span></div>
            <div class="flex-1 min-w-[80px] h-2 bg-stone-200 rounded-full overflow-hidden"><div class="h-full {{ $bar }}" style="width: {{ $c->score_int }}%"></div></div>
            <div class="font-bold w-10 text-right">{{ $c->score_int }}%</div>
            <div class="flex items-center gap-1">
              <button onclick='openEditCompModal(@json($c))' class="text-stone-400 hover:text-stone-700 p-0.5"><i data-lucide="edit-2" class="w-3.5 h-3.5"></i></button>
              <form action="{{ route('kompetensi.competencies.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Hapus kompetensi ini?')">
                @csrf @method('DELETE')
                <button class="text-stone-400 hover:text-red-600 p-0.5"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
              </form>
            </div>
          </div>
        @empty
          <div class="text-center text-stone-400 py-6">Tidak ada kompetensi pada level ini.</div>
        @endforelse
      </div>
    </div>

    <div class="lg:col-span-4 bg-[#FAF8F4] border border-stone-200 rounded-2xl p-5 flex flex-col items-center">
      <h3 class="font-serif-title font-bold text-base self-start mb-2">Ringkasan Assessment</h3>
      @php
        $radar = $allCompetencies->take(6)->values();
        $n = max(3, $radar->count());
        $rc = 100; $rr = 70;
        $pts = [];
        foreach ($radar as $i => $c) {
          $ang = -M_PI / 2 + (2 * M_PI * $i / $n);
          $r = $rr * ($c->score_int / 100);
          $pts[] = round($rc + $r * cos($ang), 1) . ',' . round($rc + 10 + $r * sin($ang), 1);
        }
        $ring = function ($f) use ($n, $rc, $rr) {
          $o = [];
          for ($i = 0; $i < $n; $i++) {
            $ang = -M_PI / 2 + (2 * M_PI * $i / $n);
            $o[] = round($rc + $rr * $f * cos($ang), 1) . ',' . round($rc + 10 + $rr * $f * sin($ang), 1);
          }
          return implode(' ', $o);
        };
      @endphp
      <svg viewBox="0 0 200 220" class="w-full max-w-[240px]">
        <polygon points="{{ $ring(1) }}" fill="none" stroke="#DDD6C7"/>
        <polygon points="{{ $ring(0.66) }}" fill="none" stroke="#DDD6C7"/>
        <polygon points="{{ $ring(0.33) }}" fill="none" stroke="#DDD6C7"/>
        @if(count($pts) >= 3)
          <polygon points="{{ implode(' ', $pts) }}" fill="rgba(184,140,64,0.35)" stroke="#A87F32" stroke-width="2"/>
        @endif
      </svg>
      <div class="w-full text-[11px] space-y-1 mt-2">
        @foreach($radar as $c)
          <div class="flex justify-between"><span>{{ $c->name }}</span><b>{{ $c->score_int }}</b></div>
        @endforeach
      </div>
    </div>
  </section>

  <section class="grid grid-cols-1 lg:grid-cols-3 gap-5 text-xs">
    <div class="lg:col-span-2 bg-[#FAF8F4] border border-stone-200 rounded-2xl p-5 space-y-3">
      <div class="flex justify-between items-center">
        <h3 class="font-serif-title font-bold text-base">Riwayat Assessment</h3>
        <button onclick="openModal('addAssessModal')" class="bg-[#6B532B] text-white text-xs font-semibold px-3 py-1.5 rounded-lg">+ Assessment</button>
      </div>
      @forelse($assessments as $a)
        <div class="p-3 bg-white border border-stone-200 rounded-xl">
          <div class="flex flex-wrap justify-between items-start gap-2">
            <div>
              <b>{{ $a->assessed_at ? \Carbon\Carbon::parse($a->assessed_at)->translatedFormat('d M Y') : '-' }}</b> - {{ $a->title }} ({{ round($a->score) }}/{{ round($a->max_score) }})
              @if($a->notes)<div class="text-[11px] text-stone-500 mt-1">{{ $a->notes }}</div>@endif
            </div>
            <div class="flex items-center gap-2">
              <span class="font-bold {{ $a->result === 'pass' ? 'text-emerald-700' : ($a->result === 'fail' ? 'text-red-700' : 'text-[#8A6729]') }}">
                {{ $a->result === 'pass' ? 'Lulus' : ($a->result === 'fail' ? 'Tidak Lulus' : 'Dalam Proses') }}
              </span>
              <button onclick='openEditAssessModal(@json($a))' class="text-stone-400 hover:text-stone-700"><i data-lucide="edit-2" class="w-3.5 h-3.5"></i></button>
              <form action="{{ route('kompetensi.assessments.destroy', $a->id) }}" method="POST" onsubmit="return confirm('Hapus assessment ini?')">
                @csrf @method('DELETE')
                <button class="text-stone-400 hover:text-red-600"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
              </form>
            </div>
          </div>
        </div>
      @empty
        <div class="text-stone-400 text-center py-4">Belum ada assessment.</div>
      @endforelse
    </div>

    <div class="bg-[#FAF8F4] border border-stone-200 rounded-2xl p-5 space-y-3">
      <h3 class="font-serif-title font-bold text-base">Feedback dari Mentor</h3>
      @foreach($assessments->filter(fn($a) => $a->notes)->take(3) as $a)
        <div><b>{{ $a->assessor_name ?? 'Mentor' }}:</b><br><span class="text-stone-600">{{ $a->notes }}</span></div>
      @endforeach
    </div>
  </section>

  <!-- Modals -->
  <div id="addCompModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex justify-between mb-4"><h3 class="font-serif-title font-bold text-lg">Tambah Kompetensi</h3><button onclick="closeModal('addCompModal')"><i data-lucide="x" class="w-4 h-4"></i></button></div>
      <form action="{{ route('kompetensi.competencies.store') }}" method="POST" class="space-y-3 text-xs">
        @csrf
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block font-semibold mb-1">Nama</label><input name="name" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
          <div><label class="block font-semibold mb-1">Kode</label><input name="code" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
        </div>
        <div><label class="block font-semibold mb-1">Kategori</label><input name="category" value="Modeling" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
        <div class="grid grid-cols-3 gap-2">
          <div><label class="block font-semibold mb-1">Skor</label><input type="number" name="score" value="50" min="0" max="100" required class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2"></div>
          <div><label class="block font-semibold mb-1">Level (1-5)</label><input type="number" name="current_level" value="2" min="1" max="5" required class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2"></div>
          <div><label class="block font-semibold mb-1">Target</label><input type="number" name="target_level" value="4" min="1" max="5" required class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2"></div>
        </div>
        <div class="flex justify-end gap-2 pt-3"><button type="button" onclick="closeModal('addCompModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button><button class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Simpan</button></div>
      </form>
    </div>
  </div>

  <div id="editCompModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex justify-between mb-4"><h3 class="font-serif-title font-bold text-lg">Edit Kompetensi</h3><button onclick="closeModal('editCompModal')"><i data-lucide="x" class="w-4 h-4"></i></button></div>
      <form id="editCompForm" method="POST" class="space-y-3 text-xs">
        @csrf @method('PUT')
        <div><label class="block font-semibold mb-1">Nama</label><input id="ec_name" name="name" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
        <div class="grid grid-cols-3 gap-2">
          <div><label class="block font-semibold mb-1">Skor</label><input id="ec_score" type="number" name="score" min="0" max="100" required class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2"></div>
          <div><label class="block font-semibold mb-1">Level</label><input id="ec_level" type="number" name="current_level" min="1" max="5" required class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2"></div>
          <div><label class="block font-semibold mb-1">Target</label><input id="ec_target" type="number" name="target_level" min="1" max="5" required class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2"></div>
        </div>
        <div class="flex justify-end gap-2 pt-3"><button type="button" onclick="closeModal('editCompModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button><button class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Perbarui</button></div>
      </form>
    </div>
  </div>

  <div id="addAssessModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex justify-between mb-4"><h3 class="font-serif-title font-bold text-lg">Tambah Assessment</h3><button onclick="closeModal('addAssessModal')"><i data-lucide="x" class="w-4 h-4"></i></button></div>
      <form action="{{ route('kompetensi.assessments.store') }}" method="POST" class="space-y-3 text-xs">
        @csrf
        <div><label class="block font-semibold mb-1">Judul</label><input name="title" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block font-semibold mb-1">Tipe</label>
            <select name="assessment_type" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
              @foreach(['quiz','project','challenge','manual','final'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach
            </select></div>
          <div><label class="block font-semibold mb-1">Skor</label><input type="number" name="score" value="80" min="0" max="100" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block font-semibold mb-1">Hasil</label>
            <select name="result" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"><option value="pass">Lulus</option><option value="fail">Tidak Lulus</option><option value="in_progress">Dalam Proses</option></select></div>
          <div><label class="block font-semibold mb-1">Tanggal</label><input type="date" name="assessed_at" value="{{ now()->toDateString() }}" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
        </div>
        <div><label class="block font-semibold mb-1">Catatan</label><textarea name="notes" rows="2" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea></div>
        <div class="flex justify-end gap-2 pt-3"><button type="button" onclick="closeModal('addAssessModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button><button class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Simpan</button></div>
      </form>
    </div>
  </div>

  <div id="editAssessModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex justify-between mb-4"><h3 class="font-serif-title font-bold text-lg">Edit Assessment</h3><button onclick="closeModal('editAssessModal')"><i data-lucide="x" class="w-4 h-4"></i></button></div>
      <form id="editAssessForm" method="POST" class="space-y-3 text-xs">
        @csrf @method('PUT')
        <div><label class="block font-semibold mb-1">Judul</label><input id="ea_title" name="title" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block font-semibold mb-1">Skor</label><input id="ea_score" type="number" name="score" min="0" max="100" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
          <div><label class="block font-semibold mb-1">Hasil</label>
            <select id="ea_result" name="result" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"><option value="pass">Lulus</option><option value="fail">Tidak Lulus</option><option value="in_progress">Dalam Proses</option></select></div>
        </div>
        <div><label class="block font-semibold mb-1">Catatan</label><textarea id="ea_notes" name="notes" rows="2" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea></div>
        <div class="flex justify-end gap-2 pt-3"><button type="button" onclick="closeModal('editAssessModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button><button class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Perbarui</button></div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  function openEditCompModal(c) {
    document.getElementById('editCompForm').action = `/kompetensi/competencies/${c.id}`;
    document.getElementById('ec_name').value = c.name;
    document.getElementById('ec_score').value = c.score_int;
    document.getElementById('ec_level').value = c.current_level;
    document.getElementById('ec_target').value = c.target_level;
    openModal('editCompModal');
  }
  function openEditAssessModal(a) {
    document.getElementById('editAssessForm').action = `/kompetensi/assessments/${a.id}`;
    document.getElementById('ea_title').value = a.title;
    document.getElementById('ea_score').value = Math.round(a.score);
    document.getElementById('ea_result').value = a.result;
    document.getElementById('ea_notes').value = a.notes || '';
    openModal('editAssessModal');
  }
</script>
@endpush

