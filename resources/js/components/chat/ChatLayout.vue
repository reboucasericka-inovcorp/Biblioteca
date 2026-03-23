<template>
  <div class="flex h-full min-h-0 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <div
      v-if="isSidebarOpen"
      class="fixed inset-0 bg-black/30 z-20 md:hidden"
      @click="isSidebarOpen = false"
    ></div>
    <ChatSidebar
      :conversations="conversationItems"
      :search="search"
      :is-admin="isAdmin"
      :active-key="activeConversationKey"
      :is-mobile-open="isSidebarOpen"
      @update:search="handleSearchUsers"
      @select-conversation="handleSelectConversation"
      @open-room-modal="showRoomModal = true"
      @close-sidebar="isSidebarOpen = false"
    />

    <ChatWindow
      :title="windowTitle"
      :status-label="windowStatusLabel"
      :status-online="windowStatusOnline"
      :show-sidebar-toggle="true"
      :avatar-url="activeTarget?.avatar || ''"
      :typing-label="typingLabel"
      :messages="messages"
      :can-send="Boolean(activeTarget)"
      :current-user-id="currentUserId"
      @send="handleSendMessage"
      @typing="handleTyping"
      @upload-image="handleUploadImage"
      @toggle-sidebar="isSidebarOpen = !isSidebarOpen"
    />

    <RoomCreatorModal
      :open="showRoomModal"
      :users="users"
      @close="showRoomModal = false"
      @create="handleCreateRoom"
    />
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import ChatSidebar from './ChatSidebar.vue';
import ChatWindow from './ChatWindow.vue';
import RoomCreatorModal from './RoomCreatorModal.vue';
import {
  createChatRoom,
  fetchChatRooms,
  fetchChatUsers,
  fetchDirectConversation,
  fetchRoomMessages,
  markMessagesAsRead,
  sendChatMessage,
  startDirectConversation,
  subscribeUserChannel,
  uploadChatImage,
  subscribeConversation,
  subscribeRoom,
} from '../../services/chatService';

const users = ref([]);
const rooms = ref([]);
const messages = ref([]);
const activeTarget = ref(null);
const showRoomModal = ref(false);
const activeSubscription = ref(null);
const search = ref('');
const typingLabel = ref('');
const typingTimeoutByUser = new Map();
const onlineByUserId = ref({});
const localUnreadByKey = ref({});
const directConversationByPeerId = ref({});
let userFeedSubscription = null;
const isSidebarOpen = ref(false);
const notificationAudio = ref(null);

const currentUserId = Number(
  document.querySelector('meta[name="user-id"]')?.getAttribute('content') || 0
);
const currentUserRole = String(
  document.querySelector('meta[name="user-role"]')?.getAttribute('content') || ''
);

const isAdmin = computed(() => currentUserRole.toLowerCase() === 'admin');
const windowTitle = computed(() => {
  if (!activeTarget.value) return 'Selecione uma conversa';
  return activeTarget.value.name;
});
const windowStatusOnline = computed(() => activeTarget.value?.type === 'direct' && activeTarget.value?.status === 'online');
const windowStatusLabel = computed(() => {
  if (!activeTarget.value) return 'offline';
  if (activeTarget.value.type === 'room') return 'Sala';
  return windowStatusOnline.value ? 'online' : 'offline';
});
const activeConversationKey = computed(() => {
  if (!activeTarget.value) return '';
  if (activeTarget.value.type === 'direct') {
    return `direct-${activeTarget.value.peer_user_id ?? activeTarget.value.id}`;
  }
  return `${activeTarget.value.type}-${activeTarget.value.id}`;
});
const conversationItems = computed(() => {
  const userItems = users.value.map((user) => ({
    key: `direct-${user.id}`,
    type: 'direct',
    id: user.id,
    raw: user,
    name: user.name,
    avatarText: userInitials(user.name),
    preview: user.last_message?.body ?? 'Clique para conversar',
    timeLabel: formatTime(user.last_message?.created_at),
    unreadCount: Number(user.unread_count ?? 0) + Number(localUnreadByKey.value[`direct-${user.id}`] ?? 0),
    status: onlineByUserId.value[user.id] ?? user.status,
    avatarUrl: user.avatar ?? '',
  }));

  const roomItems = rooms.value.map((room) => ({
    key: `room-${room.id}`,
    type: 'room',
    id: room.id,
    raw: room,
    name: room.name,
    avatarText: '#',
    preview: room.last_message?.body ?? 'Sala em grupo',
    timeLabel: formatTime(room.last_message?.created_at),
    unreadCount: Number(room.unread_count ?? 0) + Number(localUnreadByKey.value[`room-${room.id}`] ?? 0),
    status: 'room',
    avatarUrl: room.avatar ?? '',
  }));

  return [...userItems, ...roomItems];
});

