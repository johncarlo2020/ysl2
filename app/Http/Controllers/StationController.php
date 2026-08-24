<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Station;
use App\Models\User;
use App\Models\Locker;
use App\Models\RefillLog;
use App\Models\StationUser;
use App\Events\RfidCardTapped;
use App\Events\RfidCardTappedAtStation;
use App\Events\StationCheckedIn;
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

        $pusherKey     = config('broadcasting.connections.pusher.key');
        $pusherCluster = config('broadcasting.connections.pusher.options.cluster', 'us2');

        return view('station', compact('station', 'user', 'pusherKey', 'pusherCluster'));
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

    public function testRoulette()
    {
        $products = Locker::get();

        return view('test-roulette', compact('products'));
    }

    public function stock(Request $request)
    {
        $products = Locker::find($request->id);
        $previousAmount = $products->available;
        $products->available = $products->available - 1;
        $products->save();
        
        // Log the roulette activity
        RefillLog::create([
            'locker_id' => $products->id,
            'type' => 'roulette',
            'previous_amount' => $previousAmount,
            'quantity_added' => -1, // Negative for deduction
            'new_amount' => $products->available,
            'user_id' => auth()->id(),
            'notes' => 'Prize won from roulette by ' . (auth()->user()->name ?? 'Guest')
        ]);
        
        return $products;
    }

    public function refill(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:lockers,id',
            'quantity' => 'nullable|integer|min:1'
        ]);

        $products = Locker::find($request->id);
        
        if (!$products) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        
        $previousAmount = $products->available;
        
        // If quantity is provided, add that amount to available stock
        if ($request->has('quantity') && $request->quantity > 0) {
            $quantityAdded = (int)$request->quantity;
            $newAvailable = $products->available + $quantityAdded;
            
            // Allow exceeding allocation - update allocation if new stock exceeds it
            if ($newAvailable > $products->allocation) {
                $products->allocation = $newAvailable;
            }
            
            $products->available = $newAvailable;
        } else {
            // Default behavior: refill to allocation
            $quantityAdded = $products->allocation - $products->available;
            $products->available = $products->allocation;
        }
        
        $products->save();
        
        // Log the refill action
        RefillLog::create([
            'locker_id' => $products->id,
            'type' => 'refill',
            'previous_amount' => $previousAmount,
            'quantity_added' => $quantityAdded,
            'new_amount' => $products->available,
            'user_id' => auth()->id(),
            'notes' => 'Stock refilled by admin'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Stock added successfully',
            'product' => $products
        ]);
    }

    public function stocks()
    {
        $products = Locker::orderBy('id', 'asc')->get(['id', 'name', 'allocation', 'available']);
        
        // Get total stocks added per locker
        $totalAddedPerLocker = RefillLog::getTotalAddedPerLocker();
        
        // Add total_added to each product
        foreach ($products as $product) {
            $product->total_added = $totalAddedPerLocker[$product->id] ?? 0;
        }

        return view('products', compact('products'));
    }

    public function refillLogs()
    {
        $logs = RefillLog::with(['locker', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('refill-logs', compact('logs'));
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
                DB::raw('DATE(DATE_ADD(created_at, INTERVAL 0 HOUR)) as date'),
                DB::raw('LOWER(DATE_FORMAT(DATE_ADD(created_at, INTERVAL 0 HOUR), "%l%p")) as hour'),
                DB::raw('COUNT(*) as registrations')
            )
                ->whereNotNull('created_at')
                ->where(DB::raw('DATE(DATE_ADD(created_at, INTERVAL 0 HOUR))'), '>=', $startDate->toDateString())
                ->whereBetween(DB::raw('HOUR(DATE_ADD(created_at, INTERVAL 0 HOUR))'), [10, 22]) // 10AM to 10PM
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

    public function rfidReg($reg)
    {
        $totalStations = \App\Models\Station::count();

        $users = User::orderBy('id', 'desc')
            ->get(['id', 'code', 'rfid_uid'])
            ->filter(function ($user) use ($totalStations) {
                return StationUser::where('user_id', $user->id)->count() < $totalStations;
            })
            ->values();

        $regId         = $reg;
        $pusherKey     = config('broadcasting.connections.pusher.key');
        $pusherCluster = config('broadcasting.connections.pusher.options.cluster', 'us2');

        return view('rfid', compact('users', 'regId', 'pusherKey', 'pusherCluster'));
    }

    public function rfidAdmin()
    {
        $totalStations = \App\Models\Station::count();

        $users = User::orderBy('id', 'desc')
            ->get(['id', 'code', 'rfid_uid'])
            ->filter(function ($user) use ($totalStations) {
                return StationUser::where('user_id', $user->id)->count() < $totalStations;
            })
            ->values();

        $pusherKey     = config('broadcasting.connections.pusher.key');
        $pusherCluster = config('broadcasting.connections.pusher.options.cluster', 'us2');

        return view('rfid', compact('users', 'pusherKey', 'pusherCluster'));
    }

    public function receiveRfid(Request $request)
    {
        // Shared secret so only the local server.js can trigger this
        if ($request->header('X-RFID-Token') !== config('app.rfid_token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $uid = strtoupper(trim($request->input('uid', '')));
        if (empty($uid)) {
            return response()->json(['error' => 'Missing uid'], 422);
        }

        $stationId = $request->input('station_id', '');

        // Push UID to browser clients via the local WebSocket hub
        try {
            $payload = ['uid' => $uid];
            if (!empty($stationId)) {
                $payload['station_id'] = $stationId;
            }
            $ctx = stream_context_create(['http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\nX-RFID-Token: " . config('app.rfid_token'),
                'content' => json_encode($payload),
                'timeout' => 2,
            ]]);
            @file_get_contents('http://127.0.0.1:3001/push', false, $ctx);
        } catch (\Throwable $e) {
            // Non-fatal — hub may not be running
        }

        // Broadcast to general 'rfid' channel for admin pages
        broadcast(new RfidCardTapped($uid));

        // If station_id is provided, also broadcast to station-specific channel for kiosk pages
        if (!empty($stationId) && is_numeric($stationId)) {
            broadcast(new RfidCardTappedAtStation($uid, (int)$stationId));
        }

        return response()->json(['ok' => true, 'uid' => $uid]);
    }

    public function receiveReaderStatus(Request $request)
    {
        // Shared secret so only the local server.js can trigger this
        if ($request->header('X-RFID-Token') !== config('app.rfid_token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $connected = $request->input('connected', false);
        $readerName = $request->input('reader', null);
        $stationId = $request->input('station_id', '');
        $regId = $request->input('reg_id', '');
        $timestamp = $request->input('timestamp', time() * 1000);

        // Determine which channel to broadcast to
        $channels = [];
        
        if (!empty($stationId) && is_numeric($stationId)) {
            $channels[] = 'rfid-station-' . $stationId;
        } elseif (!empty($regId) && is_numeric($regId)) {
            $channels[] = 'rfid-reg-' . $regId;
        } else {
            $channels[] = 'rfid';
        }

        // Broadcast reader status to appropriate channels
        foreach ($channels as $channel) {
            try {
                $pusher = app('pusher');
                $pusher->trigger($channel, 'reader.status', [
                    'connected' => $connected,
                    'reader' => $readerName,
                    'timestamp' => $timestamp,
                    'station_id' => $stationId,
                    'reg_id' => $regId,
                ]);
            } catch (\Throwable $e) {
                \Log::warning('Failed to broadcast reader status: ' . $e->getMessage());
            }
        }

        return response()->json(['ok' => true, 'connected' => $connected]);
    }

     public function kiosk(Station $station)
    {
        $pusherKey     = config('broadcasting.connections.pusher.key');
        $pusherCluster = config('broadcasting.connections.pusher.options.cluster', 'us2');
        return view('kiosk', compact('station', 'pusherKey', 'pusherCluster'));
    }

     public function assignRfid(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'rfid_uid' => 'required|string|max:64|unique:users,rfid_uid,' . $request->user_id,
        ]);

        $user = User::findOrFail($request->user_id);
        $user->rfid_uid = trim($request->rfid_uid);
        $user->save();

        return response()->json(['message' => 'RFID assigned successfully']);
    }

   public function unlinkRfid(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->rfid_uid = null;
        $user->save();

        return response()->json(['message' => 'RFID unlinked successfully']);
    }

    public function checkRfid(Request $request)
    {
        $request->validate([
            'rfid_uid' => 'required|string|max:64',
        ]);

        $user = User::where('rfid_uid', trim($request->rfid_uid))->first();

        if ($user) {
            return response()->json([
                'linked'    => true,
                'user_code' => $user->code,
                'user_id'   => $user->id,
            ]);
        }

        return response()->json(['linked' => false]);
    }

    public function secretCheckin(Request $request)
    {
        $request->validate([
            'station_id' => 'required|integer|exists:stations,id',
        ]);

        $stationId = $request->input('station_id');

        $user = auth()->user();

        // Prevent duplicate check-in
        $alreadyCheckedIn = StationUser::where('user_id', $user->id)
            ->where('station_id', $stationId)
            ->exists();

        if ($alreadyCheckedIn) {
            return response()->json(['message' => 'Already checked in', 'status' => 'duplicate'], 200);
        }

        try {
            DB::beginTransaction();

            $lastStation = StationUser::where('user_id', $user->id)->orderBy('id', 'desc')->first();

            if (empty($lastStation)) {
                $referenceTime = $user->last_login_at ?? $user->created_at;
            } else {
                $referenceTime = $lastStation->created_at;
            }

            $secondsSpent = Carbon::now()->diffInSeconds($referenceTime);

            $stationUser = new StationUser();
            $stationUser->user_id = $user->id;
            $stationUser->station_id = $stationId;
            $stationUser->time_spent = $secondsSpent;
            $stationUser->save();

            DB::commit();

            $stationName = Station::find($stationId)?->name ?? '';
            broadcast(new StationCheckedIn($user->id, $stationId, $stationName));

            return response()->json(['message' => 'Station checked in successfully', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function rfidTap(Request $request)
    {
        $request->validate([
            'rfid_uid' => 'required|string|max:64',
            'station_id' => 'required|integer|exists:stations,id',
        ]);

        $user = User::where('rfid_uid', trim($request->rfid_uid))->first();

        if (!$user) {
            return response()->json(['message' => 'RFID card not recognised', 'status' => 'error'], 404);
        }

        $stationId = (int) $request->station_id;

        // Prevent duplicate check-in
        $alreadyCheckedIn = StationUser::where('user_id', $user->id)
            ->where('station_id', $stationId)
            ->exists();

        if ($alreadyCheckedIn) {
            return response()->json(['message' => 'Already checked in', 'status' => 'duplicate'], 200);
        }

        // Station 4 requires completion of stations 1, 2, and 3 first
        if ($stationId === 4) {
            $completedPrerequisites = StationUser::where('user_id', $user->id)
                ->whereIn('station_id', [1, 2, 3])
                ->distinct('station_id')
                ->count('station_id');

            if ($completedPrerequisites < 3) {
                return response()->json(['message' => 'Must complete stations 1, 2 and 3 first', 'status' => 'prerequisites_not_met'], 422);
            }
        }

        try {
            DB::beginTransaction();

            $lastStation = StationUser::where('user_id', $user->id)->orderBy('id', 'desc')->first();

            if (empty($lastStation)) {
                $referenceTime = $user->last_login_at ?? $user->created_at;
            } else {
                $referenceTime = $lastStation->created_at;
            }

            $secondsSpent = Carbon::now()->diffInSeconds($referenceTime);

            $stationUser = new StationUser();
            $stationUser->user_id = $user->id;
            $stationUser->station_id = $stationId;
            $stationUser->time_spent = $secondsSpent;
            $stationUser->save();

            // Station 4 is the final station — clear the RFID UID after check-in
            if ($stationId === 4) {
                $user->rfid_uid = null;
                $user->save();
            }

            DB::commit();

            // Notify the user's station page to open the check-in modal
            $stationName = Station::find($stationId)?->name ?? '';
            broadcast(new StationCheckedIn($user->id, $stationId, $stationName));

            return response()->json(['message' => 'Station checked in successfully', 'status' => 'success'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}
