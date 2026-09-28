<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeamMember\DeleteTeamMemberRequest;
use App\Http\Requests\TeamMember\IndexTeamMemberRequest;
use App\Http\Requests\TeamMember\StoreTeamMemberRequest;
use App\Http\Requests\TeamMember\UpdateTeamMemberRequest;
use App\Models\TeamMember;
use App\Repositories\Contracts\TeamCategoryRepositoryInterface;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use App\Services\TeamMemberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeamMemberController extends Controller
{
    public function __construct(private readonly TeamMemberRepositoryInterface $members, private readonly TeamMemberService $service, private readonly TeamCategoryRepositoryInterface $categories) {}

    public function index(IndexTeamMemberRequest $request): View
    {
        return view('pages.admin.team-members.index', [
            'members' => $this->members->paginate($request->validated()),
            'title' => 'Team Members',
            'categories' => $this->categories->active(),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.team-members.create', [
            'member' => new TeamMember(['is_active' => false]),
            'title' => 'Add Team Member',
            'categories' => $this->categories->active(),
        ]);
    }

    public function store(StoreTeamMemberRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route('admin.team-members.index')->with('success', 'Team member added.');
    }

    public function edit(TeamMember $teamMember): View
    {
        return view('pages.admin.team-members.edit', [
            'member' => $teamMember->load('photoMedia'),
            'title' => 'Edit Team Member',
            'categories' => $this->categories->active(),
        ]);
    }

    public function update(UpdateTeamMemberRequest $request, TeamMember $teamMember): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user(), $teamMember);

        return redirect()->route('admin.team-members.index')->with('success', 'Team member updated.');
    }

    public function destroy(DeleteTeamMemberRequest $request, TeamMember $teamMember): RedirectResponse
    {
        $this->service->delete($teamMember, $request->user());

        return back()->with('success', 'Team member deleted.');
    }
}
