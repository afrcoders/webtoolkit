@extends('install.layout')

@section('content')
    <h2>2. Verify Purchase</h2>
    <hr>
    @include ('install.messages')

        <div class="box">
            <div class="configure-form">
                Before you continue with installation we need to verify your license. Please signin with Envato for validation.
            </div>
        </div>


        <div class="content-buttons mt-3 text-end">
            <a href="{{ ($requirement->satisfied() && $verifyPurchase->satisfied()) ? route('installconfig.get') : route('verify.redirect') }}" class="btn btn-primary rounded-pill px-5 text-white btn-lg">
                {{ ($requirement->satisfied() && $verifyPurchase->satisfied()) ? 'Continue' : 'Signin with Envato' }}
            </a>
        </div>
    </form>
@endsection

@push('scripts')
    <script>

    </script>
@endpush
