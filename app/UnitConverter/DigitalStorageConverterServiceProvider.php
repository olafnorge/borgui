<?php
namespace App\UnitConverter;

use App\UnitConverter\Unit\DigitalStorage\Bit;
use App\UnitConverter\Unit\DigitalStorage\Byte;
use App\UnitConverter\Unit\DigitalStorage\GigaByte;
use App\UnitConverter\Unit\DigitalStorage\KiloByte;
use App\UnitConverter\Unit\DigitalStorage\MegaByte;
use App\UnitConverter\Unit\DigitalStorage\TeraByte;
use Blade;
use Illuminate\Support\ServiceProvider;
use UnitConverter\Calculator\SimpleCalculator;
use UnitConverter\Registry\UnitRegistry;
use UnitConverter\UnitConverter;

class DigitalStorageConverterServiceProvider extends ServiceProvider {

    private $units = [
        Bit::class,
        Byte::class,
        KiloByte::class,
        MegaByte::class,
        GigaByte::class,
        TeraByte::class,
    ];


    /**
     * Register services.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton('DigitalStorageConverter', function () {
            $registry = new UnitRegistry();
            $calculator = new SimpleCalculator();

            foreach ($this->units as $unit) {
                $registry->registerUnit(new $unit());
            }

            return new UnitConverter($registry, $calculator);
        });
    }


    /**
     * {@inheritDoc}
     */
    public function boot() {
        Blade::directive('byteToHumanReadable', function ($expression) {
            return "<?php echo \\App\\UnitConverter\\byteToHumanReadable({$expression}); ?>";
        });
    }


    /**
     * @return array
     */
    public function provides() {
        return ['DigitalStorageConverter'];
    }
}
