<?php

namespace DragNet\Models;

use DragNet\Core\Application;

/**
 * Geofence Model
 */
class Geofence extends BaseModel
{
    protected string $table = 'geofences';
    
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }
}

