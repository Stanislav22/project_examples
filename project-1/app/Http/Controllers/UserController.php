<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Facades\Users;
use App\Http\Resources\UserResource;
use App\Http\Resources\UsersListResource;
use Illuminate\Http\JsonResponse;
use App\Exceptions\CreateUserException;
use App\Exceptions\UpdateUserException;
use App\Exceptions\DeleteUserException;
use App\Exceptions\ResetPasswordException;

class UserController extends Controller
{
    /**
     * Login
     *
     * @param LoginRequest $request
     *
     * @return UserResource
     */
    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $email = $data['email'];
        $password = $data['password'];
        $device = $data['device'] ?? substr($request->header('User-Agent'), 0, 255);
        $mfaCode = $data['code'] ?? null;

        $result = Users::login($email, $password, $device, $mfaCode);

        if ($result === null) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if ($result instanceof MFAChallenge) {
            return new MFAChallengeResource($result);
        }
        
        return [
            'token' => $result->currentAccessToken()->plainTextToken,
            'user' => new UserResource($result),
        ];
    }

    /**
     * Logout
     * 
     * @param Request $request
     */
    public function logout(Request $request) 
    {
        Users::logout((bool) $request->input('all'));
    }

    /**
     * Show user date
     * 
     * @param Request $request
     */
    public function user(Request $request) 
    {
        return new UserResource($request->user());
    }

    /**
     * List users
     * 
     * @param  SearchRequest $request
     * @return  UsersListResource
     */
    public function index(SearchRequest $request)
    {
        $this->authorize('read', User::class);

        $users = Users::search($request->validated());

        return new UsersListResource($users);
    }

    /**
     * Show user data
     * 
     * @param  User $user
     * @return  UserResource
     */
    public function get(User $user)
    {
        $this->authorize('read', $user);

        return new UserResource($user);
    }

    /**
     * Check if email is registered in the system
     * 
     * @param string $email
     * @return bool
     */
    public function checkEmail($email)
    {
        return new JsonResponse([
            'exists' => Users::emailExists($email),
        ]);
    }

    /**
     * Send reset password link
     * 
     * @param ForgotPasswordRequest $request
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $data = $request->validated();

        try {
            Users::sendPasswordResetLink($data['email']);
        } catch (ResetPasswordException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reset user password
     * 
     * @param ResetPasswordRequest $request
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $data = $request->validated();
        
        try {
            Users::resetPassword($data['token'], $data['email'], $data['password']);
        } catch (ResetPasswordException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create a user
     * 
     * @param  CreateUserRequest $request
     * @return  UserResource
     */
    public function create(CreateUserRequest $request)
    {
        $this->authorize('create', User::class);

        $data = $request->validated();

        try {
            $user = Users::create($data);
        } catch (CreateUserException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 400);
        }

        return new UserResource($user);
    }

    /**
     * Update user data
     * 
     * @param  User $user
     * @param  UpdateUserRequest $request
     * @return  UserResource
     */
    public function update(User $user, UpdateUserRequest $request)
    {
        $this->authorize('update', $user);

        $data = $request->validated();
        
        try {
            Users::update($user, $data);
        } catch (UpdateUserException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 400);
        }

        return new UserResource($user);
    }

    /**
     * Change password of current user
     * 
     * @param ChangePasswordRequest $request
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $data = $request->validated();
        
        try {
            Users::changePassword($request->user(), $data['new_password']);
        } catch (UpdateUserException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete user
     * 
     * @param  User $user
     */
    public function delete(User $user)
    {
        $this->authorize('delete', $user);

        try {
            Users::delete($user);
        } catch (DeleteUserException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 400);
        }        
    }
}