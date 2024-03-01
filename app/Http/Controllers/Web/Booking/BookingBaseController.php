<?php

namespace App\Http\Controllers\Web\Booking;

use App\Http\Controllers\Web\Controller;
use App\Models\Program;
use App\Services\WebsiteThemeService;

class BookingBaseController extends Controller
{
    protected const DEFAULT_UI_COLOR = 'blue';

    protected const HSBC_UI_COLOR = 'coral';

    protected WebsiteThemeService $theme;

    public function __construct(WebsiteThemeService $theme)
    {
        parent::__construct();
        $this->theme = $theme;
        $this->theme->bodyClass = 'bg-white';
        $this->theme->hidePreFooterContacts();
    }

    protected function getUiColor($booking): string
    {
        return $booking->plan->package->program->source == Program::SOURCE_MAP['hsbc']
            ? self::HSBC_UI_COLOR : self::DEFAULT_UI_COLOR;
    }
}
