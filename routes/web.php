<?php

use App\Http\Controllers\BimCrudController;
use App\Http\Controllers\BimPlatformController;
use Illuminate\Support\Facades\Route;

// Main Pages
Route::get('/', [BimPlatformController::class, 'dashboard'])->name('home');
Route::get('/dashboard', [BimPlatformController::class, 'dashboard'])->name('dashboard');
Route::get('/belajar', [BimPlatformController::class, 'belajar'])->name('belajar');
Route::get('/bim-lab', [BimPlatformController::class, 'bimLab'])->name('bim-lab');
Route::get('/bim-studio', [BimPlatformController::class, 'bimStudio'])->name('bim-studio');
Route::get('/project', [BimPlatformController::class, 'project'])->name('project');
Route::get('/kompetensi', [BimPlatformController::class, 'kompetensi'])->name('kompetensi');
Route::get('/profil', [BimPlatformController::class, 'profil'])->name('profil');

// Optional .html redirects for compatibility
Route::redirect('/dashboard.html', '/dashboard');
Route::redirect('/belajar.html', '/belajar');
Route::redirect('/bim-lab.html', '/bim-lab');
Route::redirect('/bim-studio.html', '/bim-studio');
Route::redirect('/project.html', '/project');
Route::redirect('/kompetensi.html', '/kompetensi');
Route::redirect('/profil.html', '/profil');

// Global Search & Context Switcher
Route::get('/api/search', [BimPlatformController::class, 'search'])->name('search');
Route::post('/switch-user', [BimCrudController::class, 'switchUser'])->name('switch-user');
Route::post('/notifications/mark-read', [BimCrudController::class, 'markNotificationsRead'])->name('notifications.mark-read');

// Belajar BIM CRUD
Route::post('/belajar/modules', [BimCrudController::class, 'storeModule'])->name('belajar.modules.store');
Route::put('/belajar/modules/{id}', [BimCrudController::class, 'updateModule'])->name('belajar.modules.update');
Route::delete('/belajar/modules/{id}', [BimCrudController::class, 'destroyModule'])->name('belajar.modules.destroy');

Route::post('/belajar/lessons', [BimCrudController::class, 'storeLesson'])->name('belajar.lessons.store');
Route::put('/belajar/lessons/{id}', [BimCrudController::class, 'updateLesson'])->name('belajar.lessons.update');
Route::delete('/belajar/lessons/{id}', [BimCrudController::class, 'destroyLesson'])->name('belajar.lessons.destroy');
Route::post('/belajar/lessons/{id}/toggle-progress', [BimCrudController::class, 'toggleLessonProgress'])->name('belajar.lessons.toggle-progress');

Route::post('/belajar/files', [BimCrudController::class, 'storeFile'])->name('belajar.files.store');
Route::put('/belajar/files/{id}', [BimCrudController::class, 'updateFile'])->name('belajar.files.update');
Route::delete('/belajar/files/{id}', [BimCrudController::class, 'destroyFile'])->name('belajar.files.destroy');
Route::get('/belajar/files/{id}/download', [BimCrudController::class, 'downloadFile'])->name('belajar.files.download');

Route::post('/belajar/quizzes/{id}/submit', [BimCrudController::class, 'submitQuiz'])->name('belajar.quizzes.submit');
Route::post('/belajar/quizzes/{id}/questions', [BimCrudController::class, 'storeQuizQuestion'])->name('belajar.quizzes.questions.store');
Route::delete('/belajar/quiz-questions/{id}', [BimCrudController::class, 'destroyQuizQuestion'])->name('belajar.quiz-questions.destroy');

Route::post('/belajar/classes', [BimCrudController::class, 'storeClass'])->name('belajar.classes.store');
Route::put('/belajar/classes/{id}', [BimCrudController::class, 'updateClass'])->name('belajar.classes.update');
Route::delete('/belajar/classes/{id}', [BimCrudController::class, 'destroyClass'])->name('belajar.classes.destroy');

// BIM Lab CRUD
Route::post('/bim-lab/labs', [BimCrudController::class, 'storeLab'])->name('bim-lab.labs.store');
Route::put('/bim-lab/labs/{id}', [BimCrudController::class, 'updateLab'])->name('bim-lab.labs.update');
Route::delete('/bim-lab/labs/{id}', [BimCrudController::class, 'destroyLab'])->name('bim-lab.labs.destroy');

Route::post('/bim-lab/labs/{id}/steps', [BimCrudController::class, 'storeLabStep'])->name('bim-lab.steps.store');
Route::put('/bim-lab/labs/{id}/steps/{stepIndex}', [BimCrudController::class, 'updateLabStep'])->name('bim-lab.steps.update');
Route::delete('/bim-lab/labs/{id}/steps/{stepIndex}', [BimCrudController::class, 'destroyLabStep'])->name('bim-lab.steps.destroy');
Route::post('/bim-lab/labs/{id}/steps/{stepIndex}/toggle-complete', [BimCrudController::class, 'toggleLabStepComplete'])->name('bim-lab.steps.toggle-complete');
Route::post('/bim-lab/labs/{id}/steps/{stepIndex}/toggle-checklist', [BimCrudController::class, 'toggleLabChecklist'])->name('bim-lab.steps.toggle-checklist');

