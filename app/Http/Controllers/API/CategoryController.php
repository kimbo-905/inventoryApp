<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AuditLogController;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Category::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name'
        ]);

        $category = Category::create([
            'name' => $request->name
        ]);
        AuditLogController::logAudit('crée', 'catégorie', $category->id, 'Catégorie ajouté: ' . $category->name);    
        return response()->json($category, 201);
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $oldName = $category->name;//

        $request->validate([
            'name' => 'required|string|unique:categories,name,' . $id
        ]);
        $category->update(['name' => $request->name]);
        // AuditLogController::logAudit('modifié', 'catégorie', $category->id, 'Catégorie modifié: ' . $category->name);    
        $changeMsg = $oldName !== $request['name']//
        ? "Nom modifié: $oldName → {$request['name']}"
        : "Aucune modification détectée.";

            AuditLogController::logAudit(//
                'modifié',
                'catégories',
                $category->id,
                "Catégorie modifiée: {$request['name']} — $changeMsg"
            );
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        AuditLogController::logAudit('supprimé', 'catégorie', $category->id, 'Catégorie supprimé: ' . $category->name);    
        return response()->json(['message' => 'Category deleted']);
    }

}
