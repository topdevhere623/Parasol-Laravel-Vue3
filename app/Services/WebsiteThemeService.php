<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Program;

class WebsiteThemeService
{
    public readonly BookingThemeService $booking;

    public string $favicon;

    public bool $showHeader = true;

    public bool $showHeaderMenu = true;

    public bool $showHeaderMemberPortalLink = true;

    public bool $showFooter = true;

    public bool $showFooterDescription = true;

    public bool $showFooterNavigation = true;

    public bool $showFooterSocials = true;

    public bool $showFooterContacts = false;

    public bool $showPreFooterContacts = true;

    public bool $showTawkChat = true;

    public bool $showBreadcrumbs = true;

    public bool $showClubsListJoinTodayBanner = true;

    public ?string $rulesOfUseLink = null;

    public ?string $headerLogo = null;

    public ?string $mobileHeaderLogo = null;

    public ?string $bodyClass = null;

    public ?string $warningColor = null;

    public ?string $footerBgFirst = null;

    public ?string $footerBgSecond = null;

    public ?string $footerCopy = null;

    public ?string $sloganBackgroundImage = null;

    public ?string $sloganBackgroundImageMobile = null;

    public ?string $headerBgFirst = null;

    public ?string $headerBgSecond = null;

    public string $joinLink = '/#join';

    // Pages Url may be string/null or array as parameters for route function()
    // This way chosen cuz laravel doesn't allow to init default routes in constructor of this class
    public string|array|null $termsAndConditionsUrl = ['page.show', 'terms-and-conditions'];

    public string|array|null $privacyPolicyUrl = ['page.show', 'privacy-policy'];

    public string|array|null $exclusionPolicyUrl = ['page.show', 'exclusion-policy'];

    public string|array|null $faqUrl = ['faq.index'];

    public string|array|null $aboutUsUrl = ['page.show', 'about-us'];

    public string $clubRequestTitle = 'Need more info?';

    public string $clubRequestSubtitle = 'To get a detailed club guide<br> fill out this form:';

    public string $whatsAppUrl = 'https://api.whatsapp.com/send?phone=971521294354&text=Hi%20there%2C%20I%20just%20learned%20about%20Advantage%20Plus%21%20Please%20send%20me%20a%20brochure%2C%20pricing%20and%20info%20on%20how%20can%20I%20become%20a%20member.%20Thank%20you';

    public string $metaTagsFile;

    public string $metaTitlePostfix;

    public string $metaTitle;

    public string $metaDescription;

    public string $memberPortalUrl;

    public function __construct()
    {
        $this->booking = new BookingThemeService();
        $this->headerLogo = asset('assets/images/logo.svg');
        $this->mobileHeaderLogo = asset('assets/images/logo-sm.svg');
        $this->favicon = asset('assets/images/advplus_favicon.png');
        $this->metaTagsFile = is_entertainer_subdomain() ? 'entertainer' : 'advplus';
        $this->memberPortalUrl = \URL::member();
        $this->metaTitlePostfix = is_entertainer_subdomain() ? ' | Entertainer Soleil' : ' | adv+';

        $this->metaTitle = __($this->metaTagsFile.'.title');
        $this->metaDescription = __($this->metaTagsFile.'.description');
    }

    public function setFromPackage(Package $package): self
    {
        $this->showHeader = $package->show_header;
        $this->showHeaderMenu = $package->show_header_menu;
        $this->showHeaderMemberPortalLink = $package->show_header_member_portal_link;
        $this->showFooter = $package->show_footer;
        $this->showFooterDescription = $package->show_footer_description;
        $this->showFooterNavigation = $package->show_footer_navigation;
        $this->showFooterSocials = $package->show_footer_socials;
        $this->showFooterContacts = false;
        $this->showTawkChat = $package->show_tawk_chat;

        $this->setFromProgram($package->program);

        $this->booking->setFromPackage($package);

        $this->setMetaTitle($package->meta_title ?? $package->title);
        if ($package->meta_description) {
            $this->setMetaDescription($package->meta_description);
        }

        return $this;
    }

    public function setFromProgram(Program $program): self
    {
        if ($program->website_logo) {
            $this->headerLogo = $this->mobileHeaderLogo = file_url($program, 'website_logo', 'original');
        }

        $this->booking->setFromProgram($program);
        $this->whatsAppUrl = $program->getWhatsappUrl();

        return $this;
    }

