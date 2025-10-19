<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, nextTick, onMounted } from "vue";
import Layout from "../../shared/Layout.vue";
import Banner from "../../components/Banner.vue";

const props = defineProps({
    pageTitle: String,
    conversations: Array,
    availableAdmins: Array,
    activeConversation: Object,
    messages: Array,
});

// Debug: Log props when component mounts
onMounted(() => {
    console.log('Props received:', {
        conversations: props.conversations,
        activeConversation: props.activeConversation,
        messages: props.messages,
    });
    
    // Show messages if there's an active conversation
    if (props.activeConversation) {
        showMobileMessages.value = true;
    }
    
    scrollToBottom();
});

const newMessage = ref('');
const messagesContainer = ref(null);
const selectedAdmin = ref(null);
const startMessage = ref('');
const showMobileMessages = ref(false);

const loadConversation = (conversationId) => {
    showMobileMessages.value = true; // Show messages on mobile
    router.get('/concerns', { conversation_id: conversationId }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const backToConversations = () => {
    showMobileMessages.value = false;
};

const openNewConversationModal = () => {
    selectedAdmin.value = null;
    startMessage.value = '';
    document.getElementById('new_conversation_modal').showModal();
};

const closeModal = () => {
    document.getElementById('new_conversation_modal').close();
    selectedAdmin.value = null;
    startMessage.value = '';
};

const startNewConversation = () => {
    if (!selectedAdmin.value || !startMessage.value.trim()) return;

    router.post('/concerns', {
        admin_id: selectedAdmin.value,
        message: startMessage.value,
    }, {
        onSuccess: () => {
            closeModal();
        }
    });
};

const sendMessage = () => {
    if (!newMessage.value.trim() || !props.activeConversation) return;

    router.post(`/concerns/${props.activeConversation.id}/messages`, {
        body: newMessage.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            newMessage.value = '';
        }
    });
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const formatTime = (date) => {
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatDate = (date) => {
    const d = new Date(date);
    const now = new Date();
    const diff = now - d;
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    
    if (days === 0) {
        return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    } else if (days === 1) {
        return 'Yesterday';
    } else if (days < 7) {
        return d.toLocaleDateString('en-US', { weekday: 'short' });
    } else {
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
};

// Watch for messages changes and scroll to bottom
watch(() => props.messages, () => {
    scrollToBottom();
}, { deep: true });

onMounted(() => {
    console.log('Props received:', {
        conversations: props.conversations,
        activeConversation: props.activeConversation,
        messages: props.messages,
    });
    scrollToBottom();
});
</script>

<template>
    <Layout :pageTitle="pageTitle">
        <div class="w-full">
            <Banner :pageName="'CONCERNS & MESSAGES'" />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 h-[calc(100vh-200px)]">
                <!-- Left Sidebar: Conversations List -->
                <div 
                    class="bg-white rounded-lg border border-gray-100 overflow-hidden"
                    :class="{ 'hidden lg:block': showMobileMessages && activeConversation }"
                >
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex justify-between items-center mb-3">
                            <h2 class="text-lg font-semibold">Messages</h2>
                            <button
                                v-if="availableAdmins && availableAdmins.length > 0"
                                @click="openNewConversationModal"
                                class="btn btn-primary btn-xs"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                New
                            </button>
                        </div>
                    </div>

                    <!-- Conversations List -->
                    <div class="overflow-y-auto h-[calc(100%-80px)]">
                        <div v-if="conversations.length === 0" class="p-4 text-center text-gray-500 text-sm">
                            No conversations yet
                        </div>

                        <div v-else class="divide-y divide-gray-200">
                            <button
                                v-for="conv in conversations"
                                :key="conv.id"
                                @click="loadConversation(conv.id)"
                                class="flex items-center gap-3 w-full p-4 hover:bg-gray-50 transition-colors text-left"
                                :class="{ 'bg-blue-50': activeConversation?.id === conv.id }"
                            >
                                <div class="avatar placeholder">
                                    <div class="bg-primary text-primary-content rounded-full w-10">
                                        <span>{{ conv.other_user.name.charAt(0).toUpperCase() }}</span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start mb-1">
                                        <div class="font-semibold text-sm">{{ conv.other_user.name }}</div>
                                        <span class="text-xs text-gray-500">{{ formatDate(conv.updated_at) }}</span>
                                    </div>
                                    <p v-if="conv.latest_message" class="text-xs text-gray-600 truncate">
                                        <span v-if="conv.latest_message.is_mine" class="font-medium">You: </span>
                                        {{ conv.latest_message.body }}
                                    </p>
                                </div>
                                <div v-if="conv.unread_count > 0" class="badge badge-primary badge-sm">
                                    {{ conv.unread_count }}
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Content: Messages -->
                <div 
                    class="lg:col-span-2 bg-white rounded-lg border border-gray-100 flex flex-col overflow-hidden"
                    :class="{ 'hidden lg:flex': !showMobileMessages && !activeConversation }"
                >
                    <div v-if="!activeConversation" class="flex-1 flex items-center justify-center text-gray-500">
                        <div class="text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <p>Select a conversation to start messaging</p>
                        </div>
                    </div>

                    <div v-else class="flex flex-col h-full">
                        <!-- Header with Back Button -->
                        <div class="p-4 border-b border-gray-200 flex items-center gap-3">
                            <!-- Back button for mobile -->
                            <button 
                                @click="backToConversations"
                                class="btn btn-ghost btn-sm btn-circle lg:hidden"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            
                            <div class="avatar placeholder">
                                <div class="bg-primary text-primary-content rounded-full w-10">
                                    <span>{{ activeConversation.other_user.name.charAt(0).toUpperCase() }}</span>
                                </div>
                            </div>
                            <div>
                                <h2 class="font-semibold">{{ activeConversation.other_user.name }}</h2>
                                <p class="text-xs badge badge-ghost capitalize">
                                    {{ activeConversation.other_user.role }}
                                </p>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div 
                            ref="messagesContainer" 
                            class="flex-1 overflow-y-auto p-4 space-y-4 bg-base-200"
                        >
                            <div v-if="messages.length === 0" class="text-center py-12 text-gray-500">
                                No messages yet. Start the conversation!
                            </div>

                            <div v-for="message in messages" :key="message.id" 
                                :class="['chat', message.is_mine ? 'chat-end' : 'chat-start']">
                                <div class="chat-header mb-1">
                                    {{ message.sender.name }}
                                    <time class="text-xs opacity-50 ml-1">
                                        {{ formatTime(message.created_at) }}
                                    </time>
                                </div>
                                <div :class="['chat-bubble whitespace-pre-wrap', message.is_mine ? 'chat-bubble-primary' : '']">
                                    {{ message.body }}
                                </div>
                                <div v-if="message.is_mine && message.read_at" class="chat-footer opacity-50">
                                    Seen
                                </div>
                            </div>
                        </div>

                        <!-- Input -->
                        <div class="p-3 lg:p-4 border-t border-gray-200">
                            <form @submit.prevent="sendMessage" class="flex gap-2">
                                <textarea
                                    v-model="newMessage"
                                    placeholder="Type your message..."
                                    rows="1"
                                    class="textarea textarea-bordered flex-1 resize-none text-sm lg:text-base"
                                    @keydown.enter.exact.prevent="sendMessage"
                                ></textarea>
                                <button
                                    type="submit"
                                    :disabled="!newMessage.trim()"
                                    class="btn btn-primary btn-square btn-sm lg:btn-md"
                                >
                                    <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                </button>
                            </form>
                            <p class="text-xs text-base-content/60 mt-1 hidden sm:block">
                                Press Enter to send
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Conversation Modal -->
        <dialog id="new_conversation_modal" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Start New Conversation</h3>
                
                <form @submit.prevent="startNewConversation">
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Select Admin</span>
                        </label>
                        <select 
                            v-model="selectedAdmin"
                            class="select select-bordered w-full"
                            required
                        >
                            <option :value="null" disabled>Choose an admin...</option>
                            <option v-for="admin in availableAdmins" :key="admin.id" :value="admin.id">
                                {{ admin.name }} ({{ admin.email }})
                            </option>
                        </select>
                    </div>

                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Your Message</span>
                        </label>
                        <textarea
                            v-model="startMessage"
                            class="textarea textarea-bordered h-24"
                            placeholder="Describe your concern..."
                            required
                        ></textarea>
                    </div>

                    <div class="modal-action">
                        <button type="button" @click="closeModal" class="btn btn-sm">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            Start Conversation
                        </button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button @click="closeModal">close</button>
            </form>
        </dialog>
    </Layout>
</template>