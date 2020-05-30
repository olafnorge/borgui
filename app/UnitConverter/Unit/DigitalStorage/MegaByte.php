<?php


namespace App\UnitConverter\Unit\DigitalStorage;


class MegaByte extends DigitalStorageUnit {


    /**
     * {@inheritDoc}
     */
    protected function configure(): void {
        $this
            ->setName('megabyte')
            ->setSymbol('MB')
            ->setUnits(8000000);
    }
}
