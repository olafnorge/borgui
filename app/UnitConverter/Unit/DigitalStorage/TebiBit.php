<?php


namespace App\UnitConverter\Unit\DigitalStorage;


class TebiBit extends DigitalStorageUnit {


    /**
     * {@inheritDoc}
     */
    protected function configure(): void {
        $this
            ->setName('tebibit')
            ->setSymbol('Tib')
            ->setUnits(1100000000000);
    }
}
