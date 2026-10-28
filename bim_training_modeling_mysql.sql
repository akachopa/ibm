-- ============================================================
-- BIM TRAINING & MODELING PLATFORM
-- MySQL 8.x
-- Multi User • Multi Organization • Multi Project
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS bim_platform;
CREATE DATABASE bim_platform
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE bim_platform;

-- ============================================================
-- 1. CORE / TENANT / AUTHORIZATION
-- ============================================================

CREATE TABLE organizations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NULL,
    phone VARCHAR(50) NULL,
    address TEXT NULL,
    logo_url VARCHAR(500) NULL,
    status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
    settings JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(50) NULL,
    password VARCHAR(255) NOT NULL,
    avatar_url VARCHAR(500) NULL,
    status ENUM('active','inactive','blocked') NOT NULL DEFAULT 'active',
    email_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    preferences JSON NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    code VARCHAR(100) NOT NULL UNIQUE,
    scope ENUM('platform','organization','project') NOT NULL DEFAULT 'organization',
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(150) NOT NULL UNIQUE,
    module VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission
        FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE organization_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    employee_code VARCHAR(100) NULL,
    title VARCHAR(150) NULL,
    joined_at DATE NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_organization_user (organization_id, user_id),
    INDEX idx_organization_users_user (user_id),
    INDEX idx_organization_users_role (role_id),
    CONSTRAINT fk_organization_users_org
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    CONSTRAINT fk_organization_users_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_organization_users_role
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- 2. TRAINING PROGRAM
-- ============================================================

