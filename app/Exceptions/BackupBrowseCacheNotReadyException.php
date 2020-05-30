<?php
namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class BackupBrowseCacheNotReadyException extends HttpException {

    /**
     * @var int
     */
    private $retryAfter;


    /**
     * BackupBrowseCacheNotReadyException constructor.
     *
     * @param string|null $message
     * @param int $retryAfter
     * @param \Exception|null $previous
     */
    public function __construct(string $message = null, int $retryAfter = 60, \Exception $previous = null) {
        $this->retryAfter = $retryAfter ?: 60;

        parent::__construct(202, $message, $previous, [
            'Retry-After' => $this->retryAfter,
        ], 202);
    }


    /**
     * @return int
     */
    public function getRetryAfter(): int {
        return $this->retryAfter;
    }
}