// BIM Studio CRUD
Route::post('/bim-studio/elements', [BimCrudController::class, 'storeStudioElement'])->name('bim-studio.elements.store');
Route::put('/bim-studio/elements/{id}', [BimCrudController::class, 'updateStudioElement'])->name('bim-studio.elements.update');
Route::delete('/bim-studio/elements/{id}', [BimCrudController::class, 'destroyStudioElement'])->name('bim-studio.elements.destroy');

Route::post('/bim-studio/levels', [BimCrudController::class, 'storeStudioLevel'])->name('bim-studio.levels.store');
Route::delete('/bim-studio/levels/{id}', [BimCrudController::class, 'destroyStudioLevel'])->name('bim-studio.levels.destroy');

Route::post('/bim-studio/grids', [BimCrudController::class, 'storeStudioGrid'])->name('bim-studio.grids.store');
Route::delete('/bim-studio/grids/{id}', [BimCrudController::class, 'destroyStudioGrid'])->name('bim-studio.grids.destroy');

Route::post('/bim-studio/projects/{id}/save-version', [BimCrudController::class, 'saveStudioVersion'])->name('bim-studio.projects.save-version');
Route::post('/bim-studio/projects/{id}/validate', [BimCrudController::class, 'validateStudioModel'])->name('bim-studio.projects.validate');
Route::post('/bim-studio/projects/{id}/submit', [BimCrudController::class, 'submitStudioToProject'])->name('bim-studio.projects.submit');

// Project Management CRUD
Route::post('/project/projects', [BimCrudController::class, 'storeProject'])->name('project.projects.store');
Route::put('/project/projects/{id}', [BimCrudController::class, 'updateProject'])->name('project.projects.update');
Route::delete('/project/projects/{id}', [BimCrudController::class, 'destroyProject'])->name('project.projects.destroy');

Route::post('/project/issues', [BimCrudController::class, 'storeIssue'])->name('project.issues.store');
Route::put('/project/issues/{id}', [BimCrudController::class, 'updateIssue'])->name('project.issues.update');
Route::delete('/project/issues/{id}', [BimCrudController::class, 'destroyIssue'])->name('project.issues.destroy');
Route::post('/project/issues/{id}/comments', [BimCrudController::class, 'storeIssueComment'])->name('project.issues.comments.store');

Route::post('/project/reviews', [BimCrudController::class, 'storeReview'])->name('project.reviews.store');
Route::delete('/project/reviews/{id}', [BimCrudController::class, 'destroyReview'])->name('project.reviews.destroy');

Route::post('/project/assignments', [BimCrudController::class, 'storeAssignment'])->name('project.assignments.store');
Route::delete('/project/assignments/{id}', [BimCrudController::class, 'destroyAssignment'])->name('project.assignments.destroy');

Route::post('/project/submissions', [BimCrudController::class, 'storeSubmission'])->name('project.submissions.store');
Route::delete('/project/submissions/{id}', [BimCrudController::class, 'destroySubmission'])->name('project.submissions.destroy');

// Kompetensi CRUD
Route::post('/kompetensi/competencies', [BimCrudController::class, 'storeCompetency'])->name('kompetensi.competencies.store');
Route::put('/kompetensi/competencies/{id}', [BimCrudController::class, 'updateCompetency'])->name('kompetensi.competencies.update');
Route::delete('/kompetensi/competencies/{id}', [BimCrudController::class, 'destroyCompetency'])->name('kompetensi.competencies.destroy');

Route::post('/kompetensi/assessments', [BimCrudController::class, 'storeAssessment'])->name('kompetensi.assessments.store');
Route::put('/kompetensi/assessments/{id}', [BimCrudController::class, 'updateAssessment'])->name('kompetensi.assessments.update');
Route::delete('/kompetensi/assessments/{id}', [BimCrudController::class, 'destroyAssessment'])->name('kompetensi.assessments.destroy');

// Profil & Sertifikat CRUD
Route::put('/profil/update', [BimCrudController::class, 'updateProfile'])->name('profil.update');
Route::post('/profil/certificates', [BimCrudController::class, 'storeCertificate'])->name('profil.certificates.store');
Route::put('/profil/certificates/{id}', [BimCrudController::class, 'updateCertificate'])->name('profil.certificates.update');
Route::delete('/profil/certificates/{id}', [BimCrudController::class, 'destroyCertificate'])->name('profil.certificates.destroy');
