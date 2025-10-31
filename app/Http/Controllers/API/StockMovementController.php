<?php
namespace App\Http\Controllers\API;
use App\Models\Product;
use App\Mail\LowStockAlert;
use Illuminate\Http\Request;
use App\Models\StockMovement;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AuditLogController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Exports\StockMovementsExport;
use Maatwebsite\Excel\Facades\Excel;

class StockMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     return StockMovement::with('product.category')->orderByDesc('created_at')->get();
    // }

    public function index(Request $request)
    {
        $query = StockMovement::with('product.category')->orderByDesc('created_at');
        // Filtres dynamiques
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('product')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->product . '%');
            });
        }
        if ($request->filled('category')) {
            $query->whereHas('product.category', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->category . '%');
            });
        }
        if ($request->filled('destination')) {
            $query->where('destination', 'like', '%' . $request->destination . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        return $query->paginate(10);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:entree,sortie',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'destination' => 'nullable|string|required_if:type,sortie',
        ]);
        $product = Product::findOrFail($validated['product_id']);
        if ($validated['type'] === 'sortie' && $product->quantity < $validated['quantity']) {
            return response()->json(['error' => 'Quantité insuffisante en stock'], 400);
        }
        if ($validated['type'] === 'entree') {
            $product->increment('quantity', $validated['quantity']);
        } else {
            $product->decrement('quantity', $validated['quantity']);
        }
        $validated['user_id'] = auth()->id();
        $movement = StockMovement::create($validated);
        $movement->load('product');
        $logMessage = "Mouvement enregistré: " .
        strtoupper($validated['type']) .
        " | Produit: {$movement->product->name} | Quantité: {$validated['quantity']}";
            if (!empty($validated['description'])) {
                $logMessage .= " | Note: {$validated['description']}";
            }
            AuditLogController::logAudit(
                'ajouté',
                'mouvements',
                $movement->id,
                $logMessage
            );

        if ($product->quantity <= 5) {
            try {
                Mail::to('reeboktwist@gmail.com')->queue(new LowStockAlert($product));
            } catch (\Exception $e) {
                Log::error('Failed to send low stock alert', [
                    'error' => $e->getMessage(),
                    'product_id' => $product->id
                ]);
            }
        }
        return response()->json($movement, 201);
    }

    // in case we want to update movement
    public function update(Request $request, $id)//
    {
        $movement = StockMovement::findOrFail($id);
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:entree,sortie',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
        ]);
        $product = Product::findOrFail($validated['product_id']);
        if ($movement->type === 'entree') {
            $product->quantity -= $movement->quantity;
        } else {
            $product->quantity += $movement->quantity;
        }
        if ($validated['type'] === 'entree') {
            $product->quantity += $validated['quantity'];
        } else {
            if ($product->quantity < $validated['quantity']) {
                return response()->json(['error' => 'Stock insuffisant'], 400);
            }
            $product->quantity -= $validated['quantity'];
        }
        $product->save();
        $movement->update($validated);
        AuditLogController::logAudit('Modifié', 'mouvement', $movement->id, 'Mouvement modifié: ' . $movement->name);    
        return response()->json($movement);
    }

    public function destroy($id)//
    {
        $movement = StockMovement::findOrFail($id);
        $product = Product::findOrFail($movement->product_id);
        if ($movement->type === 'entree') {
            $product->quantity -= $movement->quantity;
        } else {
            $product->quantity += $movement->quantity;
        }
        $product->save();
        $movement->delete();
        AuditLogController::logAudit('supprimé', 'mouvement', $movement->id, 'Mouvement supprimé');
        return response()->json(['message' => 'Mouvement supprimé avec succès']);
    }

    public function export($type)
    {
        return Excel::download(new StockMovementsExport($type), "stock_{$type}.xlsx");
    }

}