<?php

namespace App\Http\Controllers\Traits;

use App\Rules\PersonName;
use App\Models\User;
use App\Services\AccountDeactivationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Team accounts for a college (college staff) or an organization (org staff).
 *
 * The head of the unit manages it: the account that handled the unit first (the oldest account there),
 * plus anyone holding the head position (Dean / Governor). Only the head can add, edit and deactivate
 * accounts and appoint another head. Everyone can edit their own account, except their position.
 * New accounts get the same role and the same college / organization as the person creating them.
 */
trait ManagesUnitUsers
{
    private const CONTACT_NUMBER_MESSAGES = [
        'contact_number.regex' => 'Enter an 11-digit contact number in the format 09xx-xxx-xxxx.',
    ];

    /** Role given to new accounts, e.g. 'college_staff'. */
    abstract protected function unitRole(): string;

    /** Route/view prefix, e.g. 'college' or 'org'. */
    abstract protected function unitPrefix(): string;

    /** Position that makes someone head of the unit, e.g. 'Dean' or 'Governor'. */
    abstract protected function headTitle(): string;

    /** Limits a users query to the signed-in user's college / organization. */
    abstract protected function scopeToUnit(Builder $query): Builder;

    /** College / organization fields copied from the signed-in user onto new accounts. */
    abstract protected function unitAttributes(): array;

    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255', new PersonName];
    }

    public function index()
    {
        $users = $this->unitUsers()->orderBy('name')->get();
        $primaryId = $this->primaryId();
        $canManage = $this->isHead(auth()->user());
        $headIds = $users->filter(fn ($u) => $this->isHead($u, $primaryId))->pluck('id')->all();
        $headTitle = $this->headTitle();

        return view($this->unitPrefix() . '.users.index', compact('users', 'primaryId', 'canManage', 'headIds', 'headTitle'));
    }

    public function create()
    {
        abort_unless($this->isHead(auth()->user()), 403);

        return view($this->unitPrefix() . '.users.create', ['editUser' => null, 'canManage' => true]);
    }

    public function store(Request $request)
    {
        abort_unless($this->isHead(auth()->user()), 403);

        $data = $request->validate([
            'name'     => $this->nameRules(),
            'last_name' => ['required', 'string', 'max:120', new PersonName],
            'contact_number' => ['nullable', 'string', 'regex:/^\d{4}-?\d{3}-?\d{4}$/'],
            'position' => 'required|string|max:120',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->max(94)->mixedCase()->symbols()],
        ], self::CONTACT_NUMBER_MESSAGES);

        User::create([
            'name'     => $data['name'],
            'last_name' => $data['last_name'],
            'contact_number' => $data['contact_number'] ?? null,
            'position' => $data['position'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $this->unitRole(),
        ] + $this->unitAttributes());

        return redirect()->route($this->unitPrefix() . '.users.index')
            ->with('status', 'Account created for ' . $data['name'] . '.');
    }

    public function edit(User $user)
    {
        $user = $this->findInUnit($user);
        $canManage = $this->isHead(auth()->user());
        abort_unless($canManage || $user->id === auth()->id(), 403);

        return view($this->unitPrefix() . '.users.create', ['editUser' => $user, 'canManage' => $canManage]);
    }

    public function update(Request $request, User $user)
    {
        $user = $this->findInUnit($user);
        $canManage = $this->isHead(auth()->user());
        abort_unless($canManage || $user->id === auth()->id(), 403);

        $data = $request->validate([
            'name'     => $this->nameRules(),
            'last_name' => ['required', 'string', 'max:120', new PersonName],
            'contact_number' => ['nullable', 'string', 'regex:/^\d{4}-?\d{3}-?\d{4}$/'],
            'position' => [$canManage ? 'required' : 'nullable', 'string', 'max:120'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)->max(94)->mixedCase()->symbols()],
        ], self::CONTACT_NUMBER_MESSAGES);

        $user->name  = $data['name'];
        $user->last_name = $data['last_name'];
        $user->contact_number = $data['contact_number'] ?? null;
        $user->email = $data['email'];
        if ($canManage) {
            $user->position = $data['position']; // only the head changes positions (and appoints heads)
        }
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return redirect()->route($this->unitPrefix() . '.users.index')
            ->with('status', $user->id === auth()->id() ? 'Your account was updated.' : 'Account updated for ' . $user->name . '.');
    }

    public function deactivate(User $user)
    {
        $user = $this->findInUnit($user);
        abort_unless($this->isHead(auth()->user()), 403);

        $back = redirect()->route($this->unitPrefix() . '.users.index');

        if ($user->id === auth()->id()) {
            return $back->with('error', "You can't deactivate your own account.");
        }
        if ($user->id === $this->primaryId()) {
            return $back->with('error', "The original {$this->headTitle()} account can't be deactivated here. Ask the GSU admin.");
        }

        $name = $user->name;
        $result = app(AccountDeactivationService::class)->deactivate($user);
        if ($result['already_deactivated']) {
            return $back->with('status', 'Account is already deactivated for ' . $name . '.');
        }

        return $back->with(
            'status',
            'Account deactivated for ' . $name . '. '
            . $result['requests'] . ' upcoming request(s) and ' . $result['bookings']
            . ' upcoming booking(s) cancelled; all records were preserved.'
        );
    }

    /** Accounts in the signed-in user's college / organization. */
    protected function unitUsers(): Builder
    {
        return $this->scopeToUnit(User::where('role', $this->unitRole()));
    }

    /** The account that handled the unit first. */
    protected function primaryId(): ?int
    {
        return $this->unitUsers()->min('id');
    }

    protected function isHead(User $user, ?int $primaryId = null): bool
    {
        return $user->id === ($primaryId ?? $this->primaryId())
            || strcasecmp(trim((string) $user->position), $this->headTitle()) === 0;
    }

    /** 404 for accounts outside the signed-in user's college / organization. */
    protected function findInUnit(User $user): User
    {
        return $this->unitUsers()->whereKey($user->id)->firstOrFail();
    }
}
