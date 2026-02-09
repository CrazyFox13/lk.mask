<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class AdvReport implements FromView
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('exports.adv_report', ['data' => (array)$this->data]);
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return collect($this->data);
    }
}
