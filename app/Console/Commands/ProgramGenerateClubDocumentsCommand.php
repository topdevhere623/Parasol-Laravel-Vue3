<?php

namespace App\Console\Commands;

use App\Jobs\ProgramGenerateClubDocumentJob;
use App\Models\Program;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ProgramGenerateClubDocumentsCommand extends Command
{
    protected $signature = 'program:generate-club-documents';

    protected $description = 'Generate club document PDFs';

    public function handle(): int
    {
        Program::where('club_document_available', 1)
            ->chunkById(
                100,
                fn (Collection $programs) => $programs->each(
                    fn ($program) => ProgramGenerateClubDocumentJob::dispatch($program->id)
                        ->onQueue('low')
                )
            );

        return self::SUCCESS;
    }
}
