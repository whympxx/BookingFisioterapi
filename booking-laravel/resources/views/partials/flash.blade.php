@if (session('success') || session('error'))
    <div class="flash-wrap" role="status" aria-live="polite">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas fa-circle-check mr-2"></i><span>{{ session('success') }}</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas fa-circle-exclamation mr-2"></i><span>{{ session('error') }}</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
    </div>
@endif
