<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return AuditLog::with('user')
            ->orderByDesc('created_at')
            ->paginate(10); // ✅ this is correct
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       //
    }


    /**
     * Display the specified resource.
     */
    public function show(AuditLog $auditLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AuditLog $auditLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AuditLog $auditLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AuditLog $auditLog)
    {
        //
    }

    public static function logAudit($action, $table, $recordId, $description = null)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'table_name' => $table,
            'record_id' => $recordId,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
