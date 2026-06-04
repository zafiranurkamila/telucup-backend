<?php

namespace App\View\Components\Layouts;

use Illuminate\View\Component;
use Illuminate\View\View;

class Dashboard extends Component
{
    public function __construct(
        public string $roleLabel = '',
    ) {}

    public function render(): View
    {
        return view('layouts.dashboard');
    }
}
