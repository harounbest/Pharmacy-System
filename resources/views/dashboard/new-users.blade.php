<div class="col-sm-6 col-lg-3">
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-column">
                <span class="fw-bolder fs-3">
                    {{ __('header.New Users') }}
                </span>
                <span class="text-muted">
                    {{ __('header.today') }}
                </span>
            </div>
            <div class="row align-items-center  mt-3">
                <div class="col-auto">
                    <span class="bg-dark avatar">
                        <i class="fa-solid fa-user fs-2"></i>
                    </span>
                </div>
                <div class="col">
                    <span class="fs-1 fw-bolder">
                        {{ number_format($TotalUsers , 0) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>