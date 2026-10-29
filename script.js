'use strict';

const currentUserId = Number(document.querySelector('meta[name="user-id"]').content);
const currentUsername = document.querySelector('meta[name="username"]').content;
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const conversation = document.getElementById('conversation');
const messages = document.getElementById('messages');
const messageInput = document.getElementById('message');
const sendButton = document.getElementById('send-button');
const feedback = document.getElementById('feedback');
const newMessagesButton = document.getElementById('new-messages');
let lastMessageId = 0;
let lastDate = '';
let firstLoad = true;
let sending = false;
let refreshing = false;

function initials(name) {
    return Array.from(name.trim()).slice(0, 2).join('').toLocaleUpperCase('hu');
}

function createElement(tag, className, text) {
    const element = document.createElement(tag);
    element.className = className;
    // A textContent nem értelmezi HTML-ként az idegen felhasználói tartalmat.
    if (text !== undefined) element.textContent = text;
    return element;
}

function makeAvatar(user) {
    const avatar = createElement('span', 'avatar color-' + (user.id % 5), initials(user.username));
    avatar.setAttribute('aria-hidden', 'true');
    return avatar;
}

document.getElementById('profile-avatar').textContent = initials(currentUsername);

function nearBottom() {
    return conversation.scrollHeight - conversation.scrollTop - conversation.clientHeight < 100;
}

function scrollToBottom() {
    conversation.scrollTop = conversation.scrollHeight;
    newMessagesButton.hidden = true;
}

newMessagesButton.addEventListener('click', scrollToBottom);
conversation.addEventListener('scroll', () => {
    if (nearBottom()) newMessagesButton.hidden = true;
});

function showMessages(items) {
    const shouldScroll = firstLoad || nearBottom();
    items.forEach(item => {
        if (item.id <= lastMessageId) return;
        const date = new Date(item.created_at);
        const dateLabel = date.toLocaleDateString('hu-HU', { year: 'numeric', month: 'long', day: 'numeric' });
        if (lastDate !== dateLabel) {
            messages.append(createElement('div', 'date-divider', dateLabel));
            lastDate = dateLabel;
        }
        const own = item.user_id === currentUserId;
        const row = createElement('article', 'message-row' + (own ? ' own-message' : ''));
        const body = createElement('div', 'message-body');
        const meta = createElement('div', 'message-meta');
        meta.append(createElement('strong', 'message-author', item.username));
        if (own) meta.append(createElement('span', 'you-tag', 'TE'));
        const time = createElement('time', '', date.toLocaleTimeString('hu-HU', { hour: '2-digit', minute: '2-digit' }));
        time.dateTime = item.created_at;
        time.title = date.toLocaleString('hu-HU');
        meta.append(time);
        body.append(meta, createElement('p', 'message-text', item.message));
        row.append(makeAvatar({ id: item.user_id, username: item.username }), body);
        messages.append(row);
        lastMessageId = item.id;
    });
    if (items.length && shouldScroll) scrollToBottom();
    else if (items.length) newMessagesButton.hidden = false;
}

function showUsers(users) {
    const list = document.getElementById('user-list');
    const fragment = document.createDocumentFragment();
    users.forEach(user => {
        const row = createElement('div', 'user-row' + (user.online ? '' : ' offline'));
        const avatarWrap = createElement('span', 'avatar-wrap');
        avatarWrap.append(makeAvatar(user), createElement('span', 'presence' + (user.online ? ' is-online' : '')));
        const name = createElement('div', 'user-name');
        name.append(createElement('strong', '', user.username + (user.id === currentUserId ? ' (te)' : '')));
        name.append(createElement('span', '', user.online ? 'Online' : 'Offline'));
        row.append(avatarWrap, name);
        fragment.append(row);
    });
    list.replaceChildren(fragment);
    document.getElementById('user-count').textContent = users.length;
    document.getElementById('online-count').textContent = users.filter(user => user.online).length;
}

