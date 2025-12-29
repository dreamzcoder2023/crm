@extends('layouts/contentNavbarLayout')

@section('title', 'Report | HOUSE FIX - A DOCTOR FOR YOUR HOUSE')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<!-- Bootstrap 4 (ONLY ONE VERSION) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">

<style>
    /* ---------- GENERAL FIXES ---------- */
    body {
        font-size: 13px;
    }

    .card {
        padding: 12px;
    }



    .dropdown-toggle {
        width: 100% !important;
    }

    /* ---------- FILTER SECTION ---------- */
    #filter-section label {
        font-weight: 600;
        font-size: 12px;
        margin-bottom: 4px;
    }

    #filter-section .form-control {
        height: 38px;
        font-size: 13px;
    }

    /* ---------- TABS ---------- */
    .tab-container {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .tab-buttons {
        display: flex;
        border-bottom: 1px solid #ddd;
    }

    .tab-button {
        flex: 1;
        padding: 12px;
        text-align: center;
        color: #555;
        text-decoration: none;
        font-weight: 500;
    }

    .tab-button.active,
    .tab-button:hover {
        background: #171f29;
        color: #fff;
    }

    /* ---------- MOBILE ---------- */
    @media (max-width: 768px) {
        .tab-buttons {
            flex-direction: column;
        }

        .d-flex.justify-content-end {
            justify-content: center !important;
        }
    }

    select,
    ::picker(select) {
        appearance: base-select !important;
        width: 200px;
        overflow: hidden;
    }

    ::picker(select) {
        border: 0;
        margin: .4rem 0;
        box-shadow: 0 0 5px rgba(0, 0, 0, .15);
    }

    option {
        font-size: 14px;
        padding: 12px;
    }

    .table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .table td,
    .table th {
        padding: 0.6rem;
        font-size: 13px;
    }
</style>
@section('content')

    <h6 class="fw-bold mb-3">
        <span class="text-muted">Over All Income & Expenses</span>
    </h6>

    <div class="card mb-3">
        <form id="submit-form">
            <div id="filter-section">
                <div class="row g-2">

                    <div class="col-md-1 col-sm-6 col-12">
                        <label>Entries</label>
                        <select class="form-control" name="paginate">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    <div class="col-md-2 col-sm-6 col-12">
                        <label>Transaction Type</label>
                        <select class="form-control" name="transaction_type">
                            <option value="">Select Type</option>
                            <option value="1" {{ request()->transaction_type == 1 ? 'selected' : '' }}>Income</option>
                            <option value="2" {{ request()->transaction_type == 2 ? 'selected' : '' }}>Expenses
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2 col-sm-6 col-12">
                        <label>Project</label>
                        <select class="form-control" name="project_id">
                            <option value="">Select Project</option>
                        </select>
                    </div>

                    <div class="col-md-2 col-sm-6 col-12">
                        <label>Expense Type</label>
                        <select class="form-control" id="expenses_type">
                            <option value="">Select</option>
                            <option value="1">Expenses</option>
                            <option value="2">Labour</option>
                            <option value="3">Vendor</option>
                        </select>
                    </div>

                    <div class="col-md-2 col-sm-6 col-12">
                        <label>Main Category</label>
                        <select class="form-control" id="main_category_id">
                            <option value="">Select</option>
                        </select>
                    </div>

                    <div class="col-md-2 col-sm-6 col-12">
                        <label>Category</label>
                        <select class="form-control" id="category_id"></select>
                    </div>

                    <div class="col-md-3 col-sm-6 col-12">
                        <label>Date Range</label>
                        <input type="text" name="date_range" id="date_range" class="form-control">
                    </div>

                    <div class="col-md-3 col-sm-6 col-12">
                        <label>Search</label>
                        <input type="text" class="form-control" placeholder="Search">
                    </div>

                    <div class="col-12 d-flex justify-content-end mt-2">
                        <button class="btn btn-primary mr-2">
                            <i class="bx bx-search"></i>
                        </button>
                        <button type="button" class="btn btn-success mr-2">
                            <i class="bi bi-file-earmark-excel-fill"></i>
                        </button>
                        <button type="button" class="btn btn-danger">
                            <i class="bi bi-file-pdf"></i>
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>
    <div class="tab-container">
        <div class="tab-buttons">
            <a href="#" class="tab-button active">Income</a>
            <a href="#" class="tab-button">Expenses</a>
        </div>
        <div class="card shadow-sm">
            <div class="card-body p-0">

                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width:50px">#</th>
                                <th>Client Name</th>
                                <th>Project Name</th>
                                <th class="text-end">Received Amount</th>
                                <th class="text-end">Total Amount</th>
                                <th>Payment Mode</th>
                                <th>Stage</th>
                                <th>Received Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($clients as $client)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        {{ $client->first_name }} {{ $client->last_name }}
                                    </td>

                                    <td>
                                        {{ $client->name }}
                                    </td>

                                    <td>
                                        ₹ {{ number_format($client->amount, 2) }}
                                    </td>

                                    <td>
                                        ₹ {{ number_format($client->total_amt, 2) }}
                                    </td>

                                    <td>

                                        {{ $client->payment }}

                                    </td>

                                    <td class="text-muted">
                                        {{ $client->stage_name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($client->currentdate)->format('d M Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No data found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <div class="paginatestyle mt-4">
            {{ $clients->links('pagination::bootstrap-5') }}
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $('#category_id').select2({
            width: '100%'
        });

        $(function() {
            $('input[name="date_range"]').daterangepicker({
                autoUpdateInput: false, // don't set default value
                opens: 'left',
                locale: {
                    cancelLabel: 'Clear'
                }
            });

            $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format(
                    'MM/DD/YYYY'));
            });

            $('input[name="date_range"]').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        });
    </script>
@endsection
