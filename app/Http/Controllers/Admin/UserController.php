<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\Booking;
use App\Models\FormSubmission;
use App\Models\MaintenanceTicket;
use App\Models\User;
use App\Rules\PersonName;
use App\Services\AccountDeactivationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /** Holds every walk-in guest request; this system account must remain active. */
    private const SYSTEM_EMAIL = 'walk-in-guest@ua-fms.local';

    public function index()
    {
        $users = User::orderBy('name')->paginate(20);

        // Show what remains attached to the account when it is deactivated.
        $ids = $users->pluck('id');
        $count = fn ($model) => $model::whereIn('requester_id', $ids)->selectRaw('requester_id, count(*) as n')->groupBy('requester_id')->pluck('n', 'requester_id');
        $bookings = $count(Booking::class);
        $requests = $count(FormSubmission::class);
        $tickets  = $count(MaintenanceTicket::class);
        $history = $ids->mapWithKeys(fn ($id) => [$id => [
            'bookings' => (int) ($bookings[$id] ?? 0),
            'requests' => (int) ($requests[$id] ?? 0),
            'tickets'  => (int) ($tickets[$id] ?? 0),
        ]]);

        $adminTeam = User::where('role', 'admin')->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'history', 'adminTeam') + ['systemEmail' => self::SYSTEM_EMAIL]);
    }

    public function create()
    {
        return view('admin.users.create', ['editUser' => null]);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $data = [
            'name'     => $validated['name'],
            'last_name' => $validated['last_name'],
            'contact_number' => $validated['contact_number'] ?? null,
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ];

        if ($validated['role'] === 'admin') {
            $data['position'] = $validated['position'] ?? null;   // title within the admin team
        }

        if ($validated['role'] === 'college_staff') {
            $data['college_name'] = $validated['college_name'];
        }

        if ($validated['role'] === 'org_staff') {
            $data['organization_name'] = $validated['organization_name'];
        }

        if ($validated['role'] === 'guest') {
            $data['organization_name'] = $validated['organization_name'];
        }

        User::create($data);

        return redirect()->route('admin.users.index')
            ->with('status', 'User account created successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.create', ['editUser' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $isSelf = $user->id === auth()->id();

        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255', new PersonName],
            'last_name'         => ['required', 'string', 'max:120', new PersonName],
            'contact_number'    => ['nullable', 'string', 'regex:/^\d{4}-?\d{3}-?\d{4}$/'],
            'email'             => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'          => ['nullable', 'string', 'confirmed', Password::min(8)->max(94)->mixedCase()->symbols()],
            // You can't take away your own admin access (you'd be locked out of this page)
            'role'              => $isSelf ? 'required|in:admin' : 'required|in:admin,college_staff,org_staff,guest',
            'college_name'      => 'nullable|string|max:255',
            'organization_name' => 'nullable|string|max:255',
            'position'          => 'nullable|string|max:120',
        ], [
            'contact_number.regex' => 'Enter an 11-digit contact number in the format 09xx-xxx-xxxx.',
            'role.in'              => $isSelf ? 'You can\'t change the role of your own account.' : 'Choose a valid role.',
        ]);

        $user->name           = $validated['name'];
        $user->last_name      = $validated['last_name'];
        $user->contact_number = $validated['contact_number'] ?? null;
        $user->email          = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Unit follows the role; a changed college name drops the old college link so the account moves with it
        $newCollege = $validated['role'] === 'college_staff' ? ($validated['college_name'] ?? null) : null;
        if ($newCollege !== $user->college_name) {
            $user->college_id = null;
        }
        // Admins get their admin-team title here; college/org positions are managed by that unit's head,
        // so they're kept unless the account moves between admin and a unit
        if ($validated['role'] === 'admin') {
            $user->position = $validated['position'] ?? null;
        } elseif ($user->role !== $validated['role']) {
            $user->position = null;
        }
        $user->role              = $validated['role'];
        $user->college_name      = $newCollege;
        $user->organization_name = in_array($validated['role'], ['org_staff', 'guest'], true) ? ($validated['organization_name'] ?? null) : null;
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('status', 'Account updated for ' . $user->name . '.');
    }

    public function deactivate(User $user)
    {
        $back = redirect()->route('admin.users.index');

        if ($user->id === auth()->id()) {
            return $back->with('error', 'You can\'t deactivate your own account.');
        }
        if ($user->email === self::SYSTEM_EMAIL) {
            return $back->with('error', 'The walk-in guest system account must remain active. It holds all walk-in guest requests.');
        }

        $result = app(AccountDeactivationService::class)->deactivate($user);

        if ($result['already_deactivated']) {
            return $back->with('status', 'Account is already deactivated for ' . $user->name . '.');
        }

        return $back->with(
            'status',
            'Account deactivated for ' . $user->name . '. '
            . $result['requests'] . ' upcoming request(s) and ' . $result['bookings']
            . ' upcoming booking(s) cancelled; all records were preserved.'
        );
    }
}
