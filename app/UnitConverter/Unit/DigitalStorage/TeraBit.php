<?php


namespace App\UnitConverter\Unit\DigitalStorage;


class TeraBit extends DigitalStorageUnit {


    /**
     * {@inheritDoc}
     */
    protected function configure(): void {
        $this
            ->setName('terabit')
            ->setSymbol('Tb')
            ->setUnits(1000000000000);
    }
}
