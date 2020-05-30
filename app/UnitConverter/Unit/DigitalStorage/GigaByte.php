<?php


namespace App\UnitConverter\Unit\DigitalStorage;


class GigaByte extends DigitalStorageUnit {


    /**
     * {@inheritDoc}
     */
    protected function configure(): void {
        $this
            ->setName('gigabyte')
            ->setSymbol('GB')
            ->setUnits(8000000000);
    }
}
