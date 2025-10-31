<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;
use Carbon\Carbon;

class DeleteExpiredAccuseRequests extends Command
{
    protected $signature = 'purchase:archive-expired';
    protected $description = 'Archive DA after 24h accusé de réception';

    public function handle()
    {
        $threshold = Carbon::now()->subHours(24);
        
        $affected = PurchaseRequest::whereNotNull('accuse_staff_at')
            ->where('accuse_staff_at', '<=', $threshold)
            ->where('archived', false)
            ->update(['archived' => true]);

        $this->info("✅ Archived {$affected} purchase requests.");
    }
}
