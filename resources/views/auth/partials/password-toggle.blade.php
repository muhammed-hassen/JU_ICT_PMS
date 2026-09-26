{{-- Show/hide for password inputs. The button names its input in data-password-toggle. --}}
@push('js')
<script>
    document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var field = document.getElementById(btn.getAttribute('data-password-toggle'));
            var reveal = field.type === 'password';
            field.type = reveal ? 'text' : 'password';
            btn.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
            btn.setAttribute('aria-pressed', reveal ? 'true' : 'false');
            btn.querySelector('[data-eye-open]').classList.toggle('hidden', reveal);
            btn.querySelector('[data-eye-closed]').classList.toggle('hidden', !reveal);
        });
    });
</script>
@endpush
