<?php

[$duration, $durationUnits] = get_duration_data($calculated);
?>

<table style="border-spacing:0; border-collapse:collapse; width: 464px;" class="sales-quote-table">
  <tr class="header">
    <td>Corporate Client:</td>
    <td>{{ $salesQuote->corporate_client }}</td>
    <td colspan="2"></td>
  </tr>
  <tr class="header uppercase">
    <td colspan="4">Membership Package Quotation</td>
  </tr>
  <tr>
    <td colspan="2">Number of Clubs</td>
    <td colspan="2">{{ $salesQuote->clubs_count }}</td>
  </tr>
  <tr>
    <td colspan="2">Number of Single Memberships</td>
    <td colspan="2">{{ $salesQuote->singles_count }}</td>
  </tr>
  <tr>
    <td colspan="2">Number of Family Memberships</td>
    <td colspan="2">{{ $salesQuote->families_count }}</td>
  </tr>
  <tr>
    <td style="width: 132px" class="individual-membership-duration">Individual Membership Duration</td>
    <td style="width: 82px">{{ $duration }}</td>
    <td colspan="2">{{ $durationUnits }}</td>
  </tr>
  <tr>
    <td colspan="2" class="original-price">Original Price (per membership)</td>
    <td colspan="2" class="estimated-price">Estimated Price After Discount (per membership)</td>
  </tr>
  <tr>
    <td style="width: 132px">Single Price</td>
    <td style="width: 82px" class="original-single-price">AED {{ number_format($calculated['single_price'], 2) }}</td>
    <td style="width: 132px">Single Price</td>
    <td style="width: 82px" class="estimated-single-price">
      AED {{ number_format($calculated['single_discount'], 2) }}</td>
  </tr>
  <tr>
    <td>Family Price</td>
    <td>AED {{ number_format($calculated['family_price'], 2) }}</td>
    <td>Family Price</td>
    <td>AED {{ number_format($calculated['family_discount'], 2) }}</td>
  </tr>
</table>

<?php
if ($salesQuote->display_monthly_value) { ?>
<table style="border-spacing:0; border-collapse:collapse; width: 488px;" class="monthly-value">
  <tr class="header">
    <td colspan="3">
      <p class="uppercase">The value proposition*</p>
      <p class="small-text">(*For illustration purposes only, membership payment is on annual basis)</p>
    </td>
  </tr>
  <tr>
    <td>Membership type:</td>
    <td>Average monthly cost:</td>
    <td>Average cost per club per month</td>
  </tr>
  <tr>
    <td>Single</td>
    <td>AED {{ number_format($calculated['single_monthly'], 2) }}</td>
    <td>AED {{ number_format($calculated['single_monthly_club'], 2) }}</td>
  </tr>
  <tr>
    <td>Family Price</td>
    <td>AED {{ number_format($calculated['family_monthly'], 2) }}</td>
    <td>AED {{ number_format($calculated['family_monthly_club'], 2) }}</td>
  </tr>
</table>
    <?php
} ?>

<table style="border-spacing:0; border-collapse:collapse; width: 488px" class="membership-package-quotation">
  <tr class="header uppercase">
    <td colspan="2">Membership Package Quotation - Price Summary</td>
  </tr>
  <tr>
    <td>Price incl VAT (before discount)</td>
    <td>AED {{ number_format($calculated['total_price'], 2) }}</td>
  </tr>
  <tr>
    <td>Discount Value</td>
    <td>AED {{ $calculated['discount'] }}%</td>
  </tr>
  <tr class="bold">
    <td>Price incl VAT (after discount)</td>
    <td>AED {{ number_format($calculated['invoice'], 2) }}</td>
  </tr>
</table>

<?php
if ($displayCommission ?? false) { ?>
<table class="commission">
  <tr class="header uppercase">
    <td colspan="2">Commission</td>
  </tr>
  <tr data-html2canvas-ignore="true">
    <td>Commisson Rate</td>
    <td>{{ $calculated['commission_rate'] }}%</td>
  </tr>
  <tr data-html2canvas-ignore="true">
    <td>Commisson</td>
    <td>{{ number_format($calculated['commission'], 2) }}</td>
  </tr>
  <tr data-html2canvas-ignore="true">
    <td>Net to adv</td>
    <td>{{ number_format($calculated['net_adv'], 2) }}</td>
  </tr>
</table>
    <?php
} ?>

<style>
    table {
        border-spacing: 0;
        border-collapse: collapse;
        width: 100%;
    }

    td {
        border: 1px solid #7A7A7A;
        background-color: #FFF;
        padding-top: 3px;
        padding-bottom: 3px;
    }

    tr.header td {
        border: none;
        padding-left: 0;
    }

    tr.header p {
        margin: 4px 0;
    }

    .uppercase {
        text-transform: uppercase;
    }

    .bold {
        font-weight: bold;
    }

    .small-text {
        font-size: 10px;
    }
</style>
