<?php

namespace Balajidharma\LaravelViewable\Models;

use BalajiDharma\LaravelViewable\Events\ViewCreated;
use Illuminate\Database\Eloquent\Model;

class Viewable extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'viewer_id',
        'viewer_type',
        'viewable_id',
        'viewable_type',
        'session_id',
        'ip_address',
        'viewed_at',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => ViewCreated::class,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable($this->getTable());
    }

    public function getTable()
    {
        return config('viewable.table_names.viewable', parent::getTable());
    }

    public function viewable()
    {
        return $this->morphTo();
    }

    public function viewer()
    {
        return $this->morphTo();
    }
}
