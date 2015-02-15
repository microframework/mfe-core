<?php namespace mfe\core\core;

use mfe\core\mfe;

/**
 * Map file
 */

#mfe::map('core', 'loader', '@engine.@core.loader.core');
mfe::loadCore('loader');
mfe::loadCore('page');
