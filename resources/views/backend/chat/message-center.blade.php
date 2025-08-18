<div id="chat-message-center">
    <a class="nav-link dropdown-toggle" href="#" id="chatMessagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-comments fa-fw"></i>
        <span id="chat-mc-count" class="badge badge-danger badge-counter" data-count="0">0</span>
    </a>
    <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="chatMessagesDropdown">
        <h6 class="dropdown-header">Message Center</h6>
        <div id="chat-mc-items"></div>
        <a class="dropdown-item text-center small text-gray-500" href="{{ url('/chat') }}">Đi tới Chat</a>
    </div>
</div>

@push('scripts')
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>
<script>
(function(){
  // Read Firebase config from meta tags (config/firebase.php -> blade meta)
  function meta(name){ var el = document.querySelector('meta[name="'+name+'"]'); return el ? el.getAttribute('content') : ''; }
  var firebaseConfig = {
    apiKey: meta('firebase-api-key'),
    authDomain: meta('firebase-auth-domain'),
    databaseURL: meta('firebase-database-url'),
    projectId: meta('firebase-project-id'),
    storageBucket: meta('firebase-storage-bucket'),
    messagingSenderId: meta('firebase-messaging-sender-id'),
    appId: meta('firebase-app-id')
  };
  if (!firebase.apps.length) {
    try { firebase.initializeApp(firebaseConfig); } catch (e) { console.error('Firebase init error', e); }
  }

  var currentUser = {
    id: {{ (int) auth()->id() }},
    role: @json(optional(auth()->user())->role ?? 'admin')
  };
  window.APP_USER_ID = currentUser.id;
  window.APP_USER_ROLE = currentUser.role;

  var db = firebase.database();
  var itemsEl = document.getElementById('chat-mc-items');
  var countEl = document.getElementById('chat-mc-count');
  var maxItems = 5;

  function timeAgo(ts){
    try { var d = new Date(ts); return d.toLocaleTimeString(); } catch(e){ return 'vừa xong'; }
  }

  function setCount(n){
    if(!countEl) return;
    countEl.setAttribute('data-count', n);
    countEl.textContent = n > maxItems ? (maxItems + '+') : n;
  }

  function playSound(){
    var a = document.getElementById('mc-audio');
    if(a) { a.play().catch(function(){}); }
  }

  function prependItem(item){
    if(!itemsEl) return;
    // Build DOM for one message item
    var a = document.createElement('a');
    a.className = 'dropdown-item d-flex align-items-center message-item';
    a.href = '/chat?conversationId=' + encodeURIComponent(item.conversationId);

    var avatarWrap = document.createElement('div');
    avatarWrap.className = 'dropdown-list-image mr-3';
    var img = document.createElement('img');
    img.className = 'rounded-circle';
    img.src = item.avatar || '{{ asset('backend/img/avatar.png') }}';
    img.alt = item.title || 'user';
    avatarWrap.appendChild(img);

    var textWrap = document.createElement('div');
    textWrap.className = 'font-weight-bold';
    var title = document.createElement('div');
    title.className = 'text-truncate';
    title.textContent = (item.title || 'Tin nhắn mới') + (item.preview ? (': ' + item.preview) : '');
    var meta = document.createElement('div');
    meta.className = 'small text-gray-500';
    meta.textContent = (item.senderName || '') + ' · ' + timeAgo(item.timestamp || Date.now());
    textWrap.appendChild(title);
    textWrap.appendChild(meta);

    a.appendChild(avatarWrap);
    a.appendChild(textWrap);

    itemsEl.prepend(a);

    // Trim list to maxItems
    var list = itemsEl.querySelectorAll('.message-item');
    if(list.length > maxItems){ itemsEl.removeChild(itemsEl.lastElementChild); }

    // Update count
    var current = parseInt(countEl.getAttribute('data-count') || '0', 10) + 1;
    setCount(current);
  }

  function bindToConversation(conversationId){
    try {
      db.ref('messages/' + conversationId)
        .limitToLast(1)
        .on('child_added', function(snap){
          var m = snap.val() || {};
          if(parseInt(m.senderId) === currentUser.id) return; // ignore self
          prependItem({
            conversationId: conversationId,
            title: m.senderName || 'Tin nhắn mới',
            preview: m.type === 'image' ? '[Hình ảnh]' : (m.content || ''),
            timestamp: m.timestamp || Date.now(),
            senderName: m.senderName || ''
          });
          playSound();
        });
    } catch(e){ console.error('bindToConversation error', e); }
  }

  function subscribe(){
    var ids = (window.ASSIGNED_CONVERSATIONS || []);
    if(ids.length){
      ids.forEach(bindToConversation);
    } else {
      // Fallback: subscribe via userConversations/{userId}
      db.ref('userConversations/' + currentUser.id)
        .limitToFirst(50)
        .once('value')
        .then(function(snap){
          var map = snap.val() || {};
          Object.keys(map).forEach(bindToConversation);
        })
        .catch(function(e){ console.warn('userConversations fetch failed', e); });
    }
  }

  document.addEventListener('DOMContentLoaded', function(){
    setCount(0);
    subscribe();
  });
})();
</script>
<audio id="mc-audio" src="/sounds/notify.mp3" preload="auto"></audio>
@endpush