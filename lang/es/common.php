<?php

return [
    // UI actions
    'cancel'           => 'Cancelar',
    'save_changes'     => 'Guardar cambios',
    'saving'           => 'Guardando…',
    'processing'       => 'Procesando…',
    'import_stripe'    => 'Importar de Stripe',
    'importing'        => 'Importando…',
    'more_details'     => 'Más detalles',
    'filter_by'        => 'Filtrar por',
    'edit'             => 'Editar',
    'log_out'          => 'Cerrar sesión',
    'collapse_sidebar' => 'Colapsar barra lateral',
    'back_to_portal'   => 'Volver al portal',

    // Common fields
    'all_statuses'   => 'Todos los estados',
    'status'         => 'Estado',
    'customer'       => 'Cliente',
    'name'           => 'Nombre',
    'email'          => 'Correo',
    'description'    => 'Descripción',
    'country'        => 'País',
    'amount'         => 'Monto',
    'date'           => 'Fecha',
    'plan'           => 'Plan',
    'payment_method' => 'Método de pago',
    'since'          => 'Desde',
    'day'            => 'día',
    'days'           => 'días',
    'subscription'   => 'Suscripción',
    'created'        => 'Creado',
    'billing'        => 'Facturación',
    'free'           => 'Gratuito',

    // Subscription status labels
    'status_active'          => 'Activo',
    'status_trialing'        => 'En prueba',
    'status_past_due'        => 'Vencido',
    'status_canceled'        => 'Cancelado',
    'status_incomplete'      => 'Incompleto',
    'status_expired'         => 'Expirado',
    'status_unpaid'          => 'Sin pagar',
    'status_paused'          => 'Pausado',
    'status_no_plan'         => 'Sin plan activo',
    'status_archived'        => 'Archivado',
    'status_no_subscription' => 'Sin suscripción',
    'status_inactive'        => 'Inactivo',

    // Transaction status labels
    'tx_successful'         => 'Exitoso',
    'tx_pending'            => 'Pendiente',
    'tx_failed'             => 'Fallido',
    'tx_refunded'           => 'Reembolsado',
    'tx_partially_refunded' => 'Parcialmente reembolsado',

    // Invoice status labels
    'invoice_paid'  => 'Pagada',
    'invoice_open'  => 'Abierta',
    'invoice_void'  => 'Anulada',
    'invoice_draft' => 'Borrador',

    // CVC check labels
    'cvc_passed'      => 'Superada',
    'cvc_failed'      => 'Fallida',
    'cvc_unavailable' => 'No disponible',
    'cvc_unchecked'   => 'No verificado',

    // Billing intervals
    'interval_monthly' => 'Mensual',
    'interval_annual'  => 'Anual',
    'interval_weekly'  => 'Semanal',
    'interval_daily'   => 'Diario',
    'billed_annually'  => 'Facturado anualmente',
    'billed_monthly'   => 'Facturado mensualmente',

    // Payment method types
    'pm_card'          => 'Tarjeta',
    'pm_bank_transfer' => 'Transferencia bancaria',
    'card_credit'      => 'tarjeta de crédito',
    'card_debit'       => 'tarjeta de débito',
    'card_prepaid'     => 'prepago',
    'card_default'     => 'tarjeta',

    // Cancellation (shared between subscription and customer panels)
    'cancellation_timing'      => 'Momento de cancelación',
    'cancel_immediately_btn'   => 'Cancelar inmediatamente',
    'cancel_immediately_label' => 'Inmediatamente',
    'sub_ends_now_desc'        => 'La suscripción termina ahora. El cliente pierde acceso de inmediato.',
    'cancel_at_period_end'     => 'Al final del período actual',
    'access_continues_until'   => 'El acceso continúa hasta :date.',
    'schedule_cancellation'    => 'Programar cancelación',
    'keep_subscription'        => 'Mantener suscripción',
    'refund'                   => 'Reembolso',
    'no_refund'                => 'Sin reembolso',
    'no_refund_desc'           => 'No se devuelve dinero al cliente.',
    'last_payment_amount'      => 'Último pago — :amount',
    'refund_full_desc'         => 'Reembolsar el monto total de la factura más reciente.',
    'prorated_amount'          => 'Prorrateado — :amount',
    'prorated_desc'            => 'Reembolso por :days :unit sin usar en el período.',
];
