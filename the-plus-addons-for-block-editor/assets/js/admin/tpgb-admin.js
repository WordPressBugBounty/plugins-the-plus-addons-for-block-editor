document.addEventListener('DOMContentLoaded', function () {
    var noticeDis = document.querySelector('.nxt-plugin-notice-dismiss')
    if(noticeDis){
        noticeDis.addEventListener('click', function (e) {
           let pluginNotice = document.querySelector('.nxt-plugin-rebranding-update');
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'nxt_dismiss_plugin_rebranding',
                    nonce: tpgb_admin.tpgb_nonce,
                })
            })
            .then(response => response.json())
            .then(data => {
                if (pluginNotice.style.opacity !== '1') {
                    if (pluginNotice.style.opacity === '0') {
                        pluginNotice.remove();
                    } else {
                        setTimeout(function () {
                            pluginNotice.remove();
                        }, 100);
                    }
                } else {
                    pluginNotice.style.transition = 'opacity 100ms';
                    pluginNotice.style.opacity = '0';
                    setTimeout(function () {
                        pluginNotice.style.display = 'none';
                        pluginNotice.remove();
                    }, 200);
                }
            })
            .catch(error => {
                alert(error.message || 'An unexpected error occurred.');
            });
        });
    }
});