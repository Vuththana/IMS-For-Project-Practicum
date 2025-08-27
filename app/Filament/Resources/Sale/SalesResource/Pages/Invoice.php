<?php

namespace App\Filament\Resources\Sale\SalesResource\Pages;

use App\Filament\Resources\Sale\SalesResource;
use App\Models\Sale\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Blade;

class Invoice extends Page
{
    protected static string $resource = SalesResource::class;

    protected static string $view = 'filament.resources.sale.sales-resource.pages.invoice';
    public Sale $record;
    public function mount(Sale $record): void
    {
        $this->record = $record->load('saleItems.product', 'deliverer', 'customer');
    }

    protected function getViewData(): array
    {
        return [
            'sale' => $this->record,
            'items' => $this->record->saleItems,
            'discount' => $this->record->discount,
            'tax' => $this->record->tax,
            'delivery_fee' => $this->record->delivery_fee,
            'subtotal' => $this->record->subtotal,
            'total_amount' => $this->record->total_amount,
        ];
    }
    public function generatePdf()
    {
        $data = $this->getViewData();
        
        $pdf = Pdf::loadHtml(
            Blade::render('pdf.invoice', $data)
        )->setPaper('a4', 'portrait')
        ->set_option('isRemoteEnabled', true);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "invoice-{$this->record->id}.pdf"
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
