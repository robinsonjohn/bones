<?php

/**
 * @package bones
 * @link https://github.com/bayfrontmedia/bones
 * @author John Robinson <john@bayfrontmedia.com>
 * @copyright 2020 Bayfront Media
 */

namespace Bayfront\Bones\Exceptions\Handler;

use Bayfront\Container\NotFoundException;
use Bayfront\HttpResponse\Response;
use Bayfront\MonologFactory\Exceptions\ChannelNotFoundException;
use Throwable;

class Handler extends ExceptionHandler implements HandlerInterface
{

    /**
     * Fully namespaced exception classes to exclude from reporting.
     *
     * @var array $excluded_classes
     */

    protected $excluded_classes = [
        'Bayfront\Bones\Exceptions\HttpException'
    ];

    /**
     * Return array of fully namespaced exception classes to exclude from reporting.
     *
     * @return array
     */

    public function getExcludedClasses(): array
    {
        return $this->excluded_classes;
    }

    /**
     * Report exception.
     *
     * @param Throwable $e
     *
     * @return void
     *
     * @throws NotFoundException
     * @throws ChannelNotFoundException
     */

    public function report(Throwable $e): void
    {
        parent::report($e);
    }

    /**
     * Respond to exception.
     *
     * @param Response $response
     * @param Throwable $e
     *
     * @return void
     */

    public function respond(Response $response, Throwable $e): void
    {
        parent::respond($response, $e);
    }

}