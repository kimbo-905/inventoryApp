<?php

namespace App\Models;

use App\Models\Category;
use App\Mail\LowStockAlert;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'sku',
        'description',
        'category_id',
        'price',
        'quantity',
        'image'
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    protected static function booted()
    {
        static::updated(function ($product) {
            if ($product->isDirty('quantity')) {
                $threshold = config('services.stock.low_threshold', 5);

                if ($product->quantity <= $threshold) {
                    try {
                        Mail::to('khadimkbe@gmail.com')->send(new LowStockAlert($product));
                    } catch (\Throwable $e) {
                        Log::error('Failed to send low stock alert', [
                            'error' => $e->getMessage(),
                            'product_id' => $product->id,
                        ]);
                    }
                }
            }
        });
    }
   
}
