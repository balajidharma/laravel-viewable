<?php

namespace BalajiDharma\LaravelViewable\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ViewCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $view;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($view)
    {
        $this->view = $view;
    }
}
