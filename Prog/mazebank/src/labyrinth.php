<?php

namespace lib;

use lib\server\Server;

require dirname(__DIR__) . '/vendor/autoload.php';

$server = new Server("0.0.0.0", 1337);
