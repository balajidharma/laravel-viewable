<?php

namespace BalajiDharma\LaravelViewable\Traits;

use BalajiDharma\LaravelViewable\Exceptions\ViewRecordException;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Config;

trait HasViewable
{
    public function views(): MorphMany
    {
        return $this->morphMany(Config::get('viewable.models.viewable'), 'viewable');
    }

    public function record()
    {
        if ($this->viewable instanceof Viewable && $this->viewable->getKey() === null) {
            throw ViewRecordException::cannotRecordViewForViewableType();
        }

        if (! $this->shouldRecord()) {
            return false;
        }

        $view = $this->createView();

        return $view->exists;
    }

    public function shouldRecord(): bool
    {
        return true;
    }

    public function createView()
    {
        $isAuthenticated = auth()->check();
        $request = request();
        
        return $this->views()->create([
            'viewer_id' => $isAuthenticated ? auth()->id() : null,
            'viewer_type' => $isAuthenticated ? auth()->user()->getMorphClass() : null,
            'viewable_id' => $this->viewable->getKey(),
            'viewable_type' => $this->viewable->getMorphClass(),
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'viewed_at' => now(),
        ]);
    }
}
