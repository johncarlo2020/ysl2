<?php

namespace App\Services;

use App\Models\StationUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UsersTable
{
    public function response(Request $request, Collection $stations)
    {
        $input = $request->validate([
            'draw' => ['required', 'integer', 'min:0'],
            'start' => ['sometimes', 'integer', 'min:0'],
            'length' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search.value' => ['nullable', 'string', 'max:255'],
            'order' => ['sometimes', 'array', 'max:1'],
            'order.*.column' => ['required', 'integer', 'min:0'],
            'order.*.dir' => ['required', 'in:asc,desc'],
        ]);
        $query = User::where('users.created_at', '>=', '2024-05-24 00:00:00')
            ->whereDoesntHave('roles', fn ($roles) => $roles->whereIn('name', ['admin', 'staff']));
        $total = (clone $query)->count();
        $search = trim($input['search']['value'] ?? '');
        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('users.code', 'like', '%' . $search . '%');
                if (ctype_digit($search)) {
                    $query->orWhere('users.id', $search);
                }
            });
        }
        $filtered = $search === '' ? $total : (clone $query)->count();

        // One first visit per station; repeat visits must not change completion time.
        $firstVisits = StationUser::select('user_id', 'station_id')
            ->selectRaw('MIN(created_at) as first_visit')
            ->whereIn('station_id', $stations->keys())
            ->groupBy('user_id', 'station_id');
        $completion = DB::query()->fromSub($firstVisits, 'first_visits')->select('user_id')
            ->selectRaw('MAX(first_visit) as completed_at')->groupBy('user_id')
            ->havingRaw('COUNT(*) = ? AND COUNT(first_visit) = ?', [$stations->count(), $stations->count()]);
        $query->leftJoinSub($completion, 'completion', 'completion.user_id', '=', 'users.id')
            ->select('users.*', 'completion.completed_at');
        $duration = DB::connection()->getDriverName() === 'sqlite'
            ? "(strftime('%s', completion.completed_at) - strftime('%s', users.created_at))"
            : 'TIMESTAMPDIFF(SECOND, users.created_at, completion.completed_at)';
        $query->selectRaw("CASE WHEN completion.completed_at >= users.created_at THEN $duration ELSE NULL END as completion_seconds");
        $column = (int) ($input['order'][0]['column'] ?? 0);
        $direction = $input['order'][0]['dir'] ?? 'desc';
        $columns = ['users.id', 'users.code', 'users.created_at', 'completion.completed_at', 'completion_seconds'];
        if (isset($columns[$column])) {
            $query->orderBy($columns[$column], $direction);
        } elseif ($stationId = $stations->keys()->values()->get($column - 5)) {
            $query->orderBy(StationUser::selectRaw('COUNT(*) > 0')
                ->whereColumn('station_users.user_id', 'users.id')->where('station_id', $stationId), $direction);
        }
        $users = $query->orderBy('users.id', 'desc')->offset($input['start'] ?? 0)
            ->limit($input['length'] ?? 10)->with('stationUser:id,user_id,station_id')->get();
        foreach ($users as $user) {
            $user->completed_at = $user->completed_at ? \Carbon\Carbon::parse($user->completed_at) : null;
            $user->completion_seconds = $user->completion_seconds === null ? null : (int) $user->completion_seconds;
            $visited = $user->stationUser->pluck('station_id');
            $user->stations = $stations->map(fn ($name, $id) => ['name' => $name, 'value' => $visited->contains($id)]);
        }

        return response()->json([
            'draw' => (int) $input['draw'],
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'rows' => view('users.rows', ['data' => ['users' => $users]])->render(),
        ]);
    }
}
