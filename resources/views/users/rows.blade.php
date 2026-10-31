                    @foreach ($data['users'] as $user)
                        <tr>
                            <td data-order="{{ $user->id }}">
                                <a href="{{ route('userData', ['user' => $user->id]) }}" class="font-weight-bold text-primary">
                                    {{ $user->id }}
                                </a>
                            </td>
                            <td class="text-nowrap">{{ $user->code ?: '—' }}</td>
                            <td data-order="{{ $user->created_at?->timestamp ?? 0 }}" data-export="{{ $user->created_at?->copy()->timezone('Asia/Manila')->format('Y-m-d H:i:s') ?? 'Unavailable' }}">
                                @if ($user->created_at)
                                    <time class="user-timestamp" datetime="{{ $user->created_at->toIso8601String() }}">{{ $user->created_at->copy()->timezone('Asia/Manila')->format('M d, Y') }}<span>{{ $user->created_at->copy()->timezone('Asia/Manila')->format('h:i:s A') }}</span></time>
                                @else
                                    <span class="user-in-progress">Unavailable</span>
                                @endif
                            </td>
                            <td data-order="{{ $user->completed_at?->timestamp ?? 0 }}" data-export="{{ $user->completed_at?->copy()->timezone('Asia/Manila')->format('Y-m-d H:i:s') ?? 'In progress' }}">
                                @if ($user->completed_at)
                                    <time class="user-timestamp" datetime="{{ $user->completed_at->toIso8601String() }}">{{ $user->completed_at->copy()->timezone('Asia/Manila')->format('M d, Y') }}<span>{{ $user->completed_at->copy()->timezone('Asia/Manila')->format('h:i:s A') }}</span></time>
                                @else
                                    <span class="user-in-progress">In progress</span>
                                @endif
                            </td>
                            <td data-order="{{ $user->completion_seconds ?? -1 }}">
                                @if ($user->completion_seconds !== null)
                                    <span class="user-duration">{{ \Carbon\CarbonInterval::seconds($user->completion_seconds)->cascade()->forHumans(['short' => true]) }}</span>
                                @else
                                    <span class="user-in-progress">{{ $user->completed_at ? 'Unavailable' : '—' }}</span>
                                @endif
                            </td>
                            @foreach ($user['stations'] as $id => $station)
                                <td class="text-center" data-order="{{ $station['value'] ? 1 : 0 }}">
                                    <span class="station-status {{ $station['value'] ? 'complete' : 'pending' }}"
                                        title="Station {{ $id }}: {{ $station['name'] }} — {{ $station['value'] ? 'Completed' : 'Pending' }}"
                                        role="img" aria-label="Station {{ $id }}: {{ $station['value'] ? 'Completed' : 'Pending' }}">
                                        @if ((int) $id === 4)
                                            <i class="fa-solid fa-gift" aria-hidden="true"></i>
                                        @else
                                            <i class="fa-solid {{ $station['value'] ? 'fa-check' : 'fa-xmark' }}" aria-hidden="true"></i>
                                        @endif
                                    </span>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
