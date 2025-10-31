<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory;
    protected $appends = ['total_ht'];

    public function getTotalHtAttribute()
    {
        return $this->items->sum(function ($item) {
            return ($item->prix_unitaire ?? 0) * $item->quantité;
        });
    }

    protected $fillable = [
        'numero', 'type', 'service_demandeur', 'nom_demandeur',
        'motif', 'statut', 'fichier_joint', 'created_by'
    ];

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function approvals()
    {
        return $this->hasMany(PurchaseApproval::class);
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function peutEtreApprouvée()
    {
        return in_array($this->statut, ['soumis', 'validé_chef']);
    }

}
