<?php
declare(strict_types=1);

require_once __DIR__ . '/classes.php';

// ---- Run
$hasFiveArguments = $argc - 1 === 5;
if ($hasFiveArguments === false) {
    print 'Usage: php ' . $argv[0] . ' [card1] [card2] [card3] [card4] [card5]';
    exit(1);
}

/**
 * @var int[] $hand
 * @var CardHand[] $cardHand
 */
$hand = array_remove_index(0, $argv);
$cardHand = [];
for ($i = 0; $i < count($hand); $i ++) {
    $cardHand[] = new CardHand($i + 1, (int) $hand[$i]);
}

$fusions = [];

for ($a1 = 0; $a1 < 4; $a1 ++) {
    $cardA1 = $cardHand[$a1];

    /** @var CardHand[] */
    $handWithoutPastCards = $cardHand;
    for ($i = 0; $i < $a1; $i ++) {
        $handWithoutPastCards = array_remove_index(0, $handWithoutPastCards);
    }
    
    /** @var CardHand[] */
    $handWithoutCardA = array_remove_index(0, $handWithoutPastCards);

    for ($b1 = 0; $b1 < count($handWithoutCardA); $b1 ++) {
        $cardB1 = $handWithoutCardA[$b1];
        // print 'Checking: ' . $cardA . ' with ' . $cardB . PHP_EOL;

        /** @var bool|Fusion */
        $result = hasFusionWithCards($cardA1->number, $cardB1->number);
        if ($result === false) continue;

        /** @var bool|Card */
        $resultCard = getCardDetails($result->result);
        $fusions[] = sprintf(
            "%s\t\t%d/%d\t%s %s\t| %d + %d",
            $resultCard->name,
            $resultCard->attack,
            $resultCard->defense,
            Attributes::from($resultCard->guardian_star_a)->name,
            Attributes::from($resultCard->guardian_star_b)->name,
            $cardA1->at,
            $cardB1->at,
        );

        // print $cardA1 . ' + ' . $cardB1 . ' = ' . $result->result . PHP_EOL;

        // Check new fusions (here there was 1 fusion [2 cards])
        /** @var CardHand[] $cardHand2 */
        $cardHand2 = array_remove_value($cardA1, $cardHand);
        $cardHand2 = array_remove_value($cardB1, $cardHand2);

        $cardA2 = $result->result;

        // print join(',', $cardHand2) . PHP_EOL;

        for ($b2 = 0; $b2 < 3; $b2 ++) {
            $cardB2 = $cardHand2[$b2];
            
            /** @var bool|Fusion */
            $result = hasFusionWithCards($cardA2, $cardB2->number);
            if ($result === false) continue;

            /** @var bool|Card */
            $resultCard = getCardDetails($result->result);
            $fusions[] = sprintf(
                "%s\t\t%d/%d\t%s %s\t| %d + %d + %d",
                $resultCard->name,
                $resultCard->attack,
                $resultCard->defense,
                Attributes::from($resultCard->guardian_star_a)->name,
                Attributes::from($resultCard->guardian_star_b)->name,
                $cardA1->at,
                $cardB1->at,
                $cardB2->at,
            );

            // print $cardA1 . ' + ' . $cardB1 . ' + ' . $cardB2 . ' = ' . $result->result . PHP_EOL;

            // Check new fusions (here there was 2 fusion [3 cards])
            /** @var CardHand[] $cardHand3 */
            $cardHand3 = array_remove_value($cardA2, $cardHand2);
            $cardHand3 = array_remove_value($cardB2, $cardHand3);

            $cardA3 = $result->result;

            // print join(',', $cardHand3) . PHP_EOL;

            for ($b3 = 0; $b3 < 2; $b3 ++) {
                $cardB3 = $cardHand3[$b3];

                /** @var bool|Fusion */
                $result = hasFusionWithCards($cardA3, $cardB3->number);
                if ($result === false) continue;

                /** @var bool|Card */
                $resultCard = getCardDetails($result->result);
                $fusions[] = sprintf(
                    "%s\t\t%d/%d\t%s %s\t| %d + %d + %d + %d",
                    $resultCard->name,
                    $resultCard->attack,
                    $resultCard->defense,
                    Attributes::from($resultCard->guardian_star_a)->name,
                    Attributes::from($resultCard->guardian_star_b)->name,
                    $cardA1->at,
                    $cardB1->at,
                    $cardB2->at,
                    $cardB3->at,
                );

                // print $cardA1 . ' + ' . $cardB1 . ' + ' . $cardB2 . ' + ' . $cardB3 . ' = ' . $result->result . PHP_EOL;

                // Last card to check
                $cardHand4 = array_remove_value($cardA3, $cardHand3);
                $cardHand4 = array_remove_value($cardB3, $cardHand4);

                $cardA4 = $result->result;

                // print join(',', $cardHand4) . PHP_EOL;

                $cardB4 = $cardHand4[0];

                /** @var bool|Fusion */
                $result = hasFusionWithCards($cardA4, $cardB4->number);
                if ($result === false) continue;

                /** @var bool|Card */
                $resultCard = getCardDetails($result->result);
                $fusions[] = sprintf(
                    "%s\t\t%d/%d\t%s %s\t| %d + %d + %d + %d + %d",
                    $resultCard->name,
                    $resultCard->attack,
                    $resultCard->defense,
                    Attributes::from($resultCard->guardian_star_a)->name,
                    Attributes::from($resultCard->guardian_star_b)->name,
                    $cardA1->at,
                    $cardB1->at,
                    $cardB2->at,
                    $cardB3->at,
                    $cardB4->at,
                );

                // print $cardA1 . ' + ' . $cardB1 . ' + ' . $cardB2 . ' + ' . $cardB3 . ' + ' . $cardB4 . ' = ' . $result->result . PHP_EOL;
            }
        }

        // print '-----' . PHP_EOL;
    }

    // print '---------------' . PHP_EOL;
}

foreach ($fusions as $fusion) {
    print $fusion . PHP_EOL;
}

// var_dump($fusions);

// ---- Methods
function initPdo(): PDO
{
    $pdoOptions = [
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE
    ];
    return new PDO(dsn: 'sqlite:database.db', options: $pdoOptions);
}

function hasFusionWithCards(int|string $card1, int|string $card2): bool|stdClass
{
    $pdo = initPdo();
    $statement = $pdo->prepare('SELECT * FROM fusions WHERE card1 = ? and card2 = ? or card1 = ? and card2 = ?');
    $statement->bindValue(1, $card1, PDO::PARAM_INT);
    $statement->bindValue(2, $card2, PDO::PARAM_INT);
    $statement->bindValue(3, $card2, PDO::PARAM_INT);
    $statement->bindValue(4, $card1, PDO::PARAM_INT);
    if ($statement->execute() === false) return false;

    /** @var bool|stdClass */
    $data = $statement->fetch();

    if ($data === false) return false;
    return $data;
}

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

function array_remove_index(int $index, array $array): array
{
    unset($array[$index]);
    return array_values($array);
}

function array_remove_value(mixed $value, array $array): array
{
    for ($i = 0; $i < count($array); $i ++) {
        if ($array[$i] !== $value) continue;

        unset($array[$i]);
        break;
    }

    return array_values($array);
}