<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Program;

class BookingThemeService
{
    public string $userInfoText = '— Please add your name and email';

    public ?string $firstMainColor = null;

    public ?string $secondMainColor = null;

    public ?string $headersColor = null;

    public ?string $secondHeadersColor = null;

    public ?string $couponButtonColor = null;

    public ?string $confirmButtonColor = null;

    public ?string $clubSelectColor = null;

    public ?string $buttonTextColor = null;

    public ?string $totalColor = null;

    public bool $showCoupons = true;

    public bool $showWithPackageYouGet = true;

    public bool $showClubs = true;

    public bool $showStepsProgress = true;

    public ?string $terms_and_conditions = null;

    public function setFromPackage(Package $package): void
    {
        $this->showCoupons = $package->show_coupons;
        $this->showClubs = $package->show_clubs;
        $this->showStepsProgress = $package->show_steps_progress;

        $this->setFromProgram($package->program);
    }

    public function setFromProgram(Program $program): void
    {
        if ($program->isProgramSource('hsbc')) {
            $this->userInfoText
                = '— Please add your name, email address & phone number as registered with HSBC';
            $this->showWithPackageYouGet = false;
            $this->terms_and_conditions
                = 'I have read and agree to the
                 <a href="https://www.theentertainerme.com/new-terms-of-use-2?v=full" target="_blank">
                    Terms and conditions
                 </a> and Exclusion Policy';
        }

        // Rak bank
        if ($program->id == Program::RAK_BANK_ID) {
            $this->showWithPackageYouGet = false;
        }

        $this->firstMainColor = $program->booking_first_main_color;
        $this->secondMainColor = $program->booking_second_main_color;
        $this->headersColor = $program->booking_headers_color;
        $this->secondHeadersColor = $program->booking_second_headers_color;
        $this->couponButtonColor = $program->booking_coupon_button_color;
        $this->confirmButtonColor = $program->booking_confirm_button_color;
        $this->totalColor = $program->booking_total_color;
        $this->clubSelectColor = $program->booking_clubs_select_color;
        $this->buttonTextColor = $program->booking_button_text_color;
    }
}
