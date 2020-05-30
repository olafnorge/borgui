<?php
namespace App\Console;

use Symfony\Component\Console\Output\OutputInterface;

trait ExitableTrait {


    /**
     * @param $message
     * @param int $code
     * @return int
     */
    protected function exit($message, int $code = 0): int {
        $this->line(
            '',
            null,
            $code > 0 ? OutputInterface::VERBOSITY_QUIET : null
        );
        $this->line(
            is_array($message) ? implode(PHP_EOL, $message) : $message,
            null,
            $code > 0 ? OutputInterface::VERBOSITY_QUIET : null
        );
        $this->line(
            '',
            null,
            $code > 0 ? OutputInterface::VERBOSITY_QUIET : null
        );

        return $code;
    }
}
