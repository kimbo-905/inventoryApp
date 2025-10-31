<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseApproval extends Model
{
    use HasFactory;
    protected $fillable = [
        'purchase_request_id',
        'étape',
        'validé_par',
        'date_validation',
        'commentaire',
        'signature',
    ];

    public function demande()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'validé_par');
    }
}
