<?php


namespace App\UnitConverter\Unit\DigitalStorage;


class MegaBit extends DigitalStorageUnit {


    /**
     * {@inheritDoc}
     */
    protected function configure(): void {
        $this
            ->setName('megabit')
            ->setSymbol('Mb')
            ->setUnits(1000000);
    }
}
