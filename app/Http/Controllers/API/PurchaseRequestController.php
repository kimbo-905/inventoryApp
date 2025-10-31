<?php
namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\StockMovement;
use App\Models\PurchaseRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PurchaseApproval;
use App\Models\PurchaseRequestItem;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Barryvdh\Snappy\Facades\SnappyPdf;
use App\Http\Controllers\AuditLogController;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseRequest::with(['items', 'auteur'])->orderByDesc('created_at');
        if (!$request->has('archived') || $request->archived === '0') {
            $query->where('archived', false);
        }
        if ($request->archived === '1') {
            $query->where('archived', true);
        }
        if ($request->filled('numero')) {
            $query->where('numero', 'like', '%' . $request->numero . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->filled('service')) {
            $query->where('service_demandeur', 'like', '%' . $request->service . '%');
        }
        if ($request->filled('demandeur')) {
            $query->where('nom_demandeur', 'like', '%' . $request->demandeur . '%');
        }
        if ($request->filled('utilisation')) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('type_utilisation', $request->utilisation);
            });
        }
        return $query->paginate(10);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:matériel,fourniture,matière',
            'service_demandeur' => 'required|string',
            'nom_demandeur' => 'required|string',
            'motif' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.article' => 'required|string',
            'items.*.quantité' => 'required|integer|min:1',
            'items.*.prix_unitaire' => 'nullable|numeric|min:0',
            'items.*.type_utilisation' => 'required|in:stock,conso_directe',
            'fichier_joint' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
        $numero = 'DA-' . strtoupper(substr($request->type, 0, 3)) . '-' . now()->format('Ymd-His');
        $requestData = $request->only([
            'type', 'service_demandeur', 'nom_demandeur', 'motif'
        ]);
        $requestData['statut'] = 'soumis';
        $requestData['numero'] = $numero;
        $requestData['created_by'] = Auth::id();
        if ($request->hasFile('fichier_joint')) {
            $path = $request->file('fichier_joint')->store('justificatifs', 'public');
            $requestData['fichier_joint'] = $path;
        }
        $purchase = PurchaseRequest::create($requestData);
        foreach ($request->items as $item) {
            $item['purchase_request_id'] = $purchase->id;
            PurchaseRequestItem::create($item);
        }
        return response()->json([
            'message' => 'Demande enregistrée',
            'numero' => $purchase->numero
        ], 201);
    }
    
    public function show($id)
    {
        $request = PurchaseRequest::with(['items', 'approvals', 'auteur'])->findOrFail($id);
        return response()->json($request);
    }

    public function approve(Request $request, $id)
    {
        try {
            $request->validate([
                'étape' => 'required|in:admin,chef_service,direction,QHSE',
                'action' => 'required|in:valider,rejeter',
                'commentaire' => 'nullable|string',
            ]);
            $purchase = PurchaseRequest::findOrFail($id);
            if (!in_array($purchase->statut, ['soumis', 'validé_chef'])) {
                return response()->json(['message' => 'Déjà traité ou non soumis'], 400);
            }            
            if ($request->action === 'rejeter') {
                $purchase->delete(); 
                return response()->json(['message' => 'Demande rejetée.']);
            } else {
                $statutMap = [
                    'chef_service' => 'validé_chef',
                    'direction' => 'validé_direction',
                    'QHSE' => 'validé_QHSE',
                    'admin' => 'validé_direction',
                ];
                $purchase->statut = $statutMap[$request->étape] ?? 'soumis';
            }
            $purchase->save();
            $approver = Auth::user();
            PurchaseApproval::create([
                'purchase_request_id' => $purchase->id,
                'étape' => $request->étape,
                'validé_par' => $approver->id,
                'date_validation' => now(),
                'commentaire' => $request->commentaire,
                'signature' => $approver->signature_url,
            ]);
                return response()->json(['message' => 'Demande mise à jour.']);     
        } catch (\Throwable $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ], 500);
        }
    }

    public function receptionner($id)
    {
        try {
            $purchase = PurchaseRequest::with('items')->findOrFail($id);
            if ($purchase->receptionne) { 
                return response()->json(['message' => 'Déjà réceptionnée'], 400);
            }
            foreach ($purchase->items as $item) {
                if ($item->type_utilisation === 'conso_directe') {
                    Log::info("✅ Article consommé directement: {$item->article}");
                    continue;
                }
                $product = Product::where('name', $item->article)->first();
                if (!$product) {
                    $defaultCategoryId = Category::first()?->id ?? 1;
                    $product = Product::create([
                        'name' => $item->article,
                        'sku' => 'AUTO-' . strtoupper(Str::random(6)),
                        'price' => $item->prix_unitaire ?? 0,
                        'description' => 'Créé automatiquement à la réception',
                        'quantity' => 0,
                        'category_id' => $defaultCategoryId,
                    ]);    
                }
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'type' => 'entree',
                    'quantity' => $item->quantité,
                    'description' => 'Réception achat - ' . $purchase->numero,
                ]);
                $product->increment('quantity', $item->quantité);
            }
            $purchase->receptionne = true;
            $purchase->save();
            AuditLogController::logAudit('réceptionné','achats',$purchase->id,'Achat réceptionné: ' . $purchase->numero . ' ('. count($purchase->items) .' articles)');
            return response()->json(['message' => 'Achat réceptionné']);
        } catch (\Throwable $e) {
            Log::error("❌ Erreur lors de la réception: " . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function generatePdf($id)
    {
        $purchase = PurchaseRequest::with(['items', 'approvals', 'auteur'])->findOrFail($id);
        $admin = \App\Models\User::where('role_id', 1)->first();
        $html = view('pdf.purchase_request', compact('purchase', 'admin'))->render();
        if (!str_starts_with(trim($html), '<!DOCTYPE') && !str_starts_with(trim($html), '<html')) {
            $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>PDF</title></head><body>'
                . $html .
                '</body></html>';
        }
        $apiKey = 'sk_a70c16b81b4906bd021ab064e49bf8adb5b76c39';
        $response = Http::withHeaders([
            'X-API-Key' => $apiKey,
            'Content-Type' => 'application/json'
        ])->post('https://api.pdfshift.io/v3/convert/pdf', [
            'source' => $html,
            'landscape' => false,
            'use_print' => false,
        ]);
        if ($response->successful()) {
            return response($response->body(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="DemandeAchat_'.$purchase->numero.'.pdf"');
        }
        Log::error('PDFShift error', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);
        return response()->json([
            'error' => 'Failed to generate PDF',
            'details' => $response->body()
        ], 500);
    }

    public function cancelValidation($id)
    { 
            $purchase = PurchaseRequest::findOrFail($id);
            $purchase->statut = 'soumis';
            $purchase->save();
            $purchase->approvals()->delete();
        return response()->json(['message' => 'Validation annulée.']);
    }

    public function destroy($id)
    {
        $purchase = PurchaseRequest::findOrFail($id);
        if ($purchase->created_by !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }
        if (!in_array($purchase->statut, ['brouillon', 'soumis'])) {
            return response()->json(['message' => 'Demande déjà validée. Annulation impossible.'], 403);
        }
        $purchase->delete();
        return response()->json(['message' => 'Demande annulée.']);
    }

    public function monthlySummary()
    {
        $summary = PurchaseRequest::with('items')
            ->get()
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->created_at)->format('Y-m');
            })
            ->map(function ($group) {
                return $group->sum(function ($purchase) {
                    return $purchase->items->sum(function ($item) {
                        return ($item->prix_unitaire ?? 0) * $item->quantité;
                    });
                });
            });

        return response()->json($summary);
    }

    public function accuseReceptionByStaff($id)
    {
        $purchase = PurchaseRequest::findOrFail($id);
        if ($purchase->accuse_staff_at) {
            return response()->json(['message' => 'Déjà accusé'], 400);
        }
        $purchase->accuse_staff_at = now();
        $purchase->save();
        return response()->json(['message' => 'Réception accusée par le magasinier']);
    }

}