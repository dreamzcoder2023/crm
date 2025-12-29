@extends('layouts/contentNavbarLayout')

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
    integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<style>
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

    .expense-card {
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .expense-card:hover {
        transform: scale(1.03);
    }

    .expense-card img {
        height: 150px;
        object-fit: cover;
    }
</style>

@section('title', 'List | HOUSE FIX - A DOCTOR FOR YOUR HOUSE')

@section('content')

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    @if (session()->has('message'))
        <script>
            $(function() {
                toastr.success('{{ session('message') }}', {
                    timeOut: 1000,
                    fadeOut: 1000,
                });
            });
        </script>
    @endif
    @if (session()->has('msg'))
        <script>
            $(function() {
                toastr.error('{{ session('msg') }}', {
                    timeOut: 1000,
                    fadeOut: 1000,
                });
            });
        </script>
    @endif
    <div style="margin-top: 30px;">
        <h4 class="fw-bold py-3 mb-4" style="margin-top:-49px;font-size:16px;color:black;">
            <span class=" fw-light">Bills </span>
        </h4>
    </div>
    <div class="card mb-3">
        <form id="submit-form">
            <div class="card-body">
                <div class="row align-items-center">
                    <!-- Expenses Type -->
                    <div class="col-md-3 d-flex align-items-center mb-2 mb-md-0">
                        <label class="mb-0 mr-2 font-weight-semibold">
                            Expenses Type:
                        </label>
                        <select class="form-control form-control-sm w-50" name="expenses_id" id="expenses_id">
                            <option value=''>Select</option>
                            <option value="1" {{ request()->expenses_id == 1 ? 'selected' : '' }}>Expenses</option>
                            <option value="2" {{ request()->expenses_id == 2 ? 'selected' : '' }}>Labour Expenses</option>
                            <option value="3" {{ request()->expenses_id == 3 ? 'selected' : '' }}>Vendor Expenses</option>
                        </select>
                    </div>
                    <!-- Main Category -->
                    <div class="col-md-3 d-flex align-items-center mb-2 mb-md-0">
                        <label class="mb-0 mr-2 font-weight-semibold">
                            Main Category:
                        </label>
                        <select class="form-control form-control-sm w-50" name="main_category_id" id="main_category_id">
                        </select>
                    </div>
                    <!-- category -->
                    <div class="col-md-3 d-flex align-items-center mb-2 mb-md-0">
                        <label class="mb-0 mr-2 font-weight-semibold">
                            Category:
                        </label>
                        <select class="form-control form-control-sm w-50" name="category_id" id="category_id">
                        </select>
                    </div>
                    <!-- Search -->
                    <div class="col-md-2 d-flex align-items-center mb-2 mb-md-0">
                        <button class="btn btn-sm btn-primary mr-2 client_search" type="submit">
                            <i class="bx bx-search"></i>
                        </button>
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('expenses-image') }}">
                            <i class="bx bx-x-circle"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="row">
                @if(count($images) > 0)
                @foreach ($images as $img)
                    <!-- Single Expense Card -->
                    <div class="col-md-3 mb-3">
                        <div class="card expense-card"
                            data-image="{{ url('images/' . $img->category_name . '/' . $img->image) }}">
                            <img src="{{ url('images/' . $img->category_name . '/' . $img->image) }}" class="card-img-top"
                                alt="Expense Image">
                            <div class="card-body p-2 text-center">
                                <h6 class="mb-1">{{ $img->category_name }}</h6>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($img->current_date)->format('d-m-Y') }}
                                </small>
                            </div>
                        </div>
                    </div>
                @endforeach
                @else
                <center>No image found.</center>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" />
                </div>
            </div>
        </div>
    </div>




    <!-- modal popup for salary details -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function(){
            var expenses_id = $('#expenses_id :selected').val();
            var main_category_id = $('#main_category_id :selected').val();
            console.log('expenses_id',expenses_id);
            console.log('main_category_id',main_category_id);
            fetchmaincategory(expenses_id);
            
        });
        $('#expenses_id').change(function() {
         expenses_id = $(this).val();
           fetchmaincategory(expenses_id);
        });
       function fetchmaincategory(expenses_id){
             $.ajax({
                type: 'get',
                url: "{{ route('bill-main-category') }}",
                data: {
                    expenses_id: expenses_id
                },
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    $('#main_category_id').empty();
                    $('#main_category_id').append('<option value="">Select main category</option>');
                    var main_categ = "<?php echo request()->main_category_id; ?>";
                    $.each(response, function(index, category) {
                        $('#main_category_id').append('<option value="' + category.id + '"' +( main_categ == category.id ? "selected" : "") +'>' +
                            category.name + '</option>');
                        // $('#category_id').append(option);
                    });
                    if(main_categ != ''){
                        fetchcategory(main_categ);
                    }
                }
            });
        }
        $('#main_category_id').change(function() {
            var main_category_id = $(this).val();
            fetchcategory(main_category_id);
        });
       function fetchcategory(main_category_id){
             $.ajax({
                type: 'get',
                url: "{{ route('bill-category') }}",
                data: {
                    main_category_id: main_category_id
                },
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    $('#category_id').empty();
                    $('#category_id').append('<option value="">Select category</option>');
                    var cag_id = "<?php echo request()->category_id; ?>";
                    $.each(response, function(index, category) {
                        $('#category_id').append('<option value="' + category.id + '"'+(cag_id == category.id ? "selected" : "")+'>' +
                            category.name + '</option>');
                        // $('#category_id').append(option);
                    });
                }
            })
        }
        $(document).on('click', '.expense-card', function() {
            var image = $(this).data('image');
            $('#modalImage').attr('src', image);
            $('#imageModal').modal('show');
        });
    </script>

@endsection
