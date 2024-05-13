<?php
declare(strict_types=1);

require_once __DIR__ . 'classes.php';

// ---- Creating database
$pdoOptions = [
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE
];
$pdo = new PDO(dsn: 'sqlite:database.db', options: $pdoOptions);

$queryCreateTableCards = file_get_contents(__DIR__ . '/sql/create-table-cards.sql');
$queryCreateTableFusions = file_get_contents(__DIR__ . '/sql/create-table-fusions.sql');

try {
    $pdo->beginTransaction();
    $pdo->prepare($queryCreateTableCards)->execute();
    $pdo->prepare($queryCreateTableFusions)->execute();
    $pdo->commit();
} catch (\Exception $exception) {
    print 'Failed to create tables: ' . $exception->getMessage();
    exit(1);
}

// ---- Insert data to database
$pdo->exec('DELETE FROM cards');
$pdo->exec('DELETE FROM fusions');

try {
    $data = file_get_contents(__DIR__ . '/data.json');
    if ($data === false) {
        print 'Failed to read "data.json".';
        exit(1);
    }

    $pdo->beginTransaction();

    /** @var Card[] $cardsJson */
    $cardsJson = json_decode($data);
    
    $queryInsertTableCards = file_get_contents(__DIR__ . '/sql/insert-table-cards.sql');
    $queryInsertTableFusions = file_get_contents(__DIR__ . '/sql/insert-table-fusions.sql');

    foreach ($cardsJson as $cardA1) {
        $statement = $pdo->prepare($queryInsertTableCards);
        $statement->bindValue(1, $cardA1->id, PDO::PARAM_INT);
        $statement->bindValue(2, $cardA1->name, PDO::PARAM_STR);
        $statement->bindValue(3, $cardA1->description, PDO::PARAM_STR);
        $statement->bindValue(4, $cardA1->guardian_star_a, PDO::PARAM_INT);
        $statement->bindValue(5, $cardA1->guardian_star_b, PDO::PARAM_INT);
        $statement->bindValue(6, $cardA1->level, PDO::PARAM_INT);
        $statement->bindValue(7, $cardA1->type, PDO::PARAM_INT);
        $statement->bindValue(8, $cardA1->attack, PDO::PARAM_INT);
        $statement->bindValue(9, $cardA1->defense, PDO::PARAM_INT);
        $statement->bindValue(10, $cardA1->stars, PDO::PARAM_INT);
        $statement->bindValue(11, $cardA1->code, PDO::PARAM_STR);
        $statement->bindValue(12, $cardA1->attribute, PDO::PARAM_INT);
        $statement->execute();

        foreach ($cardA1->fusions as $fusion) {
            $statement = $pdo->prepare($queryInsertTableFusions);
            $statement->bindValue(1, $fusion->card1, PDO::PARAM_INT);
            $statement->bindValue(2, $fusion->card2, PDO::PARAM_INT);
            $statement->bindValue(3, $fusion->result, PDO::PARAM_INT);
            $statement->execute();
        }
    }

    $hasInsertedData = $pdo->commit();
    if ($hasInsertedData === false) {
        $pdo->rollBack();
        print 'Could not save data: ' . $pdo->errorCode();
        exit(1);
    }

} catch (\Exception $exception) {
    print 'Failed to insert data from JSON to database: ' . $exception->getMessage();
    exit(1);
}

print 'Saved JSON to SQLite database!';