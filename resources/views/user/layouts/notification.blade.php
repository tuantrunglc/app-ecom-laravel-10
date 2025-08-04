@if(session('success'))
    <div class="walmart-alert walmart-alert-success alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle mr-3"></i>
            <div class="flex-grow-1">
                {{session('success')}}
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="walmart-alert walmart-alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-circle mr-3"></i>
            <div class="flex-grow-1">
                {{session('error')}}
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
@endif

@if(session('warning'))
    <div class="walmart-alert walmart-alert-warning alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle mr-3"></i>
            <div class="flex-grow-1">
                {{session('warning')}}
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
@endif

@if(session('info'))
    <div class="walmart-alert walmart-alert-info alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle mr-3"></i>
            <div class="flex-grow-1">
                {{session('info')}}
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
@endif

<style>
.flex-grow-1 {
    flex: 1 1 auto;
}

.walmart-alert .close {
    padding: 0;
    background: transparent;
    border: none;
    font-size: 1.25rem;
    line-height: 1;
    color: inherit;
    opacity: 0.7;
    cursor: pointer;
}

.walmart-alert .close:hover {
    opacity: 1;
}

.walmart-alert .close span {
    font-weight: 300;
}
</style>