<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Station;
use App\Models\User;
use App\Models\Locker;

use App\Models\StationUser;
use DB;
use Auth;
use Carbon\Carbon;

class StationController extends Controller
{
    public function index(Station $station)
    {
        $user = StationUser::where('user_id', auth()->id())
            ->where('station_id', $station->id)
            ->exists();

        return view('station', compact('station', 'user'));
    }

    public function checkExisting(Request $request)
    {
        $code = $request->code;

        $check = User::where('code', $code)->exists();

        return $check;
    }

    public function landing()
    {
        return view('landing');
    }

    public function welcome()
    {
        $user = User::with('stationUser')->where('id', auth()->id())->first();
        // dd($user->stationUser->count());

        $stationDone = $user->stationUser->count();
        $stations = Station::get();

        // Loop through each station and append a flag indicating if the user has it
        foreach ($stations as $station) {
            $userHasStation = $user
                ->StationUser()
                ->where('station_id', $station->id)
                ->exists();
            $station->status = $userHasStation;
        }
        // dd($stationDone);

        if ($stationDone < 4) {
            return view('dashboard', compact('stations', 'stationDone'));
        } else {
            return redirect()->route('congrats');
        }
    }

    public function roulette()
    {
        $products = Locker::get()->map(function ($product) {
            if ($product->available == 0) {
                $product->percentage = 0;
            }
            return $product;
        });

        return view('rollet', compact('products'));
    }

    public function stock(Request $request)
    {
        $products = Locker::find($request->id);
        $products->available = $products->available - 1;
        $products->save();
        return $products;
    }

    public function refill(Request $request)
    {
        $products = Locker::find($request->id);
        $products->available = $products->allocation;
        $products->save();
        return $products;
    }

    public function stocks()
    {
        $products = Locker::orderBy('id', 'asc')->get(['id', 'name', 'allocation', 'available']);

        return view('products', compact('products'));
    }

