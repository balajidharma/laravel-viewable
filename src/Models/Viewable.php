<?php

namespace Balajidharma\LaravelViewable\Models;

use BalajiDharma\LaravelViewable\Events\ViewCreated;
use Illuminate\Database\Eloquent\Model;

class Viewable extends Model
{

    protected $fillable = [
        'viewer_id',
        'viewer_type',
        'viewable_id',
        'viewable_type',
        'session_id',
        'ip_address',
    ];


    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => ViewCreated::class,
    ];

    public function viewable()
    {
        return $this->morphTo();
    }

    public function viewer()
    {
        return $this->morphTo();
    }
}