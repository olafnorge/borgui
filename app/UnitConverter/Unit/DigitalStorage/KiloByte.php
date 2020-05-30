<?php


namespace App\UnitConverter\Unit\DigitalStorage;


class KiloByte extends DigitalStorageUnit {


    /**
     * {@inheritDoc}
     */
    protected function configure(): void {
        $this
            ->setName('kilobyte')
            ->setSymbol('kB')
            ->setUnits(8000);
    }
}
