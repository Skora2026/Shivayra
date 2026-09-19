{{-- Premium pro-level action buttons list for Yajra DataTable --}}
<ul class="justify-content-start align-items-center gap-2 data-table-list list-group list-group-horizontal" style="list-style: none; margin: 0; padding: 0;">

    {{-- View Route --}}
    @if (!empty($viewRoute))
        <li class="p-0 bg-transparent border-0 data-table-list-item list-group-item">
            <a class="btn btn-sm btn-outline-success d-flex align-items-center justify-content-center rounded-circle" 
               href="{{ $viewRoute }}" 
               style="width: 32px; height: 32px; transition: all 0.2s;" 
               title="{{ config('button.view') }}" 
               data-bs-toggle="tooltip">
                <i class="fa-solid fa-eye fs-6"></i>
            </a>
        </li>
    @endif

    {{-- Invoice PDF --}}
    @if (!empty($invoice))
        @if ($emiStatus == config('constants.transaction_status_completed_name'))
            <li class="p-0 bg-transparent border-0 data-table-list-item list-group-item">
                <a class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-circle" 
                   href="{{ $invoice }}" 
                   target="_blank" 
                   style="width: 32px; height: 32px; transition: all 0.2s;" 
                   title="Invoice PDF" 
                   data-bs-toggle="tooltip">
                    <i class="fa-solid fa-file-pdf fs-6"></i>
                </a>
            </li>
        @endif
    @endif

    {{-- Paid Route --}}
    @if (!empty($paidRoute))
        @if ($commissionStatus == config('constants.commission_status_paid'))
            <li class="p-0 bg-transparent border-0 data-table-list-item list-group-item">
                <a class="btn btn-sm btn-outline-success d-flex align-items-center justify-content-center rounded-circle" 
                   href="{{ $paidRoute }}" 
                   style="width: 32px; height: 32px; transition: all 0.2s;" 
                   title="{{ config('button.view') }}" 
                   data-bs-toggle="tooltip">
                    <i class="fa-solid fa-circle-check fs-6"></i>
                </a>
            </li>
        @endif
    @endif

    {{-- Edit Route --}}
    @if (!empty($editRoute))
        @php
            $isDisabled =
                isset($emiStatus) &&
                in_array($emiStatus, [
                    config('constants.transaction_status_completed_name'),
                    config('constants.transaction_status_cancelled_name'),
                ]);
        @endphp

        <li class="p-0 bg-transparent border-0 data-table-list-item list-group-item">
            <a class="btn btn-sm d-flex align-items-center justify-content-center rounded-circle {{ $isDisabled ? 'btn-light disabled text-muted' : 'btn-outline-primary' }}"
               href="{{ $isDisabled ? 'javascript:void(0);' : $editRoute }}"
               style="width: 32px; height: 32px; transition: all 0.2s;" 
               title="{{ config('button.edit') }}" 
               data-bs-toggle="tooltip">
                <i class="fa-solid fa-pen-to-square fs-6" style="{{ $isDisabled ? 'display: none;' : '' }}"></i>
            </a>
        </li>
    @endif

    {{-- Document URL (PDF download) --}}
    @if (!empty($documentUrl))
        <li class="p-0 bg-transparent border-0 data-table-list-item list-group-item">
            <a href="{{ $documentUrl }}" 
               class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-circle" 
               target="_blank" 
               style="width: 32px; height: 32px; transition: all 0.2s;" 
               title="View PDF" 
               data-bs-toggle="tooltip">
                <i class="fa-solid fa-file-lines fs-6"></i>
            </a>
        </li>
    @endif

    {{-- Transaction Image --}}
    @if (!empty($transactionImage))
        <li class="p-0 bg-transparent border-0 data-table-list-item list-group-item">
            <a href="{{ $transactionImage }}" 
               class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center rounded-circle" 
               target="_blank" 
               style="width: 32px; height: 32px; transition: all 0.2s;" 
               title="View Transaction Card" 
               data-bs-toggle="tooltip">
                <i class="fa-solid fa-credit-card fs-6"></i>
            </a>
        </li>
    @endif

    {{-- Pay EMI Route --}}
    @if (!empty($payEmiRoute))
        <li class="p-0 bg-transparent border-0 data-table-list-item list-group-item">
            @if ($emiStatus === config('constants.transaction_status_completed_name'))
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill font-xs">
                    {{ config('button.paid') }}
                </span>
            @elseif($emiStatus === config('constants.transaction_status_cancelled_name'))
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill font-xs">
                    {{ config('button.cancelled') }}
                </span>
            @elseif($transaction->payment_type != config('constants.payment_type_token_value'))
                <a href="{{ $payEmiRoute }}" class="btn btn-sm btn-custom-primary px-3 py-1 rounded-pill font-xs">
                    {{ config('button.pay_now') }}
                </a>
            @endif
        </li>
    @endif

    {{-- Edit Modal Route --}}
    @if (!empty($editModalRoute))
        <li class="p-0 bg-transparent border-0 data-table-list-item list-group-item edit-btn"
            id="{{ $modalData['rowId'] }}" 
            data-bs-toggle="modal" 
            data-bs-target=".baseModal"
            modal-title="{{ $modalData['modalTitle'] }}" 
            edit-url="{{ $editModalRoute }}"
            table-id="{{ $modalData['tableId'] }}"
            form-url="{{ !empty($modalData['updateRoute']) ? route($modalData['updateRoute'], $modalData['rowId']) : '' }}">
            <a class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center rounded-circle" 
               href="javascript:void(0)" 
               style="width: 32px; height: 32px; transition: all 0.2s;" 
               title="{{ config('button.edit') }}" 
               data-bs-toggle="tooltip">
                <i class="fa-solid fa-pen-nib fs-6"></i>
            </a>
        </li>
    @endif

    {{-- Restore Route --}}
    @if (!empty($restoreRoute))
        <li class="p-0 bg-transparent border-0 data-table-list-item list-group-item">
            <form action="{{ $restoreRoute }}" method="POST" onsubmit="return confirm('Are you sure you want to restore?');">
                @csrf
                @method('PUT')
                <button type="submit" 
                        class="btn btn-sm btn-outline-warning d-flex align-items-center justify-content-center rounded-circle" 
                        style="width: 32px; height: 32px; transition: all 0.2s;" 
                        title="{{ config('button.restore') }}" 
                        data-bs-toggle="tooltip">
                    <i class="fa-solid fa-rotate-left fs-6"></i>
                </button>
            </form>
        </li>
    @endif

    {{-- Mobile Reminder Notification --}}
    @if (!empty($mobileNumber))
        @if ($sendNotification == config('constants.send_notification_active_value'))
            <li class="p-0 bg-transparent border-0 data-table-list-item list-group-item">
                <a href="#" data-mobile-number="{{ $mobileNumber }}"
                   class="sendReminder btn btn-sm btn-outline-info px-3 py-1 rounded-pill font-xs">
                    {{ config('button.send') }}
                </a>
            </li>
        @endif
    @endif

    {{-- Delete Route --}}
    @if (!empty($deleteRoute))
        <li class="p-0 bg-transparent border-0 data-table-list-item list-group-item">
            <form action="{{ $deleteRoute }}" method="POST" onsubmit="return confirm('Are you sure?');" class="deleteForm d-inline-block">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-circle" 
                        style="width: 32px; height: 32px; transition: all 0.2s;" 
                        title="{{ config('button.delete') }}" 
                        data-bs-toggle="tooltip">
                    <i class="fa-solid fa-trash fs-6"></i>
                </button>
            </form>
        </li>
    @endif

    {{-- Claim Expense Route --}}
    @if (!empty($claimExpenseRoute))
        <li class="p-0 bg-transparent border-0 data-table-list-item list-group-item">
            <a class="btn btn-sm btn-outline-success px-3 py-1 rounded-pill font-xs" href="{{ $claimExpenseRoute }}">
                {{ config('button.claim') }}
            </a>
        </li>
    @endif
</ul>
