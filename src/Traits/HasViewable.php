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
        if (Config::get('viewable.ignore_bots') && $visitor->isCrawler()) {
            return false;
        }

        // If we honor the DNT header and the current request contains the
        // DNT header, return false
        if (Config::get('viewable.honor_dnt', false) && $visitor->hasDoNotTrackHeader()) {
            return false;
        }

        // Check if IP is in ignored list
        if (collect(Config::get('viewable.ignored_ip_addresses'))->contains($visitor->ip())) {
            return false;
        }

        return true;
    }

    public function createView()
    {
        $visitor = $this->getVisitor();
        $attributes = [];

        if ($visitor->isAuthenticated() && $this->getIsUniqueViewer()) {
            $attributes['viewer_id'] = $visitor->getId();
            $attributes['viewer_type'] = $visitor->getType();
        }
        if ($this->getIsUniqueSession()) {
            $attributes['session_id'] = $visitor->getSessionId();
        }
        if ($this->getIsUniqueIp()) {
            $attributes['ip_address'] = $visitor->ip();
        }

        if (empty($attributes)) {
            $view = $this->views()->Create([
                'viewer_id' => $visitor->getId(),
                'viewer_type' => $visitor->getType(),
                'session_id' => $visitor->getSessionId(),
                'ip_address' => $visitor->ip(),
                'viewed_at' => now(),
            ]);
            $this->incrementViewCount();
            return $view;
        } else {
            $view = $this->views()->firstOrCreate($attributes, [
                'viewer_id' => $visitor->getId(),
                'viewer_type' => $visitor->getType(),
                'session_id' => $visitor->getSessionId(),
                'ip_address' => $visitor->ip(),
                'viewed_at' => now(),
            ]);
            if ($view->wasRecentlyCreated) {
                $this->incrementViewCount();
            }
            return $view;
        }
    }

    public function getIsUniqueIp(): bool
    {
        return $this->unique_ip ?? Config::get('viewable.unique_ip', true);
    }

    public function getIsUniqueSession(): bool
    {
        return $this->unique_session ?? Config::get('viewable.unique_session', true);
    }

    public function getIsUniqueViewer(): bool
    {
        return $this->unique_viewer ?? Config::get('viewable.unique_viewer', true);
    }

    public function getIsViewCountIncremented(): bool
    {
        return $this->increment_view_count ?? Config::get('viewable.increment_model_view_count', false);
    }

    public function getIncrementColumnName(): string
    {
        return $this->increment_column_name ?? Config::get('viewable.increment_model_column_name', 'view_count');
    }

    public function incrementViewCount()
    {
        if (!$this->getIsViewCountIncremented()) {
            return;
        }
        $this->timestamps = false;
        $this->increment($this->getIncrementColumnName());
    }
}