    public function scan(Request $request)
    {
        // Parse the URL to get the query string

        $qrCodeMessage = trim($request->qrCodeMessage);

        // Get the last character of the QR code message
        $station_id = substr($qrCodeMessage, -1);

        // Assume that `$station_id` is validated before this point

        try {
            DB::beginTransaction();

            if ($station_id != $request->station) {
                return response()->json(['message' => 'Invalid Qr', 'status' => 'error'], 401);
            }

            $lastStation = StationUser::where('user_id', auth()->id())->orderBy('id', 'desc')->first();

            if (empty($lastStation)) {
                $lastLoginTime = Auth::user()->last_login_at;
                $currentDateTime = Carbon::now();
                $timeSpent = $currentDateTime->diff($lastLoginTime);
                $minutesSpent = $timeSpent->i; // Minutes spent
                $secondsDifference = $timeSpent->s; // Seconds

                // Convert minutes to seconds
                $secondsSpent = $minutesSpent * 60 + $secondsDifference;
            } else {
                $lastLoginTime = $lastStation->created_at;
                $currentDateTime = Carbon::now();
                $timeSpent = $currentDateTime->diff($lastLoginTime);
                $minutesSpent = $timeSpent->i; // Minutes spent
                $secondsDifference = $timeSpent->s; // Seconds
                // Convert minutes to seconds
                $secondsSpent = $minutesSpent * 60 + $secondsDifference;
            }

            $stationUser = new StationUser();
            $stationUser->user_id = auth()->id();
            $stationUser->station_id = $station_id;
            $stationUser->time_spent = $secondsSpent;
            $stationUser->save();
            DB::commit();
            // Success response
            return response()->json(['message' => 'Station ID updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();

            // Handle the error, log it, or return an appropriate response
            return response()->json(['error' => $e], 500);
        }
    }

       public function admin()
    {
        $admin = User::find(auth()->id());
        $permission = $admin->getPermissionNames()->first();
        $today = Carbon::today();
        $startDate = Carbon::create(2025, 6, 17);
        $data['users'] = User::with('stationUser')->take(4)->orderBy('id', 'desc')->where(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d")'), '>=', $startDate->toDateString())
            ->get();
        $data['usersCount'] = User::whereDate('created_at', '>=', $startDate->toDateString())->where(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d")'), '>=', $startDate->toDateString())
            ->count();
        $data['userToday'] = User::whereDate('created_at', $today)->where(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d")'), '>=', $startDate->toDateString())
            ->count();


        $usersWithSixStationUsers = User::with('stationUser')->whereDate('created_at', '>=', $startDate->toDateString())->has('stationUser', '>=', 4)->count();
        // dd($usersWithSixStationUsers);
        $data['completedUsers'] = $usersWithSixStationUsers;
        // dd($usersWithSixStationUsers);

        if ($data['usersCount'] > 0) {
            $data['percentage'] = number_format(($usersWithSixStationUsers / $data['usersCount']) * 100, 2);
        } else {
            $data['percentage'] = 0; // Avoid division by zero
        }
        $userCounts = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')->groupBy('date')->orderBy('date')->get()->toArray();

        $userCountsArray = [];
        $data['dates'] = User::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'))->where(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d")'), '>=', $startDate->toDateString())->groupBy('date')->get();

        // $data['registrationsPerHour'] = User::select(
        //     DB::raw('DATE(DATE_ADD(created_at, INTERVAL 8 HOUR)) as date'),
        //     DB::raw('LOWER(DATE_FORMAT(DATE_ADD(created_at, INTERVAL 8 HOUR), "%l%p")) as hour'),
        //     DB::raw('COUNT(*) as registrations')
        // )
        //     ->whereNotNull('created_at')
        //     ->where(DB::raw('DATE(DATE_ADD(created_at, INTERVAL 8 HOUR))'), '>=', $startDate->toDateString())
        //     // ->whereBetween(DB::raw('HOUR(DATE_ADD(created_at, INTERVAL 8 HOUR))'), [10, 22]) // 10AM to 10PM
        //     ->groupBy('date', 'hour')
        //     ->havingRaw('hour IS NOT NULL AND hour <> \'\'')
        //     ->get()
        //     ->groupBy('hour');
        
            $rawData = User::select(
                DB::raw('DATE(DATE_ADD(created_at, INTERVAL 8 HOUR)) as date'),
                DB::raw('LOWER(DATE_FORMAT(DATE_ADD(created_at, INTERVAL 8 HOUR), "%l%p")) as hour'),
                DB::raw('COUNT(*) as registrations')
            )
                ->whereNotNull('created_at')
                ->where(DB::raw('DATE(DATE_ADD(created_at, INTERVAL 8 HOUR))'), '>=', $startDate->toDateString())
                ->whereBetween(DB::raw('HOUR(DATE_ADD(created_at, INTERVAL 8 HOUR))'), [10, 22]) // 10AM to 10PM
                ->groupBy('date', 'hour')
                ->havingRaw('hour IS NOT NULL AND hour <> \'\'')
                ->get()
                ->groupBy('hour');

            $hours = collect(range(10, 22))->mapWithKeys(function ($h) {
                $label = strtolower(Carbon::createFromTime($h)->format('gA')); 
                return [$label => 0];
            });

            $data['registrationsPerHour'] = $hours->map(function ($_, $hour) use ($rawData) {
                if (isset($rawData[$hour])) {
                    return $rawData[$hour]->map(function ($item) {
                        return [
                            'date' => $item->date,
                            'hour' => $item->hour,
                            'registrations' => $item->registrations,
                        ];
                    });
                }
            
                // No data for this hour — return one entry with null date and 0 registrations
                return collect([
                    [
                        'date' => null,
                        'hour' => $hour,
                        'registrations' => 0,
                    ]
                ]);
            });

        foreach ($userCounts as $userCount) {
            if ($userCount['date'] >= $startDate->toDateString()) {
                $userCountsArray[$userCount['date']] = $userCount['count'];
            }
        }
        $data['usersDaily'] = $userCountsArray;
        // $completed = StationUser::w

        $averageTimespentByStation = StationUser::select('station_id', \DB::raw('AVG(time_spent) as average_timespent'))->groupBy('station_id')->get()->keyBy('station_id');

        $stations = Station::pluck('name', 'id');

        $count = 0;

        foreach ($data['users'] as $user) {
            $userStations = $user->stationUser->pluck('station_id')->toArray();
            $numStations = count($userStations);

            $user->stations = $stations->map(function ($name, $id) use ($userStations, $averageTimespentByStation) {
                return [
                    'name' => $name,
                    'value' => in_array($id, $userStations),
                ];
            });
        }

        $data['stations'] = $stations->map(function ($name, $id) use ($userStations, $averageTimespentByStation) {
            return [
                'name' => $name,
                'average_timespent' => number_format(($averageTimespentByStation->get($id)['average_timespent'] ?? 0) / 60, 2),
                'id' => $id,
            ];
        });


        $averagePlaytimeByUser = StationUser::select('user_id', DB::raw('SUM(time_spent) / 60 as total_playtime'))->groupBy('user_id')->get();

        $totalAveragePlaytime = $averagePlaytimeByUser->avg('total_playtime');
        // dd($totalAveragePlaytime);
        //dd($data['users'][0]['stations']);
        //  dd($data);

        return view('dashboardadmin', compact('data', 'permission'));
    }


    public function users()
    {
        $today = Carbon::today();
        $permission = auth()->user()->getPermissionNames()->first();

        $startDate = Carbon::create(2024, 5, 24);
        $data['users'] = User::whereDate('created_at', '>=', $startDate->toDateString())->with('stationUser')->orderBy('id', 'desc')->get();

        $averageTimespentByStation = StationUser::select('station_id', \DB::raw('AVG(time_spent) as average_timespent'))->groupBy('station_id')->get()->keyBy('station_id');

        $stations = Station::pluck('name', 'id');

        foreach ($data['users'] as $user) {
            $userStations = $user->stationUser->pluck('station_id')->toArray();
            $user->stations = $stations->map(function ($name, $id) use ($userStations, $averageTimespentByStation) {
                return [
                    'name' => $name,
                    'value' => in_array($id, $userStations),
                ];
            });
        }

        $data['stations'] = $stations->map(function ($name, $id) use ($userStations, $averageTimespentByStation) {
            return [
                'name' => $name,
                'average_timespent' => number_format(($averageTimespentByStation->get($id)['average_timespent'] ?? 0) / 60, 2),
            ];
        });
        //dd($data['users'][0]['stations']);
        //  dd($data);

        return view('users', compact('data', 'permission'));
    }

    public function scanner()
    {
        return view('scanner');
    }

    public function userData(User $user)
    {
        $averagePlaytimeByUser = StationUser::where('user_id', $user->id)->avg('time_spent');
        $permission = auth()->user()->getPermissionNames()->first();

        $stations = Station::pluck('name', 'id');

        $averageTimespentByStation = StationUser::where('user_id', $user->id)
            ->orderBy('id', 'asc')
            ->get();
        $total = StationUser::where('user_id', $user->id)
            ->orderBy('id', 'asc')
            ->sum('time_spent');
        $totalMinutes = $total / 60;
        $totalMinutes = number_format($totalMinutes, 2);

        $userStations = $user->stationUser->pluck('station_id')->toArray();
        $numStations = count($userStations);

        $user->stations = $stations->map(function ($name, $id) use ($userStations, $user) {
            $spent = StationUser::where('user_id', $user->id)
                ->where('station_id', $id)
                ->first();
            if (!$spent) {
                $minute = 0;
            } else {
                $seconds = $spent->time_spent;
                $minute = $seconds / 60;
                $minute = number_format($minute, 2);
            }
            return [
                'name' => $name,
                'value' => in_array($id, $userStations),
                'time_spent' => $minute,
                'id' => $id,
            ];
        });

        return view('userData', compact('user', 'totalMinutes', 'permission'));
    }

    public function check(Request $request)
    {
        $check = StationUser::where('user_id', $request->user_id)
            ->where('station_id', $request->station_id)
            ->first();

        if (!$check) {
            $stationUser = new StationUser();
            $stationUser->user_id = $request->user_id;
            $stationUser->station_id = $request->station_id;
            $stationUser->time_spent = 60;
            $stationUser->save();
        } else {
            $check->delete();
        }

        return $check;
    }
}
