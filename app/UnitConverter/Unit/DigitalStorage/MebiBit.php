<?php


namespace App\UnitConverter\Unit\DigitalStorage;


class MebiBit extends DigitalStorageUnit {


    /**
     * {@inheritDoc}
     */
    protected function configure(): void {
        $this
            ->setName('mebibit')
            ->setSymbol('Mib')
            ->setUnits(1049000);
    }
}
