<?php

namespace BalajiDharma\LaravelViewable\Traits;

use BalajiDharma\LaravelViewable\Exceptions\ViewRecordException;
use BalajiDharma\LaravelViewable\Visitor;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Config;

trait HasViewable
{
    protected ?Visitor $visitor = null;

     /**
     * Get the visitor instance
     */
    protected function getVisitor(): Visitor
    {
        if (!$this->visitor) {
            $this->visitor = new Visitor();
        }
        return $this->visitor;
    }

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

    /**
     * Determine if we should record the view.
     *
     * @return bool
     */
    protected function shouldRecord(): bool
    {
        $visitor = $this->getVisitor();

        // If ignore bots is true and the current visitor is a bot, return false
        if ($this->getConfigValue('ignore_bots') && $visitor->isCrawler()) {
            return false;
        }

        // If we honor the DNT header and the current request contains the
        // DNT header, return false
        if ($this->getConfigValue('honor_dnt') && $visitor->hasDoNotTrackHeader()) {
            return false;
        }

        // Check if IP is in ignored list
        if (collect($this->getConfigValue('ignored_ip_addresses'))->contains($visitor->ip())) {
            return false;
        }

        $query = config('viewable.models.viewable')::query();

        $checkExists = false;

        if ($visitor->isAuthenticated() && $this->getConfigValue('unique_viewer')) {
            $query->orWhere(function ($query) use ($visitor) {
                $query->where('viewable_id', $this->getKey())
                    ->where('viewable_type', $this->getMorphClass())
                    ->where('viewer_id', $visitor->getId())
                    ->where('viewer_type', $visitor->getType());
            });
            $checkExists = true;
        }
        if ($this->getConfigValue('unique_session')) {
            $query->orWhere(function ($query) use ($visitor) {
                $query->where('viewable_id', $this->getKey())
                    ->where('viewable_type', $this->getMorphClass())
                    ->Where('session_id', $visitor->getSessionId());
            });
            $checkExists = true;
        }
        if ($this->getConfigValue('unique_ip')) {
            $query->orWhere(function ($query) use ($visitor) {
                $query->where('viewable_id', $this->getKey())
                    ->where('viewable_type', $this->getMorphClass())
                    ->orWhere('ip_address', $visitor->ip());
            });
            $checkExists = true;
        }
        if ($checkExists) {
            return !$query->exists();
        }

        return true;
    }

    public function createView()
    {
        $visitor = $this->getVisitor();

        $view = $this->views()->Create([
            'viewer_id' => $visitor->getId(),
            'viewer_type' => $visitor->getType(),
            'session_id' => $visitor->getSessionId(),
            'ip_address' => $visitor->ip(),
            'viewed_at' => now(),
        ]);
        $this->incrementViewCount();
        return $view;
    }

    private function getConfigValue(string $property): mixed
    {
        return property_exists($this, $property) ? $this->{$property} : Config::get('viewable.'.$property);
    }

    public function incrementViewCount($value = 1)
    {
        if (!$this->getConfigValue('increment_model_view_count')) {
            return;
        }
        $this->timestamps = false;
        $this->increment($this->getConfigValue('increment_model_column_name'), $value);
    }
}
