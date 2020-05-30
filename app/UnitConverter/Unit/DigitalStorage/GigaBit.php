<?php


namespace App\UnitConverter\Unit\DigitalStorage;


class GigaBit extends DigitalStorageUnit {


    /**
     * {@inheritDoc}
     */
    protected function configure(): void {
        $this
            ->setName('gigabit')
            ->setSymbol('Gb')
            ->setUnits(1000000000);
    }
}
