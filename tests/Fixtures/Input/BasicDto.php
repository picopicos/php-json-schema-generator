<?php

declare(strict_types=1);

namespace Tests\Fixtures\Input;

final readonly class BasicDto
{
    public string $name;
    public int $age;
    public bool $isActive;
    public float $score;
    /** @var int<1, 10> $rating */
    public int $rating;

    /**
     * @param int<1, 10> $rating
     */
    public function __construct(
        string $name,
        int $age,
        bool $isActive,
        float $score,
        int $rating,
    ) {
        $this->name = $name;
        $this->age = $age;
        $this->isActive = $isActive;
        $this->score = $score;
        $this->rating = $rating;
    }
}
