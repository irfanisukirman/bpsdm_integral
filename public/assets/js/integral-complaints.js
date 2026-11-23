(() => {
  const jsonHeaders = { Accept: 'application/json' };

  async function requestJson(url, options = {}) {
    const response = await fetch(url, { ...options, headers: { ...jsonHeaders, ...options.headers } });
    const data = await response.json().catch(() => ({}));
    if (!response.ok || response.redirected) {
      const error = new Error(Object.values(data.errors ?? {}).flat()[0] ?? 'Koneksi pengaduan belum tersedia. Coba lagi.');
      error.status = response.status;
      throw error;
    }
    return data;
  }

  function showPresence(container, online) {
    const label = container.querySelector('[data-complaint-presence]');
    if (!label) return;
    const message = online ? 'Admin online · Chat tersedia' : 'Admin offline · Pengaduan tetap diterima';
    if (label.textContent !== message) label.textContent = message;
    label.dataset.complaintPresence = online ? 'online' : 'offline';
  }

  const widget = document.querySelector('.complaint-widget');
  if (widget) {
    const frame = widget.querySelector('iframe');
    const menu = widget.querySelector('[data-complaint-menu]');
    const back = widget.querySelector('[data-complaint-back]');
    const launcher = widget.querySelector('summary');
    const showFrame = () => {
      frame.hidden = false;
      menu.hidden = true;
      back.hidden = false;
    };
    if (widget.dataset.complaintUnread) {
      const badge = widget.querySelector('[data-complaint-reply-count]');
      let latestUrl = null;
      let pending;
      const refreshUnread = () => {
        if (document.hidden) return Promise.resolve();
        if (!pending) {
          pending = requestJson(widget.dataset.complaintUnread).then(({ count, url }) => {
            latestUrl = url;
            badge.hidden = !count;
            badge.textContent = count > 99 ? '99+' : count;
            launcher.setAttribute('aria-label', count ? `Buka bantuan Integral, ${count} tiket dengan balasan baru` : 'Buka bantuan Integral');
          }).catch(() => {}).finally(() => { pending = null; });
        }
        return pending;
      };
      const openUnread = () => {
        if (!latestUrl) return;
        frame.src = latestUrl;
        showFrame();
      };
      launcher.addEventListener('click', () => {
        if (widget.open) return;
        if (latestUrl) {
          openUnread();
        } else {
          refreshUnread().then(() => {
            if (widget.open && frame.hidden) openUnread();
          });
        }
      });
      frame.addEventListener('load', () => {
        if (!frame.hidden) refreshUnread();
      });
      document.addEventListener('visibilitychange', refreshUnread);
      refreshUnread();
      setInterval(refreshUnread, 15000);
    }
    menu.querySelectorAll('[data-complaint-frame-link]').forEach(link => {
      link.addEventListener('click', event => {
        if (!event.ctrlKey && !event.metaKey && !event.shiftKey && !event.altKey) showFrame();
      });
    });
    back.addEventListener('click', () => {
      frame.hidden = true;
      menu.hidden = false;
      back.hidden = true;
      menu.querySelector('[data-complaint-frame-link]:not([hidden])').focus();
    });
    widget.querySelector('[data-complaint-close]').addEventListener('click', () => {
      widget.open = false;
      launcher.focus();
    });
  }

  const staffInbox = document.querySelector('[data-complaint-heartbeat]');
  if (staffInbox) {
    const badge = staffInbox.querySelector('[data-complaint-menu-count]');
    const bellEntry = document.querySelector('[data-complaint-bell-entry]');
    const bellWaiting = bellEntry.querySelector('[data-complaint-bell-count]');
    const bellBadge = document.querySelector('[data-notification-total]');
    const bellCount = document.querySelector('[data-notification-count]');
    const bellEmpty = document.querySelector('[data-notification-empty]');
    const inbox = document.querySelector('[data-complaint-staff-inbox]');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const badgeText = count => count > 99 ? '99+' : count;
    const heartbeat = async () => {
      if (document.hidden) return;
      try {
        const endpoint = new URL(staffInbox.dataset.complaintHeartbeat);
        if (inbox) endpoint.searchParams.set('inbox', '1');
        const { waiting, unread_chat_ids } = await requestJson(endpoint, {
          method: 'POST', headers: { 'X-CSRF-TOKEN': csrf },
        });
        if (inbox) {
          const unread = new Set(unread_chat_ids.map(String));
          inbox.querySelectorAll('[data-complaint-unread-ticket]').forEach(marker => {
            marker.hidden = !unread.has(marker.dataset.complaintUnreadTicket);
          });
        }
        badge.hidden = !waiting;
        badge.textContent = badgeText(waiting);
        bellEntry.hidden = !waiting;
        bellWaiting.textContent = waiting;
        const total = Number(bellBadge.dataset.notificationTotal) + (waiting ? 1 : 0);
        bellBadge.hidden = !total;
        bellBadge.textContent = badgeText(total);
        bellCount.textContent = total;
        if (bellEmpty) bellEmpty.hidden = total > 0;
      } catch {
        // Retain the last known counts until the next heartbeat.
      }
    };
    heartbeat();
    setInterval(heartbeat, 20000);
    document.addEventListener('visibilitychange', heartbeat);
    if (inbox) window.addEventListener('pageshow', event => {
      if (event.persisted) heartbeat();
    });
  }

  document.querySelectorAll('[data-complaint-availability]').forEach(container => {
    const action = container.querySelector('[data-complaint-compose]');
    const chatOption = container.querySelector('[data-complaint-chat-option]');
    const reportOption = container.querySelector('[data-complaint-report-option]');
    const updateActions = online => {
      if (chatOption) {
        chatOption.hidden = !online;
        reportOption.classList.toggle('btn-primary', !online);
        reportOption.classList.toggle('btn-outline-primary', online);
      }
      if (action) action.textContent = online ? 'Kirim Pesan' : 'Kirim Pengaduan';
    };
    let sequence = 0;
    const check = async () => {
      if (document.hidden || (container.matches('details') && !container.open)) return;
      const latest = ++sequence;
      const url = new URL(container.dataset.complaintAvailability);
      try {
        const { online } = await requestJson(url);
        if (latest !== sequence) return;
        showPresence(container, online);
        updateActions(online);
      } catch {
        if (latest !== sequence) return;
        updateActions(false);
        showPresence(container, false);
        const label = container.querySelector('[data-complaint-presence]');
        label.dataset.complaintPresence = '';
        label.textContent = 'Ketersediaan admin belum dapat diperiksa.';
      }
    };
    check();
    container.addEventListener('toggle', check);
    document.addEventListener('visibilitychange', () => check());
    setInterval(() => check(), 15000);
  });

  document.querySelectorAll('.complaint-live-form').forEach(form => {
    const textarea = form.elements.message;
    const attachment = form.elements.attachment;
    const toggle = form.querySelector('[data-complaint-emoji-toggle]');
    const picker = form.querySelector('[data-complaint-emoji-picker]');
    const showEmoji = open => {
      picker.hidden = !open;
      toggle.setAttribute('aria-expanded', String(open));
    };
    form.querySelector('[data-complaint-chat-attachment]').addEventListener('click', () => attachment.click());
    toggle.addEventListener('click', () => showEmoji(picker.hidden));
    picker.addEventListener('click', event => {
      const emoji = event.target.closest('[data-complaint-emoji]')?.dataset.complaintEmoji;
      if (!emoji) return;
      const { selectionStart, selectionEnd, value, maxLength } = textarea;
      if (value.length - (selectionEnd - selectionStart) + emoji.length <= maxLength) {
        textarea.setRangeText(emoji, selectionStart, selectionEnd, 'end');
      }
      showEmoji(false);
      textarea.focus();
    });
    form.addEventListener('keydown', event => {
      if (event.key !== 'Escape' || picker.hidden) return;
      showEmoji(false);
      toggle.focus();
    });
    form.addEventListener('submit', () => showEmoji(false));
    textarea.addEventListener('keydown', event => {
      if (event.key !== 'Enter' || event.shiftKey || event.isComposing) return;
      event.preventDefault();
      if (!textarea.form.querySelector('button[type="submit"]').disabled) textarea.form.requestSubmit();
    });
  });

  const chatStart = document.querySelector('[data-complaint-chat-start]');
  if (chatStart) {
    const messages = document.querySelector('[data-complaint-messages]');
    const { message, attachment } = chatStart.elements;
    const questions = JSON.parse(chatStart.dataset.complaintChatQuestions);
    const steps = Object.keys(questions);
    const submit = chatStart.querySelector('button[type="submit"]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let step = chatStart.dataset.complaintChatStart;
    let typing = false;
    const scrollToLatest = () => { messages.scrollTop = messages.scrollHeight; };
    const createBubble = (own) => {
      const bubble = document.createElement('li');
      bubble.className = own ? 'complaint-bubble is-own' : 'complaint-bubble';
      const avatar = document.createElement('span');
      avatar.className = 'complaint-message-avatar';
      avatar.setAttribute('aria-hidden', 'true');
      const icon = document.createElement('i');
      icon.className = own ? 'bx bx-user' : 'bx bx-support';
      avatar.append(icon);
      bubble.append(avatar);
      return bubble;
    };
    const addBubble = (text, own) => {
      const bubble = createBubble(own);
      bubble.classList.add('complaint-bubble-enter');
      const body = document.createElement('p');
      body.className = 'complaint-message-body mb-0';
      body.textContent = text;
      bubble.append(body);
      messages.append(bubble);
    };
    const showTyping = async (delay, reveal) => {
      if (!reducedMotion) {
        const indicator = createBubble(false);
        indicator.classList.add('complaint-typing');
        indicator.insertAdjacentHTML('beforeend', '<span class="visually-hidden">Bantuan Integral sedang mengetik</span><span class="complaint-typing-dots" aria-hidden="true"><i></i><i></i><i></i></span>');
        messages.append(indicator);
        scrollToLatest();
        await new Promise(resolve => setTimeout(resolve, delay));
        indicator.remove();
      }
      reveal();
      scrollToLatest();
    };
    const setTyping = active => {
      typing = active;
      message.readOnly = active;
      submit.disabled = active;
    };
    chatStart.addEventListener('submit', async event => {
      if (typing) {
        event.preventDefault();
        return;
      }
      const answer = message.value.trim();
      if (step === 'message' && (answer || attachment.files.length)) {
        submit.disabled = true;
        return;
      }
      event.preventDefault();
      if (!answer) return;
      chatStart.elements[step].value = answer;
      addBubble(answer, true);
      step = steps[steps.indexOf(step) + 1];
      message.value = '';
      setTyping(true);
      const question = questions[step].replace('{nama}', () => chatStart.elements.chat_name.value);
      await showTyping(600, () => addBubble(question, false));
      message.maxLength = step === 'message' ? 5000 : 150;
      message.placeholder = step === 'message' ? 'Ketik keluhan Kaka…' : 'Ketik nama instansi Kaka…';
      message.setAttribute('aria-label', message.placeholder);
      attachment.disabled = step !== 'message';
      chatStart.querySelector('[data-complaint-chat-tools]').hidden = attachment.disabled;
      setTyping(false);
      message.focus();
    });
    window.addEventListener('pageshow', () => {
      submit.disabled = typing;
    });
    const intro = messages.querySelector('[data-complaint-intro]');
    const firstQuestion = messages.querySelector('[data-complaint-first-question]');
    if (intro && firstQuestion) {
      const reveal = bubble => {
        bubble.classList.add('complaint-bubble-enter');
        bubble.hidden = false;
      };
      setTyping(true);
      showTyping(650, () => reveal(intro))
        .then(() => showTyping(550, () => reveal(firstQuestion)))
        .finally(() => setTyping(false));
    }
    scrollToLatest();
  }

  document.querySelectorAll('[data-complaint-image-paste]').forEach(textarea => {
    const form = textarea.form;
    const attachment = form.elements.attachment;
    const preview = form.querySelector('[data-complaint-image-preview]');
    const image = preview.querySelector('img');
    const error = form.querySelector('[data-complaint-image-error]');
    const acceptedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    let previewUrl;

    const isValid = file => {
      if (acceptedTypes.includes(file.type) && file.size <= 5 * 1024 * 1024) return true;
      error.textContent = 'Gunakan gambar JPG, PNG, atau WebP berukuran maksimal 5 MB.';
      return false;
    };

    attachment.addEventListener('change', () => {
      if (previewUrl) URL.revokeObjectURL(previewUrl);
      previewUrl = null;
      image.removeAttribute('src');
      preview.hidden = true;
      error.textContent = '';
      const file = attachment.files[0];
      if (!file) return;
      if (!isValid(file)) {
        attachment.value = '';
        return;
      }
      previewUrl = URL.createObjectURL(file);
      image.src = previewUrl;
      preview.hidden = false;
    });
    preview.querySelector('[data-complaint-image-remove]').addEventListener('click', () => {
      attachment.value = '';
      attachment.dispatchEvent(new Event('change'));
      textarea.focus();
    });
    textarea.addEventListener('paste', event => {
      const item = Array.from(event.clipboardData?.items ?? [])
        .find(entry => entry.kind === 'file' && entry.type.startsWith('image/'));
      const file = item?.getAsFile();
      if (!file) return;
      event.preventDefault();
      if (attachment.disabled) return;
      if (!isValid(file)) return;
      try {
        const transfer = new DataTransfer();
        transfer.items.add(file);
        attachment.files = transfer.files;
        attachment.dispatchEvent(new Event('change'));
      } catch {
        error.textContent = 'Gambar belum dapat ditempel. Gunakan tombol pilih file.';
      }
    });
  });

  const conversation = document.querySelector('[data-complaint-updates]');
  if (!conversation) return;
  const messages = conversation.querySelector('[data-complaint-messages]');
  const status = conversation.querySelector('[data-complaint-ticket-status]');
  const form = conversation.querySelector('[data-complaint-reply]');
  const offline = conversation.querySelector('[data-complaint-offline]');
  const ended = conversation.querySelector('[data-complaint-ticket-ended]');
  const newChat = conversation.querySelector('[data-complaint-chat-new]');
  const report = conversation.querySelector('[data-complaint-chat-report]');
  const feedback = conversation.querySelector('[data-complaint-reply-feedback]');
  let lastId = conversation.dataset.complaintLastMessage;
  let polling = false;
  let unavailable = false;
  messages.scrollTop = messages.scrollHeight;

  async function refresh() {
    if (document.hidden || polling || unavailable || (conversation.hasAttribute('data-complaint-staff-room') && !document.hasFocus())) return;
    polling = true;
    const url = new URL(conversation.dataset.complaintUpdates);
    url.searchParams.set('after', lastId);
    try {
      const data = await requestJson(url);
      if (typeof data.messages === 'string') {
        const nearEnd = messages.scrollHeight - messages.scrollTop - messages.clientHeight < 48;
        messages.insertAdjacentHTML('beforeend', data.messages);
        if (nearEnd) messages.scrollTop = messages.scrollHeight;
      }
      lastId = data.last_message_id;
      status.textContent = data.status;
      if (offline) {
        showPresence(conversation, data.online);
        offline.hidden = data.online || data.ticket_ended;
      }
      if (ended) {
        ended.hidden = !data.ticket_ended;
        if (newChat) newChat.hidden = !data.online;
        if (report) report.hidden = data.online;
      }
      if (form) form.hidden = data.ticket_ended || (!!offline && !data.online);
    } catch (error) {
      const label = conversation.querySelector('[data-complaint-presence]');
      if (error.status === 404) {
        unavailable = true;
        messages.replaceChildren();
        if (form) form.hidden = true;
        if (offline) offline.hidden = true;
        if (ended) ended.hidden = true;
        if (label) label.textContent = '';
        status.textContent = 'Tidak tersedia';
        conversation.querySelector('[data-complaint-unavailable]').hidden = false;
      } else if (label) {
        label.dataset.complaintPresence = '';
        label.textContent = 'Ketersediaan admin belum dapat diperiksa.';
      }
    } finally {
      polling = false;
    }
  }

  form?.addEventListener('submit', async event => {
    event.preventDefault();
    const submit = form.querySelector('button[type="submit"]');
    submit.disabled = true;
    feedback.textContent = 'Mengirim…';
    try {
      await requestJson(form.action, { method: 'POST', body: new FormData(form) });
      form.elements.message.value = '';
      form.elements.attachment.value = '';
      form.elements.attachment.dispatchEvent(new Event('change'));
      feedback.textContent = 'Terkirim.';
      await refresh();
    } catch (error) {
      feedback.textContent = error.message;
      await refresh();
    } finally {
      submit.disabled = false;
    }
  });
  refresh();
  setInterval(refresh, 8000);
  document.addEventListener('visibilitychange', refresh);
  if (conversation.hasAttribute('data-complaint-staff-room')) window.addEventListener('focus', refresh);
})();
