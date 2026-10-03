<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\BulkDeleteUserRequest;
use App\Http\Requests\Admin\User\BulkUserStatusRequest;
use App\Http\Requests\Admin\User\DeleteUserRequest;
use App\Http\Requests\Admin\User\IndexUserRequest;
use App\Http\Requests\Admin\User\ShowUserRequest;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Requests\Admin\User\UpdateUserStatusRequest;
use App\Models\User;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly RoleRepositoryInterface $roles,
        private readonly UserService $service,
    ) {}

    public function index(IndexUserRequest $request): View
    {
        return view('admin.pages.users.index', ['users' => $this->users->paginateForIndex($request->validated()), 'title' => 'Users']);
    }

    public function create(): View
    {
        return view('admin.pages.users.create', ['user' => new User(['status' => UserStatus::Active]), 'roles' => $this->roles->allForAssignment(), 'title' => 'Create User']);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->service->create($request->validated(), $request->user());

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user): View
    {
        return view('admin.pages.users.edit', ['user' => $this->users->findForEdit($user), 'roles' => $this->roles->allForAssignment(), 'title' => 'Edit User']);
    }

    public function show(ShowUserRequest $request, User $user): View
    {
        return view('admin.pages.users.show', ['user' => $this->users->findForShow($user), 'title' => 'User Details']);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->service->update($user, $request->validated(), $request->user());

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function status(UpdateUserStatusRequest $request, User $user): RedirectResponse
    {
        $this->service->changeStatus($user, UserStatus::from($request->validated('status')), $request->user());

        return back()->with('success', 'User status updated.');
    }

    public function bulkStatus(BulkUserStatusRequest $request): RedirectResponse|JsonResponse
    {
        $this->service->bulkChangeStatus(
            $request->validated('users'),
            UserStatus::from($request->validated('status')),
            $request->user(),
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Selected statuses updated.',
                'records' => array_map(fn ($id) => ['id' => (int) $id, 'status' => (string) $request->validated('status')], $request->validated('users')),
            ]);
        }

        return back()->with('success', 'Selected user statuses updated.');
    }

    public function destroy(DeleteUserRequest $request, User $user): RedirectResponse
    {
        $isSelf = $request->user()->getAuthIdentifier() === $user->id;
        $this->service->delete($user, $request->user());
        if ($isSelf) {
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('login');
        }

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function bulkDestroy(BulkDeleteUserRequest $request): RedirectResponse
    {
        $this->service->bulkDelete($request->validated('users'), $request->user());

        return back()->with('success', 'Selected users deleted.');
    }
}
