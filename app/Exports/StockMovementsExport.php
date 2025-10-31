<?php

namespace App\Exports;

use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockMovementsExport implements FromCollection, WithHeadings
{
    protected $type;

    public function __construct($type = null)
    {
        $this->type = $type;
    }

    public function collection()
    {
        $query = StockMovement::with('product')->orderBy('created_at', 'desc');

        if ($this->type) {
            $query->where('type', $this->type); 
        }

        return $query->get()->map(function ($movement) {
            return [
                'Produit' => $movement->product->name ?? 'Inconnu',
                'Type' => ucfirst($movement->type),
                'Quantité' => $movement->quantity,
                'Description' => $movement->description,
                'Date' => $movement->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return ['Produit', 'Type', 'Quantité', 'Description', 'Date'];
    }
}
