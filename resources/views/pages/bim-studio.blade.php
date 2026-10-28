@extends('layouts.app')

@section('title', 'BIM Studio - BIM Nusantara')
@section('page_title', 'BIM Studio')
@section('page_subtitle', 'Bangun model BIM langsung di browser.')

@section('content')
  <!-- Studio Action Toolbar -->
  <div class="bg-[#FAF8F4] border border-stone-200 rounded-xl p-2.5 mb-3 flex flex-wrap items-center justify-between gap-2 text-xs">
    <div class="flex flex-wrap items-center gap-2">
      <!-- Project Selector -->
      <form method="GET" action="{{ route('bim-studio') }}" class="flex items-center">
        <select name="project_id" onchange="this.form.submit()" class="bg-[#EFECE4] border border-stone-300 rounded-lg px-2.5 py-1.5 text-xs font-bold text-stone-800">
          @foreach($projects as $p)
            <option value="{{ $p->id }}" {{ $activeProject && $activeProject->id === $p->id ? 'selected' : '' }}>
              📦 {{ $p->title }} ({{ $p->code }})
            </option>
          @endforeach
        </select>
      </form>

      @if($activeProject)
        <!-- Save Snapshot -->
        <button onclick="openModal('saveVersionModal')" class="bg-[#735A2F] hover:bg-[#5e4824] text-white font-semibold px-3.5 py-1.5 rounded-lg transition">
          💾 Simpan ▾
        </button>
        <button onclick="openModal('addElementModal')" class="bg-white hover:bg-stone-50 border border-stone-200 px-3 py-1.5 rounded-lg text-stone-700 font-semibold">
          + Elemen BIM
        </button>
        <!-- Validate Model -->
        <form action="{{ route('bim-studio.projects.validate', $activeProject->id) }}" method="POST" class="inline">
          @csrf
          <button type="submit" class="bg-white hover:bg-emerald-50 border border-stone-200 px-3 py-1.5 rounded-lg text-emerald-800 font-semibold transition">
            ✓ Validasi Model
          </button>
        </form>
        @if($validationResults->count() > 0)
          <button onclick="openModal('validationModal')" class="bg-emerald-100 text-emerald-800 px-2.5 py-1.5 rounded-lg font-bold text-[11px]">
            Laporan Validasi ({{ $validationResults->where('status', 'passed')->count() }}/{{ $validationResults->count() }})
          </button>
        @endif
        <!-- Submit to Project -->
        <form action="{{ route('bim-studio.projects.submit', $activeProject->id) }}" method="POST" class="inline">
          @csrf
          <button type="submit" class="bg-[#6A532C] hover:bg-[#554121] text-white font-semibold px-3.5 py-1.5 rounded-lg transition">
            ↑ Submit ke Project
          </button>
        </form>
      @endif
    </div>

    <!-- View Mode Switcher -->
    <div class="flex items-center gap-1 bg-stone-200/70 p-1 rounded-lg">
      <button type="button" onclick="setViewportMode('3D')" id="btnMode3D" class="bg-[#735A2F] text-white font-semibold px-3 py-1 rounded transition">3D</button>
      <button type="button" onclick="setViewportMode('2D')" id="btnMode2D" class="px-3 py-1 text-stone-700 rounded transition">2D Plan</button>
      <button type="button" onclick="setViewportMode('Section')" id="btnModeSection" class="px-3 py-1 text-stone-700 rounded transition">Section</button>
      <button type="button" onclick="setViewportMode('Elevation')" id="btnModeElevation" class="px-3 py-1 text-stone-700 rounded transition">Elevation</button>
    </div>

    <!-- Level Selector -->
    <div class="flex items-center gap-2">
      <form method="GET" action="{{ route('bim-studio') }}" class="flex items-center gap-1.5">
        <input type="hidden" name="project_id" value="{{ $activeProject?->id }}" />
        <select name="level_id" onchange="this.form.submit()" class="bg-white border border-stone-200 rounded-lg px-3 py-1.5 text-xs font-medium">
          @foreach($levels as $lvl)
            <option value="{{ $lvl->id }}" {{ $activeLevel && $activeLevel->id === $lvl->id ? 'selected' : '' }}>
              {{ $lvl->name }} ({{ $lvl->elevation_mm >= 0 ? '+' : '' }}{{ number_format($lvl->elevation_mm / 1000, 2) }}m)
            </option>
          @endforeach
        </select>
      </form>
      <button onclick="openModal('levelsGridsModal')" class="p-1.5 bg-white border border-stone-200 rounded-lg hover:bg-stone-50" title="Kelola Level & Grid">
        <i data-lucide="settings" class="w-4 h-4"></i>
      </button>
    </div>
  </div>

  <!-- 3-Pane CAD Workspace -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 min-h-[600px]">
    <!-- Left Model Tree -->
    <div class="lg:col-span-3 bg-[#FAF8F4] border border-stone-200 rounded-xl p-3 flex flex-col text-xs max-h-[640px]">
      <div class="grid grid-cols-3 bg-stone-200/60 p-1 rounded-lg mb-3 text-center font-medium">
        <button type="button" onclick="switchLeftTab('model')" id="leftTabModel" class="bg-white rounded py-1 shadow-sm">Model</button>
        <button type="button" onclick="switchLeftTab('levels')" id="leftTabLevels" class="py-1 text-stone-600">Levels</button>
        <button type="button" onclick="switchLeftTab('library')" id="leftTabLibrary" class="py-1 text-stone-600">Library</button>
      </div>

      <!-- TAB 1: MODEL TREE -->
      <div id="leftPaneModel" class="flex-1 flex flex-col overflow-hidden">
        <input type="text" id="modelTreeSearch" oninput="filterModelTree(this.value)" placeholder="Cari elemen model..." class="bg-white border border-stone-200 rounded-lg px-3 py-1.5 text-xs mb-3 outline-none focus:border-[#8A6729]" />
        <div class="space-y-1.5 overflow-y-auto text-[11px] text-stone-700 flex-1 pr-1">
          <div class="font-bold text-stone-900">▾ 📦 BIM Model - {{ $activeProject?->title }}</div>
          <div class="pl-3">▸ 📁 Site ({{ $grids->count() }} Grid)</div>
          <div class="pl-3 font-semibold">▾ 📁 Building ({{ $elements->count() }} Elemen)</div>

          @foreach($levels as $lvl)
            @php
              $lvlElements = $elements->where('level_id', $lvl->id);
              $isLvlSelected = ($activeLevel && $activeLevel->id === $lvl->id);
            @endphp
            <div class="pl-5">
              <a href="{{ route('bim-studio', ['project_id' => $activeProject?->id, 'level_id' => $lvl->id]) }}" class="block py-1 px-2 rounded {{ $isLvlSelected ? 'bg-[#F3E7CE] font-bold text-[#6E5223]' : 'hover:bg-stone-200/50 font-semibold' }}">
                ▾ 📁 {{ $lvl->name }} ({{ $lvlElements->count() }})
              </a>
              @foreach($lvlElements->groupBy('type_name') as $typeName => $typeGroup)
                <div class="pl-4 mt-1 text-stone-600 font-medium">▾ {{ $typeName }}s ({{ $typeGroup->count() }})</div>
                @foreach($typeGroup as $item)
                  @php
                    $isSelected = ($selectedElement && $selectedElement->id === $item->id);
                  @endphp
                  <a href="{{ route('bim-studio', ['project_id' => $activeProject?->id, 'level_id' => $lvl->id, 'element_id' => $item->id]) }}"
                     data-tree-item="{{ strtolower($item->name . ' ' . $item->code . ' ' . $item->type_name) }}"
                     class="tree-element-row flex items-center justify-between pl-6 pr-2 py-1 rounded transition {{ $isSelected ? 'bg-[#F5EBD6] font-bold text-[#6E5223] border-l-2 border-[#8A6729]' : 'hover:bg-stone-100' }}">
                    <span class="truncate">{{ $item->name }}</span>
                    <span class="text-[10px] text-stone-400 ml-1 shrink-0">{{ $item->code }}</span>
                  </a>
                @endforeach
              @endforeach
            </div>
          @endforeach

          <div class="pl-3 pt-2 font-semibold">▾ 📁 Views</div>
          <div class="pl-6 py-0.5 cursor-pointer hover:text-[#8A6729]" onclick="setViewportMode('3D')">3D View - Perspektif 🔒</div>
          <div class="pl-6 py-0.5 cursor-pointer hover:text-[#8A6729]" onclick="setViewportMode('2D')">Floor Plan - {{ $activeLevel?->name ?? 'Level 1' }}</div>
          <div class="pl-6 py-0.5 cursor-pointer hover:text-[#8A6729]" onclick="setViewportMode('Section')">Section A-A</div>
          <div class="pl-6 py-0.5 cursor-pointer hover:text-[#8A6729]" onclick="setViewportMode('Elevation')">South Elevation</div>
        </div>
      </div>

      <!-- TAB 2: LEVELS & GRIDS -->
      <div id="leftPaneLevels" class="hidden flex-1 flex-col overflow-y-auto space-y-3">
        <div class="flex items-center justify-between">
          <span class="font-bold text-stone-800">Daftar Level ({{ $levels->count() }})</span>
          <button onclick="openModal('levelsGridsModal')" class="text-[11px] bg-[#6B532B] text-white px-2 py-0.5 rounded">+ Level/Grid</button>
        </div>
        @foreach($levels as $lvl)
          <div class="bg-white border border-stone-200 rounded-lg p-2 flex items-center justify-between">
            <div>
              <div class="font-bold text-stone-800">{{ $lvl->name }}</div>
              <div class="text-[10px] text-stone-500">Elevasi: {{ $lvl->elevation_mm }} mm</div>
            </div>
            @if($levels->count() > 1)
              <form action="{{ route('bim-studio.levels.destroy', $lvl->id) }}" method="POST" onsubmit="return confirm('Hapus level ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-stone-400 hover:text-red-600"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
              </form>
            @endif
          </div>
        @endforeach

        <div class="pt-2 border-t border-stone-200 font-bold text-stone-800">Daftar Grid ({{ $grids->count() }})</div>
        <div class="grid grid-cols-2 gap-1.5">
          @foreach($grids as $g)
            <div class="bg-white border border-stone-200 rounded-lg p-2 flex items-center justify-between text-[11px]">
              <span><b>{{ $g->axis }}-{{ $g->label }}</b> ({{ $g->position_mm }}mm)</span>
              <form action="{{ route('bim-studio.grids.destroy', $g->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-stone-400 hover:text-red-600">×</button>
              </form>
            </div>
          @endforeach
        </div>
      </div>

      <!-- TAB 3: FAMILY LIBRARY -->
      <div id="leftPaneLibrary" class="hidden flex-1 flex-col overflow-y-auto space-y-2">
        <div class="text-[11px] text-stone-500 mb-1">Klik komponen library untuk menambahkannya ke model aktif:</div>
        @foreach($elementTypes as $et)
          @if(!in_array($et->code, ['LEVEL', 'GRID']))
            <button type="button" onclick="openQuickAddElement('{{ $et->code }}', '{{ $et->name }} - Standard')" class="w-full text-left bg-white hover:border-[#C89D4C] border border-stone-200 rounded-lg p-2.5 flex items-center justify-between transition">
              <div>
                <div class="font-bold text-stone-800">{{ $et->name }} ({{ $et->code }})</div>
                <div class="text-[10px] text-stone-500">{{ $et->ifc_class }} • {{ ucfirst($et->category) }}</div>
              </div>
              <span class="text-xs font-bold text-[#8A6729]">+ Tambah</span>
            </button>
          @endif
        @endforeach
      </div>
    </div>

    <!-- Center 3D / 2D Interactive Viewport -->
    <div class="lg:col-span-6 relative rounded-xl overflow-hidden border border-stone-300 bg-stone-900 min-h-[460px] flex flex-col">
      <!-- Realistic Architectural Photo Backdrop -->
      <img id="viewportBackdrop" src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1400&q=80" class="absolute inset-0 w-full h-full object-cover opacity-55 transition-opacity duration-300" />

      <!-- Interactive BIM Canvas Overlay -->
      <canvas id="bimCanvas" class="relative z-10 w-full h-full flex-1 cursor-crosshair"></canvas>

      <!-- Left Floating Tool Palette -->
      <div class="absolute left-3 top-3 z-20 bg-[#FAF8F4]/95 border border-stone-300 rounded-xl p-1.5 flex flex-col gap-1 text-[10px] text-center shadow-md select-none">
        <button type="button" onclick="selectPaletteTool('SELECT')" id="tool_SELECT" class="bg-[#F2E3C2] text-[#6B4F1D] font-bold px-2 py-1.5 rounded-lg">↖<br>Select</button>
        <button type="button" onclick="openModal('levelsGridsModal')" class="px-2 py-1 hover:bg-stone-200/70 rounded">＃<br>Grid</button>
        <button type="button" onclick="openQuickAddElement('WALL', 'Basic Wall - 200mm')" class="px-2 py-1 hover:bg-stone-200/70 rounded">▭<br>Wall</button>
        <button type="button" onclick="openQuickAddElement('COLUMN', 'Column K1 - 300x300')" class="px-2 py-1 hover:bg-stone-200/70 rounded">▯<br>Column</button>
        <button type="button" onclick="openQuickAddElement('BEAM', 'Beam B1 - 250x400')" class="px-2 py-1 hover:bg-stone-200/70 rounded">═<br>Beam</button>
        <button type="button" onclick="openQuickAddElement('SLAB', 'Floor Slab - 120mm')" class="px-2 py-1 hover:bg-stone-200/70 rounded">▱<br>Slab</button>
        <button type="button" onclick="openQuickAddElement('DOOR', 'Single Door - 900x2100')" class="px-2 py-1 hover:bg-stone-200/70 rounded">🚪<br>Door</button>
        <button type="button" onclick="openQuickAddElement('WINDOW', 'Window - 1200x1200')" class="px-2 py-1 hover:bg-stone-200/70 rounded">🪟<br>Window</button>
      </div>

      <!-- Top Right Active Selection Gizmo Pill -->
      @if($selectedElement)
        <div class="absolute right-3 top-3 z-20 bg-blue-600/95 text-white text-[11px] px-3 py-1.5 rounded-lg font-bold shadow-md flex items-center gap-2">
          <span>XYZ Gizmo • {{ $selectedElement->name }} ({{ $selectedElement->code }})</span>
          <span class="text-[10px] bg-white/20 px-1.5 py-0.5 rounded">{{ $selectedElement->length_m }}m × {{ $selectedElement->height_m }}m</span>
        </div>
      @endif

      <!-- Bottom Viewport Controls -->
      <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-20 bg-[#FAF8F4]/95 border border-stone-300 rounded-xl px-4 py-1.5 flex items-center gap-4 text-xs shadow-md select-none">
        <button type="button" onclick="zoomCanvas(1.15)" title="Zoom In" class="hover:text-[#8A6729] font-bold">＋</button>
        <button type="button" onclick="zoomCanvas(0.85)" title="Zoom Out" class="hover:text-[#8A6729] font-bold">－</button>
        <button type="button" onclick="resetCanvasView()" title="Reset View" class="hover:text-[#8A6729]">↻ Reset</button>
        <button type="button" onclick="toggleRealisticBackdrop()" id="btnRealistic" class="font-semibold text-[#6B4F1D]">📦 Realistic ▾</button>
        <button type="button" onclick="toggleGridLines()" title="Toggle Grid" class="hover:text-[#8A6729]">▦ Grid</button>
      </div>
    </div>

    <!-- Right Properties Inspector -->
    <div class="lg:col-span-3 bg-[#FAF8F4] border border-stone-200 rounded-xl p-3.5 text-xs space-y-3 max-h-[640px] overflow-y-auto">
      <div class="grid grid-cols-3 bg-stone-200/60 p-1 rounded-lg text-center font-medium">
        <button type="button" onclick="switchRightTab('props')" id="rightTabProps" class="bg-white rounded py-1 shadow-sm">Properti</button>
        <button type="button" onclick="switchRightTab('qty')" id="rightTabQty" class="py-1 text-stone-600">Kuantitas</button>
        <button type="button" onclick="switchRightTab('versions')" id="rightTabVersions" class="py-1 text-stone-600">Riwayat</button>
      </div>

      @if($selectedElement)
        <div id="rightPaneProps" class="space-y-3">
          <div class="p-2.5 bg-white rounded-xl border border-stone-200 flex items-center justify-between">
            <div>
              <div class="font-bold text-stone-900">{{ $selectedElement->name }}</div>
              <div class="text-[11px] text-stone-500">{{ $selectedElement->type_name }} • Kode: <b>{{ $selectedElement->code }}</b> • ID: <b>#{{ $selectedElement->id }}</b></div>
            </div>
            <form action="{{ route('bim-studio.elements.destroy', $selectedElement->id) }}" method="POST" onsubmit="return confirm('Hapus elemen {{ $selectedElement->name }} dari model?')">
              @csrf
              @method('DELETE')
              <button type="submit" class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100" title="Hapus Elemen">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
              </button>
            </form>
          </div>

          <form action="{{ route('bim-studio.elements.update', $selectedElement->id) }}" method="POST" class="space-y-2.5 text-[11px]">
            @csrf
            @method('PUT')
            <div class="font-bold text-stone-800 border-b pb-1">Informasi Dasar</div>
            <div class="flex justify-between items-center gap-2">
              <span class="text-stone-600">Nama</span>
              <input type="text" name="name" value="{{ $selectedElement->name }}" class="border border-stone-300 rounded px-2 py-1 w-40 bg-white text-right font-medium" />
            </div>
            <div class="flex justify-between items-center gap-2">
              <span class="text-stone-600">Kode</span>
              <input type="text" name="code" value="{{ $selectedElement->code }}" class="border border-stone-300 rounded px-2 py-1 w-40 bg-white text-right" />
            </div>
            <div class="flex justify-between items-center gap-2">
              <span class="text-stone-600">Level</span>
              <select name="level_id" class="border border-stone-300 rounded px-2 py-1 w-40 bg-white">
                @foreach($levels as $lvl)
                  <option value="{{ $lvl->id }}" {{ $selectedElement->level_id === $lvl->id ? 'selected' : '' }}>{{ $lvl->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="flex justify-between items-center gap-2">
              <span class="text-stone-600">Top Constraint</span>
              <input type="text" name="top_constraint" value="{{ $selectedElement->props['topConstraint'] ?? 'Level 2 - Lantai 2' }}" class="border border-stone-300 rounded px-2 py-1 w-40 bg-white text-right" />
            </div>
            <div class="flex justify-between items-center gap-2">
              <span class="text-stone-600">IFC Class</span>
              <input type="text" name="ifc_class" value="{{ $selectedElement->ifc_class }}" class="border border-stone-300 rounded px-2 py-1 w-40 bg-white text-right" />
            </div>

            <div class="font-bold text-stone-800 border-b pb-1 pt-2">Dimensi Geometri (mm)</div>
            <div class="flex justify-between items-center gap-2">
              <span class="text-stone-600">Panjang (mm)</span>
              <input type="number" name="length_mm" value="{{ round($selectedElement->length_mm) }}" class="border border-stone-300 rounded px-2 py-1 w-32 bg-white text-right font-bold" />
            </div>
            <div class="flex justify-between items-center gap-2">
              <span class="text-stone-600">Tinggi (mm)</span>
              <input type="number" name="height_mm" value="{{ round($selectedElement->height_mm) }}" class="border border-stone-300 rounded px-2 py-1 w-32 bg-white text-right font-bold" />
            </div>
            <div class="flex justify-between items-center gap-2">
              <span class="text-stone-600">Ketebalan (mm)</span>
              <input type="number" name="thickness_mm" value="{{ round($selectedElement->thickness_mm) }}" class="border border-stone-300 rounded px-2 py-1 w-32 bg-white text-right font-bold" />
            </div>

            <div class="font-bold text-stone-800 border-b pb-1 pt-2">Material & Finishing</div>
            <div class="flex justify-between items-center gap-2">
              <span class="text-stone-600">Material</span>
              <select name="material_id" class="border border-stone-300 rounded px-2 py-1 w-40 bg-white">
                @foreach($materials as $mat)
                  <option value="{{ $mat->id }}" {{ $selectedElement->material_id === $mat->id ? 'selected' : '' }}>{{ $mat->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="flex justify-between items-center gap-2">
              <span class="text-stone-600">Finishing</span>
              <input type="text" name="finishing" value="{{ $selectedElement->props['finishing'] ?? 'Cat Dinding - Putih' }}" class="border border-stone-300 rounded px-2 py-1 w-40 bg-white text-right" />
            </div>

            <div class="pt-2">
              <button type="submit" class="w-full bg-[#735A2F] hover:bg-[#5d4723] text-white font-bold py-2 rounded-lg transition">
                Simpan Perubahan Properti
              </button>
            </div>
          </form>
        </div>

        <!-- TAB 2: KUANTITAS (QTO) -->
        <div id="rightPaneQty" class="hidden space-y-2.5 text-[11px]">
          <div class="p-3 bg-white rounded-xl border border-stone-200 space-y-2">
            <div class="font-bold text-stone-900 border-b pb-1">Quantity Takeoff (Elemen Aktif)</div>
            <div class="flex justify-between"><span>Panjang</span><b>{{ number_format($selectedElement->length_m, 3) }} m</b></div>
            <div class="flex justify-between"><span>Tinggi</span><b>{{ number_format($selectedElement->height_m, 3) }} m</b></div>
            <div class="flex justify-between"><span>Ketebalan</span><b>{{ number_format($selectedElement->thickness_m, 3) }} m</b></div>
            <div class="flex justify-between text-emerald-800"><span>Luas Penampang</span><b>{{ number_format($selectedElement->area_m2, 3) }} m²</b></div>
            <div class="flex justify-between text-[#8A6729]"><span>Volume Bersih</span><b>{{ number_format($selectedElement->volume_m3, 3) }} m³</b></div>
          </div>

          <div class="p-3 bg-white rounded-xl border border-stone-200 space-y-1.5">
            <div class="font-bold text-stone-900 border-b pb-1">Rekapitulasi Seluruh Model</div>
            @foreach($elementCountsByType as $typeCode => $cnt)
              <div class="flex justify-between">
                <span>{{ $typeCode }}</span>
                <b>{{ $cnt }} unit</b>
              </div>
            @endforeach
          </div>
        </div>
      @else
        <div class="text-center py-8 text-stone-400">
          Belum ada elemen dipilih. Klik tombol <b>+ Elemen BIM</b> untuk menambahkan.
        </div>
      @endif

      <!-- TAB 3: VERSIONS HISTORY -->
      <div id="rightPaneVersions" class="hidden space-y-2 text-[11px]">
        <div class="font-bold text-stone-800">Riwayat Snapshot Versi ({{ $versions->count() }})</div>
        @foreach($versions as $ver)
          <div class="p-2.5 bg-white border border-stone-200 rounded-xl">
            <div class="flex justify-between font-bold text-stone-900">
              <span>#v{{ $ver->version_no }} - {{ $ver->label }}</span>
              <span class="text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded">{{ $ver->version_type }}</span>
            </div>
            <div class="text-[10px] text-stone-500 mt-0.5">{{ $ver->notes }}</div>
            <div class="text-[10px] text-stone-400 mt-1">Oleh {{ $ver->creator_name }} • {{ \Carbon\Carbon::parse($ver->created_at)->diffForHumans() }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <!-- Bottom Status Bar -->
  <footer class="mt-3 pt-2 border-t border-stone-300/80 flex flex-wrap justify-between gap-2 text-[11px] text-stone-600">
    <div>
      Project: <b>{{ $activeProject?->title }}</b> | Kode: <b>{{ $activeProject?->code }}</b> |
      <span class="text-emerald-700">● {{ $elements->count() }} Elemen BIM Aktif di Database MySQL</span>
    </div>
    <div id="statusBarCoords">
      X: 8.000 &nbsp; Y: 10.000 &nbsp; Z: {{ number_format(($activeLevel?->elevation_mm ?? 0) / 1000, 3) }} m | Grid: 1.000 m | Snap: <b class="text-emerald-700">On</b> | Units: <b>{{ strtoupper($activeProject?->unit ?? 'MM') }}</b>
    </div>
  </footer>

  <!-- MODAL: TAMBAH ELEMEN BIM -->
  @if($activeProject)
    <div id="addElementModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
      <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-serif-title font-bold text-lg text-stone-900">Tambah Elemen BIM Baru</h3>
          <button onclick="closeModal('addElementModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <form action="{{ route('bim-studio.elements.store') }}" method="POST" class="space-y-3 text-xs">
          @csrf
          <input type="hidden" name="project_id" value="{{ $activeProject->id }}" />
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Kategori Elemen</label>
              <select id="quick_el_type" name="element_type_code" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
                <option value="WALL">Wall (Dinding)</option>
                <option value="COLUMN">Column (Kolom)</option>
                <option value="BEAM">Beam (Balok)</option>
                <option value="SLAB">Slab (Pelat Lantai)</option>
                <option value="DOOR">Door (Pintu)</option>
                <option value="WINDOW">Window (Jendela)</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Level Lantai</label>
              <select name="level_id" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
                @foreach($levels as $lvl)
                  <option value="{{ $lvl->id }}" {{ $activeLevel && $activeLevel->id === $lvl->id ? 'selected' : '' }}>{{ $lvl->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Nama Elemen</label>
            <input type="text" id="quick_el_name" name="name" required value="Basic Wall - 200mm" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Material</label>
            <select name="material_id" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2">
              @foreach($materials as $mat)
                <option value="{{ $mat->id }}">{{ $mat->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="grid grid-cols-3 gap-2">
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Panjang (mm)</label>
              <input type="number" name="length_mm" value="4000" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
            </div>
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Tinggi (mm)</label>
              <input type="number" name="height_mm" value="3000" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
            </div>
            <div>
              <label class="block font-semibold text-stone-700 mb-1">Tebal (mm)</label>
              <input type="number" name="thickness_mm" value="200" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
            </div>
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" onclick="closeModal('addElementModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white font-semibold">Tambahkan ke Model</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: SIMPAN VERSI -->
    <div id="saveVersionModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
      <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-serif-title font-bold text-lg text-stone-900">Simpan Snapshot Versi Model</h3>
          <button onclick="closeModal('saveVersionModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <form action="{{ route('bim-studio.projects.save-version', $activeProject->id) }}" method="POST" class="space-y-3 text-xs">
          @csrf
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Label Versi</label>
            <input type="text" name="label" required value="v{{ $versions->count() + 1 }}.0 - Update Model Arsitektur" class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2" />
          </div>
          <div>
            <label class="block font-semibold text-stone-700 mb-1">Catatan Perubahan</label>
            <textarea name="notes" rows="2" placeholder="Penambahan dinding partisi dan bukaan jendela..." class="w-full bg-white border border-stone-300 rounded-xl px-3 py-2"></textarea>
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" onclick="closeModal('saveVersionModal')" class="px-4 py-2 rounded-xl border border-stone-300">Batal</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-[#735A2F] text-white font-semibold">💾 Simpan Snapshot</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: KELOLA LEVEL & GRID -->
    <div id="levelsGridsModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
      <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-lg p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-serif-title font-bold text-lg text-stone-900">Kelola Level & Grid Bangunan</h3>
          <button onclick="closeModal('levelsGridsModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
          <form action="{{ route('bim-studio.levels.store') }}" method="POST" class="bg-white border border-stone-200 rounded-xl p-3.5 space-y-2.5">
            @csrf
            <input type="hidden" name="project_id" value="{{ $activeProject->id }}" />
            <div class="font-bold text-stone-900">Tambah Level Baru</div>
            <div>
              <label class="block text-[11px] text-stone-600 mb-1">Nama Level</label>
              <input type="text" name="name" required placeholder="Level 4 - Attic" class="w-full border border-stone-300 rounded-lg px-2.5 py-1.5" />
            </div>
            <div>
              <label class="block text-[11px] text-stone-600 mb-1">Elevasi (mm)</label>
              <input type="number" name="elevation_mm" required value="10500" class="w-full border border-stone-300 rounded-lg px-2.5 py-1.5" />
            </div>
            <button type="submit" class="w-full bg-[#6B532B] text-white font-semibold py-1.5 rounded-lg">+ Simpan Level</button>
          </form>

          <form action="{{ route('bim-studio.grids.store') }}" method="POST" class="bg-white border border-stone-200 rounded-xl p-3.5 space-y-2.5">
            @csrf
            <input type="hidden" name="project_id" value="{{ $activeProject->id }}" />
            <div class="font-bold text-stone-900">Tambah Grid Baru</div>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block text-[11px] text-stone-600 mb-1">Sumbu</label>
                <select name="axis" class="w-full border border-stone-300 rounded-lg px-2 py-1.5">
                  <option value="X">Axis X</option>
                  <option value="Y">Axis Y</option>
                </select>
              </div>
              <div>
                <label class="block text-[11px] text-stone-600 mb-1">Label</label>
                <input type="text" name="label" required placeholder="D / 4" class="w-full border border-stone-300 rounded-lg px-2 py-1.5" />
              </div>
            </div>
            <div>
              <label class="block text-[11px] text-stone-600 mb-1">Posisi (mm)</label>
              <input type="number" name="position_mm" required value="12000" class="w-full border border-stone-300 rounded-lg px-2.5 py-1.5" />
            </div>
            <button type="submit" class="w-full bg-[#6B532B] text-white font-semibold py-1.5 rounded-lg">+ Simpan Grid</button>
          </form>
        </div>
      </div>
    </div>

    <!-- MODAL: LAPORAN VALIDASI BIM -->
    <div id="validationModal" class="fixed inset-0 bg-black/50 z-50 {{ session('open_validation_modal') ? 'flex' : 'hidden' }} items-center justify-center p-4">
      <div class="bg-[#FAF8F4] border border-stone-300 rounded-2xl shadow-xl w-full max-w-lg p-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="font-serif-title font-bold text-lg text-stone-900">Hasil Validasi Rule BIM</h3>
            <p class="text-xs text-stone-500">Pemeriksaan otomatis terhadap standar Challenge & pemodelan.</p>
          </div>
          <button onclick="closeModal('validationModal')" class="text-stone-400 hover:text-stone-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <div class="space-y-2.5 max-h-80 overflow-y-auto text-xs">
          @foreach($validationResults as $vr)
            <div class="p-3 rounded-xl border {{ $vr->status === 'passed' ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200' }} flex items-center justify-between gap-3">
              <div>
                <div class="font-bold text-stone-900">{{ $vr->rule_name ?? 'Rule Validasi' }} ({{ $vr->rule_code }})</div>
                <div class="text-[11px] text-stone-600 mt-0.5">{{ $vr->message }}</div>
              </div>
              <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $vr->status === 'passed' ? 'bg-emerald-700 text-white' : 'bg-amber-600 text-white' }}">
                {{ $vr->status }}
              </span>
            </div>
          @endforeach
        </div>
        <div class="flex justify-end mt-4">
          <button onclick="closeModal('validationModal')" class="px-4 py-2 rounded-xl bg-[#6B532B] text-white text-xs font-semibold">Tutup Laporan</button>
        </div>
      </div>
    </div>
  @endif
@endsection

@push('scripts')
<script>
  const studioElements = @json($elements->values());
  const studioGrids = @json($grids->values());
  const selectedElementId = {{ $selectedElement?->id ?? 'null' }};
  const currentProjectId = {{ $activeProject?->id ?? 'null' }};
  const currentLevelId = {{ $activeLevel?->id ?? 'null' }};

  let viewportMode = '3D';
  let canvasZoom = 1.0;
  let showGridLines = true;
  let showRealistic = true;

  function switchLeftTab(tab) {
    ['model', 'levels', 'library'].forEach(t => {
      const pane = document.getElementById('leftPane' + t.charAt(0).toUpperCase() + t.slice(1));
      const btn = document.getElementById('leftTab' + t.charAt(0).toUpperCase() + t.slice(1));
      if (t === tab) {
        pane.classList.remove('hidden');
        pane.classList.add('flex');
        btn.className = 'bg-white rounded py-1 shadow-sm';
      } else {
        pane.classList.add('hidden');
        pane.classList.remove('flex');
        btn.className = 'py-1 text-stone-600';
      }
    });
  }

  function switchRightTab(tab) {
    ['props', 'qty', 'versions'].forEach(t => {
      const pane = document.getElementById('rightPane' + t.charAt(0).toUpperCase() + t.slice(1));
      const btn = document.getElementById('rightTab' + t.charAt(0).toUpperCase() + t.slice(1));
      if (!pane || !btn) return;
      if (t === tab) {
        pane.classList.remove('hidden');
        btn.className = 'bg-white rounded py-1 shadow-sm';
      } else {
        pane.classList.add('hidden');
        btn.className = 'py-1 text-stone-600';
      }
    });
  }

  function filterModelTree(q) {
    const query = (q || '').toLowerCase();
    document.querySelectorAll('.tree-element-row').forEach(row => {
      const text = row.getAttribute('data-tree-item') || '';
      row.style.display = text.includes(query) ? '' : 'none';
    });
  }

  function openQuickAddElement(typeCode, defaultName) {
    document.getElementById('quick_el_type').value = typeCode;
    document.getElementById('quick_el_name').value = defaultName;
    openModal('addElementModal');
  }

  function setViewportMode(mode) {
    viewportMode = mode;
    ['3D', '2D', 'Section', 'Elevation'].forEach(m => {
      const btn = document.getElementById('btnMode' + m);
      if (btn) {
        btn.className = (m === mode)
          ? 'bg-[#735A2F] text-white font-semibold px-3 py-1 rounded transition'
          : 'px-3 py-1 text-stone-700 rounded transition';
      }
    });
    const backdrop = document.getElementById('viewportBackdrop');
    if (backdrop) {
      backdrop.style.opacity = (mode === '3D' && showRealistic) ? '0.55' : '0.12';
    }
    renderBimCanvas();
  }

  function zoomCanvas(factor) {
    canvasZoom = Math.max(0.5, Math.min(2.5, canvasZoom * factor));
    renderBimCanvas();
  }

  function resetCanvasView() {
    canvasZoom = 1.0;
    renderBimCanvas();
  }

  function toggleRealisticBackdrop() {
    showRealistic = !showRealistic;
    const backdrop = document.getElementById('viewportBackdrop');
    if (backdrop) {
      backdrop.style.opacity = showRealistic ? '0.55' : '0.08';
    }
  }

  function toggleGridLines() {
    showGridLines = !showGridLines;
    renderBimCanvas();
  }

  function renderBimCanvas() {
    const canvas = document.getElementById('bimCanvas');
    if (!canvas) return;
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width || 700;
    canvas.height = rect.height || 500;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    const cx = canvas.width / 2;
    const cy = canvas.height / 2;
    const scale = 0.028 * canvasZoom;

    // Draw Grids
    if (showGridLines) {
      ctx.save();
      ctx.strokeStyle = 'rgba(244, 211, 139, 0.45)';
      ctx.lineWidth = 1;
      ctx.setLineDash([5, 5]);
      studioGrids.forEach(g => {
        if (g.axis === 'X') {
          const x = cx - 140 + (g.position_mm * scale);
          ctx.beginPath();
          ctx.moveTo(x, 40);
          ctx.lineTo(x, canvas.height - 40);
          ctx.stroke();
          ctx.fillStyle = '#F4D38B';
          ctx.font = 'bold 10px sans-serif';
          ctx.fillText(g.label, x - 4, 30);
        } else {
          const y = cy - 130 + (g.position_mm * scale);
          ctx.beginPath();
          ctx.moveTo(60, y);
          ctx.lineTo(canvas.width - 60, y);
          ctx.stroke();
          ctx.fillStyle = '#F4D38B';
          ctx.font = 'bold 10px sans-serif';
          ctx.fillText(g.label, 42, y + 4);
        }
      });
      ctx.restore();
    }

    // Draw Elements
    studioElements.forEach((el, idx) => {
      const isSelected = (el.id === selectedElementId);
      const geo = el.geo || {};
      const sx = (geo.start && geo.start.x !== undefined) ? geo.start.x : ((idx % 3) * 2500);
      const sy = (geo.start && geo.start.y !== undefined) ? geo.start.y : (Math.floor(idx / 3) * 3000);
      const ex = (geo.end && geo.end.x !== undefined) ? geo.end.x : (sx + (el.length_mm || 4000));
      const ey = (geo.end && geo.end.y !== undefined) ? geo.end.y : sy;

      const x1 = cx - 140 + sx * scale;
      const y1 = cy - 130 + sy * scale;
      const x2 = cx - 140 + ex * scale;
      const y2 = cy - 130 + ey * scale;

      ctx.save();
      if (el.type_code === 'SLAB') {
        ctx.fillStyle = isSelected ? 'rgba(59, 130, 246, 0.35)' : 'rgba(184, 184, 184, 0.28)';
        ctx.strokeStyle = isSelected ? '#3B82F6' : '#D8AE5E';
        ctx.lineWidth = isSelected ? 2.5 : 1.2;
        ctx.fillRect(cx - 140, cy - 130, 8000 * scale, 10000 * scale);
        ctx.strokeRect(cx - 140, cy - 130, 8000 * scale, 10000 * scale);
      } else if (el.type_code === 'COLUMN') {
        const size = Math.max(12, (el.thickness_mm || 300) * scale * 2.5);
        ctx.fillStyle = isSelected ? '#2563EB' : '#D8AE5E';
        ctx.fillRect(x1 - size / 2, y1 - size / 2, size, size);
      } else {
        // Wall / Beam / Door / Window
        ctx.strokeStyle = isSelected ? '#3B82F6' : (el.material_color || '#E5D3AC');
        ctx.lineWidth = isSelected ? 8 : (el.type_code === 'WALL' ? 6 : 4);
        ctx.lineCap = 'round';
        ctx.beginPath();
        ctx.moveTo(x1, y1);
        ctx.lineTo(x2, y2);
        ctx.stroke();

        // In 3D mode draw extruded isometric roof line
        if (viewportMode === '3D') {
          const hOffset = -Math.min(45, (el.height_mm || 3000) * 0.012 * canvasZoom);
          ctx.fillStyle = isSelected ? 'rgba(59, 130, 246, 0.32)' : 'rgba(216, 174, 94, 0.22)';
          ctx.beginPath();
          ctx.moveTo(x1, y1);
          ctx.lineTo(x2, y2);
          ctx.lineTo(x2 - hOffset * 0.6, y2 + hOffset);
          ctx.lineTo(x1 - hOffset * 0.6, y1 + hOffset);
          ctx.closePath();
          ctx.fill();
          ctx.strokeStyle = isSelected ? '#60A5FA' : 'rgba(255,255,255,0.45)';
          ctx.lineWidth = 1.2;
          ctx.stroke();
        }
      }

      // Label
      const midX = (x1 + x2) / 2;
      const midY = (y1 + y2) / 2;
      ctx.fillStyle = isSelected ? '#2563EB' : 'rgba(28, 25, 23, 0.82)';
      ctx.fillRect(midX - 28, midY - 10, 56, 18);
      ctx.fillStyle = '#FFFFFF';
      ctx.font = 'bold 9px sans-serif';
      ctx.textAlign = 'center';
      ctx.fillText(el.code || el.type_code, midX, midY + 2);
      ctx.restore();
    });
  }

  window.addEventListener('resize', renderBimCanvas);
  document.addEventListener('DOMContentLoaded', () => {
    setTimeout(renderBimCanvas, 60);
  });
</script>
@endpush

