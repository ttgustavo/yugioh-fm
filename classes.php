<?php
declare(strict_types=1);

enum Attributes : int
{
    case None = 0;
    case Mars = 1; // Mars - Fire
    case Jupiter = 2; // Jupiter - Forest
    case Saturn = 3; // Saturn - Flying
    case Uranus = 4; // Uranus - Stone
    case Pluto = 5; // Pluto - Eletric
    case Neptune = 6; // Neptune - Water
    case Mercury = 7; // Mercury - Witch
    case Sun = 8; // Sun - Light
    case Moon = 9; // Moon - Dark
    case Venus = 10; // Venus - Sorcerer
}

readonly class CardHand
{
    public function __construct(
        public int $at,
        public int $number
    )
    {
    }

    public function __toString(): string
    {
        return "[{$this->at},{$this->number}]";
    }
}

class Card extends stdClass
{
    public int $id;
    public string $name;
    public string $description;
    public int $guardian_star_a;
    public int $guardian_star_b;
    public int $level;
    public int $type;
    public int $attack;
    public int $defense;
    public int $stars;
    public string $code;
    /** @var Fusion[] */
    public array $fusions;
    public int $attribute;
}

class Fusion extends stdClass
{
    public int $card1;
    public int $card2;
    public int $result;
}