<?php

namespace App\Filament\Resources\Purchase\PurchaseResource\Pages;

use App\Filament\Resources\Purchase\PurchaseResource;
use App\Models\Purchase\Purchase;
use Filament\Resources\Pages\Page;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Blade;

class Invoice extends Page
{
    protected static string $resource = PurchaseResource::class;

    protected static string $view = 'filament.resources.purchase.purchase-resource.pages.invoice';
    public Purchase $record;
    public function mount(Purchase $record): void
    {
        $this->record = $record->load('purchaseItems.product');
    }
    protected function getViewData(): array
    {
        return [
            'purchase' => $this->record,
            'items' => $this->record->purchaseItems,
            'discount' => $this->record->discount,
            'tax' => $this->record->tax,
            'delivery_fee' => $this->record->delivery_fee,
            'subtotal' => $this->record->subtotal,
        ];
    }

    public function generatePdf()
    {
        $data = $this->getViewData();
        
        $pdf = Pdf::loadHtml(
            Blade::render('pdf.purchase-invoice', $data)
        )->setPaper('a4', 'portrait')
        ->set_option('isRemoteEnabled', true);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "purchase-order-{$this->record->id}.pdf"
        );
    }
    public function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label(_('Download PDF'))
                ->icon('heroicon-o-arrow-down')
                ->action('generatePdf'),
        ];
    }
}