    public function setFromSoleil(): self
    {
        $program = Program::find(Program::ENTERTAINER_SOLEIL_ID);
        $this
            ->setFromProgram($program)
            ->setWarningColor($program->booking_first_main_color)
            ->setHeaderBgFirst('#00b8dd')
            ->setHeaderBgSecond('#2a87a9')
            ->setFooterBgFirst('#2a87a9')
            ->setFooterBgSecond('#00b8dd')
            ->setSloganBackgroundImage('/assets/images/entertainer/header-back.jpg')
            ->setSloganBackgroundImageMobile('/assets/images/entertainer/header-back-mobile.jpg')
            ->setFooterCopy(
                date('Y').' Parasol Loyalty Cards Services LLC. All rights reserved.'
            )
            ->hideClubsListJoinTodayBanner()
            ->showFooterContacts()
            ->setRulesOfUseLink()
            ->hidePreFooterContacts()
            ->hideFooterDescription()
            ->hideFooterSocials();

        $this->favicon = asset('assets/images/entertainersoleil_favicon.png');
        $this->clubRequestTitle = 'Want to know more?';
        $this->clubRequestSubtitle = 'Please share your contact details and we will send&nbsp;you&nbsp;all the information about ENTERTAINER&nbsp;soleil';
        $this->aboutUsUrl = null;
        $this->faqUrl = ['page.show', 'soleil-faq'];
        $this->memberPortalUrl = \URL::member('', ['source' => 'entertainer']);

        return $this;
    }

    public function setBodyClass(string $bodyClass): self
    {
        $this->bodyClass = $bodyClass;

        return $this;
    }

    public function setHeaderBgFirst(string $headerBgFirst): self
    {
        $this->headerBgFirst = $headerBgFirst;

        return $this;
    }

    public function setHeaderBgSecond(string $headerBgSecond): self
    {
        $this->headerBgSecond = $headerBgSecond;

        return $this;
    }

    public function setFooterBgFirst(string $footerBgFirst): self
    {
        $this->footerBgFirst = $footerBgFirst;

        return $this;
    }

    public function setFooterBgSecond(string $footerBgSecond): self
    {
        $this->footerBgSecond = $footerBgSecond;

        return $this;
    }

    public function setFooterCopy(string $footerCopy): self
    {
        $this->footerCopy = $footerCopy;

        return $this;
    }

    public function setSloganBackgroundImage(string $sloganBackgroundImage): self
    {
        $this->sloganBackgroundImage = $sloganBackgroundImage;

        return $this;
    }

    public function setSloganBackgroundImageMobile(string $sloganBackgroundImageMobile): self
    {
        $this->sloganBackgroundImageMobile = $sloganBackgroundImageMobile;

        return $this;
    }

    public function setJoinLink(string $joinLink)
    {
        $this->joinLink = $joinLink;

        return $this;
    }

    public function hideClubsListJoinTodayBanner(): self
    {
        $this->showClubsListJoinTodayBanner = false;

        return $this;
    }

    public function hideHeader(): self
    {
        $this->showHeader = false;

        return $this;
    }

    public function hideFooter(): self
    {
        $this->showFooter = false;

        return $this;
    }

    public function hidePreFooterContacts(): self
    {
        $this->showPreFooterContacts = false;

        return $this;
    }

    public function showFooterContacts(): self
    {
        $this->showFooterContacts = true;

        return $this;
    }

    public function setRulesOfUseLink(): self
    {
        $this->rulesOfUseLink = route('page.show', [
            'slug' => 'entertainer-soleil-rules-of-use',
        ]);

        return $this;
    }

    public function hideFooterDescription(): self
    {
        $this->showFooterDescription = false;

        return $this;
    }

    public function hideFooterSocials(): self
    {
        $this->showFooterSocials = false;

        return $this;
    }

    public function setWarningColor(string $warningColor): self
    {
        $this->warningColor = $warningColor;

        return $this;
    }

    public function getTermsAndConditionsUrl(): ?string
    {
        return $this->resolvePageUrl('termsAndConditionsUrl');
    }

    public function getPrivacyPolicyUrl(): ?string
    {
        return $this->resolvePageUrl('privacyPolicyUrl');
    }

    public function getExclusionPolicyUrl(): ?string
    {
        return $this->resolvePageUrl('exclusionPolicyUrl');
    }

    public function getFaqUrl(): ?string
    {
        return $this->resolvePageUrl('faqUrl');
    }

    public function getAboutUsUrl(): ?string
    {
        return $this->resolvePageUrl('aboutUsUrl');
    }

    protected function resolvePageUrl($classProperty): ?string
    {
        $property = $this->{$classProperty};

        return is_array($property) ? route(...$property) : $property;
    }

    public function metaTag(string $name): ?string
    {
        return trans("{$this->metaTagsFile}.{$name}");
    }

    public function setMetaTitle(string $title): self
    {
        $this->metaTitle = $title.$this->metaTitlePostfix;

        return $this;
    }

    public function setMetaDescription(string $description): self
    {
        $this->metaDescription = $description;

        return $this;
    }
}
