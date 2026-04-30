@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <flux:heading size="xl" class="text-white dark:text-white">{{ $title }}</flux:heading>
    <flux:subheading class="text-zinc-400 dark:text-zinc-400">{{ $description }}</flux:subheading>
</div>
