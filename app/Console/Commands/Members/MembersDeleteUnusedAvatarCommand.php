<?php

namespace App\Console\Commands\Members;

use App\Models\Member\Member;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MembersDeleteUnusedAvatarCommand extends Command
{
    protected $signature = 'members:delete-unused-avatar';

    protected $description = 'Delete Unused Member original Avatar Command';

    public function handle()
    {
        foreach (Storage::files('member/avatar/original') as $file) {
            if (!Member::withTrashed()->whereAvatar(str_replace('member/avatar/original/', '', $file))->first()) {
                Storage::delete($file);
            }
        }
    }
}
