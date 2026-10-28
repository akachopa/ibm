<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BimPlatformTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Login default user untuk testing authenticated routes
        $user = User::first();
        if ($user) {
            $this->actingAs($user);
        }
    }

    /**
     * Test semua 7 halaman utama dapat diakses dengan HTTP 200.
     */
    public function test_all_pages_return_successful_response(): void
    {
        $endpoints = [
            '/',
            '/dashboard',
            '/belajar',
            '/bim-lab',
            '/bim-studio',
            '/project',
            '/kompetensi',
            '/profil',
        ];

        foreach ($endpoints as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
        }
    }

    /**
     * Test global search Ctrl+K API.
     */
    public function test_global_search_api_returns_results(): void
    {
        $response = $this->getJson('/api/search?q=Revit');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'results' => [
                '*' => ['category', 'title', 'subtitle', 'url']
            ]
        ]);
    }

    /**
     * Test Switch User demo mode.
     */
    public function test_switch_user_mode(): void
    {
        $currentUser = auth()->user();
        $targetUser = User::where('id', '!=', $currentUser->id)->first();
        $this->assertNotNull($targetUser);

        $response = $this->post('/switch-user', [
            'user_id' => $targetUser->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('active_user_id', $targetUser->id);
    }

    /**
     * Test CRUD Modul Pembelajaran.
     */
    public function test_module_crud_lifecycle(): void
    {
        $program = DB::table('training_programs')->first();
        $this->assertNotNull($program);

        // 1. Create Module
        $response = $this->post('/belajar/modules', [
            'training_program_id' => $program->id,
            'title' => 'Modul Otomasi BIM Dynamo',
            'description' => 'Visual scripting untuk otomatisasi permodelan BIM.',
            'sort_order' => 99,
            'estimated_minutes' => 120,
        ]);
        $response->assertRedirect();

        $module = DB::table('training_modules')->where('title', 'Modul Otomasi BIM Dynamo')->first();
        $this->assertNotNull($module);

        // 2. Update Module
        $updateResponse = $this->put("/belajar/modules/{$module->id}", [
            'title' => 'Modul Dynamo & Python BIM',
            'description' => 'Pemrograman visual & skrip lanjutan.',
            'sort_order' => 99,
            'estimated_minutes' => 180,
        ]);
        $updateResponse->assertRedirect();

        $updatedModule = DB::table('training_modules')->where('id', $module->id)->first();
        $this->assertEquals('Modul Dynamo & Python BIM', $updatedModule->title);

        // 3. Delete Module
        $deleteResponse = $this->delete("/belajar/modules/{$module->id}");
        $deleteResponse->assertRedirect();

        $deletedModule = DB::table('training_modules')->where('id', $module->id)->first();
        $this->assertNull($deletedModule);
    }

    /**
     * Test CRUD Project & Issue.
     */
    public function test_project_and_issue_lifecycle(): void
    {
        $uniqueCode = 'PRJ-TEST-' . substr(md5((string) microtime(true)), 0, 6);

        // 1. Create Project
        $response = $this->post('/project/projects', [
            'title' => 'Gedung Riset Energi Hijau',
            'code' => $uniqueCode,
            'category' => 'training',
            'status' => 'active',
            'unit' => 'mm',
            'description' => 'Studi integrasi BIM untuk target Net Zero Energy Building.',
            'building_type' => 'Laboratorium',
            'progress_percent' => 20,
        ]);
        $response->assertRedirect();

        $project = DB::table('projects')->where('code', $uniqueCode)->first();
        $this->assertNotNull($project);

        // 2. Create Issue on this Project
        $issueResponse = $this->post('/project/issues', [
            'project_id' => $project->id,
            'title' => 'Clash pipa HVAC dengan balok B2 Lt 3',
            'description' => 'Terjadi tabrakan elevasi pipa chiller 250mm dengan balok struktural.',
            'severity' => 'major',
            'discipline' => 'MEP',
        ]);
        $issueResponse->assertRedirect();

        $issue = DB::table('issues')->where('project_id', $project->id)->first();
        $this->assertNotNull($issue);
        $this->assertEquals('Clash pipa HVAC dengan balok B2 Lt 3', $issue->title);

        // 3. Update Issue Status to resolved
        $updateIssue = $this->put("/project/issues/{$issue->id}", [
            'title' => 'Clash pipa HVAC dengan balok B2 Lt 3 (Rerouted)',
            'description' => 'Pipa diturunkan 150mm di bawah soffit balok.',
            'severity' => 'major',
            'status' => 'resolved',
        ]);
        $updateIssue->assertRedirect();

        $resolvedIssue = DB::table('issues')->where('id', $issue->id)->first();
        $this->assertEquals('resolved', $resolvedIssue->status);

        // 4. Cleanup Project
        DB::table('issues')->where('id', $issue->id)->delete();
        DB::table('project_levels')->where('project_id', $project->id)->delete();
        DB::table('project_members')->where('project_id', $project->id)->delete();
        DB::table('projects')->where('id', $project->id)->delete();
    }

    /**
     * Test CRUD Studio Element.
     */
    public function test_studio_element_lifecycle(): void
    {
        $project = DB::table('projects')->first();
        $level = DB::table('project_levels')->where('project_id', $project->id)->first();
        $this->assertNotNull($project);
        $this->assertNotNull($level);

        // 1. Create Element
        $response = $this->post('/bim-studio/elements', [
            'project_id' => $project->id,
            'level_id' => $level->id,
            'element_type_code' => 'COLUMN',
            'name' => 'Test Kolom K-Test-600',
            'code' => 'K-TST-01',
            'length_mm' => 600,
            'height_mm' => 4200,
            'thickness_mm' => 600,
            'pos_x' => 1000,
            'pos_y' => 1000,
        ]);
        $response->assertRedirect();

        $element = DB::table('project_elements')->where('name', 'Test Kolom K-Test-600')->first();
        $this->assertNotNull($element);

        // 2. Update Element
        $updateResp = $this->put("/bim-studio/elements/{$element->id}", [
            'name' => 'Test Kolom K-Test-800',
            'length_mm' => 800,
            'height_mm' => 4500,
            'thickness_mm' => 800,
        ]);
        $updateResp->assertRedirect();

        $updatedElement = DB::table('project_elements')->where('id', $element->id)->first();
        $this->assertEquals('Test Kolom K-Test-800', $updatedElement->name);

        // 3. Delete Element
        $delResp = $this->delete("/bim-studio/elements/{$element->id}");
        $delResp->assertRedirect();

        $checkDeleted = DB::table('project_elements')->where('id', $element->id)->whereNull('deleted_at')->first();
        $this->assertNull($checkDeleted);
    }

    /**
     * Test Lab Step Checklist Toggle.
     */
    public function test_lab_checklist_toggle(): void
    {
        $lab = DB::table('bim_labs')->first();
        $this->assertNotNull($lab);

        $response = $this->post("/bim-lab/labs/{$lab->id}/steps/0/toggle-checklist", [
            'check_index' => 0,
        ]);

        $response->assertRedirect();

        $updatedLab = DB::table('bim_labs')->where('id', $lab->id)->first();
        $instructions = json_decode($updatedLab->instructions, true);
        $this->assertIsArray($instructions);
        $this->assertArrayHasKey('checklist', $instructions[0]);
    }

    /**
     * Test BIM Studio Actions: Save Version, Validate, and Submit.
     */
    public function test_bim_studio_actions(): void
    {
        $project = DB::table('projects')->first();
        $this->assertNotNull($project);

        // 1. Save Version Snapshot
        $saveVerResp = $this->post("/bim-studio/projects/{$project->id}/save-version", [
            'label' => 'vTest Snapshot Otomatis',
            'notes' => 'Testing save snapshot model BIM',
        ]);
        $saveVerResp->assertRedirect();

        $version = DB::table('project_versions')->where('project_id', $project->id)->orderByDesc('id')->first();
        $this->assertNotNull($version);
        $this->assertEquals('vTest Snapshot Otomatis', $version->label);

        // 2. Validate Studio Model
        $validateResp = $this->post("/bim-studio/projects/{$project->id}/validate");
        $validateResp->assertRedirect();

        $validationsCount = DB::table('validation_results')->where('project_id', $project->id)->count();
        $this->assertGreaterThan(0, $validationsCount);

        // 3. Submit Studio to Project Review
        $submitResp = $this->post("/bim-studio/projects/{$project->id}/submit", [
            'notes' => 'Pengajuan review model akhir oleh modeler',
        ]);
        $submitResp->assertRedirect();

        $submission = DB::table('submissions')->where('project_id', $project->id)->orderByDesc('id')->first();
        $this->assertNotNull($submission);
    }

    /**
     * Test Competency CRUD Lifecycle.
     */
    public function test_competency_crud_lifecycle(): void
    {
        // 1. Create Competency
        $uniqueCode = 'CMP-TST-' . rand(100, 999);
        $createResp = $this->post('/kompetensi/competencies', [
            'code' => $uniqueCode,
            'name' => 'BIM 5D Cost Estimation',
            'category' => 'Modeling & QTO',
            'score' => 85,
            'current_level' => 4,
            'target_level' => 5,
        ]);
        $createResp->assertRedirect();

        $comp = DB::table('competencies')->where('code', $uniqueCode)->first();
        $this->assertNotNull($comp);
        $this->assertEquals('BIM 5D Cost Estimation', $comp->name);

        // 2. Update Competency
        $updateResp = $this->put("/kompetensi/competencies/{$comp->id}", [
            'name' => 'BIM 5D Cost & QTO Advanced',
            'score' => 90,
            'current_level' => 4,
            'target_level' => 5,
        ]);
        $updateResp->assertRedirect();

        $updatedComp = DB::table('competencies')->where('id', $comp->id)->first();
        $this->assertEquals('BIM 5D Cost & QTO Advanced', $updatedComp->name);

        // 3. Delete Competency
        $deleteResp = $this->delete("/kompetensi/competencies/{$comp->id}");
        $deleteResp->assertRedirect();

        $deletedComp = DB::table('competencies')->where('id', $comp->id)->first();
        $this->assertNull($deletedComp);
    }

    /**
     * Test Certificate CRUD Lifecycle.
     */
    public function test_certificate_crud_lifecycle(): void
    {
        $uniqueCertNo = 'BN-CERT-TEST-' . rand(1000, 9999);

        // 1. Issue Certificate
        $createResp = $this->post('/profil/certificates', [
            'title' => 'Sertifikasi Ahli Muda BIM Arsitektur',
            'certificate_no' => $uniqueCertNo,
            'issue_date' => now()->toDateString(),
            'score' => 94.50,
            'duration_hours' => 32,
            'competency_level' => 'Advanced',
        ]);
        $createResp->assertRedirect();

        $cert = DB::table('certificates')->where('certificate_no', $uniqueCertNo)->first();
        $this->assertNotNull($cert);
        $this->assertEquals('Sertifikasi Ahli Muda BIM Arsitektur', $cert->title);

        // 2. Update Certificate
        $updateResp = $this->put("/profil/certificates/{$cert->id}", [
            'title' => 'Sertifikasi Ahli Madya BIM Arsitektur',
            'certificate_no' => $uniqueCertNo,
            'issue_date' => now()->toDateString(),
            'score' => 96.00,
            'duration_hours' => 40,
            'status' => 'active',
        ]);
        $updateResp->assertRedirect();

        $updatedCert = DB::table('certificates')->where('id', $cert->id)->first();
        $this->assertEquals('Sertifikasi Ahli Madya BIM Arsitektur', $updatedCert->title);

        // 3. Delete Certificate
        $deleteResp = $this->delete("/profil/certificates/{$cert->id}");
        $deleteResp->assertRedirect();

        $deletedCert = DB::table('certificates')->where('id', $cert->id)->first();
        $this->assertNull($deletedCert);
    }

    /**
     * Test Update Profile & Organization.
     */
    public function test_profile_update(): void
    {
        $user = auth()->user();

        $response = $this->put('/profil/update', [
            'name' => 'Budi Santoso, S.T., M.Ars',
            'email' => $user->email,
            'job_title' => 'Lead BIM Coordinator & Architect',
            'phone' => '+62 812-3456-7890',
            'location' => 'Jakarta Selatan, DKI Jakarta',
            'bio' => 'Praktisi dan konsultan BIM berfokus pada implementasi LOD 350-400.',
            'org_name' => 'PT Archadesign Nusantara Pratama',
        ]);

        $response->assertRedirect();

        $updatedUser = DB::table('users')->where('id', $user->id)->first();
        $this->assertEquals('Budi Santoso, S.T., M.Ars', $updatedUser->name);
    }
}
