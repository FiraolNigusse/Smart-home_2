<?php

namespace App\Http\Controllers;

use App\Models\Rule;
use App\Models\Role;
use App\Models\Device;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class RuleController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
        // Middleware is already applied in routes/web.php
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rules = Rule::with(['role', 'device'])->orderBy('created_at', 'desc')->get();
        return view('rules.index', compact('rules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $devices = Device::where('is_active', true)->get();
        return view('rules.create', compact('roles', 'devices'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'role_id' => 'nullable|exists:roles,id',
            'device_id' => 'nullable|exists:devices,id',
            'action' => 'nullable|string|max:255',
            'condition_type' => 'required|string|in:time_window,day_of_week,always',
            'condition_params' => 'required|json',
            'effect' => 'required|string|in:allow,deny',
            'denial_message' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Decode JSON condition_params to array
        $validated['condition_params'] = json_decode($validated['condition_params'], true);

        $rule = Rule::create($validated);

        $this->auditLogService->logAllowed(
            auth()->user(),
            null,
            'create_rule',
            $request,
            ['rule_name' => $rule->name]
        );

        return redirect()->route('rules.index')
            ->with('success', 'Rule created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Rule $rule)
    {
        $rule->load(['role', 'device']);
        return view('rules.show', compact('rule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rule $rule)
    {
        $roles = Role::all();
        $devices = Device::where('is_active', true)->get();
        return view('rules.edit', compact('rule', 'roles', 'devices'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rule $rule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'role_id' => 'nullable|exists:roles,id',
            'device_id' => 'nullable|exists:devices,id',
            'action' => 'nullable|string|max:255',
            'condition_type' => 'required|string|in:time_window,day_of_week,always',
            'condition_params' => 'required|json',
            'effect' => 'required|string|in:allow,deny',
            'denial_message' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Decode JSON condition_params to array
        $validated['condition_params'] = json_decode($validated['condition_params'], true);

        $rule->update($validated);

        $this->auditLogService->logAllowed(
            auth()->user(),
            null,
            'update_rule',
            $request,
            ['rule_name' => $rule->name, 'changes' => $validated]
        );

        return redirect()->route('rules.index')
            ->with('success', 'Rule updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rule $rule)
    {
        $this->auditLogService->logAllowed(
            auth()->user(),
            null,
            'delete_rule',
            request(),
            ['rule_name' => $rule->name]
        );

        $rule->delete();

        return redirect()->route('rules.index')
            ->with('success', 'Rule deleted successfully.');
    }
}
