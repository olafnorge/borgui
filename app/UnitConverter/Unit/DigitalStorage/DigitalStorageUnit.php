<?php
namespace App\UnitConverter\Unit\DigitalStorage;

use UnitConverter\Measure;
use UnitConverter\Unit\AbstractUnit;

abstract class DigitalStorageUnit extends AbstractUnit {

    protected $base = Bit::class;

    protected $unitOf = Measure::VOLUME;
}
