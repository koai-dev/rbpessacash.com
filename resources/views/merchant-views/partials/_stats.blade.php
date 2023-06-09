<div class="col-sm-12 col-md-4 mb-3 mb-lg-5">
    <!-- Card -->
    <a class="card card-hover-shadow h-100" href="#" style="background: #444941">
        <div class="card-body">
            <h4 class="card-subtitle"
                style="color: white!important;">{{translate('Current Ballance')}}</h4>

            <div class="row align-items-center gx-2 mb-1">
                <div class="col-10">
                    <span class="card-title" style="color: white!important;">
                        {{ Helpers::set_symbol($data['current_balance']??0) }}
                    </span>
                </div>

                <div class="col-2 mt-2">
                    <i class="tio-money-vs" style="font-size: 30px;color: white"></i>
                </div>
            </div>
            <!-- End Row -->
        </div>
    </a>
    <!-- End Card -->
</div>

<div class="col-sm-12 col-md-4 mb-3 mb-lg-5">
    <!-- Card -->
    <a class="card card-hover-shadow h-100" href="#" style="background: #7FC8A9">
        <div class="card-body">
            <h4 class="card-subtitle"
                style="color: white!important;">{{translate('Pending Ballance')}}</h4>

            <div class="row align-items-center gx-2 mb-1">
                <div class="col-10">
                    <span class="card-title" style="color: white!important;">
                        {{ Helpers::set_symbol($data['pending_balance']??0) }}
                    </span>
                </div>

                <div class="col-2 mt-2">
                    <i class="tio-money-vs" style="font-size: 30px;color: white"></i>
                </div>
            </div>
            <!-- End Row -->
        </div>
    </a>
    <!-- End Card -->
</div>

<div class="col-sm-12 col-md-4 mb-3 mb-lg-5">
    <!-- Card -->
    <a class="card card-hover-shadow h-100" href="#" style="background: #003E47">
        <div class="card-body">
            <h4 class="card-subtitle"
                style="color: white!important;">{{translate('Total Withdraw')}}</h4>
            <div class="row align-items-center gx-2 mb-1 flex-between">
                <div class="col-10">
                    <span class="card-title" style="color: white!important;">
                        {{ Helpers::set_symbol($data['total_withdraw']??0) }}
                    </span>
                </div>
                <div class="col-2 mt-2">
                    <i class="tio-money-vs" style="font-size: 30px;color: white"></i>
                </div>
            </div>
            <!-- End Row -->
        </div>
    </a>
    <!-- End Card -->
</div>




