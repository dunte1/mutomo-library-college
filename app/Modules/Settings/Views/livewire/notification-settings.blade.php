@section('title', 'Notification Settings')
<div>
    <div style="margin-bottom: 1rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin: 0;">Notification Settings</h2>
        <p style="margin: 0.25rem 0 0; color: #6b7280;">Configure alerts, reminders and notification channels</p>
    </div>

    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 1.25rem;">
        <form wire:submit="save">
            <div style="display: grid; gap: 1.25rem;">
                <div>
                    <h3 style="font-weight: 600; margin: 0 0 0.75rem;">Notification Channels</h3>
                    <div style="display: grid; gap: 0.75rem;">
                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox" wire:model="settings.email_notifications">
                            <div>
                                <div style="font-weight: 500;">Email Notifications</div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Send notifications via email</div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox" wire:model="settings.sms_notifications">
                            <div>
                                <div style="font-weight: 500;">SMS Notifications</div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Send notifications via SMS</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div style="border-top: 1px solid #e5e7eb; padding-top: 1rem;">
                    <h3 style="font-weight: 600; margin: 0 0 0.75rem;">Alert Types</h3>
                    <div style="display: grid; gap: 0.75rem;">
                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox" wire:model="settings.due_date_reminders">
                            <div>
                                <div style="font-weight: 500;">Due Date Reminders</div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Remind members before items are due</div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox" wire:model="settings.overdue_alerts">
                            <div>
                                <div style="font-weight: 500;">Overdue Alerts</div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Alert when items become overdue</div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox" wire:model="settings.new_arrival_alerts">
                            <div>
                                <div style="font-weight: 500;">New Arrival Alerts</div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Notify about new books and digital assets</div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox" wire:model="settings.fine_notifications">
                            <div>
                                <div style="font-weight: 500;">Fine Notifications</div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Notify members about fines and payments</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div style="border-top: 1px solid #e5e7eb; padding-top: 1rem;">
                    <h3 style="font-weight: 600; margin: 0 0 0.75rem;">Reminder Timing</h3>
                    <div>
                        <label style="display: block; margin-bottom: 0.35rem; font-weight: 500;">Remind (days before due)</label>
                        <input type="number" wire:model="settings.reminder_days_before" min="1" max="30" style="width: 100%; max-width: 12rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('settings.reminder_days_before')
                            <p style="margin-top: 0.35rem; color: #dc2626; font-size: 0.875rem;">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; padding-top: 1.25rem; border-top: 1px solid #e5e7eb; margin-top: 1.25rem;">
                <button type="submit" wire:loading.attr="disabled" style="background: #2563eb; color: white; border: 0; border-radius: 0.5rem; padding: 0.6rem 1rem; cursor: pointer;">
                    <span wire:loading.remove>Save Settings</span>
                    <span wire:loading>Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
