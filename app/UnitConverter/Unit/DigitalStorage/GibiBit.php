<?php


namespace App\UnitConverter\Unit\DigitalStorage;


class GibiBit extends DigitalStorageUnit {


    /**
     * {@inheritDoc}
     */
    protected function configure(): void {
        $this
            ->setName('gibibit')
            ->setSymbol('Gib')
            ->setUnits(1074000000);
    }
}
