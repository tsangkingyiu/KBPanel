@extends('layouts.admin')

@section('page-title', 'Email Configuration')

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">SMTP Settings</h3>
            <p class="text-sm text-gray-500 mt-1">Configure outgoing email settings</p>
        </div>

        <form method="POST" action="{{ route('admin.settings.email.update') }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mail Driver</label>
                <select name="mail_mailer" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="smtp" {{ config('mail.default') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                    <option value="sendmail" {{ config('mail.default') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                    <option value="log" {{ config('mail.default') === 'log' ? 'selected' : '' }}>Log (Testing)</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                    <input type="text" name="mail_host" value="{{ old('mail_host', config('mail.mailers.smtp.host')) }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                    <input type="text" name="mail_port" value="{{ old('mail_port', config('mail.mailers.smtp.port', 587)) }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Username</label>
                <input type="text" name="mail_username" value="{{ old('mail_username', config('mail.mailers.smtp.username')) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Password</label>
                <input type="password" name="mail_password" value="{{ old('mail_password') }}" 
                       placeholder="Leave blank to keep current password"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Encryption</label>
                <select name="mail_encryption" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="tls" {{ config('mail.mailers.smtp.encryption') === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ config('mail.mailers.smtp.encryption') === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="">None</option>
                </select>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">From Address</h4>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Name</label>
                        <input type="text" name="mail_from_name" value="{{ old('mail_from_name', config('mail.from.name')) }}" 
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Email</label>
                        <input type="email" name="mail_from_address" value="{{ old('mail_from_address', config('mail.from.address')) }}" 
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                <button type="button" onclick="testEmail()" 
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg">
                    Send Test Email
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function testEmail() {
        if (confirm('Send a test email to {{ auth()->user()->email }}?')) {
            fetch('{{ route("admin.settings.email.test") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            })
            .catch(error => {
                alert('Failed to send test email');
            });
        }
    }
</script>
@endpush
@endsection