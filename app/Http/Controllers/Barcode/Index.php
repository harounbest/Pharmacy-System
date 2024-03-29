<?php

namespace App\Http\Controllers\Barcode;

use App\Models\Barcode;
use Livewire\Component;
use App\Models\Products;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $barcode, $quantity, $barcode_name, $barcode_id, $Barcode;
    public function mount()
    {
        if (!Gate::allows('admin')) abort(404);
    }
    public function render()
    {
        $barcodes = Barcode::paginate(10);
        return view('barcode.index', ['barcodes' => $barcodes]);
    }
    public function done()
    {
        $this->reset(['barcode', 'quantity', 'barcode_name', 'barcode_id', 'Barcode']);
        $this->resetValidation();
        $this->resetErrorBag();
        $this->dispatchBrowserEvent('closeModal');
    }
    private function  generate()
    {
        return rand(100000000, 999999999) . rand(100000000, 999999999) . rand(100000000, 999999999);
    }

    public function GenerateBarcode()
    {
        // generate number 
        $barcode = $this->generate();
        $checkBarcode = Barcode::where('barcode',  $barcode)->first();
        if ($checkBarcode) {
            $barcode = $this->generate();
        } else {
            $checkBarcodeInProduct = Products::where('barcode', $barcode)->first();
            if ($checkBarcodeInProduct) {
                $barcode = $this->generate();
            } else {
                $barcode = $barcode;
            }
        }
        $this->barcode =  Str::substr($barcode, 0, 12);
    }

    public function updatedBarcode()
    {
        $this->barcode = $this->barcode;
    }
    public function submit()
    {
        $this->validate(
            [
                'barcode_name' => 'nullable|string|regex:/^[a-zA-Z0-9\s]+$/u',
                'barcode' => 'required|numeric|digits:12|unique:barcodes,barcode|unique:products,barcode',
                'quantity' => 'required|numeric|min:1',
            ],
            [
                'barcode_name.string' => __('validation.string', ['attribute' => __('header.name')]),
                'barcode_name.regex' => __('validation.regex', ['attribute' => __('header.name')]),
                'barcode.required' => __('validation.required', ['attribute' => __('header.barcode')]),
                'barcode.numeric' => __('validation.numeric', ['attribute' => __('header.barcode')]),
                'barcode.digits' => __('validation.digits', ['attribute' => __('header.barcode'), 'digits' => 12]),
                'barcode.unique' => __('validation.unique', ['attribute' => __('header.barcode')]),
                'quantity.required' => __('validation.required', ['attribute' => __('header.quantity')]),
                'quantity.numeric' => __('validation.numeric', ['attribute' => __('header.quantity')]),
                'quantity.min' => __('validation.min', ['attribute' => __('header.quantity'), 'min' => 1]),
            ]
        );
        Barcode::create([
            'name' => $this->barcode_name,
            'barcode' => $this->barcode,
            'quantity' => $this->quantity,
        ]);
        flash()->addSuccess('header.barcodes.SuccessfullyGenerated');
        $this->done();
    }
    public function destroy(Barcode $barcode)
    {
        $barcode->delete();
        flash()->addSuccess('header.deleted');
        $this->done();
    }
    public function show(Barcode $barcode)
    {
        $this->Barcode = $barcode;
    }
    public function download(Barcode $barcode)
    {
        $pdf = Pdf::loadView('pdf.barcode', ['barcode' => $barcode]);
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'barcode.pdf');
    }
}
