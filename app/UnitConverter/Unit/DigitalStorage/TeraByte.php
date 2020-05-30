<?php


namespace App\UnitConverter\Unit\DigitalStorage;


class TeraByte extends DigitalStorageUnit {


    /**
     * {@inheritDoc}
     */
    protected function configure(): void {
        $this
            ->setName('terabyte')
            ->setSymbol('TB')
            ->setUnits(8000000000000);
    }
}
