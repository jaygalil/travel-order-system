<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TravelOrderController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\WorkflowTemplateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Email approval routes (no auth required)
Route::get('/approval/{token}', [ApprovalController::class, 'showEmailApproval'])->name('approval.email.show');
Route::post('/approval/{token}', [ApprovalController::class, 'processEmailApproval'])->name('approval.email.process');

// Authentication routes
Auth::routes();

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [DashboardController::class, 'index'])->name('home');
    
    // Travel Orders
    Route::resource('travel-orders', TravelOrderController::class);
    Route::post('/travel-orders/{travelOrder}/submit', [TravelOrderController::class, 'submit'])->name('travel-orders.submit');
    Route::post('/travel-orders/{travelOrder}/cancel', [TravelOrderController::class, 'cancel'])->name('travel-orders.cancel');
    
    // PDF Generation
    Route::get('/travel-orders/{travelOrder}/pdf', [PDFController::class, 'generatePDF'])->name('travel-orders.pdf');
    Route::get('/travel-orders/{travelOrder}/preview', [PDFController::class, 'previewPDF'])->name('travel-orders.pdf.preview');
    
    // Approvals Management
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('/approvals/{approval}', [ApprovalController::class, 'show'])->name('approvals.show');
    Route::post('/approvals/{approval}/process', [ApprovalController::class, 'process'])->name('approvals.process');
    
    // Workflow Template Routes
    Route::resource('workflows', WorkflowTemplateController::class)->parameters([
        'workflows' => 'id'
    ]);
    Route::post('workflows/{id}/set-default', [WorkflowTemplateController::class, 'setDefault'])->name('workflows.set-default');
    
    // Simple API endpoint for user selection
    Route::get('/api/users', function (Illuminate\Http\Request $request) {
        $query = App\Models\User::select('id', 'name', 'email')
            ->where('is_active', true);
        
        // Search by name or email
        if ($search = $request->get('q')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }
        
        return response()->json(
            $query->orderBy('name')
                  ->get()
                  ->map(function($user) {
                      return [
                          'id' => $user->id,
                          'name' => $user->name,
                          'email' => $user->email,
                          'display' => $user->name . ' (' . $user->email . ')'
                      ];
                  })
        );
    })->name('api.users');
    
    // API endpoint for workflow template details
    Route::get('/api/workflow-templates/{id}', function ($id) {
        $template = App\Models\WorkflowTemplate::with(['steps' => function($query) {
            $query->orderBy('sequence')->orderBy('step_group')->orderBy('group_order');
        }])->find($id);
        
        if (!$template) {
            return response()->json(['error' => 'Template not found'], 404);
        }
        
        return response()->json([
            'id' => $template->id,
            'name' => $template->name,
            'description' => $template->description,
            'layout' => $template->layout,
            'steps' => $template->steps
        ]);
    })->name('api.workflow-templates.show');
    
    // Admin routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/travel-orders', [TravelOrderController::class, 'adminIndex'])->name('admin.travel-orders.index');
        Route::get('/admin/reports', [DashboardController::class, 'reports'])->name('admin.reports');
        
        // User Management Routes
        Route::prefix('admin/users')->name('admin.users.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\UserManagementController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\UserManagementController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\UserManagementController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [App\Http\Controllers\Admin\UserManagementController::class, 'edit'])->name('edit');
            Route::put('/{user}', [App\Http\Controllers\Admin\UserManagementController::class, 'update'])->name('update');
            Route::get('/import', [App\Http\Controllers\Admin\UserManagementController::class, 'showImport'])->name('import');
            Route::post('/import', [App\Http\Controllers\Admin\UserManagementController::class, 'import'])->name('import.process');
            Route::get('/template', [App\Http\Controllers\Admin\UserManagementController::class, 'downloadTemplate'])->name('template');
        });
    });
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
