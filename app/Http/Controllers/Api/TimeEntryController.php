<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeEntryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $userId = Auth::id();

        $start = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $entries = TimeEntry::query()
            ->where('user_id', $userId)
            ->whereBetween('entry_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $totalMinutes = (int) $entries->sum('minutes');

        $days = $entries
            ->groupBy(fn (TimeEntry $e) => $e->entry_date->format('Y-m-d'))
            ->map(fn ($items) => $items->values());

        return response()->json([
            'month' => $request->month,
            'totalMinutes' => $totalMinutes,
            'days' => $days,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'entry_date' => ['required', 'date_format:Y-m-d'],
            'minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $entry = TimeEntry::create([
            'user_id' => Auth::id(),
            ...$data,
        ]);

        return response()->json([
            'message' => 'Sikeres mentés.',
            'entry' => $entry,
        ], 201);
    }

    public function update(Request $request, TimeEntry $timeEntry)
    {
        abort_unless($timeEntry->user_id === Auth::id(), 403);

        $data = $request->validate([
            'entry_date' => ['required', 'date_format:Y-m-d'],
            'minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $timeEntry->update($data);

        return response()->json([
            'message' => 'Sikeres módosítás.',
            'entry' => $timeEntry,
        ]);
    }

    public function destroy(TimeEntry $timeEntry)
    {
        abort_unless($timeEntry->user_id === Auth::id(), 403);

        $timeEntry->delete();

        return response()->json([
            'message' => 'Sikeres törlés.',
        ]);
    }
}
