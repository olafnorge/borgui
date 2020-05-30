<?php


namespace App\UnitConverter\Unit\DigitalStorage;


class Byte extends DigitalStorageUnit {


    /**
     * {@inheritDoc}
     */
    protected function configure(): void {
        $this
            ->setName('byte')
            ->setSymbol('B')
            ->setUnits(8);
    }
}
