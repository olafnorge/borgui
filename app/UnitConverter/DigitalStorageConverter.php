<?php
namespace App\UnitConverter;

use Illuminate\Support\Facades\Facade;
use UnitConverter\Calculator\CalculatorInterface;
use UnitConverter\ConverterBuilder;
use UnitConverter\Registry\UnitRegistryInterface;
use UnitConverter\UnitConverter;

/**
 * Class DigitalStorageConverter
 *
 * @package App\UnitConverter
 * @method static ConverterBuilder createBuilder()
 * @method static array all()
 * @method static bool calculatorExists()
 * @method static void disableConversionLog()
 * @method static void enableConversionLog()
 * @method static UnitConverter convert($value, int $precision = null)
 * @method static UnitConverter from(string $unit)
 * @method static UnitConverter to(string $unit)
 * @method static CalculatorInterface getCalculator()
 * @method static array getConversionLog()
 * @method static UnitRegistryInterface getRegistry()
 * @method static bool registryExists()
 * @method static UnitConverter setCalculator(CalculatorInterface $calculator)
 * @method static UnitConverter setRegistry(UnitRegistryInterface $registry)
 * @method static string|null whichCalculator()
 */
class DigitalStorageConverter extends Facade {

    protected static function getFacadeAccessor() {
        return 'DigitalStorageConverter';
    }
}
