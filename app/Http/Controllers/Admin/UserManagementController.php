<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Imports\UsersImport;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with('roles');
        
        // Filter by role
        if ($request->filled('role')) {
            $query->role($request->role);
        }
        
        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }
        
        $users = $query->orderBy('name')->paginate(15);
        $roles = Role::all();
        
        return view('admin.users.index', compact('users', 'roles'));
    }
    
    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }
    
    /**
     * Store a newly created user in storage
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'role' => 'required|exists:roles,name',
            'approver_sequence' => 'nullable|integer|min:1|max:10',
            'approver_title' => 'nullable|string|max:255',
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('12345678!@#'), // Default password
            'position' => $request->position,
            'department' => $request->department,
            'approver_sequence' => $request->approver_sequence,
            'approver_title' => $request->approver_title,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        
        $user->assignRole($request->role);
        
        return redirect()->route('admin.users.index')
                        ->with('success', 'User created successfully.');
    }
    
    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }
    
    /**
     * Update the specified user in storage
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'role' => 'required|exists:roles,name',
            'approver_sequence' => 'nullable|integer|min:1|max:10',
            'approver_title' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'position' => $request->position,
            'department' => $request->department,
            'approver_sequence' => $request->approver_sequence,
            'approver_title' => $request->approver_title,
            'is_active' => $request->boolean('is_active', true),
        ]);
        
        // Update role
        $user->syncRoles([$request->role]);
        
        return redirect()->route('admin.users.index')
                        ->with('success', 'User updated successfully.');
    }
    
    /**
     * Show import form
     */
    public function showImport()
    {
        return view('admin.users.import');
    }
    
    /**
     * Import users from file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);
        
        try {
            $file = $request->file('file');
            $path = $file->store('temp');
            $fullPath = storage_path('app/' . $path);
            
            $importer = new UsersImport();
            $importer->import($fullPath);
            
            // Clean up temporary file
            unlink($fullPath);
            
            return redirect()->route('admin.users.index')
                            ->with('success', 'Users imported successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Import failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Download sample template
     */
    public function downloadTemplate()
    {
        $templatePath = storage_path('templates/users_template.csv');
        
        if (!file_exists($templatePath)) {
            abort(404, 'Template file not found.');
        }
        
        return response()->download($templatePath, 'users_import_template.csv');
    }
}