CREATE TABLE training_programs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    organization_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    code VARCHAR(100) NOT NULL,
    description TEXT NULL,
    level ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
    status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    estimated_minutes INT UNSIGNED NOT NULL DEFAULT 0,
    passing_score DECIMAL(5,2) NOT NULL DEFAULT 75.00,
    settings JSON NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_training_program_code_org (organization_id, code),
    INDEX idx_training_program_org (organization_id),
    CONSTRAINT fk_training_program_org
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    CONSTRAINT fk_training_program_creator
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE training_modules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    training_program_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    estimated_minutes INT UNSIGNED NOT NULL DEFAULT 0,
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_training_modules_program (training_program_id),
    CONSTRAINT fk_training_modules_program
        FOREIGN KEY (training_program_id) REFERENCES training_programs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE lessons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    training_module_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    lesson_type ENUM('video','article','document','quiz','lab','project') NOT NULL,
    content LONGTEXT NULL,
    video_url VARCHAR(500) NULL,
    document_url VARCHAR(500) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    estimated_minutes INT UNSIGNED NOT NULL DEFAULT 0,
    settings JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_lessons_module (training_module_id),
    CONSTRAINT fk_lessons_module
        FOREIGN KEY (training_module_id) REFERENCES training_modules(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE training_classes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    organization_id BIGINT UNSIGNED NOT NULL,
    training_program_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(100) NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    trainer_id BIGINT UNSIGNED NULL,
    status ENUM('draft','open','running','completed','archived') NOT NULL DEFAULT 'draft',
    settings JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_training_class_code_org (organization_id, code),
    INDEX idx_training_classes_program (training_program_id),
    INDEX idx_training_classes_trainer (trainer_id),
    CONSTRAINT fk_training_classes_org
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    CONSTRAINT fk_training_classes_program
        FOREIGN KEY (training_program_id) REFERENCES training_programs(id) ON DELETE CASCADE,
    CONSTRAINT fk_training_classes_trainer
        FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE training_enrollments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    training_class_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('enrolled','active','completed','failed','withdrawn') NOT NULL DEFAULT 'enrolled',
    progress_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    final_score DECIMAL(5,2) NULL,
    enrolled_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    UNIQUE KEY uq_training_enrollment (training_class_id, user_id),
    INDEX idx_training_enrollments_user (user_id),
    CONSTRAINT fk_training_enrollments_class
        FOREIGN KEY (training_class_id) REFERENCES training_classes(id) ON DELETE CASCADE,
    CONSTRAINT fk_training_enrollments_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE lesson_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    lesson_id BIGINT UNSIGNED NOT NULL,
    training_class_id BIGINT UNSIGNED NOT NULL,
    status ENUM('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
    progress_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    last_position_seconds INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY uq_lesson_progress (user_id, lesson_id, training_class_id),
    CONSTRAINT fk_lesson_progress_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_lesson_progress_lesson
        FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    CONSTRAINT fk_lesson_progress_class
        FOREIGN KEY (training_class_id) REFERENCES training_classes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 3. QUIZ / ASSESSMENT CONTENT
-- ============================================================

CREATE TABLE quizzes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lesson_id BIGINT UNSIGNED NULL,
    organization_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    passing_score DECIMAL(5,2) NOT NULL DEFAULT 70.00,
    time_limit_minutes INT UNSIGNED NULL,
    attempt_limit INT UNSIGNED NOT NULL DEFAULT 3,
    randomize_questions TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_quizzes_lesson (lesson_id),
    CONSTRAINT fk_quizzes_lesson
        FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
    CONSTRAINT fk_quizzes_org
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE quiz_questions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id BIGINT UNSIGNED NOT NULL,
    question_type ENUM('single_choice','multiple_choice','true_false','short_answer') NOT NULL,
    question_text TEXT NOT NULL,
    points DECIMAL(8,2) NOT NULL DEFAULT 1.00,
    explanation TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_quiz_questions_quiz (quiz_id),
    CONSTRAINT fk_quiz_questions_quiz
        FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE quiz_options (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id BIGINT UNSIGNED NOT NULL,
    option_text TEXT NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_quiz_options_question
        FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE quiz_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    training_class_id BIGINT UNSIGNED NULL,
    attempt_no INT UNSIGNED NOT NULL,
    score DECIMAL(5,2) NULL,
    status ENUM('in_progress','submitted','graded') NOT NULL DEFAULT 'in_progress',
    started_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    submitted_at TIMESTAMP NULL,
    INDEX idx_quiz_attempts_user (user_id),
    CONSTRAINT fk_quiz_attempts_quiz
        FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    CONSTRAINT fk_quiz_attempts_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_quiz_attempts_class
        FOREIGN KEY (training_class_id) REFERENCES training_classes(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE quiz_answers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_attempt_id BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,
    selected_options JSON NULL,
    answer_text TEXT NULL,
    is_correct TINYINT(1) NULL,
    score_awarded DECIMAL(8,2) NULL,
    CONSTRAINT fk_quiz_answers_attempt
        FOREIGN KEY (quiz_attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
    CONSTRAINT fk_quiz_answers_question
        FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 4. PROJECT / MULTI PROJECT
-- ============================================================

CREATE TABLE projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    organization_id BIGINT UNSIGNED NOT NULL,
    training_class_id BIGINT UNSIGNED NULL,
    owner_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    code VARCHAR(100) NOT NULL,
    description TEXT NULL,
    category ENUM('training','simulation','portfolio','real_project') NOT NULL DEFAULT 'training',
    status ENUM('draft','active','under_review','revision_required','approved','completed','archived') NOT NULL DEFAULT 'draft',
    unit ENUM('mm','cm','m') NOT NULL DEFAULT 'mm',
    schema_version VARCHAR(20) NOT NULL DEFAULT '1.0.0',
    progress_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    start_date DATE NULL,
    deadline DATE NULL,
    thumbnail_url VARCHAR(500) NULL,
    settings JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uq_project_code_org (organization_id, code),
    INDEX idx_projects_org (organization_id),
    INDEX idx_projects_owner (owner_id),
    INDEX idx_projects_status (status),
    CONSTRAINT fk_projects_org
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    CONSTRAINT fk_projects_class
        FOREIGN KEY (training_class_id) REFERENCES training_classes(id) ON DELETE SET NULL,
    CONSTRAINT fk_projects_owner
        FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE project_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NULL,
    project_role ENUM('owner','manager','trainer','modeler','reviewer','viewer') NOT NULL DEFAULT 'modeler',
    can_edit TINYINT(1) NOT NULL DEFAULT 1,
    can_review TINYINT(1) NOT NULL DEFAULT 0,
    joined_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_project_member (project_id, user_id),
    INDEX idx_project_members_user (user_id),
    CONSTRAINT fk_project_members_project
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_members_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_members_role
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 5. BIM MASTER DATA
-- ============================================================

CREATE TABLE materials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(100) NOT NULL,
    category VARCHAR(100) NULL,
    color_hex VARCHAR(20) NULL,
    opacity DECIMAL(4,3) NOT NULL DEFAULT 1.000,
    density DECIMAL(12,3) NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_materials_org (organization_id),
    CONSTRAINT fk_materials_org
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE element_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    ifc_class VARCHAR(150) NULL,
    category VARCHAR(100) NOT NULL,
    default_properties JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 6. BIM PROJECT STRUCTURE
-- ============================================================

CREATE TABLE project_levels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    project_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    elevation_mm INT NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_project_level_name (project_id, name),
    INDEX idx_project_levels_project (project_id),
    CONSTRAINT fk_project_levels_project
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE project_grids (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    project_id BIGINT UNSIGNED NOT NULL,
    axis ENUM('X','Y') NOT NULL,
    label VARCHAR(50) NOT NULL,
    position_mm INT NOT NULL DEFAULT 0,
    start_mm INT NULL,
    end_mm INT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_project_grid_label (project_id, axis, label),
    INDEX idx_project_grids_project (project_id),
    CONSTRAINT fk_project_grids_project
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE project_elements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    project_id BIGINT UNSIGNED NOT NULL,
    level_id BIGINT UNSIGNED NULL,
    parent_element_id BIGINT UNSIGNED NULL,
    element_type_id BIGINT UNSIGNED NOT NULL,
    material_id BIGINT UNSIGNED NULL,

    name VARCHAR(200) NOT NULL,
    code VARCHAR(100) NULL,
    ifc_class VARCHAR(150) NULL,

    geometry JSON NOT NULL,
    transform_data JSON NULL,
    properties JSON NULL,
    metadata JSON NULL,

    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    is_locked TINYINT(1) NOT NULL DEFAULT 0,

    created_by BIGINT UNSIGNED NOT NULL,
    updated_by BIGINT UNSIGNED NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_project_elements_project (project_id),
    INDEX idx_project_elements_level (level_id),
    INDEX idx_project_elements_type (element_type_id),
    INDEX idx_project_elements_parent (parent_element_id),
    CONSTRAINT fk_project_elements_project
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_elements_level
        FOREIGN KEY (level_id) REFERENCES project_levels(id) ON DELETE SET NULL,
    CONSTRAINT fk_project_elements_parent
        FOREIGN KEY (parent_element_id) REFERENCES project_elements(id) ON DELETE SET NULL,
    CONSTRAINT fk_project_elements_type
        FOREIGN KEY (element_type_id) REFERENCES element_types(id) ON DELETE RESTRICT,
    CONSTRAINT fk_project_elements_material
        FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE SET NULL,
    CONSTRAINT fk_project_elements_created_by
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_project_elements_updated_by
        FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE element_relationships (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    source_element_id BIGINT UNSIGNED NOT NULL,
    target_element_id BIGINT UNSIGNED NOT NULL,
    relationship_type ENUM('hosts','hosted_by','connected_to','depends_on','part_of','opening_of') NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_element_relationship (
        source_element_id,
        target_element_id,
        relationship_type
    ),
    CONSTRAINT fk_element_relationship_project
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_element_relationship_source
        FOREIGN KEY (source_element_id) REFERENCES project_elements(id) ON DELETE CASCADE,
    CONSTRAINT fk_element_relationship_target
        FOREIGN KEY (target_element_id) REFERENCES project_elements(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 7. PROJECT VERSIONING
-- ============================================================

CREATE TABLE project_versions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    project_id BIGINT UNSIGNED NOT NULL,
    version_no INT UNSIGNED NOT NULL,
    label VARCHAR(150) NULL,
    version_type ENUM('autosave','manual','submission','review','final') NOT NULL DEFAULT 'manual',
    snapshot JSON NOT NULL,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_project_version_no (project_id, version_no),
    INDEX idx_project_versions_project (project_id),
    CONSTRAINT fk_project_versions_project
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_versions_user
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE project_operations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    operation_type VARCHAR(100) NOT NULL,
    target_uuid CHAR(36) NULL,
    payload JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_project_operations_project (project_id),
    INDEX idx_project_operations_user (user_id),
    CONSTRAINT fk_project_operations_project
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_operations_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 8. BIM LAB / CHALLENGE / RULE
-- ============================================================

CREATE TABLE bim_labs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    organization_id BIGINT UNSIGNED NOT NULL,
    lesson_id BIGINT UNSIGNED NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    difficulty ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
    estimated_minutes INT UNSIGNED NOT NULL DEFAULT 0,
    template_project_id BIGINT UNSIGNED NULL,
    instructions JSON NULL,
    status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_bim_labs_org
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    CONSTRAINT fk_bim_labs_lesson
        FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
    CONSTRAINT fk_bim_labs_template
        FOREIGN KEY (template_project_id) REFERENCES projects(id) ON DELETE SET NULL,
    CONSTRAINT fk_bim_labs_creator
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE challenges (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    organization_id BIGINT UNSIGNED NOT NULL,
    bim_lab_id BIGINT UNSIGNED NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    difficulty ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
    duration_minutes INT UNSIGNED NULL,
    maximum_score DECIMAL(6,2) NOT NULL DEFAULT 100.00,
    passing_score DECIMAL(6,2) NOT NULL DEFAULT 75.00,
    project_template_id BIGINT UNSIGNED NULL,
    rubric JSON NULL,
    status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_challenges_org
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    CONSTRAINT fk_challenges_lab
        FOREIGN KEY (bim_lab_id) REFERENCES bim_labs(id) ON DELETE SET NULL,
    CONSTRAINT fk_challenges_template
        FOREIGN KEY (project_template_id) REFERENCES projects(id) ON DELETE SET NULL,
    CONSTRAINT fk_challenges_creator
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE challenge_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    challenge_id BIGINT UNSIGNED NOT NULL,
    rule_code VARCHAR(100) NOT NULL,
    rule_type ENUM(
        'MIN_ELEMENT_COUNT',
        'MAX_ELEMENT_COUNT',
        'REQUIRED_ELEMENT',
        'REQUIRED_PROPERTY',
        'PROPERTY_RANGE',
        'REQUIRED_LEVEL',
        'REQUIRED_MATERIAL',
        'HOST_REQUIRED',
        'DIMENSION_RANGE',
        'CUSTOM'
    ) NOT NULL,
    name VARCHAR(200) NOT NULL,
    config JSON NOT NULL,
    weight DECIMAL(6,2) NOT NULL DEFAULT 1.00,
    severity ENUM('info','warning','error') NOT NULL DEFAULT 'error',
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_challenge_rule_code (challenge_id, rule_code),
    CONSTRAINT fk_challenge_rules_challenge
        FOREIGN KEY (challenge_id) REFERENCES challenges(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE challenge_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    challenge_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    status ENUM('started','submitted','reviewed','completed','failed') NOT NULL DEFAULT 'started',
    auto_score DECIMAL(6,2) NULL,
    trainer_score DECIMAL(6,2) NULL,
    final_score DECIMAL(6,2) NULL,
    started_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    submitted_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    INDEX idx_challenge_attempts_user (user_id),
    CONSTRAINT fk_challenge_attempts_challenge
        FOREIGN KEY (challenge_id) REFERENCES challenges(id) ON DELETE CASCADE,
    CONSTRAINT fk_challenge_attempts_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_challenge_attempts_project
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE validation_results (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    challenge_attempt_id BIGINT UNSIGNED NULL,
    rule_id BIGINT UNSIGNED NULL,
    element_id BIGINT UNSIGNED NULL,
    status ENUM('passed','failed','warning') NOT NULL,
    score DECIMAL(6,2) NULL,
    message TEXT NULL,
    detail JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_validation_results_project (project_id),
    CONSTRAINT fk_validation_results_project
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_validation_results_attempt
        FOREIGN KEY (challenge_attempt_id) REFERENCES challenge_attempts(id) ON DELETE SET NULL,
    CONSTRAINT fk_validation_results_rule
        FOREIGN KEY (rule_id) REFERENCES challenge_rules(id) ON DELETE SET NULL,
    CONSTRAINT fk_validation_results_element
        FOREIGN KEY (element_id) REFERENCES project_elements(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 9. ASSIGNMENT / SUBMISSION
-- ============================================================

CREATE TABLE assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    training_class_id BIGINT UNSIGNED NULL,
    challenge_id BIGINT UNSIGNED NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    due_at DATETIME NULL,
    max_score DECIMAL(6,2) NOT NULL DEFAULT 100.00,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_assignments_org
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    CONSTRAINT fk_assignments_class
        FOREIGN KEY (training_class_id) REFERENCES training_classes(id) ON DELETE SET NULL,
    CONSTRAINT fk_assignments_challenge
        FOREIGN KEY (challenge_id) REFERENCES challenges(id) ON DELETE SET NULL,
    CONSTRAINT fk_assignments_creator
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE submissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    assignment_id BIGINT UNSIGNED NULL,
    challenge_attempt_id BIGINT UNSIGNED NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    project_version_id BIGINT UNSIGNED NULL,
    status ENUM('draft','submitted','under_review','revision_required','resubmitted','approved','final') NOT NULL DEFAULT 'draft',
    auto_score DECIMAL(6,2) NULL,
    trainer_score DECIMAL(6,2) NULL,
    final_score DECIMAL(6,2) NULL,
    submitted_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    final_at TIMESTAMP NULL,
    feedback TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_submissions_project (project_id),
    INDEX idx_submissions_user (user_id),
    CONSTRAINT fk_submissions_assignment
        FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE SET NULL,
    CONSTRAINT fk_submissions_attempt
        FOREIGN KEY (challenge_attempt_id) REFERENCES challenge_attempts(id) ON DELETE SET NULL,
    CONSTRAINT fk_submissions_project
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_submissions_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_submissions_version
        FOREIGN KEY (project_version_id) REFERENCES project_versions(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 10. ISSUE / REVIEW
-- ============================================================

CREATE TABLE issues (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    project_id BIGINT UNSIGNED NOT NULL,
    submission_id BIGINT UNSIGNED NULL,
    element_id BIGINT UNSIGNED NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    severity ENUM('info','minor','major','critical') NOT NULL DEFAULT 'minor',
    status ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
    assigned_to BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    camera_state JSON NULL,
    resolved_at TIMESTAMP NULL,
    closed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_issues_project (project_id),
    INDEX idx_issues_status (status),
    INDEX idx_issues_assigned (assigned_to),
    CONSTRAINT fk_issues_project
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_issues_submission
        FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE SET NULL,
    CONSTRAINT fk_issues_element
        FOREIGN KEY (element_id) REFERENCES project_elements(id) ON DELETE SET NULL,
    CONSTRAINT fk_issues_assigned
        FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_issues_creator
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE issue_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    issue_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    comment TEXT NOT NULL,
    attachment_url VARCHAR(500) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_issue_comments_issue
        FOREIGN KEY (issue_id) REFERENCES issues(id) ON DELETE CASCADE,
    CONSTRAINT fk_issue_comments_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE project_reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    submission_id BIGINT UNSIGNED NULL,
    reviewer_id BIGINT UNSIGNED NOT NULL,
    review_type ENUM('technical','assessment','final') NOT NULL DEFAULT 'technical',
    score DECIMAL(6,2) NULL,
    notes TEXT NULL,
    status ENUM('draft','completed') NOT NULL DEFAULT 'draft',
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_project_reviews_project
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_reviews_submission
        FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE SET NULL,
    CONSTRAINT fk_project_reviews_reviewer
        FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- 11. COMPETENCY / SCORE
-- ============================================================

CREATE TABLE competencies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NULL,
    code VARCHAR(100) NOT NULL,
    name VARCHAR(200) NOT NULL,
    category VARCHAR(150) NULL,
    description TEXT NULL,
    max_level INT UNSIGNED NOT NULL DEFAULT 5,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_competency_org_code (organization_id, code),
    CONSTRAINT fk_competencies_org
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE user_competencies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    organization_id BIGINT UNSIGNED NOT NULL,
    competency_id BIGINT UNSIGNED NOT NULL,
    current_level INT UNSIGNED NOT NULL DEFAULT 1,
    score DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    target_level INT UNSIGNED NULL,
    status ENUM('not_started','in_progress','achieved') NOT NULL DEFAULT 'not_started',
    last_assessed_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_competency (user_id, organization_id, competency_id),
    CONSTRAINT fk_user_competencies_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_competencies_org
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_competencies_competency
        FOREIGN KEY (competency_id) REFERENCES competencies(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE assessments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NULL,
    submission_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    assessor_id BIGINT UNSIGNED NULL,
    assessment_type ENUM('quiz','project','challenge','manual','final') NOT NULL,
    title VARCHAR(200) NOT NULL,
    score DECIMAL(6,2) NOT NULL,
    max_score DECIMAL(6,2) NOT NULL DEFAULT 100.00,
    result ENUM('pass','fail','in_progress') NOT NULL DEFAULT 'in_progress',
    rubric_result JSON NULL,
    notes TEXT NULL,
    assessed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_assessments_user (user_id),
    CONSTRAINT fk_assessments_org
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    CONSTRAINT fk_assessments_project
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    CONSTRAINT fk_assessments_submission
        FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE SET NULL,
    CONSTRAINT fk_assessments_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_assessments_assessor
        FOREIGN KEY (assessor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE assessment_competencies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assessment_id BIGINT UNSIGNED NOT NULL,
    competency_id BIGINT UNSIGNED NOT NULL,
    score DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    level_awarded INT UNSIGNED NULL,
    notes TEXT NULL,
    UNIQUE KEY uq_assessment_competency (assessment_id, competency_id),
    CONSTRAINT fk_assessment_competencies_assessment
        FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE,
    CONSTRAINT fk_assessment_competencies_competency
        FOREIGN KEY (competency_id) REFERENCES competencies(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 12. CERTIFICATE
-- ============================================================

CREATE TABLE certificates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    organization_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    training_program_id BIGINT UNSIGNED NULL,
    certificate_no VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    score DECIMAL(6,2) NULL,
    competency_level VARCHAR(100) NULL,
    issue_date DATE NOT NULL,
    expiry_date DATE NULL,
    verification_code VARCHAR(100) NOT NULL UNIQUE,
    pdf_url VARCHAR(500) NULL,
    status ENUM('active','revoked','expired') NOT NULL DEFAULT 'active',
    metadata JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_certificates_org
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    CONSTRAINT fk_certificates_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_certificates_program
        FOREIGN KEY (training_program_id) REFERENCES training_programs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 13. FILES
-- ============================================================

CREATE TABLE files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    organization_id BIGINT UNSIGNED NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NULL,
    file_type ENUM('image','document','video','ifc','model','attachment','certificate','other') NOT NULL DEFAULT 'other',
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(150) NULL,
    extension VARCHAR(50) NULL,
    size_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    storage_disk VARCHAR(50) NOT NULL DEFAULT 's3',
    storage_path VARCHAR(500) NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_files_project (project_id),
    CONSTRAINT fk_files_org
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    CONSTRAINT fk_files_user
        FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_files_project
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 14. AUDIT LOG
-- ============================================================

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    project_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NULL,
    entity_id VARCHAR(100) NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(64) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_org (organization_id),
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_project (project_id),
    INDEX idx_audit_action (action),
    CONSTRAINT fk_audit_org
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
    CONSTRAINT fk_audit_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_audit_project
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 15. NOTIFICATIONS
-- ============================================================

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    organization_id BIGINT UNSIGNED NULL,
    type VARCHAR(100) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    data JSON NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_user_read (user_id, read_at),
    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_notifications_org
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 16. IFC JOBS
-- ============================================================

CREATE TABLE ifc_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    project_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    job_type ENUM('import','export','validate','convert') NOT NULL,
    status ENUM('queued','processing','completed','failed') NOT NULL DEFAULT 'queued',
    source_file_id BIGINT UNSIGNED NULL,
    result_file_id BIGINT UNSIGNED NULL,
    progress_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    result JSON NULL,
    error_message TEXT NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ifc_jobs_project (project_id),
    CONSTRAINT fk_ifc_jobs_project
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_ifc_jobs_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ifc_jobs_source
        FOREIGN KEY (source_file_id) REFERENCES files(id) ON DELETE SET NULL,
    CONSTRAINT fk_ifc_jobs_result
        FOREIGN KEY (result_file_id) REFERENCES files(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 17. SEED MASTER DATA
-- ============================================================

INSERT INTO roles (name, code, scope, description) VALUES
('Super Admin', 'super_admin', 'platform', 'Akses penuh platform'),
('Organization Admin', 'organization_admin', 'organization', 'Admin organisasi'),
('Trainer', 'trainer', 'organization', 'Trainer dan reviewer'),
('Participant', 'participant', 'organization', 'Peserta pelatihan'),
('Project Manager', 'project_manager', 'project', 'Manager project'),
('Modeler', 'modeler', 'project', 'Editor BIM model'),
('Reviewer', 'reviewer', 'project', 'Reviewer project'),
('Viewer', 'viewer', 'project', 'Read only');

INSERT INTO permissions (name, code, module, description) VALUES
('View Dashboard', 'dashboard.view', 'dashboard', 'Melihat dashboard'),
('Manage Users', 'users.manage', 'users', 'Mengelola user'),
('Manage Training', 'training.manage', 'training', 'Mengelola program pelatihan'),
('View Training', 'training.view', 'training', 'Melihat pelatihan'),
('Create Project', 'projects.create', 'project', 'Membuat project'),
('View Project', 'projects.view', 'project', 'Melihat project'),
('Edit Project', 'projects.edit', 'project', 'Mengedit project'),
('Delete Project', 'projects.delete', 'project', 'Menghapus project'),
('Model BIM', 'bim.model', 'bim_studio', 'Membuat dan mengedit model BIM'),
('Validate BIM', 'bim.validate', 'bim_studio', 'Menjalankan validasi BIM'),
('Submit Project', 'project.submit', 'submission', 'Submit project'),
('Review Project', 'project.review', 'review', 'Review project'),
('Manage Issues', 'issues.manage', 'issue', 'Mengelola issue'),
('Manage Assessment', 'assessment.manage', 'assessment', 'Mengelola penilaian'),
('View Competency', 'competency.view', 'competency', 'Melihat kompetensi'),
('Manage Certificate', 'certificate.manage', 'certificate', 'Mengelola sertifikat');

-- Permission role mapping minimal
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p
WHERE r.code = 'super_admin';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p
WHERE r.code = 'organization_admin'
AND p.code IN (
    'dashboard.view','users.manage','training.manage','training.view',
    'projects.create','projects.view','projects.edit','projects.delete',
    'bim.model','bim.validate','project.submit','project.review',
    'issues.manage','assessment.manage','competency.view','certificate.manage'
);

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p
WHERE r.code = 'trainer'
AND p.code IN (
    'dashboard.view','training.view','projects.view','bim.validate',
    'project.review','issues.manage','assessment.manage',
    'competency.view','certificate.manage'
);

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p
WHERE r.code = 'participant'
AND p.code IN (
    'dashboard.view','training.view','projects.create','projects.view',
    'projects.edit','bim.model','bim.validate','project.submit',
    'competency.view'
);

-- Element types
INSERT INTO element_types (code, name, ifc_class, category, default_properties) VALUES
('LEVEL', 'Level', 'IfcBuildingStorey', 'spatial', JSON_OBJECT()),
('GRID', 'Grid', 'IfcGrid', 'reference', JSON_OBJECT()),
('WALL', 'Wall', 'IfcWall', 'architecture', JSON_OBJECT('height',3000,'thickness',150)),
('COLUMN', 'Column', 'IfcColumn', 'structure', JSON_OBJECT('width',300,'depth',300,'height',3000)),
('BEAM', 'Beam', 'IfcBeam', 'structure', JSON_OBJECT('width',250,'height',400)),
('SLAB', 'Slab', 'IfcSlab', 'architecture', JSON_OBJECT('thickness',120)),
('DOOR', 'Door', 'IfcDoor', 'architecture', JSON_OBJECT('width',900,'height',2100)),
('WINDOW', 'Window', 'IfcWindow', 'architecture', JSON_OBJECT('width',1200,'height',1200,'sillHeight',900));

-- Global materials
INSERT INTO materials (organization_id, name, code, category, color_hex, opacity) VALUES
(NULL, 'Concrete', 'MAT-CONCRETE', 'structure', '#B8B8B8', 1.000),
(NULL, 'Brick', 'MAT-BRICK', 'architecture', '#B46A55', 1.000),
(NULL, 'Glass', 'MAT-GLASS', 'architecture', '#9ED7E8', 0.350),
(NULL, 'Steel', 'MAT-STEEL', 'structure', '#8C949C', 1.000),
(NULL, 'Wood', 'MAT-WOOD', 'architecture', '#A7784F', 1.000),
(NULL, 'Gypsum', 'MAT-GYPSUM', 'architecture', '#E6E1D8', 1.000);

-- Competency master
INSERT INTO competencies (organization_id, code, name, category, description, max_level) VALUES
(NULL, 'BIM-FUND', 'BIM Fundamental', 'Fundamental', 'Konsep dasar BIM', 5),
(NULL, 'ARCH-MODEL', 'Architecture Modeling', 'Modeling', 'Pemodelan arsitektur', 5),
(NULL, 'STRUCT-MODEL', 'Structure Modeling', 'Modeling', 'Pemodelan struktur', 5),
(NULL, 'MEP-MODEL', 'MEP Modeling', 'Modeling', 'Pemodelan MEP', 5),
(NULL, 'BIM-COORD', 'BIM Coordination', 'Coordination', 'Koordinasi model BIM', 5),
(NULL, 'CLASH', 'Clash Detection', 'Coordination', 'Deteksi clash model', 5),
(NULL, 'BIM-MGMT', 'BIM Management', 'Management', 'Manajemen BIM', 5);

-- ============================================================
-- 18. DEMO DATA
-- Password values below are placeholder bcrypt strings for development.
-- ============================================================

INSERT INTO organizations
(uuid, name, code, email, phone, address, status, settings)
VALUES
(UUID(), 'BIM Nusantara', 'BIMNUS', 'info@bimnusantara.local', '081234567890',
 'Jakarta, Indonesia', 'active',
 JSON_OBJECT('theme','property','default_unit','mm'));

INSERT INTO users
(uuid, name, email, phone, password, status, email_verified_at)
VALUES
(UUID(), 'Admin BIM Nusantara', 'admin@bimnusantara.local', '081200000001',
 '$2y$12$LQv3c1yFBOQ5x9vJbKz7Pe6j2D9w7f1C6wGQ2x7m3G9vP0L1bPq6W',
 'active', NOW()),
(UUID(), 'Budi Santoso', 'trainer@bimnusantara.local', '081200000002',
 '$2y$12$LQv3c1yFBOQ5x9vJbKz7Pe6j2D9w7f1C6wGQ2x7m3G9vP0L1bPq6W',
 'active', NOW()),
(UUID(), 'Andi Dwiputra', 'andi@bimnusantara.local', '081200000003',
 '$2y$12$LQv3c1yFBOQ5x9vJbKz7Pe6j2D9w7f1C6wGQ2x7m3G9vP0L1bPq6W',
 'active', NOW());

SET @org_id = (SELECT id FROM organizations WHERE code='BIMNUS');
SET @role_admin = (SELECT id FROM roles WHERE code='organization_admin');
SET @role_trainer = (SELECT id FROM roles WHERE code='trainer');
SET @role_participant = (SELECT id FROM roles WHERE code='participant');
SET @admin_id = (SELECT id FROM users WHERE email='admin@bimnusantara.local');
SET @trainer_id = (SELECT id FROM users WHERE email='trainer@bimnusantara.local');
SET @participant_id = (SELECT id FROM users WHERE email='andi@bimnusantara.local');

INSERT INTO organization_users
(organization_id, user_id, role_id, title, joined_at)
VALUES
(@org_id, @admin_id, @role_admin, 'Administrator', CURDATE()),
(@org_id, @trainer_id, @role_trainer, 'BIM Trainer', CURDATE()),
(@org_id, @participant_id, @role_participant, 'Participant', CURDATE());

INSERT INTO training_programs
(uuid, organization_id, title, code, description, level, status,
 estimated_minutes, passing_score, created_by)
VALUES
(UUID(), @org_id, 'Pemodelan Arsitektur BIM', 'BIM-ARCH-001',
 'Program pembelajaran BIM Architecture Modeling berbasis project.',
 'beginner', 'published', 1440, 75.00, @admin_id);

SET @program_id = LAST_INSERT_ID();

INSERT INTO training_modules
(training_program_id, title, description, sort_order, estimated_minutes)
VALUES
(@program_id, 'Dasar-dasar BIM', 'Pengenalan konsep BIM.', 1, 180),
(@program_id, 'Pemodelan Arsitektur', 'Wall, door, window, slab.', 2, 480),
(@program_id, 'Koordinasi BIM', 'Review dan koordinasi model.', 3, 360),
(@program_id, 'Project Akhir', 'Simulasi project akhir.', 4, 420);

SET @module1 = (SELECT id FROM training_modules WHERE training_program_id=@program_id AND sort_order=1);
SET @module2 = (SELECT id FROM training_modules WHERE training_program_id=@program_id AND sort_order=2);

INSERT INTO lessons
(training_module_id, title, lesson_type, content, sort_order, estimated_minutes)
VALUES
(@module1, 'Pengenalan BIM', 'video', 'Pengenalan konsep dan manfaat BIM.', 1, 30),
(@module1, 'Workflow BIM', 'article', 'Alur kerja dasar BIM.', 2, 45),
(@module2, 'Membuat Level dan Grid', 'lab', 'Latihan level dan grid.', 1, 60),
(@module2, 'Membuat Wall', 'lab', 'Latihan membuat dinding.', 2, 90),
(@module2, 'Menambahkan Door & Window', 'lab', 'Latihan opening.', 3, 90);

INSERT INTO training_classes
(uuid, organization_id, training_program_id, name, code,
 start_date, end_date, trainer_id, status)
VALUES
(UUID(), @org_id, @program_id,
 'Kelas BIM Architecture Batch 01', 'BIM-ARCH-B01',
 CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY),
 @trainer_id, 'running');

SET @class_id = LAST_INSERT_ID();

INSERT INTO training_enrollments
(training_class_id, user_id, status, progress_percent)
VALUES
(@class_id, @participant_id, 'active', 35.00);

-- Project 1
INSERT INTO projects
(uuid, organization_id, training_class_id, owner_id, title, code,
 description, category, status, unit, progress_percent,
 start_date, deadline, settings)
VALUES
(UUID(), @org_id, @class_id, @participant_id,
 'Rumah Tinggal 2 Lantai', 'PRJ-BIM-001',
 'Project latihan pemodelan rumah tinggal dua lantai.',
 'training', 'active', 'mm', 40.00,
 CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY),
 JSON_OBJECT('snap',true,'gridSize',1000,'ortho',true));

SET @project1 = LAST_INSERT_ID();

-- Project 2
INSERT INTO projects
(uuid, organization_id, training_class_id, owner_id, title, code,
 description, category, status, unit, progress_percent,
 start_date, deadline, settings)
VALUES
(UUID(), @org_id, @class_id, @participant_id,
 'Gedung Kantor 4 Lantai', 'PRJ-BIM-002',
 'Project simulasi gedung kantor.',
 'simulation', 'draft', 'mm', 0.00,
 CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY),
 JSON_OBJECT('snap',true,'gridSize',1000,'ortho',true));

SET @project2 = LAST_INSERT_ID();

INSERT INTO project_members
(project_id, user_id, project_role, can_edit, can_review)
VALUES
(@project1, @participant_id, 'owner', 1, 0),
(@project1, @trainer_id, 'trainer', 0, 1),
(@project2, @participant_id, 'owner', 1, 0),
(@project2, @trainer_id, 'trainer', 0, 1);

-- Levels project 1
INSERT INTO project_levels
(uuid, project_id, name, elevation_mm, sort_order)
VALUES
(UUID(), @project1, 'Level 1 - Ground Floor', 0, 1),
(UUID(), @project1, 'Level 2 - Lantai 2', 3500, 2),
(UUID(), @project1, 'Roof', 7000, 3);

SET @level1 = (SELECT id FROM project_levels WHERE project_id=@project1 AND sort_order=1);
SET @level2 = (SELECT id FROM project_levels WHERE project_id=@project1 AND sort_order=2);

-- Grids project 1
INSERT INTO project_grids
(uuid, project_id, axis, label, position_mm)
VALUES
(UUID(), @project1, 'X', 'A', 0),
(UUID(), @project1, 'X', 'B', 4000),
(UUID(), @project1, 'X', 'C', 8000),
(UUID(), @project1, 'Y', '1', 0),
(UUID(), @project1, 'Y', '2', 5000),
(UUID(), @project1, 'Y', '3', 10000);

SET @wall_type = (SELECT id FROM element_types WHERE code='WALL');
SET @column_type = (SELECT id FROM element_types WHERE code='COLUMN');
SET @slab_type = (SELECT id FROM element_types WHERE code='SLAB');
SET @door_type = (SELECT id FROM element_types WHERE code='DOOR');
SET @window_type = (SELECT id FROM element_types WHERE code='WINDOW');
SET @concrete_mat = (SELECT id FROM materials WHERE code='MAT-CONCRETE');
SET @glass_mat = (SELECT id FROM materials WHERE code='MAT-GLASS');

-- Demo walls
INSERT INTO project_elements
(uuid, project_id, level_id, element_type_id, material_id,
 name, code, ifc_class, geometry, transform_data, properties,
 created_by)
VALUES
(UUID(), @project1, @level1, @wall_type, @concrete_mat,
 'Wall A-01', 'W-001', 'IfcWall',
 JSON_OBJECT(
    'start', JSON_OBJECT('x',0,'y',0),
    'end', JSON_OBJECT('x',8000,'y',0),
    'height',3000,
    'thickness',150,
    'baseOffset',0
 ),
 JSON_OBJECT(
    'position',JSON_ARRAY(0,0,0),
    'rotation',JSON_ARRAY(0,0,0),
    'scale',JSON_ARRAY(1,1,1)
 ),
 JSON_OBJECT('wallType','Basic Wall 150mm','structural',false),
 @participant_id),

(UUID(), @project1, @level1, @wall_type, @concrete_mat,
 'Wall A-02', 'W-002', 'IfcWall',
 JSON_OBJECT(
    'start', JSON_OBJECT('x',8000,'y',0),
    'end', JSON_OBJECT('x',8000,'y',10000),
    'height',3000,
    'thickness',150,
    'baseOffset',0
 ),
 JSON_OBJECT(
    'position',JSON_ARRAY(0,0,0),
    'rotation',JSON_ARRAY(0,0,0),
    'scale',JSON_ARRAY(1,1,1)
 ),
 JSON_OBJECT('wallType','Basic Wall 150mm','structural',false),
 @participant_id),

(UUID(), @project1, @level1, @wall_type, @concrete_mat,
 'Wall A-03', 'W-003', 'IfcWall',
 JSON_OBJECT(
    'start', JSON_OBJECT('x',8000,'y',10000),
    'end', JSON_OBJECT('x',0,'y',10000),
    'height',3000,
    'thickness',150,
    'baseOffset',0
 ),
 JSON_OBJECT(
    'position',JSON_ARRAY(0,0,0),
    'rotation',JSON_ARRAY(0,0,0),
    'scale',JSON_ARRAY(1,1,1)
 ),
 JSON_OBJECT('wallType','Basic Wall 150mm','structural',false),
 @participant_id),

(UUID(), @project1, @level1, @wall_type, @concrete_mat,
 'Wall A-04', 'W-004', 'IfcWall',
 JSON_OBJECT(
    'start', JSON_OBJECT('x',0,'y',10000),
    'end', JSON_OBJECT('x',0,'y',0),
    'height',3000,
    'thickness',150,
    'baseOffset',0
 ),
 JSON_OBJECT(
    'position',JSON_ARRAY(0,0,0),
    'rotation',JSON_ARRAY(0,0,0),
    'scale',JSON_ARRAY(1,1,1)
 ),
 JSON_OBJECT('wallType','Basic Wall 150mm','structural',false),
 @participant_id);

-- Columns
INSERT INTO project_elements
(uuid, project_id, level_id, element_type_id, material_id,
 name, code, ifc_class, geometry, transform_data, properties, created_by)
VALUES
(UUID(), @project1, @level1, @column_type, @concrete_mat,
 'Column C-01', 'C-001', 'IfcColumn',
 JSON_OBJECT('width',300,'depth',300,'height',3000,'baseOffset',0),
 JSON_OBJECT('position',JSON_ARRAY(0,0,0),'rotation',JSON_ARRAY(0,0,0),'scale',JSON_ARRAY(1,1,1)),
 JSON_OBJECT('columnType','Concrete 300x300'),
 @participant_id),
(UUID(), @project1, @level1, @column_type, @concrete_mat,
 'Column C-02', 'C-002', 'IfcColumn',
 JSON_OBJECT('width',300,'depth',300,'height',3000,'baseOffset',0),
 JSON_OBJECT('position',JSON_ARRAY(8000,0,0),'rotation',JSON_ARRAY(0,0,0),'scale',JSON_ARRAY(1,1,1)),
 JSON_OBJECT('columnType','Concrete 300x300'),
 @participant_id);

-- Slab
INSERT INTO project_elements
(uuid, project_id, level_id, element_type_id, material_id,
 name, code, ifc_class, geometry, properties, created_by)
VALUES
(UUID(), @project1, @level1, @slab_type, @concrete_mat,
 'Slab Level 1', 'S-001', 'IfcSlab',
 JSON_OBJECT(
    'polygon', JSON_ARRAY(
        JSON_OBJECT('x',0,'y',0),
        JSON_OBJECT('x',8000,'y',0),
        JSON_OBJECT('x',8000,'y',10000),
        JSON_OBJECT('x',0,'y',10000)
    ),
    'thickness',120,
    'baseOffset',0
 ),
 JSON_OBJECT('slabType','Concrete 120mm'),
 @participant_id);

-- Door on first wall
SET @wall1 = (SELECT id FROM project_elements WHERE project_id=@project1 AND code='W-001');

INSERT INTO project_elements
(uuid, project_id, level_id, parent_element_id, element_type_id, material_id,
 name, code, ifc_class, geometry, properties, created_by)
VALUES
(UUID(), @project1, @level1, @wall1, @door_type, NULL,
 'Main Door', 'D-001', 'IfcDoor',
 JSON_OBJECT('offset',3000,'width',900,'height',2100,'sillHeight',0),
 JSON_OBJECT('doorType','Single Flush'),
 @participant_id);

SET @door1 = LAST_INSERT_ID();

INSERT INTO element_relationships
(project_id, source_element_id, target_element_id, relationship_type)
VALUES
(@project1, @door1, @wall1, 'hosted_by');

-- Window
INSERT INTO project_elements
(uuid, project_id, level_id, parent_element_id, element_type_id, material_id,
 name, code, ifc_class, geometry, properties, created_by)
VALUES
(UUID(), @project1, @level1, @wall1, @window_type, @glass_mat,
 'Window WN-001', 'WN-001', 'IfcWindow',
 JSON_OBJECT('offset',5000,'width',1200,'height',1200,'sillHeight',900),
 JSON_OBJECT('windowType','Sliding Window'),
 @participant_id);

SET @window1 = LAST_INSERT_ID();

INSERT INTO element_relationships
(project_id, source_element_id, target_element_id, relationship_type)
VALUES
(@project1, @window1, @wall1, 'hosted_by');

-- Version snapshot demo
INSERT INTO project_versions
(uuid, project_id, version_no, label, version_type, snapshot, notes, created_by)
VALUES
(UUID(), @project1, 1, 'Initial Model', 'manual',
 JSON_OBJECT(
    'schemaVersion','1.0.0',
    'projectId',@project1,
    'notes','Initial demo snapshot'
 ),
 'Snapshot awal project',
 @participant_id);

-- BIM Lab demo
SET @lesson_lab = (
    SELECT id FROM lessons
    WHERE training_module_id=@module2
    AND lesson_type='lab'
    ORDER BY sort_order
    LIMIT 1
);

INSERT INTO bim_labs
(uuid, organization_id, lesson_id, title, description,
 difficulty, estimated_minutes, template_project_id,
 instructions, status, created_by)
VALUES
(UUID(), @org_id, @lesson_lab,
 'Architecture Modeling - Rumah Tinggal 2 Lantai',
 'Praktik level, grid, wall, door, window, dan slab.',
 'beginner', 240, @project1,
 JSON_ARRAY(
    JSON_OBJECT('step',1,'title','Persiapan Project'),
    JSON_OBJECT('step',2,'title','Membuat Level'),
    JSON_OBJECT('step',3,'title','Membuat Grid'),
    JSON_OBJECT('step',4,'title','Modeling Dinding'),
    JSON_OBJECT('step',5,'title','Menambahkan Pintu & Jendela'),
    JSON_OBJECT('step',6,'title','Finishing & Export')
 ),
 'published', @trainer_id);

SET @lab_id = LAST_INSERT_ID();

INSERT INTO challenges
(uuid, organization_id, bim_lab_id, title, description,
 difficulty, duration_minutes, maximum_score, passing_score,
 project_template_id, status, created_by)
VALUES
(UUID(), @org_id, @lab_id,
 'Model Rumah Tinggal Dasar',
 'Buat model rumah tinggal sederhana sesuai rule.',
 'beginner', 120, 100, 75,
 @project1, 'published', @trainer_id);

SET @challenge_id = LAST_INSERT_ID();

INSERT INTO challenge_rules
(challenge_id, rule_code, rule_type, name, config, weight, severity, sort_order)
VALUES
(@challenge_id, 'RULE-LEVEL-2', 'REQUIRED_LEVEL',
 'Minimal dua level',
 JSON_OBJECT('minCount',2),
 10, 'error', 1),
(@challenge_id, 'RULE-WALL-12', 'MIN_ELEMENT_COUNT',
 'Minimal 12 wall',
 JSON_OBJECT('elementType','WALL','minCount',12),
 25, 'error', 2),
(@challenge_id, 'RULE-COLUMN-4', 'MIN_ELEMENT_COUNT',
 'Minimal 4 column',
 JSON_OBJECT('elementType','COLUMN','minCount',4),
 15, 'error', 3),
(@challenge_id, 'RULE-DOOR-3', 'MIN_ELEMENT_COUNT',
 'Minimal 3 door',
 JSON_OBJECT('elementType','DOOR','minCount',3),
 15, 'error', 4),
(@challenge_id, 'RULE-WINDOW-6', 'MIN_ELEMENT_COUNT',
 'Minimal 6 window',
 JSON_OBJECT('elementType','WINDOW','minCount',6),
 15, 'error', 5),
(@challenge_id, 'RULE-HOST-OPENING', 'HOST_REQUIRED',
 'Door dan window wajib memiliki host wall',
 JSON_OBJECT('elementTypes',JSON_ARRAY('DOOR','WINDOW')),
 20, 'error', 6);

-- Competency user demo
INSERT INTO user_competencies
(user_id, organization_id, competency_id, current_level, score, target_level, status, last_assessed_at)
SELECT
@participant_id, @org_id, id,
CASE code
    WHEN 'BIM-FUND' THEN 3
    WHEN 'ARCH-MODEL' THEN 2
    ELSE 1
END,
CASE code
    WHEN 'BIM-FUND' THEN 90
    WHEN 'ARCH-MODEL' THEN 78
    WHEN 'STRUCT-MODEL' THEN 55
    WHEN 'MEP-MODEL' THEN 40
    WHEN 'BIM-COORD' THEN 58
    WHEN 'CLASH' THEN 60
    WHEN 'BIM-MGMT' THEN 35
END,
4,
'in_progress',
NOW()
FROM competencies;

-- Demo issue
INSERT INTO issues
(uuid, project_id, element_id, title, description, severity, status,
 assigned_to, created_by, camera_state)
VALUES
(UUID(), @project1, @wall1,
 'Wall belum align dengan Grid A',
 'Periksa posisi dinding terhadap grid dan lakukan koreksi.',
 'minor', 'open',
 @participant_id, @trainer_id,
 JSON_OBJECT(
    'position',JSON_ARRAY(10,8,12),
    'target',JSON_ARRAY(4,0,5),
    'zoom',1.2
 ));

-- ============================================================
-- 19. USEFUL VIEWS
-- ============================================================

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
    p.status, p.progress_percent, p.owner_id, u.name;

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
JOIN training_programs tp ON tp.id = tc.training_program_id;

CREATE OR REPLACE VIEW vw_project_element_counts AS
SELECT
    pe.project_id,
    et.code AS element_type,
    et.name AS element_name,
    COUNT(*) AS total
FROM project_elements pe
JOIN element_types et ON et.id = pe.element_type_id
WHERE pe.deleted_at IS NULL
GROUP BY pe.project_id, et.code, et.name;

-- ============================================================
-- 20. BASIC STORED PROCEDURE: DUPLICATE PROJECT
-- ============================================================

DELIMITER $$

CREATE PROCEDURE sp_duplicate_project (
    IN p_source_project_id BIGINT UNSIGNED,
    IN p_new_owner_id BIGINT UNSIGNED,
    IN p_new_title VARCHAR(200),
    IN p_new_code VARCHAR(100)
)
BEGIN
    DECLARE v_org_id BIGINT UNSIGNED;
    DECLARE v_class_id BIGINT UNSIGNED;
    DECLARE v_new_project_id BIGINT UNSIGNED;

    SELECT organization_id, training_class_id
    INTO v_org_id, v_class_id
    FROM projects
    WHERE id = p_source_project_id
    LIMIT 1;

    INSERT INTO projects (
        uuid, organization_id, training_class_id, owner_id,
        title, code, description, category, status,
        unit, schema_version, progress_percent, settings
    )
    SELECT
        UUID(), organization_id, training_class_id, p_new_owner_id,
        p_new_title, p_new_code, description, category, 'draft',
        unit, schema_version, 0, settings
    FROM projects
    WHERE id = p_source_project_id;

    SET v_new_project_id = LAST_INSERT_ID();

    INSERT INTO project_members (
        project_id, user_id, project_role, can_edit, can_review
    )
    VALUES (
        v_new_project_id, p_new_owner_id, 'owner', 1, 0
    );

    -- Level dan element sengaja tidak langsung dicopy penuh
    -- agar aplikasi dapat memutuskan apakah ini template kosong
    -- atau duplicate lengkap melalui service layer.
END$$

DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- END OF FILE
-- ============================================================
