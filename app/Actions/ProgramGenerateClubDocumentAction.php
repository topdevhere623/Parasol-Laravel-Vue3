<?php

namespace App\Actions;

use App\Models\Club\Club;
use App\Models\Program;
use App\Services\PdfService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Imagick;

class ProgramGenerateClubDocumentAction
{
    public const MAX_LINES_COUNT = 60;
    /**
     * Gap between clubs
     */
    public const GAP_LINES_COUNT = 3;

    public function handle(Program $program): void
    {
        if (!$program->club_document_available) {
            return;
        }
        if (!$package = $program->clubDocumentMainPagePackage) {
            return;
        }

        if (!$plan = $program->clubDocumentPlan) {
            return;
        }

        $pdfService = new PdfService(120);
        $tempStorage = Storage::disk('temp');
        $programLogo = file_url($program, 'website_logo', 'large', asset('assets/images/logo-white.png'));

        $programJoin = $program->club_document_join_today_available;

        // TODO: Refactor to general solution
        if ($program->id != Program::ENTERTAINER_SOLEIL_ID) {
            $website = 'advplus.ae';
            $email = 'memberships@advplus.ae';
        } else {
            $website = null;
            $email = 'entertainersoleil@advplus.ae';
        }

        // front page
        $pdfFilePath = $tempStorage->path($this->getPdfFileName($program, 0));
        $pdfFiles = [$pdfFilePath];

        $pdfService
            ->create(
                view(
                    'program.pdf.front-page',
                    [
                        'programName' => $program->name,
                        'programLogo' => $programLogo,
                        'package' => $package,
                        'plans' => $package->plans()->active()->get(),
                        'join' => $programJoin,
                        'ratio' => $pdfService->getRatio(),
                        'email' => $email,
                        'website' => $website,
                    ]
                )->render(),
                $pdfFilePath
            );

        // the rest pages
        $allClubs = $plan->clubsQuery()
            ->website()
            ->oldest('title')
            ->get();

        $linesCount = 0;
        $clubs = [];

        $i = 0;
        $page = 1;
        while ($i < count($allClubs) - 1) {
            while ($linesCount < self::MAX_LINES_COUNT && isset($allClubs[$i])) {
                $linesCount += $this->getMaxStrings($allClubs[$i]);
                $clubs[] = $allClubs[$i];
                $i++;
            };

            $pdfFilePath = $tempStorage->path($this->getPdfFileName($program, $page));
            $pdfFiles[] = $pdfFilePath;
            $pdfService
                ->create(
                    view(
                        'program.pdf.page',
                        [
                            'programLogo' => $programLogo,
                            'clubs' => $clubs,
                            'backgroundNum' => ($page - 1) % 7 + 1,
                            'join' => $programJoin,
                            'ratio' => $pdfService->getRatio(),
                            'email' => $email,
                            'website' => $website,
                        ]
                    )->render(),
                    $pdfFilePath,
                );

            $image = new Imagick();
            $image->pingImage($pdfFilePath);
            if ($image->getNumberImages() > 1 && count($clubs) > 1) {
                array_pop($clubs);
                array_pop($pdfFiles);
                $i--;
            } else {
                $page++;
                $linesCount = 0;
                $clubs = [];
            }
        }

        $mergedPdfPath = $tempStorage->path($this->getPdfFileName($program));

        $pdfService->merge(
            $pdfFiles,
            $mergedPdfPath
        );

        Storage::put(
            $program->getClubDocsPath(),
            $tempStorage->get($this->getPdfFileName($program))
        );

        File::delete([...$pdfFiles, $mergedPdfPath]);
    }

    private function getMaxStrings(Club $club): int
    {
        return max(
            $this->getLinesCount($club->title, 10, 10) + $this->getLinesCount($club->address, 15, 17) + 6,
            $this->getLinesCount($club->club_overview, 27, 35),
            $this->getLinesCount($club->description, 29, 40),
            $this->getLinesCount($club->check_in_area, 20, 30)
        ) + self::GAP_LINES_COUNT;
    }

    private function getLinesCount(?string $text, int $capitalsRate, int $minusculesRate): int
    {
        if (is_null($text)) {
            return 0;
        }

        $listItems = substr_count($text, '<li>');
        $text = preg_replace("/<li[^>]*>(.*?)<\/li>/is", '', $text);
        $text = html_entity_decode(strip_tags($text));
        $capitals = mb_strlen(preg_replace('![^A-Z]+!', '', $text));
        $minuscules = mb_strlen(preg_replace('![^a-z]+!', '', $text));
        $spaces = mb_strlen(preg_replace('![^\s]+!', '', $text));
        $cyphers = mb_strlen(preg_replace('![^0-9]!', '', $text));

        return (int)($capitals / $capitalsRate + ($minuscules + $spaces + $cyphers) / $minusculesRate + $listItems * 1.66);
    }

    private function getPdfFileName(Program $program, ?int $page = null): string
    {
        return $page.'-'.$program->getClubDocFileName();
    }
}