async function apiRequest(url, options = {}) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 10000);
    try {
        const response = await fetch(url, { ...options, signal: controller.signal, cache: 'no-store' });
        if (response.status === 401) {
            window.location.replace('login.php');
            throw new Error('A belépés lejárt.');
        }
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'A kérés nem sikerült.');
        return data;
    } finally {
        clearTimeout(timeout);
    }
}

function connectionState(connected) {
    document.getElementById('connection-status').classList.toggle('disconnected', !connected);
    document.getElementById('connection-text').textContent = connected ? 'Kapcsolat létrejött' : 'Újracsatlakozás…';
}

async function refreshMessages() {
    if (refreshing) return;
    refreshing = true;
    try {
        let data;
        do {
            data = await apiRequest('get_messages.php?after=' + lastMessageId);
            showMessages(data.messages);
            showUsers(data.users);
        } while (data.has_more);
        document.getElementById('empty-message').hidden = lastMessageId > 0;
        document.getElementById('empty-message').textContent = 'Nincs még semmi mondanivaló. Tiéd az első szó!';
        connectionState(true);
        if (feedback.dataset.network === 'true') {
            feedback.textContent = '';
            feedback.dataset.network = 'false';
        }
        firstLoad = false;
    } catch (error) {
        connectionState(false);
        feedback.textContent = error.name === 'AbortError' ? 'A szerver nem válaszol. Hamarosan újra próbáljuk.' : error.message;
        feedback.dataset.network = 'true';
        document.getElementById('empty-message').textContent = 'Várunk a kapcsolatra. A beszélgetés hamarosan betöltődik.';
    } finally {
        refreshing = false;
    }
}

function updateComposer() {
    const length = Array.from(messageInput.value).length;
    document.getElementById('character-count').textContent = length + ' / 500';
    document.getElementById('character-count').classList.toggle('over-limit', length > 500);
    sendButton.disabled = sending || !messageInput.value.trim() || length > 500;
}

messageInput.addEventListener('input', updateComposer);
messageInput.addEventListener('keydown', event => {
    if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
        event.preventDefault();
        if (!sendButton.disabled) document.getElementById('message-form').requestSubmit();
    }
});

document.getElementById('message-form').addEventListener('submit', async event => {
    event.preventDefault();
    if (sendButton.disabled) return;
    const text = messageInput.value;
    const formData = new FormData();
    formData.append('message', text);
    formData.append('csrf_token', csrfToken);
    sending = true;
    updateComposer();
    feedback.textContent = '';
    feedback.dataset.network = 'false';
    try {
        await apiRequest('send_message.php', { method: 'POST', body: formData });
        // A kérés ideje alatt beírt új szöveget nem töröljük.
        if (messageInput.value === text) messageInput.value = '';
        feedback.textContent = 'Üzenet sikeresen elpofázva.';
        await refreshMessages();
    } catch (error) {
        feedback.textContent = error.name === 'AbortError' ? 'A küldés visszaigazolása nem érkezett meg. Újraküldés előtt ellenőrizd az üzenetlistát.' : error.message;
    } finally {
        sending = false;
        updateComposer();
        messageInput.focus();
    }
});

const menuButton = document.getElementById('menu-button');
const overlay = document.getElementById('sidebar-overlay');
function toggleSidebar(open) {
    document.body.classList.toggle('sidebar-open', open);
    menuButton.setAttribute('aria-expanded', String(open));
    overlay.hidden = !open;
    if (open) document.querySelector('.channel').focus();
    else menuButton.focus();
}
menuButton.addEventListener('click', () => toggleSidebar(!document.body.classList.contains('sidebar-open')));
overlay.addEventListener('click', () => toggleSidebar(false));
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && document.body.classList.contains('sidebar-open')) toggleSidebar(false);
});

// Egy lekérés befejezése után várunk 2,5 másodpercet: nincs átfedő pollolás.
async function poll() {
    await refreshMessages();
    setTimeout(poll, 2500);
}
poll();
