<?php

namespace App\Traits;

trait EnumChangeTrait
{
    private function setEnumValues($table, $column, array $values, $nullable = false, $default = null)
    {
        $quotedValues = collect($values)
            ->map(function ($value) {
                return "'${value}'";
            })
            ->join(', ');

        $suffix = '';

        if (!$nullable) {
            $suffix .= ' NOT NULL';
        }

        if ($default) {
            $suffix .= " DEFAULT '${default}'";
        }

        $statement = "ALTER TABLE ${table} CHANGE COLUMN ${column} ${column} ENUM(${quotedValues}) ${suffix}";

        \Illuminate\Support\Facades\DB::statement($statement);
    }
}
