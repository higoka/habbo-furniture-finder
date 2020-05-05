<?php

$config = require_once 'config/app.php';

if (is_file('resource/furnidata.xml') === false) {
    exit('err: furnidata not found');
}

$pdo = new PDO("mysql:host={$config['hostname']};dbname={$config['database']}", $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$query = $pdo->prepare('SELECT id FROM items_base WHERE public_name = ? LIMIT 1');

$xml = simplexml_load_file('resource/furnidata.xml');

$roomItems = null;
$wallItems = null;

# roomitemtypes
foreach ($xml->xpath('//roomitemtypes/furnitype') as $item) {
    $query->execute([ $item->attributes()->classname ]);

    if ($query->rowCount() === 1) {
        continue;
    }

    echo "missing: {$item->attributes()->classname}\n";
    $roomItems .= "{$item->asXML()}";
}

# wallitemtypes
foreach ($xml->xpath('//wallitemtypes/furnitype') as $item) {
    $query->execute([ $item->attributes()->classname ]);

    if ($query->rowCount() === 1) {
        continue;
    }

    echo "missing: {$item->attributes()->classname}\n";
    $wallItems .= "{$item->asXML()}";
}

$missing = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<furnidata>
  <roomitemtypes>{$roomItems}</roomitemtypes>
  <wallitemtypes>{$wallItems}</wallitemtypes>
</furnidata>
EOT;

file_put_contents('resource/missing.xml', $missing);

exit('all done - created resource/missing.xml');
