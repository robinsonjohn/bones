<?php

namespace Bayfront\Bones\Services\Api\Controllers\Abstracts;

use Bayfront\Bones\Application\Services\EventService;
use Bayfront\Bones\Application\Services\FilterService;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\Bones\Exceptions\HttpException;
use Bayfront\Bones\Services\Api\Exceptions\ForbiddenException;
use Bayfront\Bones\Services\Api\Exceptions\UnauthorizedException;
use Bayfront\Bones\Services\Api\Exceptions\UnexpectedApiException;
use Bayfront\Bones\Services\Api\Models\AuthModel;
use Bayfront\Bones\Services\Api\Models\UserModel;
use Bayfront\Bones\Services\Api\Utilities\Api;
use Bayfront\Container\NotFoundException as ContainerNotFoundException;
use Bayfront\HttpRequest\Request;
use Bayfront\HttpResponse\InvalidStatusCodeException;
use Bayfront\HttpResponse\Response;
use Exception;

abstract class PrivateApiController extends ApiController
{

    protected UserModel $user;

    /**
     * @param EventService $events
     * @param FilterService $filters
     * @param Response $response
     * @throws ContainerNotFoundException
     * @throws HttpException
     * @throws InvalidStatusCodeException
     * @throws UnexpectedApiException
     */
    public function __construct(EventService $events, FilterService $filters, Response $response)
    {
        parent::__construct($events, $filters, $response);

        $this->initApi();

        $this->user = $this->validateCredentialsOrAbort();

        $events->doEvent('api.auth', $this->user->getId());

        $this->rateLimitOrAbort(md5('private-' . $this->user->getId()), $this->user_rate_limit);

        $events->doEvent('api.controller', $this);
    }

    /*
     * Default rate limit of 0 will invalidate all requests.
     * The validateCredentialsOrAbort method defines the actual rate limit
     * once the user is authenticated.
     */
    protected int $user_rate_limit = 0;

    /**
     * Validate user credentials or abort with 401 or 403 status.
     * If not validated, an "auth" rate limit attempt is recorded.
     *
     * @return UserModel
     * @throws ContainerNotFoundException
     * @throws HttpException
     * @throws InvalidStatusCodeException
     * @throws UnexpectedApiException
     */
    private function validateCredentialsOrAbort(): UserModel
    {

        /*
         * Check for enabled authentication methods
         */

        if (Request::hasHeader('Authorization') && in_array(Api::AUTH_TOKEN, App::getConfig('api.auth.methods'))) {

            return $this->validateAccessTokenOrAbort(Request::getHeader('Authorization'));

        } else if (Request::hasHeader('X-Api-Key') && in_array(Api::AUTH_KEY, App::getConfig('api.auth.methods'))) {

            return $this->validateUserKeyOrAbort(Request::getHeader('X-Api-Key'));

        } else {

            $this->rateLimitOrAbort(md5('auth-' . Request::getIp()), App::getConfig('api.rate_limit.auth'));

            App::abort(401, 'Invalid credentials');

        }

    }

    /**
     * Validate access token (JWT) or abort with 401 or 403.
     *
     * @param string $token
     * @return UserModel
     * @throws ContainerNotFoundException
     * @throws HttpException
     * @throws InvalidStatusCodeException
     * @throws UnexpectedApiException
     */
    private function validateAccessTokenOrAbort(string $token): UserModel
    {

        try {

            /** @var AuthModel $authModel */
            $authModel = App::make('Bayfront\Bones\Services\Api\Models\AuthModel');

        } catch (Exception $e) {
            throw new UnexpectedApiException($e->getMessage());
        }

        try {
            $valid = $authModel->validateToken($token);
        } catch (ForbiddenException $e) {
            App::abort(403, $e->getMessage());
        } catch (UnauthorizedException $e) {
            App::abort(401, $e->getMessage());
        }

        $this->user_rate_limit = $valid['rate_limit'];

        return $valid['user_model'];

    }

    /**
     * Validate user (API) key or abort with 401 or 403.
     *
     * @param string $key
     * @return UserModel
     * @throws ContainerNotFoundException
     * @throws HttpException
     * @throws InvalidStatusCodeException
     * @throws UnexpectedApiException
     */
    private function validateUserKeyOrAbort(string $key): UserModel
    {

        try {

            /** @var AuthModel $authModel */
            $authModel = App::make('Bayfront\Bones\Services\Api\Models\AuthModel');

        } catch (Exception $e) {
            throw new UnexpectedApiException($e->getMessage());
        }

        // Require string for referer

        $referer = Request::getReferer();

        if (!$referer) {
            $referer = 'UNKNOWN';
        }

        try {
            $valid = $authModel->validateKey($key, $referer, Request::getIp('UNKNOWN'));
        } catch (ForbiddenException $e) {
            App::abort(403, $e->getMessage());
        } catch (UnauthorizedException $e) {
            App::abort(401, $e->getMessage());
        }

        $this->user_rate_limit = $valid['rate_limit'];

        return $valid['user_model'];

    }

    /**
     * User has all global and tenant permissions or abort.
     *
     * @param array $permissions
     * @param string $tenant_id
     * @return void
     * @throws ContainerNotFoundException
     * @throws HttpException
     * @throws InvalidStatusCodeException
     */
    public function canDoAllOrAbort(array $permissions, string $tenant_id = ''): void
    {

        if (!$this->user->hasAllPermissions($permissions, $tenant_id)) {
            App::abort(403);
        }

    }

    /**
     * User has any global and tenant permissions or abort.
     *
     * @param array $permissions
     * @param string $tenant_id
     * @return void
     * @throws ContainerNotFoundException
     * @throws HttpException
     * @throws InvalidStatusCodeException
     */
    public function canDoAnyOrAbort(array $permissions, string $tenant_id = ''): void
    {

        if (!$this->user->hasAnyPermissions($permissions, $tenant_id)) {
            App::abort(403);
        }

    }

}