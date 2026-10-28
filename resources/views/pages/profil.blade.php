@extends('layouts.app')

@section('title', 'Profil & Sertifikat - BIM Nusantara')
@section('page_title', 'Profil & Sertifikat')
@section('page_subtitle', 'Kelola profil, portofolio hasil project, dan sertifikat digital.')

@section('content')
  @php
    $prefs = $currentUserPrefs ?? [];
    $jobTitle = $currentOrgUser->title ?? ($prefs['job_title'] ?? 'BIM Modeler');
  @endphp

  <!-- Profile Header -->
  <section class="profile-banner rounded-2xl p-5 sm:p-6 border border-stone-200 mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex flex-col sm:flex-row sm:items-center gap-5">
      <img src="{{ $currentUser->avatar_url ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80' }}" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow" />
      <div>
        <div class="flex flex-wrap items-center gap-2.5">
          <h2 class="font-serif-title text-2xl font-bold text-stone-900">{{ $currentUser->name }}</h2>
          <span class="bg-[#F2E3C2] text-[#7A591E] text-xs font-semibold px-3 py-0.5 rounded-full">{{ $currentOrgUser->role_name ?? 'Peserta' }} Aktif</span>
        </div>
        <div class="text-xs text-stone-600 mt-0.5">{{ $currentUser->email }}</div>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-stone-700 mt-2">
          <span>🏢 {{ $currentOrg->name }}</span>
          <span>📐 {{ $jobTitle }}</span>
          <span>📍 {{ $prefs['location'] ?? 'Jakarta, Indonesia' }}</span>
        </div>
        <p class="font-serif-title italic text-xs text-stone-600 mt-2">“{{ $prefs['bio'] ?? 'Terus belajar dan membangun karya yang berdampak melalui BIM.' }}”</p>
      </div>
    </div>
    <button onclick="openModal('editProfileModal')" class="bg-white border border-stone-300 text-xs font-semibold px-4 py-2 rounded-xl shadow-sm hover:bg-stone-50 self-start md:self-center">✎ Edit Profil</button>
  </section>

  <!-- Sertifikat -->
  <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-6">
    <div class="lg:col-span-6 bg-[#FAF8F4] border border-stone-200 rounded-2xl p-5">
      <h3 class="font-serif-title font-bold text-base mb-3">Sertifikat Utama</h3>
      @if($activeCertificate)
        <div class="bg-[#FCF9F2] border-4 border-[#C89D4C]/60 rounded-xl p-5 sm:p-6 text-center shadow-inner">
          <div class="text-[11px] font-bold tracking-widest text-stone-700">BIM NUSANTARA</div>
          <div class="font-serif-title text-xl sm:text-2xl font-bold text-[#9C732B] tracking-wide mt-2">SERTIFIKAT KOMPETENSI</div>
          <div class="text-xs text-stone-500 mt-2">Diberikan kepada</div>
          <div class="font-serif-title text-2xl sm:text-3xl font-bold text-stone-900 my-2">{{ $activeCertificate->holder_name }}</div>
          <div class="text-xs text-stone-600">Telah berhasil menyelesaikan pelatihan dan dinyatakan kompeten dalam</div>
          <div class="font-serif-title text-lg font-bold text-[#8A6322] mt-1">{{ $activeCertificate->title }}</div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px] border-t border-stone-200 mt-5 pt-3">
            <div><span class="text-stone-400 block">Tanggal Penyelesaian</span><b>{{ \Carbon\Carbon::parse($activeCertificate->issue_date)->translatedFormat('d F Y') }}</b></div>
            <div><span class="text-stone-400 block">Durasi Pelatihan</span><b>{{ $activeCertificate->meta['duration_hours'] ?? 24 }} Jam</b></div>
            <div><span class="text-stone-400 block">No. Sertifikat</span><b>{{ $activeCertificate->certificate_no }}</b></div>
          </div>
          <div class="flex justify-between items-center mt-4 pt-3 border-t border-stone-200 text-left text-[10px]">
            <div>
              <div class="font-serif-title italic text-base">{{ $activeCertificate->meta['director'] ?? 'Dian Pratama' }}</div>
              <div class="text-stone-500">{{ $activeCertificate->meta['director_title'] ?? 'Direktur BIM Nusantara' }}</div>
            </div>
            <div class="bg-stone-900 text-white p-2 rounded text-[9px] font-mono">{{ $activeCertificate->status === 'active' ? 'QR VERIFIED' : strtoupper($activeCertificate->status) }}</div>
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-3 no-print">
          <button onclick="window.print()" class="text-xs border border-stone-300 bg-white px-3 py-1.5 rounded-lg hover:bg-stone-50">🖨 Cetak</button>
        </div>
      @else
        <div class="text-center text-stone-400 text-xs py-10">Belum ada sertifikat.</div>
      @endif
    </div>

    <div class="lg:col-span-6 bg-[#FAF8F4] border border-stone-200 rounded-2xl p-5 space-y-3">
      <div class="flex justify-between items-center">
        <h3 class="font-serif-title font-bold text-base">Daftar Sertifikat ({{ $certificates->count() }})</h3>
        <button onclick="openModal('addCertModal')" class="bg-[#6B532B] text-white text-xs font-semibold px-3 py-1.5 rounded-lg">+ Sertifikat</button>
      </div>
      @foreach($certificates as $cert)
        <div class="bg-white border {{ $activeCertificate && $activeCertificate->id === $cert->id ? 'border-[#C89D4C]' : 'border-stone-200' }} rounded-xl p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <div class="min-w-0">
            <div class="text-xs font-bold">{{ $cert->title }}</div>
            <div class="text-[11px] text-stone-500">Diterbitkan {{ \Carbon\Carbon::parse($cert->issue_date)->translatedFormat('d F Y') }} • No: {{ $cert->certificate_no }}</div>
            <span class="inline-block mt-1 text-[10px] {{ $cert->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-700' }} px-2 py-0.5 rounded font-semibold">
              {{ $cert->status === 'active' ? '✓ Terverifikasi' : ucfirst($cert->status) }}
            </span>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('profil', ['cert_id' => $cert->id]) }}" class="border border-stone-200 text-xs px-3 py-1.5 rounded-lg hover:bg-stone-50">Lihat</a>
            <button onclick='openEditCertModal(@json($cert))' class="text-stone-400 hover:text-stone-700 p-1"><i data-lucide="edit-2" class="w-3.5 h-3.5"></i></button>
            <form action="{{ route('profil.certificates.destroy', $cert->id) }}" method="POST" onsubmit="return confirm('Hapus sertifikat ini?')">
              @csrf @method('DELETE')
              <button class="text-stone-400 hover:text-red-600 p-1"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
            </form>
          </div>
        </div>
      @endforeach
    </div>
  </section>

  <!-- Portofolio & Riwayat -->
  <section class="grid grid-cols-1 lg:grid-cols-12 gap-5">
    <div class="lg:col-span-7 bg-[#FAF8F4] border border-stone-200 rounded-2xl p-5">
      <div class="flex justify-between items-center mb-3">
        <h3 class="font-serif-title font-bold text-base">Portofolio Project</h3>
        <a href="{{ route('project') }}" class="text-xs text-[#8A6729] hover:underline">Kelola →</a>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
        @foreach($projects as $p)
          <a href="{{ route('project', ['project_id' => $p->id]) }}" class="bg-white border border-stone-200 hover:border-[#C89D4C] rounded-xl p-2.5 block transition">
            <img src="{{ $p->thumbnail_url ?: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=400&q=80' }}" class="w-full h-24 rounded-lg object-cover mb-2" />
            <div class="font-bold truncate">{{ $p->title }}</div>
            <div class="text-[11px] text-stone-500 mb-2">{{ ucfirst($p->category) }}</div>
            <span class="text-[10px] bg-blue-50 text-blue-700 px-2 py-0.5 rounded">{{ $p->meta['software'] ?? 'Revit' }}</span>
            <span class="text-[10px] bg-stone-100 px-2 py-0.5 rounded">{{ $p->meta['lod'] ?? 'LOD 300' }}</span>
          </a>
        @endforeach
      </div>
    </div>

    <div class="lg:col-span-5 bg-[#FAF8F4] border border-stone-200 rounded-2xl p-5 space-y-2.5 text-xs">
      <h3 class="font-serif-title font-bold text-base mb-1">Riwayat Pelatihan</h3>
      @forelse($enrollments as $en)
        <div class="flex justify-between items-center gap-2 py-1 border-b border-stone-200/70 last:border-none">
          <span><b>{{ $en->class_name }}</b> <span class="text-stone-500">({{ $en->start_date ? \Carbon\Carbon::parse($en->start_date)->format('d M Y') : '-' }})</span></span>
          <div class="shrink-0">
            @if($en->final_score)<span class="bg-blue-100 text-blue-800 font-bold px-2 py-0.5 rounded mr-1">{{ round($en->final_score) }}</span>@endif
            <span class="{{ $en->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-[#F5EBD6] text-[#8A6729]' }} px-2 py-0.5 rounded">{{ $en->status === 'completed' ? 'Selesai' : ucfirst($en->status) }}</span>
          </div>
        </div>
      @empty
        <div class="text-stone-400">Belum ada riwayat pelatihan.</div>
      @endforelse
    </div>
  </section>

  <!-- Modal edit profil -->
  <div id="editProfileModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between mb-4"><h3 class="font-serif-title font-bold text-lg">Edit Profil</h3><button onclick="closeModal('editProfileModal')"><i data-lucide="x" class="w-4 h-4"></i></button></div>
      <form action="{{ route('profil.update') }}" method="POST" class="space-y-3 text-xs">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block font-semibold mb-1">Nama</label><input name="name" required value="{{ $currentUser->name }}" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
          <div><label class="block font-semibold mb-1">Email</label><input type="email" name="email" required value="{{ $currentUser->email }}" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block font-semibold mb-1">Telepon</label><input name="phone" value="{{ $currentUser->phone }}" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
          <div><label class="block font-semibold mb-1">Jabatan</label><input name="job_title" value="{{ $jobTitle }}" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block font-semibold mb-1">Lokasi</label><input name="location" value="{{ $prefs['location'] ?? '' }}" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
          <div><label class="block font-semibold mb-1">Organisasi</label><input name="org_name" value="{{ $currentOrg->name }}" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
        </div>
        <div><label class="block font-semibold mb-1">URL Foto</label><input name="avatar_url" value="{{ $currentUser->avatar_url }}" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
        <div><label class="block font-semibold mb-1">Bio</label><textarea name="bio" rows="2" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">{{ $prefs['bio'] ?? '' }}</textarea></div>
        <div class="flex justify-end gap-2 pt-3"><button type="button" onclick="closeModal('editProfileModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button><button class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Simpan</button></div>
      </form>
    </div>
  </div>

  <!-- Modal tambah sertifikat -->
  <div id="addCertModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex justify-between mb-4"><h3 class="font-serif-title font-bold text-lg">Terbitkan Sertifikat</h3><button onclick="closeModal('addCertModal')"><i data-lucide="x" class="w-4 h-4"></i></button></div>
      <form action="{{ route('profil.certificates.store') }}" method="POST" class="space-y-3 text-xs">
        @csrf
        <div><label class="block font-semibold mb-1">Judul</label><input name="title" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block font-semibold mb-1">No. Sertifikat (opsional)</label><input name="certificate_no" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
          <div><label class="block font-semibold mb-1">Tanggal</label><input type="date" name="issue_date" required value="{{ now()->toDateString() }}" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
        </div>
        <div class="grid grid-cols-3 gap-2">
          <div><label class="block font-semibold mb-1">Skor</label><input type="number" name="score" value="90" class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2"></div>
          <div><label class="block font-semibold mb-1">Durasi (jam)</label><input type="number" name="duration_hours" value="24" class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2"></div>
          <div><label class="block font-semibold mb-1">Level</label><input name="competency_level" value="Intermediate" class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2"></div>
        </div>
        <div class="flex justify-end gap-2 pt-3"><button type="button" onclick="closeModal('addCertModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button><button class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Terbitkan</button></div>
      </form>
    </div>
  </div>

  <!-- Modal edit sertifikat -->
  <div id="editCertModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex justify-between mb-4"><h3 class="font-serif-title font-bold text-lg">Edit Sertifikat</h3><button onclick="closeModal('editCertModal')"><i data-lucide="x" class="w-4 h-4"></i></button></div>
      <form id="editCertForm" method="POST" class="space-y-3 text-xs">
        @csrf @method('PUT')
        <div><label class="block font-semibold mb-1">Judul</label><input id="ecert_title" name="title" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block font-semibold mb-1">No. Sertifikat</label><input id="ecert_no" name="certificate_no" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
          <div><label class="block font-semibold mb-1">Tanggal</label><input id="ecert_date" type="date" name="issue_date" required class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></div>
        </div>
        <div class="grid grid-cols-3 gap-2">
          <div><label class="block font-semibold mb-1">Skor</label><input id="ecert_score" type="number" name="score" class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2"></div>
          <div><label class="block font-semibold mb-1">Durasi</label><input id="ecert_dur" type="number" name="duration_hours" class="w-full bg-white border border-stone-300 rounded-xl px-2.5 py-2"></div>
          <div><label class="block font-semibold mb-1">Status</label>
            <select id="ecert_status" name="status" class="w-full bg-white border border-stone-300 rounded-xl px-2 py-2"><option value="active">Active</option><option value="revoked">Revoked</option><option value="expired">Expired</option></select></div>
        </div>
        <div class="flex justify-end gap-2 pt-3"><button type="button" onclick="closeModal('editCertModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button><button class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Perbarui</button></div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  function openEditCertModal(c) {
    document.getElementById('editCertForm').action = `/profil/certificates/${c.id}`;
    document.getElementById('ecert_title').value = c.title;
    document.getElementById('ecert_no').value = c.certificate_no;
    document.getElementById('ecert_date').value = (c.issue_date || '').substring(0, 10);
    document.getElementById('ecert_score').value = c.score ? Math.round(c.score) : '';
    document.getElementById('ecert_dur').value = (c.meta && c.meta.duration_hours) || 24;
    document.getElementById('ecert_status').value = c.status;
    openModal('editCertModal');
  }
</script>
@endpush

