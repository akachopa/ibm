<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $roles = [
            ['name' => 'Super Admin', 'code' => 'super_admin', 'scope' => 'platform', 'description' => 'Akses penuh platform'],
            ['name' => 'Organization Admin', 'code' => 'organization_admin', 'scope' => 'organization', 'description' => 'Admin organisasi'],
            ['name' => 'Trainer', 'code' => 'trainer', 'scope' => 'organization', 'description' => 'Trainer dan reviewer'],
            ['name' => 'Participant', 'code' => 'participant', 'scope' => 'organization', 'description' => 'Peserta pelatihan'],
            ['name' => 'Project Manager', 'code' => 'project_manager', 'scope' => 'project', 'description' => 'Manager project'],
            ['name' => 'Modeler', 'code' => 'modeler', 'scope' => 'project', 'description' => 'Editor BIM model'],
            ['name' => 'Reviewer', 'code' => 'reviewer', 'scope' => 'project', 'description' => 'Reviewer project'],
            ['name' => 'Viewer', 'code' => 'viewer', 'scope' => 'project', 'description' => 'Read only'],
        ];
        foreach ($roles as $r) {
            DB::table('roles')->updateOrInsert(['code' => $r['code']], $r);
        }

        // 2. Permissions
        $permissions = [
            ['name' => 'View Dashboard', 'code' => 'dashboard.view', 'module' => 'dashboard', 'description' => 'Melihat dashboard'],
            ['name' => 'Manage Users', 'code' => 'users.manage', 'module' => 'users', 'description' => 'Mengelola user'],
            ['name' => 'Manage Training', 'code' => 'training.manage', 'module' => 'training', 'description' => 'Mengelola program pelatihan'],
            ['name' => 'View Training', 'code' => 'training.view', 'module' => 'training', 'description' => 'Melihat pelatihan'],
            ['name' => 'Create Project', 'code' => 'projects.create', 'module' => 'project', 'description' => 'Membuat project'],
            ['name' => 'View Project', 'code' => 'projects.view', 'module' => 'project', 'description' => 'Melihat project'],
            ['name' => 'Edit Project', 'code' => 'projects.edit', 'module' => 'project', 'description' => 'Mengedit project'],
            ['name' => 'Delete Project', 'code' => 'projects.delete', 'module' => 'project', 'description' => 'Menghapus project'],
            ['name' => 'Model BIM', 'code' => 'bim.model', 'module' => 'bim_studio', 'description' => 'Membuat dan mengedit model BIM'],
            ['name' => 'Validate BIM', 'code' => 'bim.validate', 'module' => 'bim_studio', 'description' => 'Menjalankan validasi BIM'],
            ['name' => 'Submit Project', 'code' => 'project.submit', 'module' => 'submission', 'description' => 'Submit project'],
            ['name' => 'Review Project', 'code' => 'project.review', 'module' => 'review', 'description' => 'Review project'],
            ['name' => 'Manage Issues', 'code' => 'issues.manage', 'module' => 'issue', 'description' => 'Mengelola issue'],
            ['name' => 'Manage Assessment', 'code' => 'assessment.manage', 'module' => 'assessment', 'description' => 'Mengelola penilaian'],
            ['name' => 'View Competency', 'code' => 'competency.view', 'module' => 'competency', 'description' => 'Melihat kompetensi'],
            ['name' => 'Manage Certificate', 'code' => 'certificate.manage', 'module' => 'certificate', 'description' => 'Mengelola sertifikat'],
        ];
        foreach ($permissions as $p) {
            DB::table('permissions')->updateOrInsert(['code' => $p['code']], $p);
        }

        // Role permissions
        $allPermissions = DB::table('permissions')->pluck('id', 'code');
        $roleIds = DB::table('roles')->pluck('id', 'code');

        foreach ($allPermissions as $permId) {
            DB::table('role_permissions')->updateOrInsert([
                'role_id' => $roleIds['super_admin'],
                'permission_id' => $permId,
            ]);
            DB::table('role_permissions')->updateOrInsert([
                'role_id' => $roleIds['organization_admin'],
                'permission_id' => $permId,
            ]);
        }

        // 3. Element Types
        $elementTypes = [
            ['code' => 'LEVEL', 'name' => 'Level', 'ifc_class' => 'IfcBuildingStorey', 'category' => 'spatial', 'default_properties' => json_encode([])],
            ['code' => 'GRID', 'name' => 'Grid', 'ifc_class' => 'IfcGrid', 'category' => 'reference', 'default_properties' => json_encode([])],
            ['code' => 'WALL', 'name' => 'Wall', 'ifc_class' => 'IfcWall', 'category' => 'architecture', 'default_properties' => json_encode(['height' => 3000, 'thickness' => 150])],
            ['code' => 'COLUMN', 'name' => 'Column', 'ifc_class' => 'IfcColumn', 'category' => 'structure', 'default_properties' => json_encode(['width' => 300, 'depth' => 300, 'height' => 3000])],
            ['code' => 'BEAM', 'name' => 'Beam', 'ifc_class' => 'IfcBeam', 'category' => 'structure', 'default_properties' => json_encode(['width' => 250, 'height' => 400])],
            ['code' => 'SLAB', 'name' => 'Slab', 'ifc_class' => 'IfcSlab', 'category' => 'architecture', 'default_properties' => json_encode(['thickness' => 120])],
            ['code' => 'DOOR', 'name' => 'Door', 'ifc_class' => 'IfcDoor', 'category' => 'architecture', 'default_properties' => json_encode(['width' => 900, 'height' => 2100])],
            ['code' => 'WINDOW', 'name' => 'Window', 'ifc_class' => 'IfcWindow', 'category' => 'architecture', 'default_properties' => json_encode(['width' => 1200, 'height' => 1200, 'sillHeight' => 900])],
        ];
        foreach ($elementTypes as $et) {
            DB::table('element_types')->updateOrInsert(['code' => $et['code']], $et);
        }

        // 4. Global Materials
        $materials = [
            ['organization_id' => null, 'name' => 'Beton Bertulang (Concrete)', 'code' => 'MAT-CONCRETE', 'category' => 'structure', 'color_hex' => '#B8B8B8', 'opacity' => 1.000],
            ['organization_id' => null, 'name' => 'Bata Merah (Brick)', 'code' => 'MAT-BRICK', 'category' => 'architecture', 'color_hex' => '#B46A55', 'opacity' => 1.000],
            ['organization_id' => null, 'name' => 'Kaca Tempered (Glass)', 'code' => 'MAT-GLASS', 'category' => 'architecture', 'color_hex' => '#9ED7E8', 'opacity' => 0.350],
            ['organization_id' => null, 'name' => 'Baja Konstruksi (Steel)', 'code' => 'MAT-STEEL', 'category' => 'structure', 'color_hex' => '#8C949C', 'opacity' => 1.000],
            ['organization_id' => null, 'name' => 'Kayu Jati (Wood)', 'code' => 'MAT-WOOD', 'category' => 'architecture', 'color_hex' => '#A7784F', 'opacity' => 1.000],
            ['organization_id' => null, 'name' => 'Gypsum Board', 'code' => 'MAT-GYPSUM', 'category' => 'architecture', 'color_hex' => '#E6E1D8', 'opacity' => 1.000],
        ];
        foreach ($materials as $m) {
            DB::table('materials')->updateOrInsert(['code' => $m['code']], $m);
        }

        // 5. Competencies
        $competencies = [
            ['organization_id' => null, 'code' => 'BIM-FUND', 'name' => 'BIM Fundamental', 'category' => 'Fundamental', 'description' => 'Konsep dasar, manfaat, dan standar ekosistem BIM ISO 19650.', 'max_level' => 5],
            ['organization_id' => null, 'code' => 'ARCH-MODEL', 'name' => 'Architecture Modeling', 'category' => 'Modeling', 'description' => 'Pemodelan arsitektur bangunan dengan Revit dari LOD 100 hingga LOD 350.', 'max_level' => 5],
            ['organization_id' => null, 'code' => 'STRUCT-MODEL', 'name' => 'Structure Modeling', 'category' => 'Modeling', 'description' => 'Pemodelan kolom, balok, pelat lantai, dan pondasi struktur.', 'max_level' => 5],
            ['organization_id' => null, 'code' => 'MEP-MODEL', 'name' => 'MEP Modeling', 'category' => 'Modeling', 'description' => 'Pemodelan sistem Mekanikal, Elektrikal, dan Plumbing.', 'max_level' => 5],
            ['organization_id' => null, 'code' => 'BIM-COORD', 'name' => 'BIM Coordination', 'category' => 'Coordination', 'description' => 'Koordinasi multi-disiplin model BIM dan kolaborasi CDE.', 'max_level' => 5],
            ['organization_id' => null, 'code' => 'CLASH', 'name' => 'Clash Detection', 'category' => 'Coordination', 'description' => 'Deteksi dan resolusi clash antar elemen disiplin.', 'max_level' => 5],
            ['organization_id' => null, 'code' => 'BIM-MGMT', 'name' => 'BIM Management', 'category' => 'Management', 'description' => 'Penyusunan BEP, manajemen standar, dan pelaksanaan proyek BIM.', 'max_level' => 5],
        ];
        foreach ($competencies as $c) {
            DB::table('competencies')->updateOrInsert(['code' => $c['code']], $c);
        }

        // 6. Organizations
        $orgId = DB::table('organizations')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'PT. Arsitek Muda Indonesia',
            'code' => 'BIMNUS',
            'email' => 'info@arsitekmuda.co.id',
            'phone' => '081234567890',
            'address' => 'Jl. Jend. Sudirman Kav. 52-53, Jakarta Selatan, Indonesia',
            'status' => 'active',
            'settings' => json_encode(['theme' => 'property', 'default_unit' => 'mm', 'brand' => 'BIM Nusantara']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 7. Users
        $password = Hash::make('password123');
        $adminId = DB::table('users')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'Admin BIM Nusantara',
            'email' => 'admin@bimnusantara.local',
            'phone' => '081200000001',
            'password' => $password,
            'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80',
            'status' => 'active',
            'email_verified_at' => now(),
            'preferences' => json_encode(['bio' => 'Administrator Utama Platform BIM Nusantara', 'location' => 'Jakarta, Indonesia', 'role_label' => 'Platform Admin']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $trainerId = DB::table('users')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'Budi Santoso',
            'email' => 'trainer@bimnusantara.local',
            'phone' => '081200000002',
            'password' => $password,
            'avatar_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=300&q=80',
            'status' => 'active',
            'email_verified_at' => now(),
            'preferences' => json_encode(['bio' => 'Mentor Arsitektur & Struktur BIM Bersertifikasi', 'location' => 'Bandung, Indonesia', 'role_label' => 'BIM Trainer']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $participantId = DB::table('users')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'Andi Dwiputra',
            'email' => 'andi.dwiputra@arsitekmuda.co.id',
            'phone' => '081200000003',
            'password' => $password,
            'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80',
            'status' => 'active',
            'email_verified_at' => now(),
            'preferences' => json_encode([
                'bio' => 'Terus belajar dan membangun karya yang berdampak melalui BIM.',
                'location' => 'Jakarta, Indonesia',
                'job_title' => 'BIM Modeler',
                'role_label' => 'Peserta',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('organization_users')->insert([
            ['organization_id' => $orgId, 'user_id' => $adminId, 'role_id' => $roleIds['organization_admin'], 'employee_code' => 'EMP-001', 'title' => 'Administrator', 'joined_at' => now()->subMonths(12)->toDateString(), 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['organization_id' => $orgId, 'user_id' => $trainerId, 'role_id' => $roleIds['trainer'], 'employee_code' => 'EMP-002', 'title' => 'Senior BIM Trainer', 'joined_at' => now()->subMonths(10)->toDateString(), 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['organization_id' => $orgId, 'user_id' => $participantId, 'role_id' => $roleIds['participant'], 'employee_code' => 'EMP-018', 'title' => 'BIM Modeler', 'joined_at' => now()->subMonths(4)->toDateString(), 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 8. Training Program, Modules, Lessons
        $programId = DB::table('training_programs')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $orgId,
            'title' => 'Menjadi BIM Professional - Pemodelan Arsitektur BIM',
            'code' => 'BIM-ARCH-001',
            'description' => 'Ikuti jalur pembelajaran terstruktur dari dasar hingga siap mengaplikasikan BIM di proyek nyata.',
            'level' => 'intermediate',
            'status' => 'published',
            'estimated_minutes' => 1440,
            'passing_score' => 75.00,
            'created_by' => $adminId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mod1 = DB::table('training_modules')->insertGetId([
            'training_program_id' => $programId,
            'title' => 'Dasar-dasar BIM',
            'description' => 'Pengenalan konsep, manfaat, dan ekosistem BIM.',
            'sort_order' => 1,
            'estimated_minutes' => 180,
            'is_required' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mod2 = DB::table('training_modules')->insertGetId([
            'training_program_id' => $programId,
            'title' => 'Pemodelan Arsitektur dengan Revit',
            'description' => 'Pemodelan bangunan dengan Revit untuk arsitektur.',
            'sort_order' => 2,
            'estimated_minutes' => 480,
            'is_required' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mod3 = DB::table('training_modules')->insertGetId([
            'training_program_id' => $programId,
            'title' => 'Pemodelan Struktur',
            'description' => 'Pemodelan struktur dan analisis dengan Revit.',
            'sort_order' => 3,
            'estimated_minutes' => 360,
            'is_required' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mod4 = DB::table('training_modules')->insertGetId([
            'training_program_id' => $programId,
            'title' => 'Koordinasi dan Kolaborasi BIM',
            'description' => 'Clash detection, koordinasi model, BIM 360 / ACC.',
            'sort_order' => 4,
            'estimated_minutes' => 360,
            'is_required' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mod5 = DB::table('training_modules')->insertGetId([
            'training_program_id' => $programId,
            'title' => 'BIM untuk Konstruksi',
            'description' => '4D/5D, quantity takeoff, dan integrasi konstruksi.',
            'sort_order' => 5,
            'estimated_minutes' => 420,
            'is_required' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $lessonsData = [
            ['training_module_id' => $mod1, 'title' => 'Pengenalan Konsep & Ekosistem BIM', 'lesson_type' => 'video', 'content' => 'Memahami definisi Building Information Modeling (BIM), dimensi BIM (3D-7D), serta standar ISO 19650.', 'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'sort_order' => 1, 'estimated_minutes' => 25],
            ['training_module_id' => $mod1, 'title' => 'Workflow BIM & Common Data Environment (CDE)', 'lesson_type' => 'article', 'content' => 'Alur kerja kolaborasi proyek konstruksi modern menggunakan CDE dan pembagian informasi model.', 'video_url' => null, 'sort_order' => 2, 'estimated_minutes' => 35],
            ['training_module_id' => $mod2, 'title' => 'Pengenalan Interface Revit 2024', 'lesson_type' => 'video', 'content' => 'Pelajari alur kerja pemodelan arsitektur dari konsep hingga dokumentasi menggunakan Autodesk Revit.', 'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'sort_order' => 1, 'estimated_minutes' => 15],
            ['training_module_id' => $mod2, 'title' => 'Membuat Level dan Grid Bangunan', 'lesson_type' => 'lab', 'content' => 'Praktik langsung membuat datum Level lantai dan Grid as bangunan pada proyek rumah tinggal.', 'video_url' => null, 'sort_order' => 2, 'estimated_minutes' => 45],
            ['training_module_id' => $mod2, 'title' => 'Membuat Family Pintu dan Jendela', 'lesson_type' => 'video', 'content' => 'Cara membuat dan mengatur parameter family pintu serta jendela arsitektural.', 'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'sort_order' => 3, 'estimated_minutes' => 22],
            ['training_module_id' => $mod2, 'title' => 'Pemodelan Dinding, Lantai & Atap', 'lesson_type' => 'lab', 'content' => 'Latihan memodelkan elemen arsitektur utama dengan ketebalan dan material berlapis.', 'video_url' => null, 'sort_order' => 4, 'estimated_minutes' => 60],
            ['training_module_id' => $mod3, 'title' => 'Pemodelan Kolom & Balok Struktur', 'lesson_type' => 'video', 'content' => 'Teknik penempatan kolom struktural pada perpotongan grid dan sistem balok induk/anak.', 'video_url' => null, 'sort_order' => 1, 'estimated_minutes' => 30],
            ['training_module_id' => $mod4, 'title' => 'Clash Detection dengan Navisworks', 'lesson_type' => 'video', 'content' => 'Menjalankan deteksi benturan antar elemen Arsitektur, Struktur, dan MEP secara otomatis.', 'video_url' => null, 'sort_order' => 1, 'estimated_minutes' => 18],
            ['training_module_id' => $mod5, 'title' => 'Workflow 4D dengan Synchro', 'lesson_type' => 'video', 'content' => 'Menghubungkan jadwal proyek konstruksi dengan elemen model 3D untuk simulasi 4D.', 'video_url' => null, 'sort_order' => 1, 'estimated_minutes' => 24],
        ];

        $lessonIds = [];
        foreach ($lessonsData as $ld) {
            $lessonIds[] = DB::table('lessons')->insertGetId(array_merge($ld, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Training Classes
        $class1Id = DB::table('training_classes')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $orgId,
            'training_program_id' => $programId,
            'name' => 'Live Class: Pemodelan Arsitektur Lanjutan',
            'code' => 'BIM-ARCH-B01',
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'trainer_id' => $trainerId,
            'status' => 'running',
            'settings' => json_encode(['time' => '19:00 - 21:00 WIB', 'platform' => 'Online (Zoom)', 'badge' => 'Akan Datang', 'date_label' => 'APR 28']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $class2Id = DB::table('training_classes')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $orgId,
            'training_program_id' => $programId,
            'name' => 'Workshop: Family & Parameter',
            'code' => 'BIM-ARCH-B02',
            'start_date' => now()->addDays(4)->toDateString(),
            'end_date' => now()->addDays(35)->toDateString(),
            'trainer_id' => $trainerId,
            'status' => 'open',
            'settings' => json_encode(['time' => '19:00 - 21:00 WIB', 'platform' => 'Online (Zoom)', 'badge' => 'Terdaftar', 'date_label' => 'APR 30']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $class3Id = DB::table('training_classes')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $orgId,
            'training_program_id' => $programId,
            'name' => 'Q&A Session: Koordinasi BIM',
            'code' => 'BIM-COORD-B01',
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => now()->addDays(40)->toDateString(),
            'trainer_id' => $trainerId,
            'status' => 'open',
            'settings' => json_encode(['time' => '14:00 - 15:30 WIB', 'platform' => 'Online (Zoom)', 'badge' => 'Terbuka', 'date_label' => 'MEI 03']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('training_enrollments')->insert([
            ['training_class_id' => $class1Id, 'user_id' => $participantId, 'status' => 'active', 'progress_percent' => 62.00, 'final_score' => 92.00, 'enrolled_at' => now()->subDays(16), 'completed_at' => null],
            ['training_class_id' => $class2Id, 'user_id' => $participantId, 'status' => 'completed', 'progress_percent' => 100.00, 'final_score' => 88.00, 'enrolled_at' => now()->subDays(20), 'completed_at' => now()->subDays(2)],
            ['training_class_id' => $class3Id, 'user_id' => $participantId, 'status' => 'completed', 'progress_percent' => 100.00, 'final_score' => 90.00, 'enrolled_at' => now()->subDays(30), 'completed_at' => now()->subDays(5)],
        ]);

        // Lesson progress
        foreach ([$lessonIds[0], $lessonIds[1], $lessonIds[2], $lessonIds[3]] as $lid) {
            DB::table('lesson_progress')->insert([
                'user_id' => $participantId,
                'lesson_id' => $lid,
                'training_class_id' => $class1Id,
                'status' => 'completed',
                'progress_percent' => 100.00,
                'started_at' => now()->subDays(5),
                'completed_at' => now()->subDays(2),
                'last_position_seconds' => 900,
            ]);
        }
        DB::table('lesson_progress')->insert([
            'user_id' => $participantId,
            'lesson_id' => $lessonIds[4],
            'training_class_id' => $class1Id,
            'status' => 'in_progress',
            'progress_percent' => 60.00,
            'started_at' => now()->subDay(),
            'completed_at' => null,
            'last_position_seconds' => 450,
        ]);

        // 9. Quiz & Questions
        $quizId = DB::table('quizzes')->insertGetId([
            'lesson_id' => $lessonIds[2],
            'organization_id' => $orgId,
            'title' => 'Quiz Modul 2 - Pemodelan Arsitektur',
            'passing_score' => 70.00,
            'time_limit_minutes' => 15,
            'attempt_limit' => 5,
            'randomize_questions' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $q1 = DB::table('quiz_questions')->insertGetId([
            'quiz_id' => $quizId,
            'question_type' => 'single_choice',
            'question_text' => 'Elemen IFC yang merepresentasikan dinding struktural maupun arsitektural pada BIM adalah?',
            'points' => 25.00,
            'explanation' => 'IfcWall digunakan untuk semua elemen dinding pada skema IFC.',
            'sort_order' => 1,
        ]);
        DB::table('quiz_options')->insert([
            ['question_id' => $q1, 'option_text' => 'IfcWall / IfcWallStandardCase', 'is_correct' => true, 'sort_order' => 1],
            ['question_id' => $q1, 'option_text' => 'IfcSlab', 'is_correct' => false, 'sort_order' => 2],
            ['question_id' => $q1, 'option_text' => 'IfcBuildingStorey', 'is_correct' => false, 'sort_order' => 3],
            ['question_id' => $q1, 'option_text' => 'IfcColumn', 'is_correct' => false, 'sort_order' => 4],
        ]);

        $q2 = DB::table('quiz_questions')->insertGetId([
            'quiz_id' => $quizId,
            'question_type' => 'single_choice',
            'question_text' => 'Pada pemodelan BIM, elemen Pintu (Door) dan Jendela (Window) wajib memiliki relasi host terhadap?',
            'points' => 25.00,
            'explanation' => 'Opening seperti pintu dan jendela membutuhkan elemen Wall sebagai host.',
            'sort_order' => 2,
        ]);
        DB::table('quiz_options')->insert([
            ['question_id' => $q2, 'option_text' => 'Elemen Dinding (Wall)', 'is_correct' => true, 'sort_order' => 1],
            ['question_id' => $q2, 'option_text' => 'Elemen Grid', 'is_correct' => false, 'sort_order' => 2],
            ['question_id' => $q2, 'option_text' => 'Elemen Kolom (Column)', 'is_correct' => false, 'sort_order' => 3],
            ['question_id' => $q2, 'option_text' => 'Tidak membutuhkan host', 'is_correct' => false, 'sort_order' => 4],
        ]);

        $q3 = DB::table('quiz_questions')->insertGetId([
            'quiz_id' => $quizId,
            'question_type' => 'single_choice',
            'question_text' => 'Shortcut keyboard standar di Autodesk Revit untuk membuat dinding (Wall) adalah?',
            'points' => 25.00,
            'explanation' => 'WA adalah shortcut standar untuk Wall di Revit.',
            'sort_order' => 3,
        ]);
        DB::table('quiz_options')->insert([
            ['question_id' => $q3, 'option_text' => 'WA (Wall)', 'is_correct' => true, 'sort_order' => 1],
            ['question_id' => $q3, 'option_text' => 'DR (Door)', 'is_correct' => false, 'sort_order' => 2],
            ['question_id' => $q3, 'option_text' => 'WN (Window)', 'is_correct' => false, 'sort_order' => 3],
            ['question_id' => $q3, 'option_text' => 'LL (Level)', 'is_correct' => false, 'sort_order' => 4],
        ]);

        $q4 = DB::table('quiz_questions')->insertGetId([
            'quiz_id' => $quizId,
            'question_type' => 'single_choice',
            'question_text' => 'Level of Development (LOD) yang menunjukkan model siap untuk koordinasi detail dan fabrikasi awal adalah?',
            'points' => 25.00,
            'explanation' => 'LOD 350 mencakup antarmuka koneksi antar sistem bangunan.',
            'sort_order' => 4,
        ]);
        DB::table('quiz_options')->insert([
            ['question_id' => $q4, 'option_text' => 'LOD 350', 'is_correct' => true, 'sort_order' => 1],
            ['question_id' => $q4, 'option_text' => 'LOD 100', 'is_correct' => false, 'sort_order' => 2],
            ['question_id' => $q4, 'option_text' => 'LOD 200', 'is_correct' => false, 'sort_order' => 3],
            ['question_id' => $q4, 'option_text' => 'LOD 500', 'is_correct' => false, 'sort_order' => 4],
        ]);

        DB::table('quiz_attempts')->insert([
            'quiz_id' => $quizId,
            'user_id' => $participantId,
            'training_class_id' => $class1Id,
            'attempt_no' => 1,
            'score' => 85.00,
            'status' => 'graded',
            'started_at' => now()->subDays(3),
            'submitted_at' => now()->subDays(3)->addMinutes(11),
        ]);

        // 10. Projects
        $project1Id = DB::table('projects')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $orgId,
            'training_class_id' => $class1Id,
            'owner_id' => $participantId,
            'title' => 'Rumah Tinggal 2 Lantai',
            'code' => 'PRJ-001',
            'description' => 'Pemodelan arsitektur lengkap rumah tinggal 2 lantai beserta koordinasi struktur dan MEP sederhana.',
            'category' => 'training',
            'status' => 'under_review',
            'unit' => 'mm',
            'schema_version' => '1.0.0',
            'progress_percent' => 72.00,
            'start_date' => '2024-03-20',
            'deadline' => '2026-11-10',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80',
            'settings' => json_encode([
                'snap' => true,
                'gridSize' => 1000,
                'ortho' => true,
                'building_type' => 'Residential',
                'software' => 'Revit',
                'tasks_done' => 12,
                'tasks_total' => 18,
                'team_size' => 4,
                'area_m2' => 240,
                'floors' => 2,
                'lod' => 'LOD 300',
                'stages' => [
                    ['name' => 'Konsep & Studi Awal', 'progress' => 100],
                    ['name' => 'Pemodelan Arsitektur', 'progress' => 100],
                    ['name' => 'Koordinasi Struktur', 'progress' => 70],
                    ['name' => 'Koordinasi MEP', 'progress' => 0],
                    ['name' => 'Finalisasi & Submission', 'progress' => 0],
                ],
            ]),
            'created_at' => now()->subDays(25),
            'updated_at' => now(),
        ]);

        $project2Id = DB::table('projects')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $orgId,
            'training_class_id' => $class1Id,
            'owner_id' => $participantId,
            'title' => 'Gedung Kantor 4 Lantai',
            'code' => 'PRJ-002',
            'description' => 'Pemodelan gedung perkantoran 4 lantai dengan BIM koordinasi multi-disiplin.',
            'category' => 'simulation',
            'status' => 'revision_required',
            'unit' => 'mm',
            'schema_version' => '1.0.0',
            'progress_percent' => 45.00,
            'start_date' => '2024-04-01',
            'deadline' => '2026-11-25',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80',
            'settings' => json_encode([
                'snap' => true,
                'gridSize' => 1000,
                'ortho' => true,
                'building_type' => 'Commercial',
                'software' => 'Revit',
                'tasks_done' => 8,
                'tasks_total' => 18,
                'team_size' => 5,
                'area_m2' => 1450,
                'floors' => 4,
                'lod' => 'LOD 300',
                'stages' => [
                    ['name' => 'Konsep & Studi Awal', 'progress' => 100],
                    ['name' => 'Pemodelan Arsitektur', 'progress' => 80],
                    ['name' => 'Koordinasi Struktur', 'progress' => 45],
                    ['name' => 'Koordinasi MEP', 'progress' => 0],
                    ['name' => 'Finalisasi & Submission', 'progress' => 0],
                ],
            ]),
            'created_at' => now()->subDays(18),
            'updated_at' => now(),
        ]);

        $project3Id = DB::table('projects')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $orgId,
            'training_class_id' => $class1Id,
            'owner_id' => $participantId,
            'title' => 'Mixed-Use Building – Sudirman',
            'code' => 'PRJ-003',
            'description' => 'Pemodelan kompleks bangunan mixed-use dengan tower, podium, dan area komersial.',
            'category' => 'portfolio',
            'status' => 'approved',
            'unit' => 'm',
            'schema_version' => '1.0.0',
            'progress_percent' => 100.00,
            'start_date' => '2024-02-10',
            'deadline' => '2026-11-18',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80',
            'settings' => json_encode([
                'snap' => true,
                'gridSize' => 1000,
                'ortho' => true,
                'building_type' => 'Mixed-Use',
                'software' => 'Revit',
                'tasks_done' => 20,
                'tasks_total' => 20,
                'team_size' => 6,
                'area_m2' => 2450,
                'floors' => 12,
                'lod' => 'LOD 350',
                'stages' => [
                    ['name' => 'Konsep & Studi Awal', 'progress' => 100],
                    ['name' => 'Pemodelan Arsitektur', 'progress' => 100],
                    ['name' => 'Koordinasi Struktur', 'progress' => 100],
                    ['name' => 'Koordinasi MEP', 'progress' => 100],
                    ['name' => 'Finalisasi & Submission', 'progress' => 100],
                ],
            ]),
            'created_at' => now()->subDays(40),
            'updated_at' => now(),
        ]);

        foreach ([$project1Id, $project2Id, $project3Id] as $pid) {
            DB::table('project_members')->insert([
                ['project_id' => $pid, 'user_id' => $participantId, 'project_role' => 'owner', 'can_edit' => true, 'can_review' => false, 'joined_at' => now()],
                ['project_id' => $pid, 'user_id' => $trainerId, 'project_role' => 'trainer', 'can_edit' => false, 'can_review' => true, 'joined_at' => now()],
                ['project_id' => $pid, 'user_id' => $adminId, 'project_role' => 'manager', 'can_edit' => true, 'can_review' => true, 'joined_at' => now()],
            ]);
        }

        // Levels & Grids for Project 1 and Project 3
        $level1 = DB::table('project_levels')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'project_id' => $project1Id,
            'name' => 'Level 1 - Ground Floor',
            'elevation_mm' => 0,
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $level2 = DB::table('project_levels')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'project_id' => $project1Id,
            'name' => 'Level 2 - Lantai 2',
            'elevation_mm' => 3500,
            'sort_order' => 2,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $level3 = DB::table('project_levels')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'project_id' => $project1Id,
            'name' => 'Level 3 - Roof',
            'elevation_mm' => 7000,
            'sort_order' => 3,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([
            ['axis' => 'X', 'label' => 'A', 'position_mm' => 0],
            ['axis' => 'X', 'label' => 'B', 'position_mm' => 4000],
            ['axis' => 'X', 'label' => 'C', 'position_mm' => 8000],
            ['axis' => 'Y', 'label' => '1', 'position_mm' => 0],
            ['axis' => 'Y', 'label' => '2', 'position_mm' => 5000],
            ['axis' => 'Y', 'label' => '3', 'position_mm' => 10000],
        ] as $g) {
            DB::table('project_grids')->insert(array_merge($g, [
                'uuid' => (string) Str::uuid(),
                'project_id' => $project1Id,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Also seed levels for Project 2 and Project 3
        foreach ([$project2Id, $project3Id] as $pid) {
            DB::table('project_levels')->insert([
                ['uuid' => (string) Str::uuid(), 'project_id' => $pid, 'name' => 'Level 1 - Ground Floor', 'elevation_mm' => 0, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['uuid' => (string) Str::uuid(), 'project_id' => $pid, 'name' => 'Level 2 - Lantai 2', 'elevation_mm' => 4000, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['uuid' => (string) Str::uuid(), 'project_id' => $pid, 'name' => 'Level 3 - Roof', 'elevation_mm' => 8000, 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $typeMap = DB::table('element_types')->pluck('id', 'code');
        $matMap = DB::table('materials')->pluck('id', 'code');

        // Project 1 Elements
        $wall1Id = DB::table('project_elements')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'project_id' => $project1Id,
            'level_id' => $level1,
            'element_type_id' => $typeMap['WALL'],
            'material_id' => $matMap['MAT-CONCRETE'],
            'name' => 'Basic Wall - 200mm (Utara)',
            'code' => 'W-001',
            'ifc_class' => 'IfcWallStandardCase',
            'geometry' => json_encode(['start' => ['x' => 0, 'y' => 0], 'end' => ['x' => 8000, 'y' => 0], 'length' => 5200, 'height' => 3000, 'thickness' => 200, 'baseOffset' => 0]),
            'transform_data' => json_encode(['position' => [0, 0, 0], 'rotation' => [0, 0, 0], 'scale' => [1, 1, 1]]),
            'properties' => json_encode(['wallType' => 'Basic Wall', 'topConstraint' => 'Level 2 - Lantai 2', 'finishing' => 'Cat Dinding - Putih', 'structural' => false]),
            'created_by' => $participantId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $elementsBatch = [
            [
                'uuid' => (string) Str::uuid(),
                'project_id' => $project1Id,
                'level_id' => $level1,
                'parent_element_id' => null,
                'element_type_id' => $typeMap['WALL'],
                'material_id' => $matMap['MAT-BRICK'],
                'name' => 'Basic Wall - 150mm (Timur)',
                'code' => 'W-002',
                'ifc_class' => 'IfcWallStandardCase',
                'geometry' => json_encode(['start' => ['x' => 8000, 'y' => 0], 'end' => ['x' => 8000, 'y' => 10000], 'length' => 10000, 'height' => 3000, 'thickness' => 150, 'baseOffset' => 0]),
                'transform_data' => json_encode(['position' => [8, 0, 0], 'rotation' => [0, 90, 0], 'scale' => [1, 1, 1]]),
                'properties' => json_encode(['wallType' => 'Basic Wall', 'topConstraint' => 'Level 2 - Lantai 2', 'finishing' => 'Plester + Cat Putih', 'structural' => false]),
                'created_by' => $participantId,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'project_id' => $project1Id,
                'level_id' => $level1,
                'parent_element_id' => null,
                'element_type_id' => $typeMap['WALL'],
                'material_id' => $matMap['MAT-CONCRETE'],
                'name' => 'Basic Wall - 300mm (Selatan)',
                'code' => 'W-003',
                'ifc_class' => 'IfcWallStandardCase',
                'geometry' => json_encode(['start' => ['x' => 8000, 'y' => 10000], 'end' => ['x' => 0, 'y' => 10000], 'length' => 8000, 'height' => 3000, 'thickness' => 300, 'baseOffset' => 0]),
                'transform_data' => json_encode(['position' => [0, 10, 0], 'rotation' => [0, 0, 0], 'scale' => [1, 1, 1]]),
                'properties' => json_encode(['wallType' => 'Structural Wall', 'topConstraint' => 'Level 2 - Lantai 2', 'finishing' => 'Expose Concrete', 'structural' => true]),
                'created_by' => $participantId,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'project_id' => $project1Id,
                'level_id' => $level1,
                'parent_element_id' => null,
                'element_type_id' => $typeMap['WALL'],
                'material_id' => $matMap['MAT-BRICK'],
                'name' => 'Basic Wall - 150mm (Barat)',
                'code' => 'W-004',
                'ifc_class' => 'IfcWallStandardCase',
                'geometry' => json_encode(['start' => ['x' => 0, 'y' => 10000], 'end' => ['x' => 0, 'y' => 0], 'length' => 10000, 'height' => 3000, 'thickness' => 150, 'baseOffset' => 0]),
                'transform_data' => json_encode(['position' => [0, 0, 0], 'rotation' => [0, 90, 0], 'scale' => [1, 1, 1]]),
                'properties' => json_encode(['wallType' => 'Basic Wall', 'topConstraint' => 'Level 2 - Lantai 2', 'finishing' => 'Cat Dinding - Krem', 'structural' => false]),
                'created_by' => $participantId,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'project_id' => $project1Id,
                'level_id' => $level1,
                'parent_element_id' => null,
                'element_type_id' => $typeMap['COLUMN'],
                'material_id' => $matMap['MAT-CONCRETE'],
                'name' => 'Column K1 - 300x300 (A1)',
                'code' => 'C-001',
                'ifc_class' => 'IfcColumn',
                'geometry' => json_encode(['width' => 300, 'depth' => 300, 'height' => 3500, 'length' => 300, 'thickness' => 300, 'baseOffset' => 0]),
                'transform_data' => json_encode(['position' => [0, 0, 0], 'rotation' => [0, 0, 0], 'scale' => [1, 1, 1]]),
                'properties' => json_encode(['columnType' => 'Concrete Rectangular', 'topConstraint' => 'Level 2 - Lantai 2', 'finishing' => 'Beton Ekspos']),
                'created_by' => $participantId,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'project_id' => $project1Id,
                'level_id' => $level1,
                'parent_element_id' => null,
                'element_type_id' => $typeMap['COLUMN'],
                'material_id' => $matMap['MAT-CONCRETE'],
                'name' => 'Column K1 - 300x300 (C1)',
                'code' => 'C-002',
                'ifc_class' => 'IfcColumn',
                'geometry' => json_encode(['width' => 300, 'depth' => 300, 'height' => 3500, 'length' => 300, 'thickness' => 300, 'baseOffset' => 0]),
                'transform_data' => json_encode(['position' => [8000, 0, 0], 'rotation' => [0, 0, 0], 'scale' => [1, 1, 1]]),
                'properties' => json_encode(['columnType' => 'Concrete Rectangular', 'topConstraint' => 'Level 2 - Lantai 2', 'finishing' => 'Beton Ekspos']),
                'created_by' => $participantId,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'project_id' => $project1Id,
                'level_id' => $level2,
                'parent_element_id' => null,
                'element_type_id' => $typeMap['BEAM'],
                'material_id' => $matMap['MAT-CONCRETE'],
                'name' => 'Beam B1 - 250x400',
                'code' => 'B-001',
                'ifc_class' => 'IfcBeam',
                'geometry' => json_encode(['start' => ['x' => 0, 'y' => 0], 'end' => ['x' => 8000, 'y' => 0], 'length' => 8000, 'height' => 400, 'thickness' => 250]),
                'transform_data' => json_encode(['position' => [0, 0, 3500], 'rotation' => [0, 0, 0], 'scale' => [1, 1, 1]]),
                'properties' => json_encode(['beamType' => 'Concrete Beam 250x400', 'topConstraint' => 'Level 2 - Lantai 2', 'finishing' => 'Beton Bertulang']),
                'created_by' => $participantId,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'project_id' => $project1Id,
                'level_id' => $level1,
                'parent_element_id' => null,
                'element_type_id' => $typeMap['SLAB'],
                'material_id' => $matMap['MAT-CONCRETE'],
                'name' => 'Floor Slab Level 1 - 120mm',
                'code' => 'S-001',
                'ifc_class' => 'IfcSlab',
                'geometry' => json_encode([
                    'polygon' => [['x' => 0, 'y' => 0], ['x' => 8000, 'y' => 0], ['x' => 8000, 'y' => 10000], ['x' => 0, 'y' => 10000]],
                    'length' => 10000,
                    'height' => 8000,
                    'thickness' => 120,
                    'baseOffset' => 0,
                ]),
                'transform_data' => json_encode(['position' => [0, 0, 0], 'rotation' => [0, 0, 0], 'scale' => [1, 1, 1]]),
                'properties' => json_encode(['slabType' => 'Structural Floor 120mm', 'topConstraint' => 'Level 1 - Ground Floor', 'finishing' => 'Homogenous Tile 60x60']),
                'created_by' => $participantId,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'project_id' => $project1Id,
                'level_id' => $level1,
                'parent_element_id' => $wall1Id,
                'element_type_id' => $typeMap['DOOR'],
                'material_id' => $matMap['MAT-WOOD'],
                'name' => 'Single Flush Door - 900x2100mm',
                'code' => 'D-001',
                'ifc_class' => 'IfcDoor',
                'geometry' => json_encode(['offset' => 2500, 'length' => 900, 'width' => 900, 'height' => 2100, 'thickness' => 50, 'sillHeight' => 0]),
                'transform_data' => json_encode(['position' => [2500, 0, 0], 'rotation' => [0, 0, 0], 'scale' => [1, 1, 1]]),
                'properties' => json_encode(['doorType' => 'Single Flush Wood', 'topConstraint' => 'Level 1 - Ground Floor', 'finishing' => 'Pelitur Jati']),
                'created_by' => $participantId,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'project_id' => $project1Id,
                'level_id' => $level1,
                'parent_element_id' => $wall1Id,
                'element_type_id' => $typeMap['WINDOW'],
                'material_id' => $matMap['MAT-GLASS'],
                'name' => 'Double Casement Window - 1200x1200mm',
                'code' => 'WN-001',
                'ifc_class' => 'IfcWindow',
                'geometry' => json_encode(['offset' => 5200, 'length' => 1200, 'width' => 1200, 'height' => 1200, 'thickness' => 20, 'sillHeight' => 900]),
                'transform_data' => json_encode(['position' => [5200, 0, 900], 'rotation' => [0, 0, 0], 'scale' => [1, 1, 1]]),
                'properties' => json_encode(['windowType' => 'Aluminium Casement', 'topConstraint' => 'Level 1 - Ground Floor', 'finishing' => 'Clear Tempered Glass']),
                'created_by' => $participantId,
            ],
        ];

        foreach ($elementsBatch as $el) {
            DB::table('project_elements')->insert(array_merge($el, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Version snapshot
        $version1Id = DB::table('project_versions')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'project_id' => $project1Id,
            'version_no' => 1,
            'label' => 'v1.0 - Model Arsitektur & Struktur Dasar',
            'version_type' => 'submission',
            'snapshot' => json_encode(['schemaVersion' => '1.0.0', 'projectId' => $project1Id, 'elementCount' => 10]),
            'notes' => 'Pengumpulan model tahap koordinasi arsitektur & struktur.',
            'created_by' => $participantId,
            'created_at' => now()->subDays(2),
        ]);

        // 11. BIM Lab, Challenges & Rules
        $labId = DB::table('bim_labs')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $orgId,
            'lesson_id' => $lessonIds[3],
            'title' => 'Architecture Modeling - Rumah Tinggal 2 Lantai',
            'description' => 'Pelajari alur lengkap pemodelan arsitektur rumah tinggal 2 lantai dari persiapan project hingga menghasilkan model siap presentasi.',
            'difficulty' => 'beginner',
            'estimated_minutes' => 240,
            'template_project_id' => $project1Id,
            'instructions' => json_encode([
                [
                    'step' => 1,
                    'title' => 'Persiapan Project',
                    'summary' => 'Membuat project baru dan mengatur template, unit, serta lokasi file.',
                    'description' => 'Pada tahap ini Anda akan membuat project baru, memilih template yang tepat, mengatur unit proyek, dan menyiapkan folder kerja.',
                    'completed' => true,
                    'viewport_title' => 'Autodesk Revit - RT_2Lantai - 3D View: {3D}',
                    'viewport_levels' => 'Atap +7.20 | Lantai 2 +3.60 | Lantai 1 +0.00',
                    'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
                    'steps_list' => [
                        'Buka Autodesk Revit dan pilih template Architectural.rte',
                        'Simpan project dengan nama RT_2Lantai_Anda.rvt',
                        'Atur unit proyek ke meter (m) atau millimeter (mm)',
                        'Periksa pengaturan lokasi file dan folder kerja',
                        'Simpan dan pastikan project siap untuk dimulai',
                    ],
                    'checklist' => [
                        ['label' => 'Project baru berhasil dibuat', 'done' => true],
                        ['label' => 'Template arsitektur dipilih', 'done' => true],
                        ['label' => 'Unit proyek diatur dengan benar', 'done' => true],
                        ['label' => 'File disimpan dengan nama yang benar', 'done' => true],
                        ['label' => 'Folder kerja sudah tersusun', 'done' => true],
                    ],
                    'tips' => [
                        'Gunakan template Architectural.rte untuk pemodelan arsitektur.',
                        'Pastikan unit proyek sesuai agar dimensi akurat.',
                        'Buat folder terpisah untuk file latihan dan hasil akhir.',
                        'Simpan project secara berkala (Ctrl + S).',
                    ],
                ],
                [
                    'step' => 2,
                    'title' => 'Membuat Level',
                    'summary' => 'Membuat level lantai 1, lantai 2 dan level atap.',
                    'description' => 'Tentukan elevasi vertikal bangunan dengan membuat Level 1 (+0.00), Level 2 (+3.50), dan Roof (+7.00).',
                    'completed' => true,
                    'viewport_title' => 'Autodesk Revit - RT_2Lantai - Elevation: South',
                    'viewport_levels' => 'Roof +7.00 | Level 2 +3.50 | Level 1 +0.00',
                    'image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80',
                    'steps_list' => [
                        'Buka tampak selatan (South Elevation) pada Project Browser',
                        'Gunakan perintah Level (LL) untuk menambah Level baru',
                        'Atur elevasi Level 1 = 0 mm, Level 2 = 3500 mm, dan Roof = 7000 mm',
                        'Pastikan opsi Make Plan View aktif untuk setiap level',
                    ],
                    'checklist' => [
                        ['label' => 'Level 1 - Ground Floor (0 mm) tersedia', 'done' => true],
                        ['label' => 'Level 2 - Lantai 2 (3500 mm) tersedia', 'done' => true],
                        ['label' => 'Level Roof (7000 mm) tersedia', 'done' => true],
                    ],
                    'tips' => [
                        'Gunakan fitur Rename Level agar nama Floor Plan otomatis tersinkronisasi.',
                        'Kunci (Pin) Level dasar agar tidak bergeser secara tidak sengaja.',
                    ],
                ],
                [
                    'step' => 3,
                    'title' => 'Membuat Grid',
                    'summary' => 'Membuat grid sebagai acuan pemodelan.',
                    'description' => 'Buat garis as bangunan (Grid X: A, B, C dan Grid Y: 1, 2, 3) sebagai referensi penempatan kolom dan dinding.',
                    'completed' => true,
                    'viewport_title' => 'Autodesk Revit - RT_2Lantai - Floor Plan: Level 1',
                    'viewport_levels' => 'Grid X: A - B - C | Grid Y: 1 - 2 - 3',
                    'image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1200&q=80',
                    'steps_list' => [
                        'Buka Floor Plan Level 1',
                        'Klik perintah Grid (GR) pada tab Architecture',
                        'Buat Grid vertikal A, B, C dengan jarak masing-masing 4000 mm',
                        'Buat Grid horizontal 1, 2, 3 dengan jarak masing-masing 5000 mm',
                    ],
                    'checklist' => [
                        ['label' => 'Grid vertikal A, B, C selesai dibuat', 'done' => true],
                        ['label' => 'Grid horizontal 1, 2, 3 selesai dibuat', 'done' => true],
                        ['label' => 'Dimensi antar grid telah diverifikasi', 'done' => true],
                    ],
                    'tips' => [
                        'Beri penomoran huruf untuk satu arah dan angka untuk arah tegak lurusnya.',
                        'Gunakan Equal Dimension (EQ) bila jarak antar bentang seragam.',
                    ],
                ],
                [
                    'step' => 4,
                    'title' => 'Modeling Dinding',
                    'summary' => 'Membuat dan mengatur dinding eksterior dan interior.',
                    'description' => 'Modelkan dinding keliling eksterior dan dinding partisi interior menggunakan Basic Wall 150mm & 200mm.',
                    'completed' => false,
                    'viewport_title' => 'Autodesk Revit - RT_2Lantai - 3D Walls View',
                    'viewport_levels' => 'Base: Level 1 | Top Constraint: Level 2',
                    'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
                    'steps_list' => [
                        'Pilih tool Wall: Architectural (WA)',
                        'Pilih tipe Basic Wall - 150mm atau 200mm pada Properties',
                        'Atur Base Constraint ke Level 1 dan Top Constraint ke Level 2',
                        'Tarik garis dinding mengikuti perpotongan Grid A-C dan 1-3',
                    ],
                    'checklist' => [
                        ['label' => 'Dinding eksterior lantai 1 selesai', 'done' => true],
                        ['label' => 'Dinding partisi interior lantai 1 selesai', 'done' => false],
                        ['label' => 'Top Constraint terhubung ke Level 2', 'done' => false],
                    ],
                    'tips' => [
                        'Gunakan Location Line: Wall Centerline saat menelusuri garis Grid.',
                        'Tekan Spacebar saat menggambar untuk membalik orientasi eksterior/interior.',
                    ],
                ],
                [
                    'step' => 5,
                    'title' => 'Menambahkan Pintu & Jendela',
                    'summary' => 'Menempatkan family pintu dan jendela pada model.',
                    'description' => 'Tempatkan bukaan pintu utama, pintu kamar, serta jendela pencahayaan alami pada dinding host.',
                    'completed' => false,
                    'viewport_title' => 'Autodesk Revit - RT_2Lantai - Openings Placement',
                    'viewport_levels' => 'Sill Height Jendela: 900 mm | Tinggi Pintu: 2100 mm',
                    'image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
                    'steps_list' => [
                        'Gunakan tool Door (DR) dan arahkan kursor ke dinding host',
                        'Tempatkan Single Flush Door 900x2100mm pada akses masuk',
                        'Gunakan tool Window (WN) dan atur Sill Height = 900 mm',
                        'Pastikan bukaan tidak bertabrakan dengan kolom struktur',
                    ],
                    'checklist' => [
                        ['label' => 'Pintu utama dan pintu ruang terpasang', 'done' => false],
                        ['label' => 'Jendela fasad depan dan samping terpasang', 'done' => false],
                    ],
                    'tips' => [
                        'Pintu dan jendela adalah hosted elements yang hanya bisa ditempatkan pada Wall.',
                        'Gunakan Tag on Placement bila ingin langsung menampilkan kode pintu/jendela.',
                    ],
                ],
                [
                    'step' => 6,
                    'title' => 'Finishing & Export',
                    'summary' => 'Memberikan material, membuat view, dan mengekspor gambar.',
                    'description' => 'Lengkapi pelat lantai, atap, terapkan material akhir, jalankan validasi BIM Studio, dan submit model ke project.',
                    'completed' => false,
                    'viewport_title' => 'Autodesk Revit - RT_2Lantai - Final Presentation Sheet',
                    'viewport_levels' => 'Export IFC4 & Sheet A-101',
                    'image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80',
                    'steps_list' => [
                        'Periksa kelengkapan material Beton, Bata, dan Kaca pada seluruh elemen',
                        'Buka BIM Studio untuk memvalidasi rule model secara otomatis',
                        'Simpan versi terbaru dan klik Submit ke Project untuk direview Mentor',
                    ],
                    'checklist' => [
                        ['label' => 'Material seluruh elemen sudah sesuai', 'done' => false],
                        ['label' => 'Validasi otomatis lulus minimal 75%', 'done' => false],
                        ['label' => 'Model disubmit ke Mentor', 'done' => false],
                    ],
                    'tips' => [
                        'Periksa kembali daftar Issue pada halaman Project sebelum melakukan final submission.',
                    ],
                ],
            ]),
            'status' => 'published',
            'created_by' => $trainerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $challengeId = DB::table('challenges')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $orgId,
            'bim_lab_id' => $labId,
            'title' => 'Validasi Standar Model Rumah Tinggal 2 Lantai',
            'description' => 'Pemeriksaan otomatis kelengkapan level, dinding, kolom, lantai, bukaan, serta relasi host pada model BIM.',
            'difficulty' => 'beginner',
            'duration_minutes' => 120,
            'maximum_score' => 100.00,
            'passing_score' => 75.00,
            'project_template_id' => $project1Id,
            'status' => 'published',
            'created_by' => $trainerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('challenge_rules')->insert([
            ['challenge_id' => $challengeId, 'rule_code' => 'RULE-LEVEL-2', 'rule_type' => 'REQUIRED_LEVEL', 'name' => 'Minimal memiliki 2 Level lantai', 'config' => json_encode(['minCount' => 2]), 'weight' => 20, 'severity' => 'error', 'sort_order' => 1],
            ['challenge_id' => $challengeId, 'rule_code' => 'RULE-WALL-4', 'rule_type' => 'MIN_ELEMENT_COUNT', 'name' => 'Minimal memiliki 4 elemen Dinding (Wall)', 'config' => json_encode(['elementType' => 'WALL', 'minCount' => 4]), 'weight' => 25, 'severity' => 'error', 'sort_order' => 2],
            ['challenge_id' => $challengeId, 'rule_code' => 'RULE-COLUMN-2', 'rule_type' => 'MIN_ELEMENT_COUNT', 'name' => 'Minimal memiliki 2 elemen Kolom (Column)', 'config' => json_encode(['elementType' => 'COLUMN', 'minCount' => 2]), 'weight' => 15, 'severity' => 'error', 'sort_order' => 3],
            ['challenge_id' => $challengeId, 'rule_code' => 'RULE-SLAB-1', 'rule_type' => 'MIN_ELEMENT_COUNT', 'name' => 'Minimal memiliki 1 Pelat Lantai (Slab)', 'config' => json_encode(['elementType' => 'SLAB', 'minCount' => 1]), 'weight' => 15, 'severity' => 'warning', 'sort_order' => 4],
            ['challenge_id' => $challengeId, 'rule_code' => 'RULE-OPENING-2', 'rule_type' => 'MIN_ELEMENT_COUNT', 'name' => 'Memiliki Pintu (Door) dan Jendela (Window)', 'config' => json_encode(['elementType' => 'DOOR', 'minCount' => 1]), 'weight' => 15, 'severity' => 'warning', 'sort_order' => 5],
            ['challenge_id' => $challengeId, 'rule_code' => 'RULE-HOST-OPENING', 'rule_type' => 'HOST_REQUIRED', 'name' => 'Pintu & Jendela memiliki relasi Host Wall', 'config' => json_encode(['elementTypes' => ['DOOR', 'WINDOW']]), 'weight' => 10, 'severity' => 'error', 'sort_order' => 6],
        ]);

        // 12. Assignments & Submissions
        $assign1 = DB::table('assignments')->insertGetId([
            'organization_id' => $orgId,
            'training_class_id' => $class1Id,
            'challenge_id' => $challengeId,
            'title' => 'Upload Model Arsitektur - Modul 3',
            'description' => 'Pemodelan Arsitektur dengan Revit',
            'due_at' => now()->addDays(3),
            'max_score' => 100.00,
            'created_by' => $trainerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $assign2 = DB::table('assignments')->insertGetId([
            'organization_id' => $orgId,
            'training_class_id' => $class1Id,
            'challenge_id' => $challengeId,
            'title' => 'Kuis: Families dan Parameter',
            'description' => 'Pemodelan Arsitektur dengan Revit',
            'due_at' => now()->addDays(5),
            'max_score' => 100.00,
            'created_by' => $trainerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $assign3 = DB::table('assignments')->insertGetId([
            'organization_id' => $orgId,
            'training_class_id' => $class2Id,
            'challenge_id' => null,
            'title' => 'Review Model Struktur',
            'description' => 'Koordinasi dan Kolaborasi BIM',
            'due_at' => now()->addDays(8),
            'max_score' => 100.00,
            'created_by' => $trainerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $assign4 = DB::table('assignments')->insertGetId([
            'organization_id' => $orgId,
            'training_class_id' => $class3Id,
            'challenge_id' => null,
            'title' => 'Presentasi Project Akhir',
            'description' => 'BIM untuk Konstruksi',
            'due_at' => now()->addDays(10),
            'max_score' => 100.00,
            'created_by' => $trainerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $submission1Id = DB::table('submissions')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'assignment_id' => $assign1,
            'project_id' => $project1Id,
            'user_id' => $participantId,
            'project_version_id' => $version1Id,
            'status' => 'under_review',
            'auto_score' => 90.00,
            'trainer_score' => 88.00,
            'final_score' => 89.00,
            'submitted_at' => now()->subHours(5),
            'reviewed_at' => null,
            'feedback' => 'Model arsitektur dan gambar kerja sudah lengkap, menunggu verifikasi akhir mentor.',
            'created_at' => now()->subHours(5),
            'updated_at' => now(),
        ]);

        // 13. Issues & Comments & Project Reviews
        $issue1Id = DB::table('issues')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'project_id' => $project1Id,
            'submission_id' => $submission1Id,
            'element_id' => $wall1Id,
            'title' => 'Clash struktur dengan arsitektur pada lantai 2',
            'description' => 'Balok induk B1 memotong bukaan atas dinding koridor lantai 2 sebesar 120mm. Perlu penyesuaian elevasi atau tinggi bukaan.',
            'severity' => 'critical',
            'status' => 'open',
            'assigned_to' => $participantId,
            'created_by' => $trainerId,
            'camera_state' => json_encode(['position' => [10, 8, 12], 'target' => [4, 0, 5], 'zoom' => 1.2, 'discipline' => 'Struktur']),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $issue2Id = DB::table('issues')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'project_id' => $project1Id,
            'submission_id' => $submission1Id,
            'element_id' => $wall1Id,
            'title' => 'Dimensi pintu tidak sesuai standar',
            'description' => 'Lebar bersih pintu kamar mandi tertulis 600mm, harap disesuaikan menjadi minimal 750mm sesuai standar teknis.',
            'severity' => 'major',
            'status' => 'in_progress',
            'assigned_to' => $participantId,
            'created_by' => $trainerId,
            'camera_state' => json_encode(['position' => [5, 3, 2], 'target' => [2, 0, 1], 'zoom' => 1.5, 'discipline' => 'Arsitektur']),
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        DB::table('issues')->insert([
            'uuid' => (string) Str::uuid(),
            'project_id' => $project1Id,
            'submission_id' => $submission1Id,
            'element_id' => $wall1Id,
            'title' => 'Wall belum align dengan Grid A',
            'description' => 'Periksa posisi dinding terhadap grid A dan lakukan koreksi alignment.',
            'severity' => 'minor',
            'status' => 'open',
            'assigned_to' => $participantId,
            'created_by' => $trainerId,
            'camera_state' => json_encode(['position' => [8, 4, 6], 'target' => [0, 0, 0], 'zoom' => 1.0, 'discipline' => 'Arsitektur']),
            'created_at' => now()->subHours(12),
            'updated_at' => now()->subHours(12),
        ]);

        DB::table('issue_comments')->insert([
            ['issue_id' => $issue1Id, 'user_id' => $trainerId, 'comment' => 'Mohon segera diperbaiki sebelum jadwal review minggu ini.', 'created_at' => now()->subDay()],
            ['issue_id' => $issue1Id, 'user_id' => $participantId, 'comment' => 'Siap Pak Budi, sedang saya sesuaikan offset balok pada Level 2.', 'created_at' => now()->subHours(6)],
        ]);

        DB::table('project_reviews')->insert([
            [
                'project_id' => $project1Id,
                'submission_id' => $submission1Id,
                'reviewer_id' => $trainerId,
                'review_type' => 'technical',
                'score' => 88.00,
                'notes' => 'Rizky Pratama (Trainer): Model arsitektur sudah sangat baik. Perlu penyesuaian pada detail tangga dan railing sesuai standar SNI.',
                'status' => 'completed',
                'reviewed_at' => now()->subDays(2),
                'created_at' => now()->subDays(2),
            ],
            [
                'project_id' => $project1Id,
                'submission_id' => $submission1Id,
                'reviewer_id' => $trainerId,
                'review_type' => 'assessment',
                'score' => 86.00,
                'notes' => 'Siti Rahmawati (Trainer): Koordinasi struktur sudah bagus, namun perlu pengecekan ulang pada kolom lantai 2.',
                'status' => 'completed',
                'reviewed_at' => now()->subDay(),
                'created_at' => now()->subDay(),
            ],
        ]);

        // 14. User Competencies & Assessments
        $compRows = DB::table('competencies')->get();
        $scoreMap = [
            'BIM-FUND' => ['level' => 4, 'score' => 90, 'target' => 5, 'status' => 'achieved'],
            'ARCH-MODEL' => ['level' => 3, 'score' => 78, 'target' => 4, 'status' => 'in_progress'],
            'STRUCT-MODEL' => ['level' => 3, 'score' => 72, 'target' => 4, 'status' => 'in_progress'],
            'MEP-MODEL' => ['level' => 3, 'score' => 68, 'target' => 3, 'status' => 'in_progress'],
            'BIM-COORD' => ['level' => 2, 'score' => 58, 'target' => 3, 'status' => 'in_progress'],
            'CLASH' => ['level' => 3, 'score' => 76, 'target' => 4, 'status' => 'in_progress'],
            'BIM-MGMT' => ['level' => 2, 'score' => 45, 'target' => 3, 'status' => 'in_progress'],
        ];

        foreach ($compRows as $comp) {
            $cfg = $scoreMap[$comp->code] ?? ['level' => 2, 'score' => 60, 'target' => 4, 'status' => 'in_progress'];
            DB::table('user_competencies')->insert([
                'user_id' => $participantId,
                'organization_id' => $orgId,
                'competency_id' => $comp->id,
                'current_level' => $cfg['level'],
                'score' => $cfg['score'],
                'target_level' => $cfg['target'],
                'status' => $cfg['status'],
                'last_assessed_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('assessments')->insert([
            [
                'organization_id' => $orgId,
                'project_id' => $project1Id,
                'submission_id' => $submission1Id,
                'user_id' => $participantId,
                'assessor_id' => $trainerId,
                'assessment_type' => 'project',
                'title' => 'Assessment Architecture Modeling',
                'score' => 85.00,
                'max_score' => 100.00,
                'result' => 'pass',
                'notes' => 'Budi Santoso (Mentor Arsitektur BIM): Pemahaman Anda tentang family dan parameter sudah sangat baik. Terus tingkatkan pada aspek optimasi workflow.',
                'assessed_at' => '2026-04-28 14:00:00',
                'created_at' => now()->subDays(5),
            ],
            [
                'organization_id' => $orgId,
                'project_id' => $project1Id,
                'submission_id' => null,
                'user_id' => $participantId,
                'assessor_id' => $trainerId,
                'assessment_type' => 'quiz',
                'title' => 'Assessment BIM Fundamental',
                'score' => 92.00,
                'max_score' => 100.00,
                'result' => 'pass',
                'notes' => 'Penguasaan konsep ISO 19650 dan dimensi BIM sangat memuaskan.',
                'assessed_at' => '2026-03-15 10:00:00',
                'created_at' => now()->subDays(20),
            ],
            [
                'organization_id' => $orgId,
                'project_id' => $project2Id,
                'submission_id' => null,
                'user_id' => $participantId,
                'assessor_id' => $trainerId,
                'assessment_type' => 'challenge',
                'title' => 'Assessment MEP Modeling',
                'score' => 68.00,
                'max_score' => 100.00,
                'result' => 'in_progress',
                'notes' => 'Perlu penyempurnaan routing ducting HVAC pada area shaft.',
                'assessed_at' => '2026-02-28 16:00:00',
                'created_at' => now()->subDays(35),
            ],
            [
                'organization_id' => $orgId,
                'project_id' => $project3Id,
                'submission_id' => null,
                'user_id' => $participantId,
                'assessor_id' => $trainerId,
                'assessment_type' => 'final',
                'title' => 'Assessment Structure Modeling',
                'score' => 75.00,
                'max_score' => 100.00,
                'result' => 'pass',
                'notes' => 'Rina Putri (Mentor Struktur BIM): Hasil modeling struktur sudah rapi dan sesuai standar. Coba eksplorasi fitur analytical model.',
                'assessed_at' => '2026-01-12 11:00:00',
                'created_at' => now()->subDays(50),
            ],
        ]);

        // 15. Certificates
        DB::table('certificates')->insert([
            [
                'uuid' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'user_id' => $participantId,
                'training_program_id' => $programId,
                'certificate_no' => 'BN-AR-2024-00458',
                'title' => 'Pemodelan Arsitektur dengan Revit',
                'score' => 92.00,
                'competency_level' => 'Intermediate (LOD 350)',
                'issue_date' => '2024-04-28',
                'expiry_date' => '2027-04-28',
                'verification_code' => 'VERIFY-BN-00458',
                'status' => 'active',
                'metadata' => json_encode(['duration_hours' => 24, 'director' => 'Dian Pratama', 'director_title' => 'Direktur BIM Nusantara']),
                'created_at' => now(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'user_id' => $participantId,
                'training_program_id' => $programId,
                'certificate_no' => 'BN-AR-2024-00476',
                'title' => 'Families dan Parameter di Revit',
                'score' => 88.00,
                'competency_level' => 'Intermediate',
                'issue_date' => '2024-04-30',
                'expiry_date' => '2027-04-30',
                'verification_code' => 'VERIFY-BN-00476',
                'status' => 'active',
                'metadata' => json_encode(['duration_hours' => 18, 'director' => 'Dian Pratama', 'director_title' => 'Direktur BIM Nusantara']),
                'created_at' => now(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'user_id' => $participantId,
                'training_program_id' => $programId,
                'certificate_no' => 'BN-CB-2024-00121',
                'title' => 'Koordinasi dan Kolaborasi BIM',
                'score' => 90.00,
                'competency_level' => 'Intermediate',
                'issue_date' => '2024-05-05',
                'expiry_date' => '2027-05-05',
                'verification_code' => 'VERIFY-BN-00121',
                'status' => 'active',
                'metadata' => json_encode(['duration_hours' => 20, 'director' => 'Dian Pratama', 'director_title' => 'Direktur BIM Nusantara']),
                'created_at' => now(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'user_id' => $participantId,
                'training_program_id' => $programId,
                'certificate_no' => 'BN-ST-2024-00098',
                'title' => 'BIM untuk Konstruksi',
                'score' => 85.00,
                'competency_level' => 'Advanced',
                'issue_date' => '2024-05-12',
                'expiry_date' => '2027-05-12',
                'verification_code' => 'VERIFY-BN-00098',
                'status' => 'active',
                'metadata' => json_encode(['duration_hours' => 32, 'director' => 'Dian Pratama', 'director_title' => 'Direktur BIM Nusantara']),
                'created_at' => now(),
            ],
        ]);

        // 16. Files (Dokumen Pendukung)
        DB::table('files')->insert([
            [
                'uuid' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'uploaded_by' => $trainerId,
                'project_id' => $project1Id,
                'file_type' => 'document',
                'original_name' => 'Modul 2 - Pemodelan Arsitektur (PDF)',
                'stored_name' => 'modul-2-pemodelan-arsitektur.pdf',
                'mime_type' => 'application/pdf',
                'extension' => 'PDF',
                'size_bytes' => 12582912,
                'storage_disk' => 'local',
                'storage_path' => 'docs/modul-2-pemodelan-arsitektur.pdf',
                'metadata' => json_encode(['badge_color' => 'bg-red-600', 'size_label' => '12 MB', 'date_label' => '25 Apr 2024']),
                'created_at' => now()->subDays(10),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'uploaded_by' => $trainerId,
                'project_id' => $project1Id,
                'file_type' => 'model',
                'original_name' => 'File Latihan Revit - Gedung Perkantoran',
                'stored_name' => 'RT_2Lantai_Start.rvt',
                'mime_type' => 'application/octet-stream',
                'extension' => 'RVT',
                'size_bytes' => 256901120,
                'storage_disk' => 'local',
                'storage_path' => 'models/RT_2Lantai_Start.rvt',
                'metadata' => json_encode(['badge_color' => 'bg-blue-600', 'size_label' => '245 MB', 'date_label' => '22 Apr 2024']),
                'created_at' => now()->subDays(12),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'uploaded_by' => $trainerId,
                'project_id' => $project2Id,
                'file_type' => 'document',
                'original_name' => 'Template BIM Execution Plan (BEP)',
                'stored_name' => 'template-bep-iso19650.xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'extension' => 'XLS',
                'size_bytes' => 1887436,
                'storage_disk' => 'local',
                'storage_path' => 'docs/template-bep-iso19650.xlsx',
                'metadata' => json_encode(['badge_color' => 'bg-emerald-700', 'size_label' => '1.8 MB', 'date_label' => '18 Apr 2024']),
                'created_at' => now()->subDays(15),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'organization_id' => $orgId,
                'uploaded_by' => $trainerId,
                'project_id' => $project3Id,
                'file_type' => 'document',
                'original_name' => 'Studi Kasus - Proyek Mixed Use (DOCX)',
                'stored_name' => 'studi-kasus-mixed-use.docx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'extension' => 'DOC',
                'size_bytes' => 4404019,
                'storage_disk' => 'local',
                'storage_path' => 'docs/studi-kasus-mixed-use.docx',
                'metadata' => json_encode(['badge_color' => 'bg-orange-600', 'size_label' => '4.2 MB', 'date_label' => '15 Apr 2024']),
                'created_at' => now()->subDays(18),
            ],
        ]);

        // 17. Audit Logs & Notifications
        DB::table('audit_logs')->insert([
            [
                'organization_id' => $orgId,
                'user_id' => $participantId,
                'project_id' => $project1Id,
                'action' => 'submission.submitted',
                'entity_type' => 'submission',
                'entity_id' => (string) $submission1Id,
                'new_values' => json_encode(['title' => 'Anda mengumpulkan tugas Modul 2', 'badge' => 'Berhasil', 'badge_type' => 'emerald', 'icon' => 'file-check', 'time_label' => '2 jam yang lalu']),
                'created_at' => now()->subHours(2),
            ],
            [
                'organization_id' => $orgId,
                'user_id' => $participantId,
                'project_id' => null,
                'action' => 'quiz.graded',
                'entity_type' => 'quiz',
                'entity_id' => (string) $quizId,
                'new_values' => json_encode(['title' => 'Nilai kuis Modul 1 diumumkan', 'badge' => '92', 'badge_type' => 'blue', 'icon' => 'award', 'time_label' => '1 hari yang lalu']),
                'created_at' => now()->subDay(),
            ],
            [
                'organization_id' => $orgId,
                'user_id' => $participantId,
                'project_id' => null,
                'action' => 'lesson.completed',
                'entity_type' => 'lesson',
                'entity_id' => (string) $lessonIds[4],
                'new_values' => json_encode(['title' => 'Anda menyelesaikan materi "Families"', 'badge' => 'Selesai', 'badge_type' => 'emerald', 'icon' => 'check-circle-2', 'time_label' => '2 hari yang lalu']),
                'created_at' => now()->subDays(2),
            ],
            [
                'organization_id' => $orgId,
                'user_id' => $trainerId,
                'project_id' => $project1Id,
                'action' => 'review.commented',
                'entity_type' => 'project_review',
                'entity_id' => (string) $project1Id,
                'new_values' => json_encode(['title' => 'Komentar baru dari Mentor pada project Anda', 'badge' => '1 komentar', 'badge_type' => 'gold', 'icon' => 'message-circle', 'time_label' => '3 hari yang lalu']),
                'created_at' => now()->subDays(3),
            ],
        ]);

        DB::table('notifications')->insert([
            [
                'user_id' => $participantId,
                'organization_id' => $orgId,
                'type' => 'review',
                'title' => 'Review Baru pada Rumah Tinggal 2 Lantai',
                'message' => 'Budi Santoso memberikan skor 88 dan catatan teknis pada model Anda.',
                'data' => json_encode(['url' => '/project']),
                'read_at' => null,
                'created_at' => now()->subHours(3),
            ],
            [
                'user_id' => $participantId,
                'organization_id' => $orgId,
                'type' => 'deadline',
                'title' => 'Pengingat Deadline Tugas Modul 3',
                'message' => 'Upload Model Arsitektur - Modul 3 jatuh tempo dalam 3 hari.',
                'data' => json_encode(['url' => '/belajar']),
                'read_at' => null,
                'created_at' => now()->subHours(8),
            ],
        ]);
    }
}
