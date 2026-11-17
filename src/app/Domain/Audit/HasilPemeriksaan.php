<?php

declare(strict_types=1);

namespace App\Domain\Audit;

final class HasilPemeriksaan
{
    /**
     * @param array<int, string> $temuan
     */
    public function __construct(
        public readonly int $jumlah,
        public readonly string $hashTerakhir,
        public readonly array $temuan = [],
    ) {
    }

    public function utuh(): bool
    {
        return $this->temuan === [];
    }

    public function jangkar(): string
    {
        return $this->jumlah.':'.$this->hashTerakhir;
    }
}