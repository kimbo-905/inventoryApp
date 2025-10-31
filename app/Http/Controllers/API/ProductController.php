<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AuditLogController;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     return response()->json(Product::with('category')->get());
    // }
    public function index(Request $request)
    {
        $query = Product::query();
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('sku')) {
            $query->where('sku', 'like', '%' . $request->sku . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        return $query->with('category')->orderBy('created_at', 'desc')->paginate(10);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:products,name',
            'sku' => 'required|string|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        $product = Product::create($validated);
        AuditLogController::logAudit('crée', 'produits', $product->id, 'Produit ajouté: ' . $product->name);    
        return response()->json($product, 201);
    }

    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product = Product::with('category')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:products,name,' . $id,
            'sku' => 'required|string|unique:products,sku,' . $id,
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validated = $validator->validated();

        $oldData = $product->toArray(); //
        $oldCategoryName = optional($product->category)->name; //

        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }
        $product->update($validated);

        // +++++++++
        $product->load('category'); // recharge la nouvelle catégorie
        $changes = [];
        foreach ($validated as $key => $newValue) {
            $oldValue = $oldData[$key] ?? null;
            // Si le champ est `category_id`, compare les noms
            if ($key === 'category_id') {
                $newCategoryName = optional($product->category)->name;
                if ($oldCategoryName !== $newCategoryName) {
                    $changes[] = "catégorie (ancien: $oldCategoryName → nouveau: $newCategoryName)";
                }
            } elseif ($oldValue != $newValue) {
                $changes[] = "$key (ancien: $oldValue → nouveau: $newValue)";
            }
        }
        $changeMessage = count($changes)
            ? 'Champs modifiés: ' . implode(', ', $changes)
            : 'Aucune modification détectée.';
        // Log
        AuditLogController::logAudit(
            'modifié',
            'produits',
            $product->id,
            'Produit modifié: ' . $product->name . ' — ' . $changeMessage
        );
        return response()->json($product);
    }
    

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        AuditLogController::logAudit('Supprimé', 'produits', $product->id, 'Produit supprimé: ' . $product->name);    
        return response()->json(['message' => 'Produit supprimé']);
    }
}
