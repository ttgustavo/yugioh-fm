<?php
declare(strict_types=1);

require_once __DIR__ . '/classes.php';

// ---- Run
$hasFiveArguments = $argc - 1 === 1;
if ($hasFiveArguments === false) {
    print 'Usage: php ' . $argv[0] . ' [card]';
    exit(1);
}

$card =getCardDetails($argv[1]);
print_r($card);


function getCardDetails(int|string $number): bool|stdClass
{
    $pdo = initPdo();
    $statement = $pdo->prepare('SELECT * FROM cards WHERE id = ?');
    $statement->bindValue(1, $number, PDO::PARAM_INT);
    
    if ($statement->execute() === false) return false;

    /** @var bool|Card $model */
    $model = $statement->fetchObject(stdClass::class);
    return $model;
}

function initPdo(): PDO
{
    $pdoOptions = [
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE
    ];
    return new PDO(dsn: 'sqlite:database.db', options: $pdoOptions);
}