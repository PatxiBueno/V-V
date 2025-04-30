<?php

declare(strict_types=1);

namespace TwitchAnalytics\Controllers\GetUserPlatformAge;

use Laravel\Lumen\Routing\Controller as BaseController;
use TwitchAnalytics\Application\Services\UserAccountService;
use TwitchAnalytics\Domain\Exceptions\UserNotFoundException;
use TwitchAnalytics\Domain\Exceptions\ApplicationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetUserPlatformAgeController extends BaseController
{
    private UserAccountService $userAccountService;
    private UserNameValidator $userNameValidator;

    public function __construct(
        UserAccountService $userAccountService,
        UserNameValidator $userNameValidator
    ) {
        $this->userAccountService = $userAccountService;
        $this->userNameValidator = $userNameValidator;
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $name = $this->userNameValidator->validate($request->get('name'));
            $result = $this->userAccountService->getAccountAge($name);

            return new JsonResponse($result);
        } catch (ValidationException $e) {
            return new JsonResponse([
                'error' => 'INVALID_REQUEST',
                'message' => $e->getMessage(),
                'status' => 400
            ], 400);
        } catch (UserNotFoundException $e) {
            return new JsonResponse([
                'error' => 'USER_NOT_FOUND',
                'message' => $e->getMessage(),
                'status' => 404
            ], 404);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An unexpected error occurred',
                'status' => 500
            ], 500);
        }
    }
}
