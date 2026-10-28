<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BimCrudController extends Controller
{
    protected function currentUserId(Request $request): int
    {
        $sessionUserId = $request->session()->get('active_user_id');
        if ($sessionUserId && DB::table('users')->where('id', $sessionUserId)->exists()) {
            return (int) $sessionUserId;
        }
        if (auth()->check()) {
            return (int) auth()->id();
        }
        $user = DB::table('users')->where('email', 'andi.dwiputra@arsitekmuda.co.id')->first()
            ?? DB::table('users')->first();

        return (int) $user->id;
    }

    protected function currentOrgId(Request $request): int
    {
        $userId = $this->currentUserId($request);
        $orgId = DB::table('organization_users')->where('user_id', $userId)->value('organization_id');
        if ($orgId) {
            return (int) $orgId;
        }

        return (int) DB::table('organizations')->value('id');
    }

    protected function logActivity(Request $request, string $action, string $title, string $badge = 'Berhasil', string $badgeType = 'emerald', string $icon = 'check-circle-2', ?int $projectId = null): void
    {
        DB::table('audit_logs')->insert([
            'organization_id' => $this->currentOrgId($request),
            'user_id' => $this->currentUserId($request),
            'project_id' => $projectId,
            'action' => $action,
            'entity_type' => explode('.', $action)[0] ?? 'system',
            'new_values' => json_encode([
                'title' => $title,
                'badge' => $badge,
                'badge_type' => $badgeType,
                'icon' => $icon,
                'time_label' => 'Baru saja',
            ]),
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
    }

    // =========================================================================
    // USER SWITCHER & NOTIFICATIONS
    // =========================================================================
    public function switchUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $request->session()->put('active_user_id', (int) $validated['user_id']);
        $user = DB::table('users')->where('id', $validated['user_id'])->first();

        return back()->with('success', 'Berhasil beralih akun ke ' . $user->name);
    }

    public function markNotificationsRead(Request $request)
    {
        $userId = $this->currentUserId($request);
        DB::table('notifications')->where('user_id', $userId)->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    // =========================================================================
    // BELAJAR BIM CRUD (Modules, Lessons, Files, Quizzes, Classes)
    // =========================================================================
    public function storeModule(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'estimated_minutes' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        $orgId = $this->currentOrgId($request);
        $program = DB::table('training_programs')->where('organization_id', $orgId)->first();
        $maxOrder = (int) DB::table('training_modules')->where('training_program_id', $program->id)->max('sort_order');

        $moduleId = DB::table('training_modules')->insertGetId([
            'training_program_id' => $program->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'sort_order' => $data['sort_order'] ?? ($maxOrder + 1),
            'estimated_minutes' => $data['estimated_minutes'] ?? 120,
            'is_required' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->logActivity($request, 'module.created', 'Menambahkan modul "' . $data['title'] . '"', 'Baru', 'emerald', 'book-open');

        return redirect()->route('belajar', ['module_id' => $moduleId])->with('success', 'Modul pembelajaran berhasil ditambahkan.');
    }

    public function updateModule(Request $request, int $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'estimated_minutes' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        DB::table('training_modules')->where('id', $id)->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'estimated_minutes' => $data['estimated_minutes'] ?? 120,
            'sort_order' => $data['sort_order'] ?? 1,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Modul pembelajaran berhasil diperbarui.');
    }

    public function destroyModule(Request $request, int $id)
    {
        DB::table('training_modules')->where('id', $id)->delete();

        return redirect()->route('belajar')->with('success', 'Modul pembelajaran berhasil dihapus.');
    }

    public function storeLesson(Request $request)
    {
        $data = $request->validate([
            'training_module_id' => 'required|exists:training_modules,id',
            'title' => 'required|string|max:200',
            'lesson_type' => 'required|in:video,article,document,quiz,lab,project',
            'content' => 'nullable|string',
            'video_url' => 'nullable|string|max:500',
            'estimated_minutes' => 'nullable|integer|min:1',
        ]);

        $maxOrder = (int) DB::table('lessons')->where('training_module_id', $data['training_module_id'])->max('sort_order');

        $lessonId = DB::table('lessons')->insertGetId([
            'training_module_id' => $data['training_module_id'],
            'title' => $data['title'],
            'lesson_type' => $data['lesson_type'],
            'content' => $data['content'] ?? '',
            'video_url' => $data['video_url'] ?? null,
            'sort_order' => $maxOrder + 1,
            'estimated_minutes' => $data['estimated_minutes'] ?? 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->logActivity($request, 'lesson.created', 'Menambahkan materi "' . $data['title'] . '"', 'Materi', 'emerald', 'file-check');

        return redirect()->route('belajar', ['module_id' => $data['training_module_id'], 'lesson_id' => $lessonId])
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    public function updateLesson(Request $request, int $id)
    {
        $data = $request->validate([
            'training_module_id' => 'required|exists:training_modules,id',
            'title' => 'required|string|max:200',
            'lesson_type' => 'required|in:video,article,document,quiz,lab,project',
            'content' => 'nullable|string',
            'video_url' => 'nullable|string|max:500',
            'estimated_minutes' => 'nullable|integer|min:1',
        ]);

        DB::table('lessons')->where('id', $id)->update([
            'training_module_id' => $data['training_module_id'],
            'title' => $data['title'],
            'lesson_type' => $data['lesson_type'],
            'content' => $data['content'] ?? '',
            'video_url' => $data['video_url'] ?? null,
            'estimated_minutes' => $data['estimated_minutes'] ?? 20,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroyLesson(Request $request, int $id)
    {
        DB::table('lessons')->where('id', $id)->delete();

        return redirect()->route('belajar')->with('success', 'Materi berhasil dihapus.');
    }

    public function toggleLessonProgress(Request $request, int $id)
    {
        $userId = $this->currentUserId($request);
        $orgId = $this->currentOrgId($request);
        $lesson = DB::table('lessons')->where('id', $id)->firstOrFail();
        $class = DB::table('training_classes')->where('organization_id', $orgId)->first();

        $existing = DB::table('lesson_progress')
            ->where('user_id', $userId)
            ->where('lesson_id', $id)
            ->first();

        $newStatus = ($existing && $existing->status === 'completed') ? 'in_progress' : 'completed';
        $newPercent = $newStatus === 'completed' ? 100.00 : 50.00;

        if ($existing) {
            DB::table('lesson_progress')->where('id', $existing->id)->update([
                'status' => $newStatus,
                'progress_percent' => $newPercent,
                'completed_at' => $newStatus === 'completed' ? now() : null,
            ]);
        } else {
            DB::table('lesson_progress')->insert([
                'user_id' => $userId,
                'lesson_id' => $id,
                'training_class_id' => $class->id,
                'status' => $newStatus,
                'progress_percent' => $newPercent,
                'started_at' => now(),
                'completed_at' => $newStatus === 'completed' ? now() : null,
                'last_position_seconds' => 600,
            ]);
        }

        if ($newStatus === 'completed') {
            $this->logActivity($request, 'lesson.completed', 'Anda menyelesaikan materi "' . $lesson->title . '"', 'Selesai', 'emerald', 'check-circle-2');
        }

        return back()->with('success', 'Status progres materi "' . $lesson->title . '" diperbarui menjadi ' . strtoupper($newStatus) . '.');
    }

    public function storeFile(Request $request)
    {
        $data = $request->validate([
            'original_name' => 'required|string|max:255',
            'extension' => 'required|string|max:20',
            'size_label' => 'nullable|string|max:50',
            'file_type' => 'nullable|in:image,document,video,ifc,model,attachment,certificate,other',
        ]);

        $ext = strtoupper($data['extension']);
        $badgeColor = match ($ext) {
            'PDF' => 'bg-red-600',
            'RVT', 'R', 'IFC' => 'bg-blue-600',
            'XLS', 'XLSX', 'CSV' => 'bg-emerald-700',
            default => 'bg-orange-600',
        };

        DB::table('files')->insert([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->currentOrgId($request),
            'uploaded_by' => $this->currentUserId($request),
            'file_type' => $data['file_type'] ?? 'document',
            'original_name' => $data['original_name'],
            'stored_name' => Str::slug($data['original_name']) . '.' . strtolower($ext),
            'extension' => $ext,
            'size_bytes' => 5242880,
            'storage_disk' => 'local',
            'storage_path' => 'docs/' . Str::slug($data['original_name']) . '.' . strtolower($ext),
            'metadata' => json_encode([
                'badge_color' => $badgeColor,
                'size_label' => $data['size_label'] ?: '5.0 MB',
                'date_label' => now()->format('d M Y'),
            ]),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Dokumen pendukung berhasil ditambahkan.');
    }

    public function updateFile(Request $request, int $id)
    {
        $data = $request->validate([
            'original_name' => 'required|string|max:255',
            'extension' => 'required|string|max:20',
            'size_label' => 'nullable|string|max:50',
        ]);

        $file = DB::table('files')->where('id', $id)->firstOrFail();
        $meta = json_decode($file->metadata ?? '{}', true) ?: [];
        $ext = strtoupper($data['extension']);
        $meta['size_label'] = $data['size_label'] ?: ($meta['size_label'] ?? '5.0 MB');
        $meta['badge_color'] = match ($ext) {
            'PDF' => 'bg-red-600',
            'RVT', 'R', 'IFC' => 'bg-blue-600',
            'XLS', 'XLSX', 'CSV' => 'bg-emerald-700',
            default => 'bg-orange-600',
        };

        DB::table('files')->where('id', $id)->update([
            'original_name' => $data['original_name'],
            'extension' => $ext,
            'metadata' => json_encode($meta),
        ]);

        return back()->with('success', 'Dokumen pendukung berhasil diperbarui.');
    }

    public function destroyFile(Request $request, int $id)
    {
        DB::table('files')->where('id', $id)->delete();

        return back()->with('success', 'Dokumen pendukung berhasil dihapus.');
    }

    public function downloadFile(int $id)
    {
        $file = DB::table('files')->where('id', $id)->firstOrFail();
        $content = "BIM NUSANTARA - DOKUMEN & FILE LATIHAN\n"
            . "========================================\n"
            . "Nama Dokumen : {$file->original_name}\n"
            . "Format       : {$file->extension}\n"
            . "UUID         : {$file->uuid}\n"
            . "Tanggal Unduh: " . now()->toDateTimeString() . "\n";

        $filename = $file->stored_name ?: (Str::slug($file->original_name) . '.txt');

        return response($content, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function submitQuiz(Request $request, int $id)
    {
        $quiz = DB::table('quizzes')->where('id', $id)->firstOrFail();
        $questions = DB::table('quiz_questions')->where('quiz_id', $id)->get();
        $userId = $this->currentUserId($request);

        $totalPoints = 0;
        $earnedPoints = 0;
        $answersPayload = $request->input('answers', []);

        $attemptCount = (int) DB::table('quiz_attempts')->where('quiz_id', $id)->where('user_id', $userId)->count();
        $attemptId = DB::table('quiz_attempts')->insertGetId([
            'quiz_id' => $id,
            'user_id' => $userId,
            'attempt_no' => $attemptCount + 1,
            'score' => 0,
            'status' => 'graded',
            'started_at' => now()->subMinutes(5),
            'submitted_at' => now(),
        ]);

        foreach ($questions as $q) {
            $totalPoints += (float) $q->points;
            $selectedOptionId = $answersPayload[$q->id] ?? null;
            $correctOption = DB::table('quiz_options')
                ->where('question_id', $q->id)
                ->where('is_correct', true)
                ->first();

            $isCorrect = $selectedOptionId && $correctOption && ((int) $selectedOptionId === (int) $correctOption->id);
            $awarded = $isCorrect ? (float) $q->points : 0.0;
            $earnedPoints += $awarded;

            DB::table('quiz_answers')->insert([
                'quiz_attempt_id' => $attemptId,
                'question_id' => $q->id,
                'selected_options' => json_encode([$selectedOptionId]),
                'is_correct' => $isCorrect,
                'score_awarded' => $awarded,
            ]);
        }

        $finalScore = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 100.00;
        DB::table('quiz_attempts')->where('id', $attemptId)->update(['score' => $finalScore]);

        $this->logActivity($request, 'quiz.graded', 'Nilai ' . $quiz->title . ' diumumkan', (string) round($finalScore), 'blue', 'award');

        return back()->with('success', 'Quiz selesai dikerjakan! Skor Anda: ' . round($finalScore) . ' / 100');
    }

    public function storeQuizQuestion(Request $request, int $id)
    {
        $data = $request->validate([
            'question_text' => 'required|string',
            'points' => 'nullable|numeric|min:1',
            'option_1' => 'required|string',
            'option_2' => 'required|string',
            'option_3' => 'nullable|string',
            'option_4' => 'nullable|string',
            'correct_option' => 'required|in:1,2,3,4',
        ]);

        $maxOrder = (int) DB::table('quiz_questions')->where('quiz_id', $id)->max('sort_order');
        $qId = DB::table('quiz_questions')->insertGetId([
            'quiz_id' => $id,
            'question_type' => 'single_choice',
            'question_text' => $data['question_text'],
            'points' => $data['points'] ?? 25,
            'sort_order' => $maxOrder + 1,
            'created_at' => now(),
        ]);

        foreach ([1, 2, 3, 4] as $idx) {
            $optText = $data['option_' . $idx] ?? null;
            if ($optText !== null && trim($optText) !== '') {
                DB::table('quiz_options')->insert([
                    'question_id' => $qId,
                    'option_text' => $optText,
                    'is_correct' => ((int) $data['correct_option'] === $idx),
                    'sort_order' => $idx,
                ]);
            }
        }

        return back()->with('success', 'Pertanyaan quiz berhasil ditambahkan.');
    }

    public function destroyQuizQuestion(int $id)
    {
        DB::table('quiz_questions')->where('id', $id)->delete();

        return back()->with('success', 'Pertanyaan quiz berhasil dihapus.');
    }

    public function storeClass(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'code' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'time_range' => 'nullable|string|max:100',
            'platform' => 'nullable|string|max:100',
            'badge' => 'nullable|string|max:50',
        ]);

        $orgId = $this->currentOrgId($request);
        $program = DB::table('training_programs')->where('organization_id', $orgId)->first();
        $code = $data['code'] ?: ('CLS-' . strtoupper(Str::random(5)));
        $dateLabel = strtoupper(\Carbon\Carbon::parse($data['start_date'])->translatedFormat('M d'));

        DB::table('training_classes')->insert([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $orgId,
            'training_program_id' => $program->id,
            'name' => $data['name'],
            'code' => $code,
            'start_date' => $data['start_date'],
            'end_date' => \Carbon\Carbon::parse($data['start_date'])->addDays(14)->toDateString(),
            'trainer_id' => $this->currentUserId($request),
            'status' => 'open',
            'settings' => json_encode([
                'time' => $data['time_range'] ?: '19:00 - 21:00 WIB',
                'platform' => $data['platform'] ?: 'Online (Zoom)',
                'badge' => $data['badge'] ?: 'Terbuka',
                'date_label' => $dateLabel,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Jadwal kelas baru berhasil ditambahkan.');
    }

    public function updateClass(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'start_date' => 'required|date',
            'time_range' => 'nullable|string|max:100',
            'platform' => 'nullable|string|max:100',
            'badge' => 'nullable|string|max:50',
        ]);

        $dateLabel = strtoupper(\Carbon\Carbon::parse($data['start_date'])->translatedFormat('M d'));
        DB::table('training_classes')->where('id', $id)->update([
            'name' => $data['name'],
            'start_date' => $data['start_date'],
            'settings' => json_encode([
                'time' => $data['time_range'] ?: '19:00 - 21:00 WIB',
                'platform' => $data['platform'] ?: 'Online (Zoom)',
                'badge' => $data['badge'] ?: 'Terdaftar',
                'date_label' => $dateLabel,
            ]),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Jadwal kelas berhasil diperbarui.');
    }

    public function destroyClass(int $id)
    {
        DB::table('training_classes')->where('id', $id)->delete();

        return back()->with('success', 'Jadwal kelas berhasil dihapus.');
    }

    // =========================================================================
    // BIM LAB CRUD
    // =========================================================================
    public function storeLab(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'estimated_minutes' => 'required|integer|min:15',
        ]);

        $labId = DB::table('bim_labs')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->currentOrgId($request),
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'difficulty' => $data['difficulty'],
            'estimated_minutes' => $data['estimated_minutes'],
            'instructions' => json_encode([
                [
                    'step' => 1,
                    'title' => 'Persiapan & Setup Model',
                    'summary' => 'Menyiapkan template dan parameter dasar lab.',
                    'description' => $data['description'] ?? 'Ikuti panduan langkah demi langkah untuk menyelesaikan lab ini.',
                    'completed' => false,
                    'viewport_title' => 'Autodesk Revit - 3D View: {3D}',
                    'viewport_levels' => 'Level 2 +3.60 | Level 1 +0.00',
                    'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
                    'steps_list' => [
                        'Buka template project sesuai instruksi',
                        'Atur unit dan grid bangunan',
                        'Mulai pemodelan elemen utama',
                    ],
                    'checklist' => [
                        ['label' => 'Template terbuka dengan benar', 'done' => false],
                        ['label' => 'Unit proyek sudah sesuai', 'done' => false],
                    ],
                    'tips' => [
                        'Simpan pekerjaan secara berkala (Ctrl + S).',
                    ],
                ],
            ]),
            'status' => 'published',
            'created_by' => $this->currentUserId($request),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('bim-lab', ['lab_id' => $labId])->with('success', 'BIM Lab baru berhasil dibuat.');
    }

    public function updateLab(Request $request, int $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'estimated_minutes' => 'required|integer|min:15',
        ]);

        DB::table('bim_labs')->where('id', $id)->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'difficulty' => $data['difficulty'],
            'estimated_minutes' => $data['estimated_minutes'],
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Informasi BIM Lab berhasil diperbarui.');
    }

    public function destroyLab(int $id)
    {
        DB::table('bim_labs')->where('id', $id)->delete();

        return redirect()->route('bim-lab')->with('success', 'BIM Lab berhasil dihapus.');
    }

    public function storeLabStep(Request $request, int $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'summary' => 'nullable|string|max:300',
            'description' => 'nullable|string',
            'steps_raw' => 'nullable|string',
            'checklist_raw' => 'nullable|string',
            'tips_raw' => 'nullable|string',
        ]);

        $lab = DB::table('bim_labs')->where('id', $id)->firstOrFail();
        $instructions = json_decode($lab->instructions ?? '[]', true) ?: [];

        $stepsList = array_values(array_filter(array_map('trim', explode("\n", $data['steps_raw'] ?? ''))));
        if (empty($stepsList)) {
            $stepsList = ['Lakukan langkah pemodelan sesuai instruksi modul.'];
        }

        $checklistItems = array_values(array_filter(array_map('trim', explode("\n", $data['checklist_raw'] ?? ''))));
        $checklist = array_map(fn ($label) => ['label' => $label, 'done' => false], $checklistItems ?: ['Langkah selesai diverifikasi']);

        $tipsList = array_values(array_filter(array_map('trim', explode("\n", $data['tips_raw'] ?? ''))));
        if (empty($tipsList)) {
            $tipsList = ['Pastikan snap dan grid aktif untuk presisi tinggi.'];
        }

        $newStepNo = count($instructions) + 1;
        $instructions[] = [
            'step' => $newStepNo,
            'title' => $data['title'],
            'summary' => $data['summary'] ?: $data['title'],
            'description' => $data['description'] ?: $data['summary'],
            'completed' => false,
            'viewport_title' => 'Autodesk Revit - RT_2Lantai - Step ' . $newStepNo,
            'viewport_levels' => 'Atap +7.20 | Lantai 2 +3.60 | Lantai 1 +0.00',
            'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
            'steps_list' => $stepsList,
            'checklist' => $checklist,
            'tips' => $tipsList,
        ];

        DB::table('bim_labs')->where('id', $id)->update([
            'instructions' => json_encode($instructions),
            'updated_at' => now(),
        ]);

        return redirect()->route('bim-lab', ['lab_id' => $id, 'step' => count($instructions) - 1])
            ->with('success', 'Langkah praktik baru berhasil ditambahkan.');
    }

    public function updateLabStep(Request $request, int $id, int $stepIndex)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'summary' => 'nullable|string|max:300',
            'description' => 'nullable|string',
            'steps_raw' => 'nullable|string',
            'checklist_raw' => 'nullable|string',
            'tips_raw' => 'nullable|string',
        ]);

        $lab = DB::table('bim_labs')->where('id', $id)->firstOrFail();
        $instructions = json_decode($lab->instructions ?? '[]', true) ?: [];

        if (isset($instructions[$stepIndex])) {
            $stepsList = array_values(array_filter(array_map('trim', explode("\n", $data['steps_raw'] ?? ''))));
            $checklistItems = array_values(array_filter(array_map('trim', explode("\n", $data['checklist_raw'] ?? ''))));
            $tipsList = array_values(array_filter(array_map('trim', explode("\n", $data['tips_raw'] ?? ''))));

            $instructions[$stepIndex]['title'] = $data['title'];
            $instructions[$stepIndex]['summary'] = $data['summary'] ?: $data['title'];
            $instructions[$stepIndex]['description'] = $data['description'] ?? '';
            if (! empty($stepsList)) {
                $instructions[$stepIndex]['steps_list'] = $stepsList;
            }
            if (! empty($checklistItems)) {
                $existingDone = collect($instructions[$stepIndex]['checklist'] ?? [])->pluck('done', 'label');
                $instructions[$stepIndex]['checklist'] = array_map(
                    fn ($lbl) => ['label' => $lbl, 'done' => (bool) ($existingDone[$lbl] ?? false)],
                    $checklistItems
                );
            }
            if (! empty($tipsList)) {
                $instructions[$stepIndex]['tips'] = $tipsList;
            }

            DB::table('bim_labs')->where('id', $id)->update([
                'instructions' => json_encode($instructions),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Langkah praktik berhasil diperbarui.');
    }

    public function destroyLabStep(int $id, int $stepIndex)
    {
        $lab = DB::table('bim_labs')->where('id', $id)->firstOrFail();
        $instructions = json_decode($lab->instructions ?? '[]', true) ?: [];

        if (isset($instructions[$stepIndex]) && count($instructions) > 1) {
            array_splice($instructions, $stepIndex, 1);
            foreach ($instructions as $i => &$st) {
                $st['step'] = $i + 1;
            }
            DB::table('bim_labs')->where('id', $id)->update([
                'instructions' => json_encode($instructions),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('bim-lab', ['lab_id' => $id, 'step' => 0])
            ->with('success', 'Langkah praktik berhasil dihapus.');
    }

    public function toggleLabStepComplete(Request $request, int $id, int $stepIndex)
    {
        $lab = DB::table('bim_labs')->where('id', $id)->firstOrFail();
        $instructions = json_decode($lab->instructions ?? '[]', true) ?: [];

        if (isset($instructions[$stepIndex])) {
            $instructions[$stepIndex]['completed'] = ! ($instructions[$stepIndex]['completed'] ?? false);
            DB::table('bim_labs')->where('id', $id)->update([
                'instructions' => json_encode($instructions),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Status penyelesaian langkah berhasil diperbarui.');
    }

    public function toggleLabChecklist(Request $request, int $id, int $stepIndex)
    {
        $checkIndex = (int) $request->input('check_index', 0);
        $lab = DB::table('bim_labs')->where('id', $id)->firstOrFail();
        $instructions = json_decode($lab->instructions ?? '[]', true) ?: [];

        if (isset($instructions[$stepIndex]['checklist'][$checkIndex])) {
            $instructions[$stepIndex]['checklist'][$checkIndex]['done'] = ! ($instructions[$stepIndex]['checklist'][$checkIndex]['done'] ?? false);
            $allDone = collect($instructions[$stepIndex]['checklist'])->every(fn ($c) => ! empty($c['done']));
            if ($allDone) {
                $instructions[$stepIndex]['completed'] = true;
            }
            DB::table('bim_labs')->where('id', $id)->update([
                'instructions' => json_encode($instructions),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Checklist langkah berhasil diperbarui.');
    }

    // =========================================================================
    // BIM STUDIO CRUD (Elements, Levels, Grids, Versions, Validation, Submit)
    // =========================================================================
    public function storeStudioElement(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'level_id' => 'nullable|exists:project_levels,id',
            'element_type_code' => 'required|string',
            'material_id' => 'nullable|exists:materials,id',
            'name' => 'required|string|max:200',
            'code' => 'nullable|string|max:100',
            'length_mm' => 'nullable|numeric|min:100',
            'height_mm' => 'nullable|numeric|min:100',
            'thickness_mm' => 'nullable|numeric|min:10',
            'pos_x' => 'nullable|numeric',
            'pos_y' => 'nullable|numeric',
        ]);

        $elType = DB::table('element_types')->where('code', $data['element_type_code'])->first()
            ?? DB::table('element_types')->where('code', 'WALL')->first();

        $count = (int) DB::table('project_elements')->where('project_id', $data['project_id'])->count();
        $code = $data['code'] ?: (substr($elType->code, 0, 1) . '-' . str_pad((string) ($count + 1), 3, '0', STR_PAD_LEFT));

        $lengthMm = (float) ($data['length_mm'] ?? 4000);
        $heightMm = (float) ($data['height_mm'] ?? 3000);
        $thicknessMm = (float) ($data['thickness_mm'] ?? 200);
        $posX = (float) ($data['pos_x'] ?? (($count % 4) * 2000));
        $posY = (float) ($data['pos_y'] ?? ((int) ($count / 4) * 2500));

        $parentWallId = null;
        if (in_array($elType->code, ['DOOR', 'WINDOW'], true)) {
            $parentWallId = DB::table('project_elements')
                ->join('element_types', 'element_types.id', '=', 'project_elements.element_type_id')
                ->where('project_elements.project_id', $data['project_id'])
                ->where('element_types.code', 'WALL')
                ->whereNull('project_elements.deleted_at')
                ->value('project_elements.id');
        }

        $newId = DB::table('project_elements')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'project_id' => $data['project_id'],
            'level_id' => $data['level_id'],
            'parent_element_id' => $parentWallId,
            'element_type_id' => $elType->id,
            'material_id' => $data['material_id'] ?? DB::table('materials')->value('id'),
            'name' => $data['name'],
            'code' => $code,
            'ifc_class' => $elType->ifc_class ?? 'IfcBuildingElement',
            'geometry' => json_encode([
                'start' => ['x' => $posX, 'y' => $posY],
                'end' => ['x' => $posX + $lengthMm, 'y' => $posY],
                'length' => $lengthMm,
                'width' => $lengthMm,
                'height' => $heightMm,
                'thickness' => $thicknessMm,
                'baseOffset' => 0,
            ]),
            'transform_data' => json_encode([
                'position' => [$posX, $posY, 0],
                'rotation' => [0, 0, 0],
                'scale' => [1, 1, 1],
            ]),
            'properties' => json_encode([
                'elementType' => $elType->name,
                'topConstraint' => 'Level 2 - Lantai 2',
                'finishing' => 'Standar Arsitektur',
            ]),
            'is_visible' => true,
            'is_locked' => false,
            'created_by' => $this->currentUserId($request),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('project_operations')->insert([
            'project_id' => $data['project_id'],
            'user_id' => $this->currentUserId($request),
            'operation_type' => 'CREATE_ELEMENT',
            'payload' => json_encode(['element_id' => $newId, 'name' => $data['name']]),
            'created_at' => now(),
        ]);

        return redirect()->route('bim-studio', [
            'project_id' => $data['project_id'],
            'level_id' => $data['level_id'],
            'element_id' => $newId,
        ])->with('success', 'Elemen BIM "' . $data['name'] . '" berhasil ditambahkan ke model.');
    }

    public function updateStudioElement(Request $request, int $id)
    {
        $el = DB::table('project_elements')->where('id', $id)->firstOrFail();
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'code' => 'nullable|string|max:100',
            'ifc_class' => 'nullable|string|max:150',
            'level_id' => 'nullable|exists:project_levels,id',
            'material_id' => 'nullable|exists:materials,id',
            'length_mm' => 'nullable|numeric|min:50',
            'height_mm' => 'nullable|numeric|min:50',
            'thickness_mm' => 'nullable|numeric|min:10',
            'top_constraint' => 'nullable|string|max:150',
            'finishing' => 'nullable|string|max:150',
            'is_visible' => 'nullable|boolean',
            'is_locked' => 'nullable|boolean',
        ]);

        $geo = json_decode($el->geometry ?? '{}', true) ?: [];
        $props = json_decode($el->properties ?? '{}', true) ?: [];

        if (isset($data['length_mm'])) {
            $geo['length'] = (float) $data['length_mm'];
            $geo['width'] = (float) $data['length_mm'];
            if (isset($geo['start'])) {
                $geo['end'] = ['x' => ($geo['start']['x'] ?? 0) + (float) $data['length_mm'], 'y' => ($geo['start']['y'] ?? 0)];
            }
        }
        if (isset($data['height_mm'])) {
            $geo['height'] = (float) $data['height_mm'];
        }
        if (isset($data['thickness_mm'])) {
            $geo['thickness'] = (float) $data['thickness_mm'];
            $geo['depth'] = (float) $data['thickness_mm'];
        }

        if (isset($data['top_constraint'])) {
            $props['topConstraint'] = $data['top_constraint'];
        }
        if (isset($data['finishing'])) {
            $props['finishing'] = $data['finishing'];
        }

        DB::table('project_elements')->where('id', $id)->update([
            'name' => $data['name'],
            'code' => ($data['code'] ?? null) ?: $el->code,
            'ifc_class' => ($data['ifc_class'] ?? null) ?: $el->ifc_class,
            'level_id' => ($data['level_id'] ?? null) ?: $el->level_id,
            'material_id' => ($data['material_id'] ?? null) ?: $el->material_id,
            'geometry' => json_encode($geo),
            'properties' => json_encode($props),
            'is_visible' => $request->has('is_visible') ? (bool) $request->input('is_visible') : $el->is_visible,
            'is_locked' => $request->has('is_locked') ? (bool) $request->input('is_locked') : $el->is_locked,
            'updated_by' => $this->currentUserId($request),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Properti elemen "' . $data['name'] . '" berhasil diperbarui.');
    }

    public function destroyStudioElement(Request $request, int $id)
    {
        $el = DB::table('project_elements')->where('id', $id)->firstOrFail();
        DB::table('project_elements')->where('id', $id)->delete();

        return redirect()->route('bim-studio', ['project_id' => $el->project_id])
            ->with('success', 'Elemen "' . $el->name . '" berhasil dihapus dari model.');
    }

    public function storeStudioLevel(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:150',
            'elevation_mm' => 'required|integer',
        ]);

        $maxOrder = (int) DB::table('project_levels')->where('project_id', $data['project_id'])->max('sort_order');
        DB::table('project_levels')->insert([
            'uuid' => (string) Str::uuid(),
            'project_id' => $data['project_id'],
            'name' => $data['name'],
            'elevation_mm' => $data['elevation_mm'],
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Level "' . $data['name'] . '" berhasil ditambahkan.');
    }

    public function destroyStudioLevel(int $id)
    {
        DB::table('project_levels')->where('id', $id)->delete();

        return back()->with('success', 'Level berhasil dihapus.');
    }

    public function storeStudioGrid(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'axis' => 'required|in:X,Y',
            'label' => 'required|string|max:50',
            'position_mm' => 'required|integer',
        ]);

        DB::table('project_grids')->updateOrInsert(
            ['project_id' => $data['project_id'], 'axis' => $data['axis'], 'label' => $data['label']],
            [
                'uuid' => (string) Str::uuid(),
                'position_mm' => $data['position_mm'],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return back()->with('success', 'Grid ' . $data['axis'] . '-' . $data['label'] . ' berhasil disimpan.');
    }

    public function destroyStudioGrid(int $id)
    {
        DB::table('project_grids')->where('id', $id)->delete();

        return back()->with('success', 'Grid berhasil dihapus.');
    }

    public function saveStudioVersion(Request $request, int $id)
    {
        $data = $request->validate([
            'label' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
        ]);

        $maxVer = (int) DB::table('project_versions')->where('project_id', $id)->max('version_no');
        $elementsCount = DB::table('project_elements')->where('project_id', $id)->whereNull('deleted_at')->count();
        $newVerNo = $maxVer + 1;

        DB::table('project_versions')->insert([
            'uuid' => (string) Str::uuid(),
            'project_id' => $id,
            'version_no' => $newVerNo,
            'label' => $data['label'] ?: ('v' . $newVerNo . '.0 - Snapshot Model'),
            'version_type' => 'manual',
            'snapshot' => json_encode([
                'schemaVersion' => '1.0.0',
                'projectId' => $id,
                'elementCount' => $elementsCount,
                'savedAt' => now()->toIso8601String(),
            ]),
            'notes' => $data['notes'] ?: 'Snapshot disimpan dari BIM Studio.',
            'created_by' => $this->currentUserId($request),
            'created_at' => now(),
        ]);

        $this->logActivity($request, 'version.saved', 'Menyimpan versi v' . $newVerNo . '.0 pada BIM Studio', 'v' . $newVerNo . '.0', 'emerald', 'file-check', $id);

        return back()->with('success', 'Snapshot Model Versi #' . $newVerNo . ' berhasil disimpan.');
    }

    public function validateStudioModel(Request $request, int $id)
    {
        $rules = DB::table('challenge_rules')->orderBy('sort_order')->get();
        $elements = DB::table('project_elements')
            ->join('element_types', 'element_types.id', '=', 'project_elements.element_type_id')
            ->select('project_elements.*', 'element_types.code as type_code')
            ->where('project_elements.project_id', $id)
            ->whereNull('project_elements.deleted_at')
            ->get();

        $levelsCount = DB::table('project_levels')->where('project_id', $id)->count();
        $countsByType = $elements->groupBy('type_code')->map->count();

        DB::table('validation_results')->where('project_id', $id)->delete();

        $passedCount = 0;
        $totalWeight = 0;
        $earnedWeight = 0;

        foreach ($rules as $rule) {
            $cfg = json_decode($rule->config ?? '{}', true) ?: [];
            $passed = true;
            $msg = 'Memenuhi kriteria standar BIM.';

            if ($rule->rule_type === 'REQUIRED_LEVEL') {
                $min = (int) ($cfg['minCount'] ?? 2);
                $passed = $levelsCount >= $min;
                $msg = "Terdapat {$levelsCount} dari minimal {$min} level lantai.";
            } elseif ($rule->rule_type === 'MIN_ELEMENT_COUNT') {
                $type = $cfg['elementType'] ?? 'WALL';
                $min = (int) ($cfg['minCount'] ?? 1);
                $actual = (int) ($countsByType[$type] ?? 0);
                $passed = $actual >= $min;
                $msg = "Terdapat {$actual} dari minimal {$min} elemen {$type}.";
            } elseif ($rule->rule_type === 'HOST_REQUIRED') {
                $openings = $elements->whereIn('type_code', ['DOOR', 'WINDOW']);
                $unhosted = $openings->filter(fn ($o) => empty($o->parent_element_id))->count();
                $passed = $unhosted === 0;
                $msg = $passed ? 'Semua bukaan memiliki Host Wall.' : "Terdapat {$unhosted} bukaan tanpa Host Wall.";
            }

            $totalWeight += (float) $rule->weight;
            if ($passed) {
                $passedCount++;
                $earnedWeight += (float) $rule->weight;
            }

            DB::table('validation_results')->insert([
                'project_id' => $id,
                'rule_id' => $rule->id,
                'status' => $passed ? 'passed' : ($rule->severity === 'warning' ? 'warning' : 'failed'),
                'score' => $passed ? $rule->weight : 0,
                'message' => $msg,
                'created_at' => now(),
            ]);
        }

        $scorePercent = $totalWeight > 0 ? round(($earnedWeight / $totalWeight) * 100) : 100;
        $this->logActivity($request, 'bim.validated', 'Validasi Model BIM Studio selesai (Skor: ' . $scorePercent . '%)', $scorePercent . '%', 'emerald', 'check-circle-2', $id);

        return back()->with('success', "Validasi Model Selesai! {$passedCount}/{$rules->count()} Rule Terpenuhi (Skor Validasi: {$scorePercent}%).")
            ->with('open_validation_modal', true);
    }

    public function submitStudioToProject(Request $request, int $id)
    {
        $project = DB::table('projects')->where('id', $id)->firstOrFail();
        $latestVer = DB::table('project_versions')->where('project_id', $id)->orderByDesc('version_no')->first();

        DB::table('submissions')->insert([
            'uuid' => (string) Str::uuid(),
            'project_id' => $id,
            'user_id' => $this->currentUserId($request),
            'project_version_id' => $latestVer?->id,
            'status' => 'under_review',
            'auto_score' => 92.00,
            'submitted_at' => now(),
            'feedback' => $request->input('notes', 'Model dikirim dari BIM Studio untuk direview oleh Mentor.'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('projects')->where('id', $id)->update([
            'status' => 'under_review',
            'progress_percent' => max((float) $project->progress_percent, 80.00),
            'updated_at' => now(),
        ]);

        $this->logActivity($request, 'project.submitted', 'Mengumpulkan model "' . $project->title . '" ke Mentor', 'Submitted', 'emerald', 'file-check', $id);

        return back()->with('success', 'Model berhasil disubmit ke Project "' . $project->title . '" untuk direview Mentor!');
    }

    // =========================================================================
    // PROJECT MANAGEMENT CRUD (Projects, Stages, Assignments, Submissions, Issues, Reviews)
    // =========================================================================
    public function storeProject(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'code' => 'nullable|string|max:100',
            'category' => 'required|in:training,simulation,portfolio,real_project',
            'status' => 'required|in:draft,active,under_review,revision_required,approved,completed,archived',
            'unit' => 'required|in:mm,cm,m',
            'deadline' => 'nullable|date',
            'description' => 'nullable|string',
            'building_type' => 'nullable|string|max:100',
            'progress_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $orgId = $this->currentOrgId($request);
        $userId = $this->currentUserId($request);
        $count = (int) DB::table('projects')->where('organization_id', $orgId)->count();
        $code = ($data['code'] ?? null) ?: ('PRJ-' . str_pad((string) ($count + 1), 3, '0', STR_PAD_LEFT));

        $projectId = DB::table('projects')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $orgId,
            'owner_id' => $userId,
            'title' => $data['title'],
            'code' => $code,
            'description' => $data['description'] ?? 'Proyek pemodelan dan koordinasi BIM terpadu.',
            'category' => $data['category'],
            'status' => $data['status'],
            'unit' => $data['unit'],
            'progress_percent' => $data['progress_percent'] ?? 25.00,
            'start_date' => now()->toDateString(),
            'deadline' => ($data['deadline'] ?? null) ?: now()->addDays(21)->toDateString(),
            'thumbnail_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80',
            'settings' => json_encode([
                'snap' => true,
                'gridSize' => 1000,
                'ortho' => true,
                'building_type' => ($data['building_type'] ?? null) ?: 'Residential',
                'software' => 'Revit',
                'tasks_done' => 4,
                'tasks_total' => 16,
                'team_size' => 3,
                'area_m2' => 450,
                'floors' => 3,
                'lod' => 'LOD 300',
                'stages' => [
                    ['name' => 'Konsep & Studi Awal', 'progress' => 100],
                    ['name' => 'Pemodelan Arsitektur', 'progress' => 50],
                    ['name' => 'Koordinasi Struktur', 'progress' => 0],
                    ['name' => 'Koordinasi MEP', 'progress' => 0],
                    ['name' => 'Finalisasi & Submission', 'progress' => 0],
                ],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('project_members')->insert([
            'project_id' => $projectId,
            'user_id' => $userId,
            'project_role' => 'owner',
            'can_edit' => true,
            'can_review' => true,
            'joined_at' => now(),
        ]);

        // Seed default levels & grids so it's immediately usable in BIM Studio
        $lvl1 = DB::table('project_levels')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'project_id' => $projectId,
            'name' => 'Level 1 - Ground Floor',
            'elevation_mm' => 0,
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('project_levels')->insert([
            'uuid' => (string) Str::uuid(),
            'project_id' => $projectId,
            'name' => 'Level 2 - Lantai 2',
            'elevation_mm' => 3500,
            'sort_order' => 2,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $wallType = DB::table('element_types')->where('code', 'WALL')->first();
        $concreteMat = DB::table('materials')->where('code', 'MAT-CONCRETE')->first();
        if ($wallType) {
            DB::table('project_elements')->insert([
                'uuid' => (string) Str::uuid(),
                'project_id' => $projectId,
                'level_id' => $lvl1,
                'element_type_id' => $wallType->id,
                'material_id' => $concreteMat?->id,
                'name' => 'Basic Wall - 200mm (Utama)',
                'code' => 'W-001',
                'ifc_class' => 'IfcWallStandardCase',
                'geometry' => json_encode(['start' => ['x' => 0, 'y' => 0], 'end' => ['x' => 6000, 'y' => 0], 'length' => 6000, 'height' => 3000, 'thickness' => 200]),
                'properties' => json_encode(['topConstraint' => 'Level 2 - Lantai 2', 'finishing' => 'Cat Putih']),
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->logActivity($request, 'project.created', 'Membuat project baru "' . $data['title'] . '"', 'Project Baru', 'emerald', 'folder', $projectId);

        return redirect()->route('project', ['project_id' => $projectId])->with('success', 'Project "' . $data['title'] . '" berhasil dibuat.');
    }

    public function updateProject(Request $request, int $id)
    {
        $project = DB::table('projects')->where('id', $id)->firstOrFail();
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'required|in:training,simulation,portfolio,real_project',
            'status' => 'required|in:draft,active,under_review,revision_required,approved,completed,archived',
            'unit' => 'required|in:mm,cm,m',
            'progress_percent' => 'required|numeric|min:0|max:100',
            'deadline' => 'nullable|date',
            'description' => 'nullable|string',
            'building_type' => 'nullable|string|max:100',
            'tasks_done' => 'nullable|integer|min:0',
            'tasks_total' => 'nullable|integer|min:1',
            'stage_progress' => 'nullable|array',
        ]);

        $meta = json_decode($project->settings ?? '{}', true) ?: [];
        if (isset($data['building_type'])) {
            $meta['building_type'] = $data['building_type'];
        }
        if (isset($data['tasks_done'])) {
            $meta['tasks_done'] = (int) $data['tasks_done'];
        }
        if (isset($data['tasks_total'])) {
            $meta['tasks_total'] = (int) $data['tasks_total'];
        }
        if (! empty($data['stage_progress']) && is_array($data['stage_progress'])) {
            $stages = $meta['stages'] ?? [
                ['name' => 'Konsep & Studi Awal', 'progress' => 100],
                ['name' => 'Pemodelan Arsitektur', 'progress' => 100],
                ['name' => 'Koordinasi Struktur', 'progress' => 70],
                ['name' => 'Koordinasi MEP', 'progress' => 0],
                ['name' => 'Finalisasi & Submission', 'progress' => 0],
            ];
            foreach ($stages as $idx => &$st) {
                if (isset($data['stage_progress'][$idx])) {
                    $st['progress'] = max(0, min(100, (int) $data['stage_progress'][$idx]));
                }
            }
            $meta['stages'] = $stages;
        }

        DB::table('projects')->where('id', $id)->update([
            'title' => $data['title'],
            'category' => $data['category'],
            'status' => $data['status'],
            'unit' => $data['unit'],
            'progress_percent' => $data['progress_percent'],
            'deadline' => $data['deadline'] ?: $project->deadline,
            'description' => $data['description'] ?? $project->description,
            'settings' => json_encode($meta),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Data project "' . $data['title'] . '" berhasil diperbarui.');
    }

    public function destroyProject(int $id)
    {
        DB::table('projects')->where('id', $id)->delete();

        return redirect()->route('project')->with('success', 'Project berhasil dihapus.');
    }

    public function storeIssue(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'element_id' => 'nullable|exists:project_elements,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'severity' => 'required|in:info,minor,major,critical',
            'discipline' => 'nullable|string|max:100',
        ]);

        DB::table('issues')->insert([
            'uuid' => (string) Str::uuid(),
            'project_id' => $data['project_id'],
            'element_id' => ($data['element_id'] ?? null) ?: null,
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'severity' => $data['severity'],
            'status' => 'open',
            'assigned_to' => $this->currentUserId($request),
            'created_by' => $this->currentUserId($request),
            'camera_state' => json_encode([
                'discipline' => ($data['discipline'] ?? null) ?: 'Arsitektur',
                'position' => [10, 8, 12],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->logActivity($request, 'issue.created', 'Melaporkan issue: "' . $data['title'] . '"', ucfirst($data['severity']), 'gold', 'alert-triangle', (int) $data['project_id']);

        return back()->with('success', 'Issue berhasil ditambahkan.');
    }

    public function updateIssue(Request $request, int $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'severity' => 'required|in:info,minor,major,critical',
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        DB::table('issues')->where('id', $id)->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'severity' => $data['severity'],
            'status' => $data['status'],
            'resolved_at' => in_array($data['status'], ['resolved', 'closed'], true) ? now() : null,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Issue berhasil diperbarui.');
    }

    public function destroyIssue(int $id)
    {
        DB::table('issues')->where('id', $id)->delete();

        return back()->with('success', 'Issue berhasil dihapus.');
    }

    public function storeIssueComment(Request $request, int $id)
    {
        $data = $request->validate([
            'comment' => 'required|string',
        ]);

        DB::table('issue_comments')->insert([
            'issue_id' => $id,
            'user_id' => $this->currentUserId($request),
            'comment' => $data['comment'],
            'created_at' => now(),
        ]);

        return back()->with('success', 'Komentar pada issue berhasil ditambahkan.');
    }

    public function storeReview(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'review_type' => 'required|in:technical,assessment,final',
            'score' => 'required|numeric|min:0|max:100',
            'notes' => 'required|string',
        ]);

        DB::table('project_reviews')->insert([
            'project_id' => $data['project_id'],
            'reviewer_id' => $this->currentUserId($request),
            'review_type' => $data['review_type'],
            'score' => $data['score'],
            'notes' => $data['notes'],
            'status' => 'completed',
            'reviewed_at' => now(),
            'created_at' => now(),
        ]);

        $this->logActivity($request, 'review.created', 'Menambahkan review pada project', 'Skor ' . round($data['score']), 'gold', 'message-circle', (int) $data['project_id']);

        return back()->with('success', 'Review project berhasil ditambahkan.');
    }

    public function destroyReview(int $id)
    {
        DB::table('project_reviews')->where('id', $id)->delete();

        return back()->with('success', 'Review berhasil dihapus.');
    }

    public function storeAssignment(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'due_at' => 'required|date',
            'max_score' => 'nullable|numeric|min:1|max:100',
        ]);

        DB::table('assignments')->insert([
            'organization_id' => $this->currentOrgId($request),
            'title' => $data['title'],
            'description' => $data['description'] ?: 'Pemodelan Arsitektur dengan Revit',
            'due_at' => $data['due_at'],
            'max_score' => $data['max_score'] ?? 100,
            'created_by' => $this->currentUserId($request),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Tugas baru berhasil ditambahkan.');
    }

    public function destroyAssignment(int $id)
    {
        DB::table('assignments')->where('id', $id)->delete();

        return back()->with('success', 'Tugas berhasil dihapus.');
    }

    public function storeSubmission(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'assignment_id' => 'nullable|exists:assignments,id',
            'status' => 'required|in:draft,submitted,under_review,revision_required,resubmitted,approved,final',
            'trainer_score' => 'nullable|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $score = $data['trainer_score'] ?? 90.00;
        DB::table('submissions')->insert([
            'uuid' => (string) Str::uuid(),
            'assignment_id' => $data['assignment_id'] ?: null,
            'project_id' => $data['project_id'],
            'user_id' => $this->currentUserId($request),
            'status' => $data['status'],
            'auto_score' => $score,
            'trainer_score' => $score,
            'final_score' => $score,
            'submitted_at' => now(),
            'feedback' => $data['feedback'] ?? 'Submission baru dikumpulkan.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Data submission berhasil disimpan.');
    }

    public function destroySubmission(int $id)
    {
        DB::table('submissions')->where('id', $id)->delete();

        return back()->with('success', 'Data submission berhasil dihapus.');
    }

    // =========================================================================
    // KOMPETENSI CRUD (Competencies, User Competencies, Assessments)
    // =========================================================================
    public function storeCompetency(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:100',
            'category' => 'nullable|string|max:150',
            'score' => 'required|numeric|min:0|max:100',
            'current_level' => 'required|integer|min:1|max:5',
            'target_level' => 'required|integer|min:1|max:5',
        ]);

        $orgId = $this->currentOrgId($request);
        $userId = $this->currentUserId($request);

        $compId = DB::table('competencies')->insertGetId([
            'organization_id' => $orgId,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'category' => $data['category'] ?: 'Modeling',
            'description' => 'Area kompetensi BIM ' . $data['name'],
            'max_level' => 5,
            'created_at' => now(),
        ]);

        DB::table('user_competencies')->insert([
            'user_id' => $userId,
            'organization_id' => $orgId,
            'competency_id' => $compId,
            'current_level' => $data['current_level'],
            'score' => $data['score'],
            'target_level' => $data['target_level'],
            'status' => $data['score'] >= 80 ? 'achieved' : 'in_progress',
            'last_assessed_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Kompetensi "' . $data['name'] . '" berhasil ditambahkan.');
    }

    public function updateCompetency(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'score' => 'required|numeric|min:0|max:100',
            'current_level' => 'required|integer|min:1|max:5',
            'target_level' => 'required|integer|min:1|max:5',
        ]);

        $orgId = $this->currentOrgId($request);
        $userId = $this->currentUserId($request);

        DB::table('competencies')->where('id', $id)->update([
            'name' => $data['name'],
        ]);

        DB::table('user_competencies')->updateOrInsert(
            [
                'user_id' => $userId,
                'organization_id' => $orgId,
                'competency_id' => $id,
            ],
            [
                'current_level' => $data['current_level'],
                'score' => $data['score'],
                'target_level' => $data['target_level'],
                'status' => $data['score'] >= 80 ? 'achieved' : 'in_progress',
                'last_assessed_at' => now(),
                'updated_at' => now(),
            ]
        );

        return back()->with('success', 'Skor kompetensi "' . $data['name'] . '" berhasil diperbarui.');
    }

    public function destroyCompetency(int $id)
    {
        DB::table('competencies')->where('id', $id)->delete();

        return back()->with('success', 'Kompetensi berhasil dihapus.');
    }

    public function storeAssessment(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'assessment_type' => 'required|in:quiz,project,challenge,manual,final',
            'score' => 'required|numeric|min:0|max:100',
            'result' => 'required|in:pass,fail,in_progress',
            'notes' => 'nullable|string',
            'assessed_at' => 'nullable|date',
        ]);

        DB::table('assessments')->insert([
            'organization_id' => $this->currentOrgId($request),
            'user_id' => $this->currentUserId($request),
            'assessor_id' => $this->currentUserId($request),
            'assessment_type' => $data['assessment_type'],
            'title' => $data['title'],
            'score' => $data['score'],
            'max_score' => 100.00,
            'result' => $data['result'],
            'notes' => $data['notes'] ?? '',
            'assessed_at' => $data['assessed_at'] ?: now(),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Riwayat assessment berhasil ditambahkan.');
    }

    public function updateAssessment(Request $request, int $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'score' => 'required|numeric|min:0|max:100',
            'result' => 'required|in:pass,fail,in_progress',
            'notes' => 'nullable|string',
        ]);

        DB::table('assessments')->where('id', $id)->update([
            'title' => $data['title'],
            'score' => $data['score'],
            'result' => $data['result'],
            'notes' => $data['notes'] ?? '',
        ]);

        return back()->with('success', 'Data assessment berhasil diperbarui.');
    }

    public function destroyAssessment(int $id)
    {
        DB::table('assessments')->where('id', $id)->delete();

        return back()->with('success', 'Data assessment berhasil dihapus.');
    }

    // =========================================================================
    // PROFIL & SERTIFIKAT CRUD
    // =========================================================================
    public function updateProfile(Request $request)
    {
        $userId = $this->currentUserId($request);
        $orgId = $this->currentOrgId($request);

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:50',
            'avatar_url' => 'nullable|string|max:500',
            'job_title' => 'nullable|string|max:150',
            'location' => 'nullable|string|max:150',
            'bio' => 'nullable|string|max:500',
            'org_name' => 'nullable|string|max:150',
        ]);

        $user = DB::table('users')->where('id', $userId)->first();
        $prefs = json_decode($user->preferences ?? '{}', true) ?: [];
        $prefs['job_title'] = $data['job_title'] ?? ($prefs['job_title'] ?? 'BIM Modeler');
        $prefs['location'] = $data['location'] ?? ($prefs['location'] ?? 'Jakarta, Indonesia');
        $prefs['bio'] = $data['bio'] ?? ($prefs['bio'] ?? '');

        DB::table('users')->where('id', $userId)->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => ($data['phone'] ?? null) ?: $user->phone,
            'avatar_url' => ($data['avatar_url'] ?? null) ?: $user->avatar_url,
            'preferences' => json_encode($prefs),
            'updated_at' => now(),
        ]);

        if (! empty($data['job_title'])) {
            DB::table('organization_users')
                ->where('user_id', $userId)
                ->where('organization_id', $orgId)
                ->update(['title' => $data['job_title'], 'updated_at' => now()]);
        }

        if (! empty($data['org_name'])) {
            DB::table('organizations')->where('id', $orgId)->update([
                'name' => $data['org_name'],
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Profil dan organisasi berhasil diperbarui.');
    }

    public function storeCertificate(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'certificate_no' => 'nullable|string|max:100',
            'issue_date' => 'required|date',
            'score' => 'nullable|numeric|min:0|max:100',
            'duration_hours' => 'nullable|integer|min:1',
            'competency_level' => 'nullable|string|max:100',
        ]);

        $orgId = $this->currentOrgId($request);
        $userId = $this->currentUserId($request);
        $certNo = $data['certificate_no'] ?: ('BN-CERT-' . date('Y') . '-' . rand(10000, 99999));

        $certId = DB::table('certificates')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $orgId,
            'user_id' => $userId,
            'certificate_no' => $certNo,
            'title' => $data['title'],
            'score' => $data['score'] ?? 90.00,
            'competency_level' => $data['competency_level'] ?: 'Intermediate',
            'issue_date' => $data['issue_date'],
            'expiry_date' => \Carbon\Carbon::parse($data['issue_date'])->addYears(3)->toDateString(),
            'verification_code' => 'VERIFY-' . strtoupper(Str::random(8)),
            'status' => 'active',
            'metadata' => json_encode([
                'duration_hours' => $data['duration_hours'] ?? 24,
                'director' => 'Dian Pratama',
                'director_title' => 'Direktur BIM Nusantara',
            ]),
            'created_at' => now(),
        ]);

        return redirect()->route('profil', ['cert_id' => $certId])->with('success', 'Sertifikat "' . $data['title'] . '" berhasil diterbitkan.');
    }

    public function updateCertificate(Request $request, int $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'certificate_no' => 'required|string|max:100',
            'issue_date' => 'required|date',
            'score' => 'nullable|numeric|min:0|max:100',
            'duration_hours' => 'nullable|integer|min:1',
            'status' => 'nullable|in:active,revoked,expired',
        ]);

        $cert = DB::table('certificates')->where('id', $id)->firstOrFail();
        $meta = json_decode($cert->metadata ?? '{}', true) ?: [];
        if (isset($data['duration_hours'])) {
            $meta['duration_hours'] = (int) $data['duration_hours'];
        }

        DB::table('certificates')->where('id', $id)->update([
            'title' => $data['title'],
            'certificate_no' => $data['certificate_no'],
            'issue_date' => $data['issue_date'],
            'score' => $data['score'] ?? $cert->score,
            'status' => $data['status'] ?? $cert->status,
            'metadata' => json_encode($meta),
        ]);

        return back()->with('success', 'Data sertifikat berhasil diperbarui.');
    }

    public function destroyCertificate(int $id)
    {
        DB::table('certificates')->where('id', $id)->delete();

        return redirect()->route('profil')->with('success', 'Sertifikat berhasil dihapus.');
    }
}

