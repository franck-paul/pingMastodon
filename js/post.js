/*global dotclear */
'use strict';

dotclear.ready(() => {
  // DOM ready and content loaded

  document.getElementById('edit-entry')?.addEventListener('onetabload', () => {
    const title = document.querySelector('h5.ping-mastodon');
    if (title) {
      const siblings = document.querySelectorAll('p.ping-mastodon');
      if (siblings)
        dotclear.toggleWithLegend(title, siblings, {
          user_pref: 'dcx_ping_mastodon',
          legend_click: true,
        });
    }
  });
});
