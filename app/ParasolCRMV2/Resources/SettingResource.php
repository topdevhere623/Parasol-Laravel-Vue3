<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Package;
use App\Models\Setting;
use ParasolCRMV2\Containers\TabElement;
use ParasolCRMV2\Containers\VerticalTab;
use ParasolCRMV2\Fields\KeyValRepeaterJSON;
use ParasolCRMV2\Fields\RepeaterJSON;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\ResourceScheme;

class SettingResource extends ResourceScheme
{
    public static $model = Setting::class;

    public function fields(): array
    {
        $packages = Package::getSelectable();

        return [
            RepeaterJSON::make('referral_reward_admin_emails', 'Reward admin emails list'),
            // HSBC
            Select::make('hsbc_free_checkout_package_id', 'HSBC Free checkout package')
                ->nullable()
                ->options($packages),
            Select::make('hsbc_paid_checkout_package_id', 'HSBC Paid checkout package')
                ->nullable()
                ->options($packages),

            Text::make('blogs_heading'),
            Text::make('blogs_meta_title'),
            Text::make('blogs_meta_description'),
            Text::make('blogs_banner_link'),

            KeyValRepeaterJSON::make('links', 'Links'),

            Text::make('zoho_organization_id'),
            Text::make('zoho_account_deposit_id'),
            Text::make('zoho_template_id'),
            Text::make('zoho_tax_id'),
            Text::make('zoho_membership_item_id'),
            Text::make('zoho_currency_id'),
        ];
    }

    public function layout(): array
    {
        return [
            VerticalTab::make('Settings')->attach([
                TabElement::make('Misc')->attach([
                    'links',
                ]),
                TabElement::make('HSBC')->attach([
                    'hsbc_free_checkout_package_id',
                    'hsbc_paid_checkout_package_id',
                ]),
                TabElement::make('Referrals')->attach([
                    'referral_reward_admin_emails',
                ]),
                TabElement::make('Blog')->attach([
                    'blogs_heading',
                    'blogs_meta_title',
                    'blogs_meta_description',
                    'blogs_banner_link',
                    'blogs_banner_picture',
                ]),
                TabElement::make('Zoho')->attach([
                    'zoho_organization_id',
                    'zoho_account_deposit_id',
                    'zoho_template_id',
                    'zoho_tax_id',
                    'zoho_membership_item_id',
                    'zoho_currency_id',
                ]),
            ]),
        ];
    }
}
