<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;


class DynamicDropdown extends Component
{

    public $name;
    public $id;
    public $label;
    public $selected;
    public $table;
    public $options;

    /**
     * Create a new component instance.
     */
    public function __construct($name, $table, $id = null, $label = '', $selected = null)
    {
        $this->name = $name;
        $this->id = $id ?? $name;
        $this->label = $label;
        $this->selected = $selected;
        $this->table = $table;

        $this->options = DB::table($table)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dynamic-dropdown');
    }


}
