@extends('layouts/contentNavbarLayout')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
    integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
    integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>


<style>
    @media only screen and (max-width:320px) {
        .aa {
            display: inline !important;
        }
    }

    .dataTables_wrapper {
        font-family: tahoma;
        font-size: 13px;
        clear: both;

    }

    .dataTables_length select {
        width: 120px;
    }

    .table-responsive {
        margin-top: 5px;
        margin-left: 5px;
    }

    .dropdown-toggle {
        width: 146px !important;
    }

    .bs-caret::after {
        color: #f7f7f7 !important;
        content: "";
        display: none !important;
    }

    table {
        width: 50%;
        border-spacing: 0;
        /* Remove spacing between cells */
        border-collapse: collapse;
        /* Collapse cell borders */
    }

    td,
    th {
        padding: 5px;
        /* Reduce cell padding */
    }
    @media (max-width: 768px) {

#filter-section .col-md-3,
#filter-section .col-md-2,
#filter-section .col-md-1 {
    margin-bottom: 10px;
}
}

#filter-section {
padding: 5px !important;
}
</style>
@section('title', 'Expenses | HOUSE FIX - A DOCTOR FOR YOUR HOUSE')

@section('content')

    @if (session()->has('expenses-popup'))
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"
            integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

        <script>
            $(function() {
                $('.success-msg').text('Expenses Updated Successfully')
                $('#walletsuccess').removeClass('fade');
                $('#walletsuccess').modal('show');
            });
        </script>
    @endif
    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session()->get('message') }}
        </div>
    @endif
    @if (session()->has('msg'))
        <div class="alert alert-danger">
            {{ session()->get('msg') }}
        </div>
    @endif
    <div class="card" style="top:-12px;">
        {{-- <div class="card-header">
            <div class="container text-center">
              <div style="float: right">
                <a href="{{ route('expenses-delete_record') }}"><img
                        src="{{ asset('assets/img/icons/clearfilter.png') }}"  style="height: 25px;width:25px;" alt="slack" class="me-3"
                        height="40" width="40"></a>
           <!-- Reduce the column size from 1 to 2 -->
                <button type="button" class="btn btn-light" id="expense-export"
                    ><img src="{{ asset('assets/img/icons/excel.png') }}" style="height: 25px;width:25px;" alt=""> </button>
            <!-- Reduce the column size from 1 to 2 -->
                <button type="button" class="btn btn-light" id="expense-pdf"
                    ><img src="{{ asset('assets/img/icons/file.png') }}"  style="height: 25px;width:25px;" alt=""></button>
            </div>
                <div class="row aa">



                    <div class="col-md-2">
                        <select class="form-group selectpicker" name="category_id" id="category_id" data-live-search="true">
                            <option value="">Select category</option>
                            @foreach ($category as $category)
                                <option
                                    value="{{ $category->id }}"{{ $category->id == $category_filter ? 'selected' : '' }}>
                                    {{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                      <select class="form-group selectpicker" name="project_id" id="project_id"
                            data-live-search="true">
                            <option value="">Select Project</option>
                            @foreach ($project as $project)
                                <option value="{{ $project->id }}"{{ $project->id == $project_filter ? 'selected' : '' }}>
                                    {{ $project->name }}</option>
                            @endforeach
                        </select></div>
                    @role('Admin') <div class="col-md-2"><select class="form-group selectpicker" name="user_id"
                                id="user_id" data-live-search="true">
                                <option value="">Select Member</option>
                                @foreach ($user as $user)
                                    <option value="{{ $user->id }}"{{ $user->id == $user_filter ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }} -
                                        {{ $user->name }}</option>
                                @endforeach
                        </select></div> @endrole
                    <div class="col-md-3"> <!-- Reduce the column size from 1 to 2 -->
                        <span> <label>From:&nbsp;</label>
                            <input type="date" class="form-control bb" id="from_date" name="from_date"
                                value="{{ $from_date }}" style="width: 144px;display:initial;"></span>
                    </div>
                    <div class="col-md-3"> <!-- Reduce the column size from 1 to 2 -->
                        <label>To</label>
                        <input type="date" class="form-control" id="to_date" name="to_date"
                            value="{{ $to_date1 }}" style="width: 144px;display:initial;">
                    </div>

                </div>
            </div>
        </div> --}}
        <form id="submit-form">
            <div id="filter-section">
                <!-- First Row: Filters + Search -->
                <div class="row g-3 align-items-end">
                    <div class="col-md-1">
                        <label for="entries">Entities</label>
                        <select id="entries" name="paginate" class="form-control" onchange="submitform()">
                            <option value="10" {{ request('paginate') == '10' ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('paginate') == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('paginate') == '50' ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('paginate') == '100' ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id" class="glass-select2 form-control ">
                            <option value="">Select Category</option>
                            @foreach ($category as $category)
                                <option value="{{ $category->id }}"
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-md-2">
                        <label for="project_id">Project</label>
                        <select id="project_id" name="project_id" class="form-control">
                            <option value="">Select Project</option>
                            @foreach ($project as $project)
                                <option value="{{ $project->id }}"
                                    {{ request('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @role('Admin')
                        <div class="col-md-2">
                            <label for="user_id">Member</label>
                            <select id="user_id" name="user_id" class="form-control">
                                <option value="">Select Member</option>
                                @foreach ($user as $member)
                                    <option value="{{ $member->id }}"
                                        {{ request('user_id') == $member->id ? 'selected' : '' }}>{{ $member->first_name }}
                                        {{ $member->last_name }} - {{ $member->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endrole

                    <div class="col-md-3">
                        <label for="date_range">Date Range</label>
                        <input type="text" id="date_range" name="date_range" class="form-control"
                            value="{{ request('date_range') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Search">
                    </div>
                </div>

                <!-- Second Row: Buttons -->
                <div class="row g-2 mt-3">
                    <div class="col-md-12 text-end">
                        <div class="d-flex justify-content-end flex-wrap gap-2">
                            <button class="btn btn-primary client_search" type="submit">
                                <i class="bx bx-search"></i>
                            </button>
                            <a class="btn btn-danger" href="{{ route('expenses-delete_record') }}">
                                <i class="bx bx-x-circle"></i>
                            </a>
                            <button type="button" class="btn btn-success" id="expense-export">
                                <i class="bi bi-file-earmark-excel-fill"></i>
                            </button>
                            <button type="button" class="btn btn-danger" id="expense-pdf">
                                <i class="bi bi-file-pdf"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- Basic Bootstrap Table -->
    <div class="card" style="max-width: 100%; top:-1px; height:547px">
        <!-- <h5 class="card-header">Table Basic</h5> -->
        <div class="table-responsive text-nowrap" style="width: 99%;">
            <table class="table" id="expenses_listing_table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Paid date</th>
                        <th>Category <br /> Name</th>
                        <th>Project Name</th>
                        <th>Reason</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Unpaid</th>
                        <th>Advanced <br /> Amount</th>
                        <th>Description</th>
                        <th>Image</th>
                        <th>Payment Mode</th>


                        @role('Admin')
                            <th>Added By</th>

                            <th>Edited By</th>
                        @endrole

                        <th>Deleted date</th>

                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @if(count($expenses) > 0)
                    @foreach ($expenses as $expense)
                        <tr>
                            <td>{{ $loop->index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($expense->current_date)->format('d-m-Y') }}
                                <br />{{ \Carbon\Carbon::parse($expense->current_date)->format('h:i A') }} </td>

                            <td>{{ $expense->category_name ? $expense->category_name : '--' }}</td>
                            <td>{{ $expense->project_name ? $expense->project_name : '--' }}</td>
                            <td>{{ $expense->reason }}</td>
                            <td><b><span style="color:#ef6a0e">{{ $expense->amount }}</span></b></td>
                            <td><b><span style="color: green;">{{ $expense->paid_amt }}</span></b></td>
                            <td>
                                @if ($expense->unpaid_amt != 0)
                                    <b><a style="color:red;text-decorative:none">{{ $expense->unpaid_amt }}</a></b>
                                @else<b>
                                        <p style="color:red">{{ $expense->unpaid_amt }}</p>
                                    </b>
                                @endif
                                </a>
                            <td><b><span style="color:#840eef;">{{ $expense->extra_amt }}</span></b></td>
                            <td>{{ $expense->description ? $expense->description : '--' }}</td>
                            <td>
                                @if ($expense->image != '' || $expense->image != null)
                                    <img src="/public/images/{{ $expense->image }}" width="50px">
                                @endif
                            </td>
                            <td>{{ $expense->payment_name }}</td>


                            @role('Admin')
                                <td>{{ $expense->first . '' . $expense->last }}</td>
                                <td>{{ $expense->first_name . '' . $expense->last_name }}</td>
                            @endrole

                            <td>{{ $expense->deleted_at }}</td>

                        </tr>
                    @endforeach
                    @else
                    <tr><td colspan='10'><center>No data found.</center></td></tr>
                    @endif
                </tbody>
            </table>
            <div class="paginatestyle mt-4">
                {{ $expenses->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!--/ Basic Bootstrap Table -->


    <p class="text-end" style="margin-top: 18px; margin-right: 147px; font-size: medium;">
        <span class="d-inline"><b>Total Amount:</b> <b><span style="color:#ef6a0e">{{ $sum }}</span></b></span>
        <span class="d-inline ms-3"><b>Total Paid Amount:</b> <b><span
                    style="color: green;">{{ $paid_amt }}</span></b></span>
        <span class="d-inline ms-3"><b>Total Unpaid Amount:</b> <b><span
                    style="color: red;">{{ $unpaid_amt }}</span></b></span>
        <span class="d-inline ms-3"><b>Total Advanced Amount:</b><b><span style="color: #840eef;">
                    {{ $advanced_amt }}</span></b></span>
    </p>

    <!--- modal popup for transfer -->
    <div class="modal fade" id="unpaid-popup" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog d-flex justify-content-center">
            <div class="modal-content w-75">
                <div class="modal-header">
                    <h5 class="modal-title" id="unpaid_title">Unpaid Details</h5>
                    <button type="button" class="btn-close unpaid-close" data-mdb-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="unpaidform"></div>
                </div>
            </div>
        </div>
    </div>
    <!--- modal popup for transfer -->

    <!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog modal-sm">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Confirmation</h4>

                </div>
                <div class="modal-body">
                    <p style="text-align: center;">Are you sure want to delete this?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary yes-delete" data-dismiss="modal">Yes</button>
                    <button type="button" class="btn btn-danger no-delete" data-dismiss="modal">No</button>
                </div>
            </div>

        </div>
    </div>
    <div class="modal fade" id="myModal_reason" role="dialog">
        <div class="modal-dialog modal-sm">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Reason</h4>
                    <hr>
                </div>
                <div class="modal-body">
                    <input type="text" id="reason" name="reason" class="form-control" placeholder="Enter reason"
                        value="" />
                    <label id="reason-error" class="error" style="color:red" for="basic-default-email">Reason is
                        required</label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary yes-reason" data-dismiss="modal">Submit</button>
                    <button type="button" class="btn btn-danger no-reason" data-dismiss="modal">cancel</button>
                </div>
            </div>

        </div>
    </div>

    <!-- modal popup for delete role ended -->

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        function submitform() {
            $('#submit-form').submit();
        }
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
    $('#category_id').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%',
    });
    $('#project_id').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%',
    });
    $('#user_id').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%',
    });
 
        $("document").ready(function() {
            var roleid;
            $('.error').addClass('hide');
            setTimeout(function() {
                $("div.alert").remove();
            }, 5000); // 5 secs
            $("#expenses_listing_table").on("click", ".deleteExpense", function() {
                roleid = $(this).attr('data-id');
                $("#myModal").removeClass('fade');
                $("#myModal").modal('show');
            });
            $('.no-delete').click(function() {
                $("#myModal").addClass('fade');
                $("#myModal").modal('hide');
            });
            $('.no-reason').click(function() {
                $("#myModal_reason").addClass('fade');
                $("#myModal_reason").modal('hide');
            });
            $('.yes-delete').click(function() {
                console.log('roleid', roleid);
                $('#myModal').modal('hide');
                $('#myModal_reason').modal('show');
                // $("#myModal").modal('hide');
                // var url = '{{ route('expenses-delete', ':id') }}';
                //       url1 = url.replace(':id', roleid);
                //       window.location.href=url1;
            });
            $('.yes-reason').click(function() {
                console.log('roleid', roleid);
                var reason = $('#reason').val();
                console.log('reason', reason);
                if (reason == '') {
                    $('#reason-error').removeClass('hide');
                } else {
                    $('#reason-error').addClass('hide');
                    $("#myModal_reason").modal('hide');
                    var url = '{{ route('expenses-delete') }}';

                    window.location.href = url + '?id=' + roleid + '&reason=' + reason;
                }
            });
        });
  
       // });
        $('#expense-export').click(function() {
            console.log('test');
            var user = $('#user_id').find(":selected").val();
            var project = $('#project_id').find(":selected").val();
            var category = $('#category_id').find(":selected").val();

            var date_range = $('#date_range').val();
            var search = $('#search').val();
            var url = "{{ route('deleteexpenses-export') }}";
            window.location.href = url + '?date_range=' + date_range + '&search=' + search + '&category_id=' +
                category + '&project_id=' + project + '&user_id=' + user;
        });
        $('#expense-pdf').click(function() {
            console.log('test1');
            var user = $('#user_id').find(":selected").val();
            var project = $('#project_id').find(":selected").val();
            var category = $('#category_id').find(":selected").val();

            var date_range = $('#date_range').val();
            var search = $('#search').val();
            var url = "{{ route('deleteexpenses-pdf') }}";
            window.location.href = url + '?date_range=' + date_range + '&search=' + search + '&category_id=' +
                category + '&project_id=' + project + '&user_id=' + user;
        });
    </script>
@endsection
