@extends('layouts/contentNavbarLayout')

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
    integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
@section('title', 'List | HOUSE FIX - A DOCTOR FOR YOUR HOUSE')
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
</style>
@section('content')

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
    <div style="margin-top: 30px;">
        <h4 class="fw-bold py-3 mb-4" style="margin-top:-49px;font-size:16px;color:black">
            <span class="fw-light">Category
        </h4>
        <div class="row" style="position:absolute; top:90px; right:50px ">
            <div class="col-md-12">
                @can('category-create')
                    <ul class="nav nav-pills flex-column flex-md-row mb-3">
                        <li class="nav-item"><a class="nav-link active" href="{{ route('category-create') }}"><i
                                    class="bx bxs-category me-1"></i> Add Category</a></li>

                    </ul>
                @endcan
            </div>
        </div>
    </div>
    <!-- Basic Bootstrap Table -->
    <div class="card" style="max-width: 1200px; margin: 46px auto;top:-32px;">
        <!-- <h5 class="card-header">Table Basic</h5> -->
        <div class="table-responsive text-nowrap" style="width: 99%;">
            <table class="table" id="category_listing_table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        @canany(['category-edit', 'category-delete'])
                            <th>Action</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    <?php //print_r($categorys); exit;
                    ?>

                    @foreach ($categorys as $category)
                        <tr>
                            <td>{{ $loop->index + 1 }}</td>
                            <td>{{ $category->name }}</td>
                            @canany(['category-edit', 'category-delete'])
                                <td>
                                  @if($category->name != "salary")
                                    @can(['category-edit'])
                                        <a class="" href="{{ route('category-edit', $category->id) }}"><i class="fa fa-edit"
                                                style="font-size:24px"></i></a>
                                    @endcan
                                    @can('category-delete')
                                        @if (!in_array($category->id, $categorynot))
                                            <a data-toggle="modal" href="javascript:void(0)" data-id="{{ $category->id }}"
                                                class="deleteCategory"><i class="fa fa-trash-o"
                                                    style="font-size:24px; color:red"></i> </a><br />
                                        @endif
                                    @endcan
                                    @endif
                                </td>
                            @endcanany
                        </tr>
                    @endforeach

                </tbody>
            </table>

        </div>
    </div>

    <!--/ Basic Bootstrap Table -->



    <!--- modal popup for delete role started--->



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


    <!-- modal popup for delete role ended -->

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            var data = new DataTable('#category_listing_table', {
                "lengthMenu": [15, 50, 100],
                processing: true,


            });
        });
        $("document").ready(function() {
            var categoryid;
            setTimeout(function() {
                $("div.alert").remove();
            }, 5000); // 5 secs
            $('#category_listing_table').on('click', '.deleteCategory', function() {

                categoryid = $(this).attr('data-id');
                $("#myModal").removeClass('fade');
                $("#myModal").modal('show');
            });
            $('.no-delete').click(function() {
                $("#myModal").addClass('fade');
                $("#myModal").modal('hide');
            });
            $('.yes-delete').click(function() {
                console.log('categoryid', categoryid);
                $("#myModal").modal('hide');
                var url = '{{ route('category-delete', ':id') }}';
                url1 = url.replace(':id', categoryid);
                window.location.href = url1;
            });
        });
    </script>

@endsection