onMounted(async () => {
  await Promise.all([loadUsers(), loadRooms()]);
  hydrateOnlineUsersFromGlobal();
  window.addEventListener('chat-presence-updated', handleGlobalPresenceUpdate);
  subscribeToUserFeed();
  notificationAudio.value = new Audio('/sounds/notify.mp3');
});

onBeforeUnmount(() => {
  unsubscribeActiveChannel();
  window.removeEventListener('chat-presence-updated', handleGlobalPresenceUpdate);
  unsubscribeUserFeed();
});

async function loadUsers(search = '') {
  users.value = await fetchChatUsers(search);
}

async function loadRooms() {
  rooms.value = await fetchChatRooms();
}

function handleSearchUsers(value) {
  search.value = value;
  loadUsers(value);
}

function handleSelectConversation(item) {
  if (item.type === 'room') {
    handleSelectRoom(item.raw);
    return;
  }
  handleStartDirect(item.raw);
}

async function handleStartDirect(user) {
  isSidebarOpen.value = false;
  const conversation = await startDirectConversation(user.id);
  const directData = await fetchDirectConversation(conversation.id);

  activeTarget.value = {
    type: 'direct',
    id: conversation.id,
    peer_user_id: user.id,
    name: user.name,
    status: onlineByUserId.value[user.id] ?? user.status,
    avatar: user.avatar ?? '',
  };
  directConversationByPeerId.value = {
    ...directConversationByPeerId.value,
    [user.id]: conversation.id,
  };
  messages.value = directData?.messages ?? [];

  await afterTargetChange();
}

async function handleSelectRoom(room) {
  isSidebarOpen.value = false;
  const roomData = await fetchRoomMessages(room.id);

  activeTarget.value = {
    type: 'room',
    id: room.id,
    name: room.name,
    status: 'room',
    avatar: room.avatar ?? '',
  };
  messages.value = roomData?.messages ?? [];

  await afterTargetChange();
}

async function handleCreateRoom(payload) {
  try {
    const room = await createChatRoom(payload);
    showRoomModal.value = false;
    await loadRooms();

    if (room?.id) {
      await handleSelectRoom(room);
    }
  } catch (error) {
    const message = error?.response?.data?.message || 'Não foi possível criar a sala.';
    if (window.showToast) {
      window.showToast(message, 'error');
    }
    console.error(error);
  }
}

async function handleSendMessage(body) {
  if (!activeTarget.value) return;

  const message = await sendChatMessage({
    target_type: activeTarget.value.type,
    target_id: activeTarget.value.id,
    body,
  });

  messages.value.push(message);
  updateConversationPreview(activeTarget.value, message);
  clearTypingLabel();
}

async function handleUploadImage(file) {
  if (!activeTarget.value || !file) return;
  try {
    const uploaded = await uploadChatImage(file);
    if (!uploaded?.url) return;
    const message = await sendChatMessage({
      target_type: activeTarget.value.type,
      target_id: activeTarget.value.id,
      body: uploaded.url,
      type: 'image',
    });
    messages.value.push(message);
    updateConversationPreview(activeTarget.value, message);
  } catch (error) {
    const message = error?.response?.data?.message || 'Falha ao enviar imagem.';
    if (window.showToast) {
      window.showToast(message, 'error');
    }
    console.error(error);
  }
}

