<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    use HasFactory;
    protected $fillable = ['purchase_request_id', 'référence', 'article', 'quantité', 'prix_unitaire', 'destination', 'type_utilisation'];

    public function request()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }
}
