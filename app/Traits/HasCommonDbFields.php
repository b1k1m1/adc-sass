<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasCommonDbFields
{
    protected function addCommonFieldsForCreate(array $data): array
    {
        $user = Auth::user();

        return array_merge($data, [
            'update_flag' => 'Y',
            'user_cd'     => $user?->user_cd,   // safe if running console/no user
            'update_dt'   => now(),
            'company_id'  => 'DS02',
        ]);
    }

    public function addCommonFieldsForUpdate(array $data): array
    {
        $user = Auth::user();

        return array_merge($data, [
            'update_flag' => 'Y',
            'user_cd'     => $user?->user_cd,
            'update_dt'   => now(),
            'company_id'  => 'DS02',
        ]);
    }

    public function commonFields(): array
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        return [
            'update_flag' => 'Y',
            'user_cd'     => $user?->user_cd,
            'update_dt'   => now(),
            'company_id'  => 'DS02',
        ];
    }

}