function handleGlobalIncomingMessage(event) {
  const message = event?.message ?? event;
  if (!message?.id) return;

  const targetType = message.target_type ?? (message.messageable_type?.includes('ChatRoom') ? 'room' : 'direct');
  const targetId = Number(message.target_id ?? message.messageable_id ?? 0);
  const active = activeTarget.value;
  const activeMatches =
    active
    && (
      (targetType === 'room' && active.type === 'room' && Number(active.id) === targetId)
      || (targetType === 'direct' && active.type === 'direct' && Number(active.id) === targetId)
    );

  if (!activeMatches) {
    if (Number(message.user_id) !== currentUserId) {
      playIncomingSound();
    }
    incrementUnreadByMessage(targetType, targetId, message);
    updatePreviewByMessage(targetType, targetId, message);
    return;
  }

  if (!messages.value.some((m) => Number(m.id) === Number(message.id))) {
    messages.value.push(message);
  }
}

function handleTyping() {
  if (!activeTarget.value || !activeSubscription.value?.whisperTyping) return;
  activeSubscription.value.whisperTyping({
    user_id: currentUserId,
    user_name: getCurrentUserName(),
  });
}

async function afterTargetChange() {
  clearTypingLabel();
  unsubscribeActiveChannel();
  clearUnreadForActiveConversation();
  subscribeToActiveChannel();

  if (!activeTarget.value) return;
  await markMessagesAsRead({
    target_type: activeTarget.value.type,
    target_id: activeTarget.value.id,
  });
}

function subscribeToActiveChannel() {
  if (!activeTarget.value) return;

  const onMessage = (event) => {
    const message = event?.message ?? event;
    if (!message?.id) return;
    if (!messages.value.some((m) => Number(m.id) === Number(message.id))) {
      messages.value.push(message);
    }
    updateConversationPreview(activeTarget.value, message);
  };
  const onTyping = (payload) => {
    if (!payload || Number(payload.user_id) === currentUserId) return;
    if (payload.event === 'presence' && Number(payload.user_id) !== currentUserId) {
      setUserOnlineStatus(Number(payload.user_id), payload.status === 'online' ? 'online' : 'offline');
      return;
    }
    const name = payload.user_name || 'Utilizador';
    typingLabel.value = `${name} está a escrever...`;
    resetTypingTimer(Number(payload.user_id));
  };

  activeSubscription.value =
    activeTarget.value.type === 'room'
      ? subscribeRoom(activeTarget.value.id, onMessage, onTyping)
      : subscribeConversation(activeTarget.value.id, onMessage, onTyping);
}

function unsubscribeActiveChannel() {
  if (!activeSubscription.value) return;
  activeSubscription.value.unsubscribe();
  activeSubscription.value = null;
  clearTypingLabel();
}

function userInitials(name) {
  if (!name) return '?';
  return String(name)
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? '')
    .join('');
}

function formatTime(value) {
  if (!value) return '';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '';
  return new Intl.DateTimeFormat('pt-PT', {
    hour: '2-digit',
    minute: '2-digit',
  }).format(date);
}

function resetTypingTimer(userId) {
  if (typingTimeoutByUser.has(userId)) {
    clearTimeout(typingTimeoutByUser.get(userId));
  }
  const timeoutId = setTimeout(() => {
    typingTimeoutByUser.delete(userId);
    if (typingTimeoutByUser.size === 0) {
      typingLabel.value = '';
    }
  }, 2000);
  typingTimeoutByUser.set(userId, timeoutId);
}

function clearTypingLabel() {
  for (const timeoutId of typingTimeoutByUser.values()) {
    clearTimeout(timeoutId);
  }
  typingTimeoutByUser.clear();
  typingLabel.value = '';
}

function getCurrentUserName() {
  const fullName = document.querySelector('meta[name="user-name"]')?.getAttribute('content')?.trim();
  if (fullName) return fullName;
  return 'Utilizador';
}

