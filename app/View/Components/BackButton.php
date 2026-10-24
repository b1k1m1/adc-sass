<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BackButton extends Component
{
    public $defaultRoute;
    public $class;
    /**
     * Create a new component instance.
     */
    public function __construct($defaultRoute = null, $class = '')
    {
        $this->defaultRoute = $defaultRoute; // optional fallback route name
        $this->class = $class;               // extra classes if needed
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.back-button');
    }
}
