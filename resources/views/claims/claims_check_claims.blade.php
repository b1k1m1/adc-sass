<form method="POST" action="{{ route('claims.check') }}" target="_blank">
    @csrf
    <button type="submit" class="btn btn-warning btn-sm">
        Check Claims
    </button>
</form>
