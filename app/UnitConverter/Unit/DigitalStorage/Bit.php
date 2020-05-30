<?php


namespace App\UnitConverter\Unit\DigitalStorage;


class Bit extends DigitalStorageUnit {


    /**
     * {@inheritDoc}
     */
    protected function configure(): void {
        $this
            ->setName('bit')
            ->setSymbol('b')
            ->setUnits(1);
    }
}
