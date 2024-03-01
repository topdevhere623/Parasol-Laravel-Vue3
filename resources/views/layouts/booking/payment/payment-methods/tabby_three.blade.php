<x-tabby-card name="tabby_three"
              payments-count="3"
              :total-price="$booking->total_price"
              :is-available="$paymentMethod->is_available"
/>
