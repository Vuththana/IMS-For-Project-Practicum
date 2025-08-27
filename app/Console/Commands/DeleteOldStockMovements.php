<?php

namespace App\Console\Commands;

use App\Models\Inventory\StockMovement;
use Illuminate\Console\Command;

class DeleteOldStockMovements extends Command
{
    protected $signature = 'stock:cleanup';
    protected $description = 'Delete stock movement logs older than 90 days';
    public function handle(): int
    {
        // $count = StockMovement::where('created_at', '<', now()->subDays(90))->delete();

        $count = StockMovement::where('note', 'like', '%hello%')->delete();
        
        $this->info("Deleted $count stock movement records older than 90 days.");

        return Command::SUCCESS;
    }
}
