<div class="card">
  <div class="card-body">
    <div class="container-fluid flex flex-column justify-content-between flex-gap-20">
      @include('sales-quote.table', compact('salesQuote', 'calculated'))
    </div>
  </div>
</div>

<style>
    .card {
        position: relative;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-direction: column;
        flex-direction: column;
        min-width: 0;
        word-wrap: break-word;
        background-color: #fff;
        background-clip: border-box;
        border: 1px solid rgba(24, 28, 33, 0.06);
        border-radius: 0.25rem;
        margin-bottom: 20px;
    }

    .card-body {
        -ms-flex: 1 1 auto;
        flex: 1 1 auto;
        padding: 1.5rem;
    }

    .card-body .sales-quote-table {
        width: 720px !important;
    }

    .card-body .individual-membership-duration {
        width: 300px !important;
    }

    .card-body .original-price {
        width: 360px !important;
    }

    .card-body .estimated-price {
        width: 360px !important;
    }

    .card-body .original-single-price {
        width: 108px !important;
    }

    .card-body .estimated-single-price {
        width: 108px !important;
    }

    .card-body .commission {
        width: 300px !important;
    }

    .container-fluid {
        padding: 1rem;
    }

    @media (min-width: 992px) {
        .container-fluid {
            padding-right: 2rem;
            padding-left: 2rem;
        }
    }

    .container-fluid {
        width: 100%;
        padding-right: 0.75rem;
        padding-left: 0.75rem;
        margin-right: auto;
        margin-left: auto;
    }

    td {
        padding: 6px 10px;
        font-size: 0.83125rem;
        line-height: 1.54;
    }

    tr.header td {
        font-size: 18px;
    }

    .flex {
        display: flex;
    }

    .flex-gap-20 {
        gap: 20px;
    }

    .flex-column {
        flex-direction: column;
    }

    .justify-content-between {
        justify-content: space-between;
    }
</style>
