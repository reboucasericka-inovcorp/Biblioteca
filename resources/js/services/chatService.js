export async function fetchChatUsers(search = '') {
  const response = await window.axios.get('/api/chat/users', {
    params: { search },
  });

  return response.data?.data ?? [];
}

export async function fetchChatRooms() {
  const response = await window.axios.get('/api/chat/rooms');
  return response.data?.data ?? [];
}

export async function createChatRoom(payload) {
  const response = await window.axios.post('/api/chat/rooms', payload);
  return response.data?.data;
}

export async function inviteUserToRoom(roomId, userId) {
  await window.axios.post(`/api/chat/rooms/${roomId}/invite`, { user_id: userId });
}

export async function removeUserFromRoom(roomId, userId) {
  await window.axios.delete(`/api/chat/rooms/${roomId}/users/${userId}`);
}

export async function startDirectConversation(userId) {
  const response = await window.axios.post(`/api/chat/direct/${userId}`);
  return response.data?.data;
}

export async function fetchDirectConversation(conversationId) {
  const response = await window.axios.get(`/api/chat/direct/${conversationId}`);
  return response.data?.data;
}

export async function fetchRoomMessages(roomId) {
  const response = await window.axios.get(`/api/chat/rooms/${roomId}/messages`);
  return response.data?.data;
}

export async function sendChatMessage(payload) {
  const response = await window.axios.post('/api/chat/messages', payload);
  return response.data?.data;
}

export async function uploadChatImage(file) {
  const formData = new FormData();
  formData.append('image', file);
  const response = await window.axios.post('/api/chat/upload', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  });
  return response.data?.data;
}

export async function markMessagesAsRead(payload) {
  await window.axios.post('/api/chat/messages/read', payload);
}

export function joinChatPresence({ here, joining, leaving } = {}) {
  if (!window.Echo) return null;

  const channel = window.Echo.join('chat');
  if (here) channel.here(here);
  if (joining) channel.joining(joining);
  if (leaving) channel.leaving(leaving);

  return {
    unsubscribe() {
      window.Echo.leave('presence-chat');
    },
  };
}

export function subscribeUserChannel(userId, onMessage) {
  if (!window.Echo || !userId) return null;

  const channel = window.Echo.private(`user.${userId}`);
  channel.listen('.message.sent', (event) => {
    if (onMessage) onMessage(event);
  });

  return {
    unsubscribe() {
      window.Echo.leave(`private-user.${userId}`);
    },
  };
}

export function subscribeConversation(conversationId, onMessage, onTyping) {
  if (!window.Echo) return null;

  const channel = window.Echo.private(`conversation.${conversationId}`);
  channel.listen('.message.sent', (event) => {
    onMessage(event);
  });
  channel.listenForWhisper('typing', (payload) => {
    if (onTyping) onTyping(payload);
  });

  return {
    whisper(event, payload) {
      channel.whisper(event, payload);
    },
    whisperTyping(payload) {
      channel.whisper('typing', payload);
    },
    unsubscribe() {
      window.Echo.leave(`private-conversation.${conversationId}`);
    },
  };
}

export function subscribeRoom(roomId, onMessage, onTyping) {
  if (!window.Echo) return null;

  const channel = window.Echo.private(`room.${roomId}`);
  channel.listen('.message.sent', (event) => {
    onMessage(event);
  });
  channel.listenForWhisper('typing', (payload) => {
    if (onTyping) onTyping(payload);
  });

  return {
    whisper(event, payload) {
      channel.whisper(event, payload);
    },
    whisperTyping(payload) {
      channel.whisper('typing', payload);
    },
    unsubscribe() {
      window.Echo.leave(`private-room.${roomId}`);
    },
  };
}
