<?php

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/bootstrap/App.php';

//This app supports hot reload and shutdown triggered by SIGTERM
(new App())->run();
