<?php


namespace App\UnitConverter\Unit\DigitalStorage;


class KiloBit extends DigitalStorageUnit {


    /**
     * {@inheritDoc}
     */
    protected function configure(): void {
        $this
            ->setName('kilobit')
            ->setSymbol('kb')
            ->setUnits(1000);
    }
}
