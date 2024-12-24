<?php

namespace BalajiDharma\LaravelViewable\Exceptions;

use Exception;

class ViewRecordException extends Exception
{
    public static function cannotRecordViewForViewableType()
    {
        return new static('Cannot record a view for a viewable type.');
    }
}
