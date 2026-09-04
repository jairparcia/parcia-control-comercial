<?php

return [
    // UI actions
    'cancel'           => 'Cancel',
    'save_changes'     => 'Save changes',
    'saving'           => 'Saving…',
    'processing'       => 'Processing…',
    'import_stripe'    => 'Import from Stripe',
    'importing'        => 'Importing…',
    'more_details'     => 'More details',
    'filter_by'        => 'Filter by',
    'edit'             => 'Edit',
    'log_out'          => 'Log out',
    'collapse_sidebar' => 'Collapse sidebar',
    'back_to_portal'   => 'Back to portal',

    // Common fields
    'all_statuses'   => 'All statuses',
    'status'         => 'Status',
    'customer'       => 'Customer',
    'name'           => 'Name',
    'email'          => 'Email',
    'description'    => 'Description',
    'country'        => 'Country',
    'amount'         => 'Amount',
    'date'           => 'Date',
    'plan'           => 'Plan',
    'payment_method' => 'Payment method',
    'since'          => 'Since',
    'day'            => 'day',
    'days'           => 'days',
    'subscription'   => 'Subscription',
    'created'        => 'Created',
    'billing'        => 'Billing',
    'free'           => 'Free',

    // Subscription status labels
    'status_active'          => 'Active',
    'status_trialing'        => 'Trialing',
    'status_past_due'        => 'Past due',
    'status_canceled'        => 'Canceled',
    'status_incomplete'      => 'Incomplete',
    'status_expired'         => 'Expired',
    'status_unpaid'          => 'Unpaid',
    'status_paused'          => 'Paused',
    'status_no_plan'         => 'No active plan',
    'status_archived'        => 'Archived',
    'status_no_subscription' => 'No subscription',
    'status_inactive'        => 'Inactive',

    // Transaction status labels
    'tx_successful'         => 'Successful',
    'tx_pending'            => 'Pending',
    'tx_failed'             => 'Failed',
    'tx_refunded'           => 'Refunded',
    'tx_partially_refunded' => 'Partially refunded',

    // Invoice status labels
    'invoice_paid'  => 'Paid',
    'invoice_open'  => 'Open',
    'invoice_void'  => 'Void',
    'invoice_draft' => 'Draft',

    // CVC check labels
    'cvc_passed'      => 'Passed',
    'cvc_failed'      => 'Failed',
    'cvc_unavailable' => 'Unavailable',
    'cvc_unchecked'   => 'Unchecked',

    // Billing intervals
    'interval_monthly' => 'Monthly',
    'interval_annual'  => 'Annual',
    'interval_weekly'  => 'Weekly',
    'interval_daily'   => 'Daily',
    'billed_annually'  => 'Billed annually',
    'billed_monthly'   => 'Billed monthly',

    // Payment method types
    'pm_card'          => 'Card',
    'pm_bank_transfer' => 'Bank transfer',
    'card_credit'      => 'credit card',
    'card_debit'       => 'debit card',
    'card_prepaid'     => 'prepaid',
    'card_default'     => 'card',

    // Cancellation (shared between subscription and customer panels)
    'cancellation_timing'    => 'Cancellation timing',
    'cancel_immediately_btn' => 'Cancel immediately',
    'cancel_immediately_label' => 'Immediately',
    'sub_ends_now_desc'      => 'Subscription ends now. Customer loses access right away.',
    'cancel_at_period_end'   => 'At end of current period',
    'access_continues_until' => 'Access continues until :date.',
    'schedule_cancellation'  => 'Schedule cancellation',
    'keep_subscription'      => 'Keep subscription',
    'refund'                 => 'Refund',
    'no_refund'              => 'No refund',
    'no_refund_desc'         => 'No money is returned to the customer.',
    'last_payment_amount'    => 'Last payment — :amount',
    'refund_full_desc'       => 'Refund the full amount of the most recent invoice.',
    'prorated_amount'        => 'Prorated — :amount',
    'prorated_desc'          => 'Refund for :days unused :unit remaining in the period.',
];
