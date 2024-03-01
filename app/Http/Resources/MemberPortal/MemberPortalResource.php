<?php

namespace App\Http\Resources\MemberPortal;

use App\Traits\DynamicImageResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member\Member */
class MemberPortalResource extends JsonResource
{
    use DynamicImageResourceTrait;

    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $program = $this->program;

        return [
            // 'uuid' => $this->uuid,
            //            'name' => $this->name,
            //            'prefix' => $this->prefix,
            //            'source' => $this->source,

            'logo' => $this->when(
                $program->member_portal_logo,
                file_url($program, 'member_portal_logo', 'original'),
                asset('assets/images/logo_adv-stacker.svg')
            )
            ,
            'primary_color' => $program->member_portal_main_color,

            'passkit_faq_url_ios' => $program->passkit_faq_url_ios,
            'passkit_faq_url_android' => $program->passkit_faq_url_android,
            'passkit_button_on_top' => $this->when($program->passkit_button_on_top, $program->passkit_button_on_top),
            'has_access_clubs' => $this->when($program->has_access_clubs, $program->has_access_clubs),
            'has_access_about_membership' => $this->when(
                $program->has_access_about_membership,
                $program->has_access_about_membership
            ),
            'has_access_profile' => $this->when($program->has_access_profile, $program->has_access_profile),
            'has_access_referrals' => $this->when($this->hasAccess('referrals'), true),
            'has_access_offers' => $this->when($program->has_access_offers, $program->has_access_offers),
            'has_access_visiting_family_membership' => $this->when(
                $program->has_access_visiting_family_membership,
                $program->has_access_visiting_family_membership
            ),
            'has_access_password_change' => $this->when(
                $program->has_access_password_change,
                $program->has_access_password_change
            ),
            'has_access_contact_us' => $this->when(
                $program->has_access_contact_us,
                $program->has_access_contact_us
            ),
            'has_access_all_clubs' => $this->when(
                $program->has_access_all_clubs,
                $program->has_access_all_clubs
            ),
            'contact_us_page' => $this->when($program->has_access_contact_us, $program->contact_us_page),

            'referrals_page' => $this->when($this->hasAccess('referrals'), $program->referrals_page),
            'referrals_page_img' => $this->when(
                $this->hasAccess('referrals'),
                $program->referrals_page_img ? file_url(
                    $program,
                    'referrals_page_img',
                    'original'
                ) : null
            ),

            'terms_and_conditions_url' => $this->when(
                $program->has_access_about_membership,
                $program->getTermsAndConditionsUrl()
            ),
            'faq_page_url' => $this->when($program->has_access_about_membership, $program->getFaqPageUrl()),
            'club_guide_url' => $this->when($program->has_access_about_membership, $program->getClubGuideUrl()),
            'whatsapp_url' => $program->getWhatsappUrl(),

        ];
    }
}
