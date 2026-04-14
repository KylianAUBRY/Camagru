(function () {
    'use strict';

    // === Like buttons ===
    document.querySelectorAll('.like-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var imageId = this.dataset.id;
            var csrf    = this.dataset.csrf;
            var self    = this;

            var formData = new FormData();
            formData.append('csrf_token', csrf);
            formData.append('image_id', imageId);

            fetch('/gallery/like', {
                method: 'POST',
                body: formData,
            })
            .then(function (r) {
                if (r.status === 401) {
                    window.location.href = '/login';
                    return null;
                }
                return r.json();
            })
            .then(function (data) {
                if (!data) return;
                if (data.error) return;
                self.classList.toggle('liked', data.liked);
                var count = self.querySelector('.like-count');
                if (count) count.textContent = data.count;
            })
            .catch(function () {});
        });
    });

    // === Comment forms ===
    document.querySelectorAll('.comment-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var imageId = this.dataset.id;
            var csrf    = this.dataset.csrf;
            var input   = this.querySelector('input[name="content"]');
            var content = input.value.trim();
            if (!content) return;

            var formData = new FormData();
            formData.append('csrf_token', csrf);
            formData.append('image_id', imageId);
            formData.append('content', content);

            fetch('/gallery/comment', {
                method: 'POST',
                body: formData,
            })
            .then(function (r) {
                if (r.status === 401) {
                    window.location.href = '/login';
                    return null;
                }
                return r.json();
            })
            .then(function (data) {
                if (!data || data.error) return;

                var card = document.querySelector('.gallery-card[data-id="' + imageId + '"]');
                var list = card ? card.querySelector('.comments-list') : null;
                if (!list) return;

                var li = document.createElement('li');
                var strong = document.createElement('strong');
                strong.textContent = data.username;

                var text = document.createTextNode(' ' + data.content);

                var time = document.createElement('time');
                time.textContent = data.created_at;

                li.appendChild(strong);
                li.appendChild(text);
                li.appendChild(time);
                list.appendChild(li);
                list.scrollTop = list.scrollHeight;

                var h3 = card.querySelector('.comments-section h3');
                if (h3) {
                    var match = h3.textContent.match(/\d+/);
                    var n = match ? parseInt(match[0], 10) + 1 : 1;
                    h3.textContent = 'Comments (' + n + ')';
                }

                input.value = '';
            })
            .catch(function () {});
        });
    });

}());
