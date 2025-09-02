<script setup lang="ts">
import { Chat } from '@ai-sdk/vue';
import { usePage } from '@inertiajs/vue3';
import type { UIMessage } from 'ai';
import { createIdGenerator, TextStreamChatTransport } from 'ai';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const csrfToken = computed(() => page.props.csrf);

const messages = ref<UIMessage[]>([]);

const chat = new Chat({
    transport: new TextStreamChatTransport({
        api: '/dashboard/chat',
        fetch: (url, options) => {
            return fetch(url, {
                ...options,
                headers: {
                    ...(options?.headers),
                    'X-CSRF-TOKEN': String(csrfToken.value ?? ''),
                }
            });
        }
    }),
    generateId: createIdGenerator({ prefix: 'msgc', size: 16 }),
    messages: messages.value,
});

const messageList = computed(() => chat.messages); // computed property for type inference
const input = ref('');

const handleSubmit = (e: Event) => {
    e.preventDefault();
    console.log(input.value);
    chat.sendMessage({ text: input.value });
    input.value = '';
};
</script>

<template>
    <div class="m-2 flex h-[90vh] flex-col overflow-hidden rounded-xl border bg-gray-100 text-black">
        <header class="flex items-center justify-between border-b bg-blue-500 px-4 py-3">
            <h1 class="px-4 text-lg font-bold">My AI Chat</h1>
            <span class="text-white">สวัสดีคุณ {{ user.name }} ID: {{ user.id }}</span>
        </header>

        <div class="flex-1 overflow-y-auto p-4">
            <div
                v-for="message in messageList"
                :key="message.id"
                :class="['my-2 flex gap-3', message.role === 'user' ? 'flex-row-reverse' : 'flex-row']"
            >
                <strong>{{ `${message.role}: ` }}</strong>
                <div :class="['rounded-2xl px-4 py-2.5 whitespace-pre-wrap', message.role === 'user' ? 'bg-gray-200' : 'bg-yellow-200']">
                    {{ message.parts.map((part) => (part.type === 'text' ? part.text : '')).join('') }}
                </div>
            </div>
        </div>

        <div class="bg-blue-500 p-4 border-t">
            <form @submit="handleSubmit" class="flex items-center gap-2">
                <input
                    class="flex-1 px-4 py-2 rounded-xl bg-white"
                    v-model="input"
                    placeholder="Say something..."
                />
            </form>
        </div>
    </div>
</template>
