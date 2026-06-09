<div>
    <div class="page-header flex items-center justify-between">
        <div>
            <h1 class="page-title">Broadcast Messages</h1>
            <p class="page-subtitle">Send messages to all users or specific groups</p>
        </div>
    </div>

    <div class="card max-w-2xl">
        <div class="card-body">
            <form wire:submit="send" class="space-y-4">
                <div>
                    <label class="label">Subject</label>
                    <input type="text" wire:model="subject" class="input w-full" placeholder="Message subject..." maxlength="255">
                    @error('subject') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label">Target Audience</label>
                    <select wire:model="targetType" class="input w-full">
                        <option value="all">All Users</option>
                        <option value="staff">Staff Only</option>
                        <option value="students">Students Only</option>
                    </select>
                </div>

                <div>
                    <label class="label">Message</label>
                    <textarea wire:model="body" class="input w-full" rows="8" placeholder="Type your broadcast message here..."></textarea>
                    @error('body') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model="sendEmail" class="rounded border-surface-300 text-primary-600" id="sendEmail">
                    <label for="sendEmail" class="text-sm">Also send via email</label>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Send Broadcast</span>
                        <span wire:loading>Sending...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
