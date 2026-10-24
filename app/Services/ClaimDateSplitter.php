<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class ClaimDateSplitter
{
    /**
     * Split a service period into multiple segments based on:
     *  - Month boundaries
     *  - PA start date
     *  - PA end date
     *
     * Returns array of segments:
     * [
     *   [
     *     'from' => Carbon,
     *     'to'   => Carbon,
     *     'days' => int,
     *   ],
     *   ...
     * ]
     */
    public static function split(
        string|\DateTimeInterface $serviceFrom,
        string|\DateTimeInterface $serviceTo,
        ?string $paStart = null,
        ?string $paEnd   = null
    ): array {
        $from = Carbon::parse($serviceFrom)->startOfDay();
        $to   = Carbon::parse($serviceTo)->startOfDay();

        if ($from->gt($to)) {
            throw new InvalidArgumentException('service_from cannot be after service_to');
        }

        $boundaries = [];

        // Always start at original service_from
        $boundaries[] = $from->copy();

        // 1) Month boundaries (first day of each new month)
        $cursor = $from->copy()->addMonthNoOverflow()->startOfMonth();
        while ($cursor->lte($to)) {
            // first day of a month inside the range
            if ($cursor->gt($from) && $cursor->lte($to)) {
                $boundaries[] = $cursor->copy();
            }
            $cursor->addMonthNoOverflow();
        }

        // 2) PA start (if inside the range)
        if ($paStart) {
            $paStartDate = Carbon::parse($paStart)->startOfDay();
            if ($paStartDate->gt($from) && $paStartDate->lte($to)) {
                $boundaries[] = $paStartDate->copy();
            }
        }

        // 3) PA end (we use pa_end + 1 day as a new "from" boundary)
        if ($paEnd) {
            $paEndDate = Carbon::parse($paEnd)->startOfDay();
            $afterPaEnd = $paEndDate->copy()->addDay();

            if ($afterPaEnd->gt($from) && $afterPaEnd->lte($to)) {
                $boundaries[] = $afterPaEnd->copy();
            }
        }

        // Remove duplicates & sort
        $boundaries = collect($boundaries)
            ->unique(fn ($date) => $date->toDateString())
            ->sort()
            ->values()
            ->all();

        $segments = [];

        /** @var Carbon $segFrom */
        foreach ($boundaries as $index => $segFrom) {
            // Last segment goes up to the original service_to
            if ($index === count($boundaries) - 1) {
                $segTo = $to->copy();
            } else {
                /** @var Carbon $nextBoundary */
                $nextBoundary = $boundaries[$index + 1];
                $segTo = $nextBoundary->copy()->subDay();
            }

            // Skip empty / inverted ranges (can happen if PA/Month cut collapses)
            if ($segFrom->gt($segTo)) {
                continue;
            }

            $segments[] = [
                'from' => $segFrom->copy(),
                'to'   => $segTo->copy(),
                'days' => $segFrom->diffInDays($segTo) + 1,
            ];
        }

        return $segments;
    }
}