function subscribeToUserFeed() {
  userFeedSubscription = subscribeUserChannel(currentUserId, handleGlobalIncomingMessage);
}

function unsubscribeUserFeed() {
  if (!userFeedSubscription) return;
  userFeedSubscription.unsubscribe();
  userFeedSubscription = null;
}

function setUserOnlineStatus(userId, status) {
  onlineByUserId.value = {
    ...onlineByUserId.value,
    [userId]: status,
  };
  if (activeTarget.value?.type === 'direct' && activeTarget.value?.peer_user_id === userId) {
    activeTarget.value.status = status;
  }
}

function hydrateOnlineUsersFromGlobal() {
  const globalMap = window.onlineUsersMap ?? {};
  onlineByUserId.value = { ...globalMap };
  refreshActiveStatus();
}

function handleGlobalPresenceUpdate(event) {
  onlineByUserId.value = { ...(event?.detail ?? {}) };
  refreshActiveStatus();
}

function clearUnreadForActiveConversation() {
  if (!activeTarget.value) return;
  const key = getConversationKey(activeTarget.value);
  if (!localUnreadByKey.value[key]) return;
  localUnreadByKey.value = {
    ...localUnreadByKey.value,
    [key]: 0,
  };
}

function updateConversationPreview(target, message) {
  if (!target || !message) return;
  if (target.type === 'direct') {
    users.value = users.value.map((user) => {
      if (user.id !== target.id) return user;
      return {
        ...user,
        last_message: {
          body: message.body,
          created_at: message.created_at ?? new Date().toISOString(),
        },
      };
    });
    return;
  }

  rooms.value = rooms.value.map((room) => {
    if (room.id !== target.id) return room;
    return {
      ...room,
      last_message: {
        body: message.body,
        created_at: message.created_at ?? new Date().toISOString(),
      },
    };
  });
}

function incrementUnreadByMessage(targetType, targetId, message) {
  if (Number(message.user_id) === currentUserId) return;
  let key = '';
  if (targetType === 'room') {
    key = `room-${targetId}`;
  } else {
    const peerUserId = Number(message.user_id);
    key = `direct-${peerUserId}`;
  }
  if (!key) return;

  localUnreadByKey.value = {
    ...localUnreadByKey.value,
    [key]: Number(localUnreadByKey.value[key] ?? 0) + 1,
  };
}

function updatePreviewByMessage(targetType, targetId, message) {
  if (targetType === 'room') {
    rooms.value = rooms.value.map((room) => {
      if (Number(room.id) !== Number(targetId)) return room;
      return {
        ...room,
        last_message: {
          body: message.body,
          created_at: message.created_at ?? new Date().toISOString(),
        },
      };
    });
    return;
  }

  const peerUserId = Number(message.user_id);
  if (peerUserId === currentUserId) return;
  directConversationByPeerId.value = {
    ...directConversationByPeerId.value,
    [peerUserId]: Number(targetId),
  };
  users.value = users.value.map((user) => {
    if (Number(user.id) !== peerUserId) return user;
    return {
      ...user,
      last_message: {
        body: message.body,
        created_at: message.created_at ?? new Date().toISOString(),
      },
    };
  });
}

function getConversationKey(target) {
  if (!target) return '';
  if (target.type === 'direct') {
    return `direct-${target.peer_user_id ?? target.id}`;
  }
  return `room-${target.id}`;
}

function refreshActiveStatus() {
  if (activeTarget.value?.type !== 'direct') return;
  const peerId = Number(activeTarget.value.peer_user_id ?? 0);
  if (!peerId) return;
  activeTarget.value.status = onlineByUserId.value[peerId] ?? 'offline';
}

function playIncomingSound() {
  try {
    if (!notificationAudio.value) return;
    notificationAudio.value.currentTime = 0;
    notificationAudio.value.play().catch(() => {});
  } catch (error) {
    // fail silently to avoid blocking message flow
  }
}
</script>