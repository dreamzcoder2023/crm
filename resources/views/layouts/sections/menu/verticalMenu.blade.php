
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <!-- ! Hide app brand if navbar-full -->
  <div class="app-brand demo">
    <a href="{{url('/')}}" class="app-brand-link">
      <!-- <span class="app-brand-logo demo">
        @include('_partials.macros',["width"=>25,"withbg"=>'#696cff'])
      </span>
      <span class="app-brand-text demo menu-text fw-bold ms-2">{{config('variables.templateName')}}</span> -->
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-autod-block d-xl-none">
      <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    <!--- header -->
    <!-- <li class="menu-header small text-uppercase">
      <span class="menu-header-text"></span>
    </li> -->
    <!--- header -->


    {{-- main menu --}}
    <!--- dashboard -->
    <li class="menu-item {{(\Request::route()->getName() == 'dashboard') ? 'active open' : '' }}">
      <a href="{{route('dashboard')}}" class="menu-link" >
        <i class="menu-icon tf-icons bx bx-home-circle"></i>
        <div>Dashboard</div>
      </a>
    </li>
    <!--- dashboard -->
     <!--- roles -->
     @can('role-list')
     <li class="menu-item {{(\Request::route()->getName() == 'roles.index') ? 'active open' : '' }}">
      <a href="{{route('roles.index')}}" class="menu-link" >
        <i class="menu-icon tf-icons bx bx-user-circle"></i>
        <div>Roles</div>
      </a>
    </li>
    @endcan
    <!--- roles -->
     <!--- members -->
     @can('user-list')
     <li class="menu-item {{(\Request::route()->getName() == 'user-index') ? 'active open' : '' }}">
      <a href="{{route('user-index')}}" class="menu-link" >
        <i class="menu-icon tf-icons bx bx-user-circle"></i>
        <div>Members</div>
      </a>
    </li>
    @endcan
    <!--- members -->
       <!--- labour role -->
       @can('labour role-list')
       <li class="menu-item {{(\Request::route()->getName() == 'labourrole-index') ? 'active open' : '' }}">
         <a href="{{route('labourrole-index')}}" class="menu-link" >
          <img src="{{asset('assets/img/icons/labour-job.png')}}" alt="slack" class="me-3" height="25" >
           <div>Labour Role</div>
         </a>
       </li>
       @endcan
       <!--- labour -->
    <!--- labour -->
    @can('labour-list')
    <li class="menu-item {{(\Request::route()->getName() == 'labour-index') ? 'active open' : '' }}">
      <a href="{{route('labour-index')}}" class="menu-link" >
        <img src="{{asset('assets/img/icons/labour-icon.jpg')}}" alt="slack" class="me-3" height="25" >
        <div>Labour</div>
      </a>
    </li>
    @endcan
    <!--- labour -->
     <!--- category -->
     @can('category-list')
     <li class="menu-item {{(\Request::route()->getName() == 'category-index') ? 'active open' : '' }}">
      <a href="{{route('category-index')}}" class="menu-link" >
        <i class="menu-icon tf-icons bx bxs-category"></i>
        <div>Category</div>
      </a>
    </li>
    @endcan
    <!--- category -->
     <!--- payment -->
     @can('payment-list')
     <li class="menu-item {{(\Request::route()->getName() == 'payment-index') ? 'active open' : '' }}">
      <a href="{{route('payment-index')}}" class="menu-link" >
        <i class="menu-icon tf-icons bi bi-cash"></i>
        <div>Payment</div>
      </a>
    </li>
    @endcan
    <!--- payment -->
     <!--- client -->
     @can('client-list')
     <li class="menu-item {{(\Request::route()->getName() == 'client-index') ? 'active open' : '' }}">
      <a href="{{route('client-index')}}" class="menu-link" >
        <i class="menu-icon tf-icons bx bx-user-circle"></i>
        <div>Client Details</div>
      </a>
    </li>
    @endcan
    <!--- client -->
    <!--- project -->
    @can('project-list')
    <li class="menu-item {{(\Request::route()->getName() == 'project-index') ? 'active open' : '' }}">
      <a href="{{route('project-index')}}" class="menu-link" >
        <i class="menu-icon tf-icons bi bi-tools"></i>
        <div>Project Details</div>
      </a>
    </li>
    @endcan
    @can('stage-list')
    <li class="menu-item {{(\Request::route()->getName() == 'stage-index') ? 'active open' : '' }}">
      <a href="{{route('stage-index')}}" class="menu-link" >
      <img src="{{asset('assets/img/icons/project-management.png')}}" alt="slack" class="me-3" height="20" >
        <div>Stages</div>
      </a>
    </li>
    @endcan
    <!--- project -->
     <!--- transfer -->
     @can('transfer-history')
    <li class="menu-item {{(\Request::route()->getName() == 'transfer-history') ? 'active open' : '' }}">
      <a href="{{route('transfer-history')}}" class="menu-link" >
      <img src="{{asset('assets/img/icons/transfer-history.png')}}" alt="slack" class="me-3" height="20" >
        <!-- <i class="menu-icon tf-icons bi bi-currency-exchange"></i> -->
        <div>Transfer History</div>
      </a>
    </li>
    @endcan
    <!--- transfer -->
       <!--- expenses -->
     @canany(['expenses-history','unpaid-history','expenses-deleted history'])
    <li class="menu-item ">
      <a href="javascript:void(0);" class="menu-link menu-toggle" >
        <i class="menu-icon tf-icons bi bi-currency-exchange"></i>
        <div>Expenses</div>
      </a>
          <ul class="menu-sub">

