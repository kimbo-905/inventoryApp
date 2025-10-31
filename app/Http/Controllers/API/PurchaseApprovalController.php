<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseApproval;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseApprovalController extends Controller
{
    public function store(Request $request, $purchaseRequestId)
    {
        $request->validate([
            'étape' => 'required|in:admin,direction,chef_service,QHSE',
            'action' => 'required|in:valider,rejeter',
            'commentaire' => 'nullable|string',
        ]);
        $demande = PurchaseRequest::findOrFail($purchaseRequestId);
        if ($request->action === 'rejeter') {
            $demande->update(['statut' => 'rejeté']);
        } else {
            $statutMap = [
                'chef_service' => 'validé_chef',
                'direction' => 'validé_direction',
                'QHSE' => 'validé_QHSE',
                'admin' => 'validé_direction',
            ];
            $newStatut = $statutMap[$request->étape] ?? 'soumis';
            $demande->update(['statut' => $newStatut]);
        }
        PurchaseApproval::create([
            'purchase_request_id' => $demande->id,
            'étape' => $request->étape,
            'validé_par' => Auth::id(),
            'date_validation' => now(),
            'commentaire' => $request->commentaire,
        ]);
        return response()->json(['message' => 'Demande traitée avec succès.']);
    }

}
