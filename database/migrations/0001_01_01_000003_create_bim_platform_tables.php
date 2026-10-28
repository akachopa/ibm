<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('name', 150);
            $table->string('code', 50)->unique();
            $table->string('email', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 100)->unique();
            $table->enum('scope', ['platform', 'organization', 'project'])->default('organization');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 150)->unique();
            $table->string('module', 100);
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('organization_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->string('employee_code', 100)->nullable();
            $table->string('title', 150)->nullable();
            $table->date('joined_at')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->unique(['organization_id', 'user_id'], 'uq_organization_user');
        });

        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('code', 100);
            $table->text('description')->nullable();
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->unsignedInteger('estimated_minutes')->default(0);
            $table->decimal('passing_score', 5, 2)->default(75.00);
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['organization_id', 'code'], 'uq_training_program_code_org');
        });

        Schema::create('training_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->unsignedInteger('estimated_minutes')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_module_id')->constrained('training_modules')->cascadeOnDelete();
            $table->string('title', 200);
            $table->enum('lesson_type', ['video', 'article', 'document', 'quiz', 'lab', 'project']);
            $table->longText('content')->nullable();
            $table->string('video_url', 500)->nullable();
            $table->string('document_url', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->unsignedInteger('estimated_minutes')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('training_classes', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('training_program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('code', 100);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('trainer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'open', 'running', 'completed', 'archived'])->default('draft');
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->unique(['organization_id', 'code'], 'uq_training_class_code_org');
        });

        Schema::create('training_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_class_id')->constrained('training_classes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['enrolled', 'active', 'completed', 'failed', 'withdrawn'])->default('enrolled');
            $table->decimal('progress_percent', 5, 2)->default(0.00);
            $table->decimal('final_score', 5, 2)->nullable();
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->unique(['training_class_id', 'user_id'], 'uq_training_enrollment');
        });

        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->foreignId('training_class_id')->constrained('training_classes')->cascadeOnDelete();
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->decimal('progress_percent', 5, 2)->default(0.00);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('last_position_seconds')->default(0);
            $table->unique(['user_id', 'lesson_id', 'training_class_id'], 'uq_lesson_progress');
        });

        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('title', 200);
            $table->decimal('passing_score', 5, 2)->default(70.00);
            $table->unsignedInteger('time_limit_minutes')->nullable();
            $table->unsignedInteger('attempt_limit')->default(3);
            $table->boolean('randomize_questions')->default(false);
            $table->timestamps();
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->enum('question_type', ['single_choice', 'multiple_choice', 'true_false', 'short_answer']);
            $table->text('question_text');
            $table->decimal('points', 8, 2)->default(1.00);
            $table->text('explanation')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('quiz_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->integer('sort_order')->default(0);
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('training_class_id')->nullable()->constrained('training_classes')->nullOnDelete();
            $table->unsignedInteger('attempt_no');
            $table->decimal('score', 5, 2)->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'graded'])->default('in_progress');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('submitted_at')->nullable();
        });

        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->json('selected_options')->nullable();
            $table->text('answer_text')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('score_awarded', 8, 2)->nullable();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('training_class_id')->nullable()->constrained('training_classes')->nullOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('title', 200);
            $table->string('code', 100);
            $table->text('description')->nullable();
            $table->enum('category', ['training', 'simulation', 'portfolio', 'real_project'])->default('training');
            $table->enum('status', ['draft', 'active', 'under_review', 'revision_required', 'approved', 'completed', 'archived'])->default('draft');
            $table->enum('unit', ['mm', 'cm', 'm'])->default('mm');
            $table->string('schema_version', 20)->default('1.0.0');
            $table->decimal('progress_percent', 5, 2)->default(0.00);
            $table->date('start_date')->nullable();
            $table->date('deadline')->nullable();
            $table->string('thumbnail_url', 500)->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['organization_id', 'code'], 'uq_project_code_org');
        });

        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->enum('project_role', ['owner', 'manager', 'trainer', 'modeler', 'reviewer', 'viewer'])->default('modeler');
            $table->boolean('can_edit')->default(true);
            $table->boolean('can_review')->default(false);
            $table->timestamp('joined_at')->useCurrent();
            $table->unique(['project_id', 'user_id'], 'uq_project_member');
        });

        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 100);
            $table->string('category', 100)->nullable();
            $table->string('color_hex', 20)->nullable();
            $table->decimal('opacity', 4, 3)->default(1.000);
            $table->decimal('density', 12, 3)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('element_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->string('name', 150);
            $table->string('ifc_class', 150)->nullable();
            $table->string('category', 100);
            $table->json('default_properties')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('project_levels', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name', 150);
            $table->integer('elevation_mm')->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['project_id', 'name'], 'uq_project_level_name');
        });

        Schema::create('project_grids', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->enum('axis', ['X', 'Y']);
            $table->string('label', 50);
            $table->integer('position_mm')->default(0);
            $table->integer('start_mm')->nullable();
            $table->integer('end_mm')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'axis', 'label'], 'uq_project_grid_label');
        });

        Schema::create('project_elements', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('project_levels')->nullOnDelete();
            $table->foreignId('parent_element_id')->nullable()->constrained('project_elements')->nullOnDelete();
            $table->foreignId('element_type_id')->constrained('element_types')->restrictOnDelete();
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->string('name', 200);
            $table->string('code', 100)->nullable();
            $table->string('ifc_class', 150)->nullable();
            $table->json('geometry');
            $table->json('transform_data')->nullable();
            $table->json('properties')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('element_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('source_element_id')->constrained('project_elements')->cascadeOnDelete();
            $table->foreignId('target_element_id')->constrained('project_elements')->cascadeOnDelete();
            $table->enum('relationship_type', ['hosts', 'hosted_by', 'connected_to', 'depends_on', 'part_of', 'opening_of']);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['source_element_id', 'target_element_id', 'relationship_type'], 'uq_element_relationship');
        });

        Schema::create('project_versions', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->unsignedInteger('version_no');
            $table->string('label', 150)->nullable();
            $table->enum('version_type', ['autosave', 'manual', 'submission', 'review', 'final'])->default('manual');
            $table->json('snapshot');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['project_id', 'version_no'], 'uq_project_version_no');
        });

        Schema::create('project_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('operation_type', 100);
            $table->char('target_uuid', 36)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('bim_labs', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->unsignedInteger('estimated_minutes')->default(0);
            $table->foreignId('template_project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->json('instructions')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('bim_lab_id')->nullable()->constrained('bim_labs')->nullOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->decimal('maximum_score', 6, 2)->default(100.00);
            $table->decimal('passing_score', 6, 2)->default(75.00);
            $table->foreignId('project_template_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->json('rubric')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('challenge_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('challenges')->cascadeOnDelete();
            $table->string('rule_code', 100);
            $table->enum('rule_type', [
                'MIN_ELEMENT_COUNT',
                'MAX_ELEMENT_COUNT',
                'REQUIRED_ELEMENT',
                'REQUIRED_PROPERTY',
                'PROPERTY_RANGE',
                'REQUIRED_LEVEL',
                'REQUIRED_MATERIAL',
                'HOST_REQUIRED',
                'DIMENSION_RANGE',
                'CUSTOM',
            ]);
            $table->string('name', 200);
            $table->json('config');
            $table->decimal('weight', 6, 2)->default(1.00);
            $table->enum('severity', ['info', 'warning', 'error'])->default('error');
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['challenge_id', 'rule_code'], 'uq_challenge_rule_code');
        });

        Schema::create('challenge_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('challenges')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->enum('status', ['started', 'submitted', 'reviewed', 'completed', 'failed'])->default('started');
            $table->decimal('auto_score', 6, 2)->nullable();
            $table->decimal('trainer_score', 6, 2)->nullable();
            $table->decimal('final_score', 6, 2)->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });

        Schema::create('validation_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('challenge_attempt_id')->nullable()->constrained('challenge_attempts')->nullOnDelete();
            $table->foreignId('rule_id')->nullable()->constrained('challenge_rules')->nullOnDelete();
            $table->foreignId('element_id')->nullable()->constrained('project_elements')->nullOnDelete();
            $table->enum('status', ['passed', 'failed', 'warning']);
            $table->decimal('score', 6, 2)->nullable();
            $table->text('message')->nullable();
            $table->json('detail')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('training_class_id')->nullable()->constrained('training_classes')->nullOnDelete();
            $table->foreignId('challenge_id')->nullable()->constrained('challenges')->nullOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->decimal('max_score', 6, 2)->default(100.00);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('assignment_id')->nullable()->constrained('assignments')->nullOnDelete();
            $table->foreignId('challenge_attempt_id')->nullable()->constrained('challenge_attempts')->nullOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project_version_id')->nullable()->constrained('project_versions')->nullOnDelete();
            $table->enum('status', ['draft', 'submitted', 'under_review', 'revision_required', 'resubmitted', 'approved', 'final'])->default('draft');
            $table->decimal('auto_score', 6, 2)->nullable();
            $table->decimal('trainer_score', 6, 2)->nullable();
            $table->decimal('final_score', 6, 2)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('final_at')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
        });

        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained('submissions')->nullOnDelete();
            $table->foreignId('element_id')->nullable()->constrained('project_elements')->nullOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('severity', ['info', 'minor', 'major', 'critical'])->default('minor');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->json('camera_state')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('issue_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained('issues')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('comment');
            $table->string('attachment_url', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('project_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained('submissions')->nullOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->enum('review_type', ['technical', 'assessment', 'final'])->default('technical');
            $table->decimal('score', 6, 2)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->cascadeOnDelete();
            $table->string('code', 100);
            $table->string('name', 200);
            $table->string('category', 150)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('max_level')->default(5);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['organization_id', 'code'], 'uq_competency_org_code');
        });

        Schema::create('user_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('competency_id')->constrained('competencies')->cascadeOnDelete();
            $table->unsignedInteger('current_level')->default(1);
            $table->decimal('score', 6, 2)->default(0.00);
            $table->unsignedInteger('target_level')->nullable();
            $table->enum('status', ['not_started', 'in_progress', 'achieved'])->default('not_started');
            $table->timestamp('last_assessed_at')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['user_id', 'organization_id', 'competency_id'], 'uq_user_competency');
        });

        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained('submissions')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assessor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('assessment_type', ['quiz', 'project', 'challenge', 'manual', 'final']);
            $table->string('title', 200);
            $table->decimal('score', 6, 2);
            $table->decimal('max_score', 6, 2)->default(100.00);
            $table->enum('result', ['pass', 'fail', 'in_progress'])->default('in_progress');
            $table->json('rubric_result')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('assessed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('assessment_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->foreignId('competency_id')->constrained('competencies')->cascadeOnDelete();
            $table->decimal('score', 6, 2)->default(0.00);
            $table->unsignedInteger('level_awarded')->nullable();
            $table->text('notes')->nullable();
            $table->unique(['assessment_id', 'competency_id'], 'uq_assessment_competency');
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('training_program_id')->nullable()->constrained('training_programs')->nullOnDelete();
            $table->string('certificate_no', 100)->unique();
            $table->string('title', 200);
            $table->decimal('score', 6, 2)->nullable();
            $table->string('competency_level', 100)->nullable();
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->string('verification_code', 100)->unique();
            $table->string('pdf_url', 500)->nullable();
            $table->enum('status', ['active', 'revoked', 'expired'])->default('active');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->enum('file_type', ['image', 'document', 'video', 'ifc', 'model', 'attachment', 'certificate', 'other'])->default('other');
            $table->string('original_name', 255);
            $table->string('stored_name', 255);
            $table->string('mime_type', 150)->nullable();
            $table->string('extension', 50)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('storage_disk', 50)->default('local');
            $table->string('storage_path', 500);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('action', 100);
            $table->string('entity_type', 100)->nullable();
            $table->string('entity_id', 100)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('type', 100);
            $table->string('title', 200);
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('ifc_jobs', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('job_type', ['import', 'export', 'validate', 'convert']);
            $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued');
            $table->foreignId('source_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->foreignId('result_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->decimal('progress_percent', 5, 2)->default(0.00);
            $table->json('result')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        DB::statement("
            CREATE OR REPLACE VIEW vw_project_summary AS
            SELECT
                p.id,
                p.uuid,
                p.organization_id,
                p.title,
                p.code,
                p.status,
                p.progress_percent,
                p.owner_id,
                u.name AS owner_name,
                COUNT(DISTINCT pm.user_id) AS member_count,
                COUNT(DISTINCT pe.id) AS element_count,
                COUNT(DISTINCT i.id) AS issue_count,
                SUM(CASE WHEN i.status IN ('open','in_progress') THEN 1 ELSE 0 END) AS open_issue_count
            FROM projects p
            JOIN users u ON u.id = p.owner_id
            LEFT JOIN project_members pm ON pm.project_id = p.id
            LEFT JOIN project_elements pe ON pe.project_id = p.id AND pe.deleted_at IS NULL
            LEFT JOIN issues i ON i.project_id = p.id
            WHERE p.deleted_at IS NULL
            GROUP BY
                p.id, p.uuid, p.organization_id, p.title, p.code,
                p.status, p.progress_percent, p.owner_id, u.name
        ");

        DB::statement("
            CREATE OR REPLACE VIEW vw_user_training_progress AS
            SELECT
                te.id AS enrollment_id,
                tc.organization_id,
                te.user_id,
                u.name AS user_name,
                tp.id AS program_id,
                tp.title AS program_title,
                tc.id AS class_id,
                tc.name AS class_name,
                te.status,
                te.progress_percent,
                te.final_score,
                te.enrolled_at,
                te.completed_at
            FROM training_enrollments te
            JOIN users u ON u.id = te.user_id
            JOIN training_classes tc ON tc.id = te.training_class_id
            JOIN training_programs tp ON tp.id = tc.training_program_id
        ");

        DB::statement("
            CREATE OR REPLACE VIEW vw_project_element_counts AS
            SELECT
                pe.project_id,
                et.code AS element_type,
                et.name AS element_name,
                COUNT(*) AS total
            FROM project_elements pe
            JOIN element_types et ON et.id = pe.element_type_id
            WHERE pe.deleted_at IS NULL
            GROUP BY pe.project_id, et.code, et.name
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_project_element_counts');
        DB::statement('DROP VIEW IF EXISTS vw_user_training_progress');
        DB::statement('DROP VIEW IF EXISTS vw_project_summary');

        Schema::dropIfExists('ifc_jobs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('files');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('assessment_competencies');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('user_competencies');
        Schema::dropIfExists('competencies');
        Schema::dropIfExists('project_reviews');
        Schema::dropIfExists('issue_comments');
        Schema::dropIfExists('issues');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('validation_results');
        Schema::dropIfExists('challenge_attempts');
        Schema::dropIfExists('challenge_rules');
        Schema::dropIfExists('challenges');
        Schema::dropIfExists('bim_labs');
        Schema::dropIfExists('project_operations');
        Schema::dropIfExists('project_versions');
        Schema::dropIfExists('element_relationships');
        Schema::dropIfExists('project_elements');
        Schema::dropIfExists('project_grids');
        Schema::dropIfExists('project_levels');
        Schema::dropIfExists('element_types');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('quiz_answers');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_options');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('lesson_progress');
        Schema::dropIfExists('training_enrollments');
        Schema::dropIfExists('training_classes');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('training_modules');
        Schema::dropIfExists('training_programs');
        Schema::dropIfExists('organization_users');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('organizations');
    }
};