@can('expenses-history')
        <li class="menu-item {{(\Request::route()->getName() == 'expenses-history') ? 'active open' : '' }}">
        <a href="{{route('expenses-history')}}" class="menu-link" ><img src="{{asset('assets/img/icons/capital.png')}}" alt="slack" class="me-3" height="20" >
          <div>Expenses History</div>
        </a></li>
        @endcan
        @can('unpaid-history')
        <li class="menu-item {{(\Request::route()->getName() == 'unpaid-history') ? 'active open' : '' }}">
        <a href="{{route('unpaid-history')}}" class="menu-link" ><img src="{{asset('assets/img/icons/capital.png')}}" alt="slack" class="me-3" height="20" >
          <div>Unpaid History</div>
        </a></li> @endcan
        @can('expenses-deleted history')
    <li class="menu-item {{(\Request::route()->getName() == 'expenses-delete_record') ? 'active open' : '' }}">
        <a href="{{route('expenses-delete_record')}}" class="menu-link" ><img src="{{asset('assets/img/icons/capital.png')}}" alt="slack" class="me-3" height="20" >
          <div>Deleted History</div>
        </a></li> @endcan
      </ul>

    </li>


     @endcanany
    <!--- expenses -->
           <!--- reports -->
           @canany(['client-summary','payment-summary'])
    <li class="menu-item ">
      <a href="javascript:void(0);" class="menu-link menu-toggle" >
      <img src="{{asset('assets/img/icons/reports.png')}}" alt="slack" class="me-3" height="20" >
        <div>Reports</div>
      </a>
          <ul class="menu-sub">
            @can('client-summary')
          <li class="menu-item {{(\Request::route()->getName() == 'client-summary') ? 'active open' : '' }}">
        <a href="{{route('client-summary')}}" class="menu-link" ><img src="{{asset('assets/img/icons/client.png')}}" alt="slack" class="me-3" height="20" >
          <div>Client Summary</div>
        </a></li>
        @endcan
@can('payment-summary')
        <li class="menu-item {{(\Request::route()->getName() == 'payment-summary') ? 'active open' : '' }}">
        <a href="{{route('payment-summary')}}" class="menu-link" ><img src="{{asset('assets/img/icons/payment.png')}}" alt="slack" class="me-3" height="20" >
          <div>Payment Summary</div>
        </a></li>
        @endcan
      </ul>
    </li>

     @endcanany
    <!--- expenses -->
  </ul>

</aside>
