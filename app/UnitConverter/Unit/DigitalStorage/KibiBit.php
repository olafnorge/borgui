<?php


namespace App\UnitConverter\Unit\DigitalStorage;


class KibiBit extends DigitalStorageUnit {


    /**
     * {@inheritDoc}
     */
    protected function configure(): void {
        $this
            ->setName('kibibit')
            ->setSymbol('Kib')
            ->setUnits(1024);
    }
}
