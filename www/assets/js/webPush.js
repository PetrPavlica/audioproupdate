document.addEventListener("DOMContentLoaded", () => {
    const applicationServerKey = "BOmHSPJVbmlw3uWx5oAbKOaUlfnYRyiXD6zuFIX--g8qP5Kjo2qgvdwcOgG4bhdEeRB7Os0nRdym1H656CFTHE4";
    let isPushEnabled = false;

    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires="+d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    function getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    /**
     * Lightweight script to detect whether the browser is running in Private mode.
     * @returns {Promise<boolean>}
     *
     * Live demo:
     * @see https://output.jsbin.com/tazuwif
     *
     * This snippet uses Promises. If you want to run it in old browsers, polyfill it:
     * @see https://cdn.jsdelivr.net/npm/es6-promise@4/dist/es6-promise.auto.min.js
     *
     * More Promise Polyfills:
     * @see https://ourcodeworld.com/articles/read/316/top-5-best-javascript-promises-polyfills
     */
     function isPrivateMode() {
        return new Promise(function detect(resolve) {
            var yes = function() { resolve(true); }; // is in private mode
            var not = function() { resolve(false); }; // not in private mode

            function detectChromeOpera() {
                // https://developers.google.com/web/updates/2017/08/estimating-available-storage-space
                var isChromeOpera = /(?=.*(opera|chrome)).*/i.test(navigator.userAgent) && navigator.storage && navigator.storage.estimate;
                if (isChromeOpera) {
                    navigator.storage.estimate().then(function(data) {
                        return data.quota < 120000000 ? yes() : not();
                    });
                }
                return !!isChromeOpera;
            }

            function detectFirefox() {
                var isMozillaFirefox = 'MozAppearance' in document.documentElement.style;
                if (isMozillaFirefox) {
                    if (indexedDB == null) yes();
                    else {
                        var db = indexedDB.open('inPrivate');
                        db.onsuccess = not;
                        db.onerror = yes;
                    }
                }
                return isMozillaFirefox;
            }

            function detectSafari() {
                var isSafari = navigator.userAgent.match(/Version\/([0-9\._]+).*Safari/);
                if (isSafari) {
                    var testLocalStorage = function() {
                        try {
                            if (localStorage.length) not();
                            else {
                                localStorage.setItem('inPrivate', '0');
                                localStorage.removeItem('inPrivate');
                                not();
                            }
                        } catch (_) {
                            // Safari only enables cookie in private mode
                            // if cookie is disabled, then all client side storage is disabled
                            // if all client side storage is disabled, then there is no point
                            // in using private mode
                            navigator.cookieEnabled ? yes() : not();
                        }
                        return true;
                    };

                    var version = parseInt(isSafari[1], 10);
                    if (version < 11) return testLocalStorage();
                    try {
                        window.openDatabase(null, null, null, null);
                        not();
                    } catch (_) {
                        yes();
                    }
                }
                return !!isSafari;
            }

            function detectEdgeIE10() {
                var isEdgeIE10 = !window.indexedDB && (window.PointerEvent || window.MSPointerEvent);
                if (isEdgeIE10) yes();
                return !!isEdgeIE10;
            }

            // when a browser is detected, it runs tests for that browser
            // and skips pointless testing for other browsers.
            if (detectChromeOpera()) return;
            if (detectFirefox()) return;
            if (detectSafari()) return;
            if (detectEdgeIE10()) return;

            // default navigation mode
            return not();
        });
    }

    /*const pushButton = document.querySelector('#notification-bell');
    if (!pushButton) {
        return;
    }

    pushButton.addEventListener('click', function() {
        if (isPushEnabled) {
            push_unsubscribe();
        } else {
            push_subscribe();
        }
    });*/

    isPrivateMode().then( value => {
        if (!value) {


            if (!('serviceWorker' in navigator)) {
                console.warn("Service workers are not supported by this browser");
                changePushButtonState('incompatible');
                return;
            }

            if (!('PushManager' in window)) {
                console.warn('Push notifications are not supported by this browser');
                changePushButtonState('incompatible');
                return;
            }

            if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
                console.warn('Notifications are not supported by this browser');
                changePushButtonState('incompatible');
                return;
            }

            // Check the current Notification permission.
            // If its denied, the button should appears as such, until the user changes the permission manually
            if (Notification.permission === 'denied') {
                console.warn('Notifications are denied by the user');
                changePushButtonState('incompatible');
                return;
            }

            navigator.serviceWorker.register(PATHS.basePath + "/serviceWorker.js")
                .then(() => {
                    //console.log('[SW] Service worker has been registered');
                    //changePushButtonState('disabled');
                    push_updateSubscription();
                }, e => {
                    //console.error('[SW] Service worker registration failed', e);
                    changePushButtonState('incompatible');
                });

        }
    });

    function changePushButtonState (state) {
        isPushEnabled = true;
        switch (state) {
            case 'disabled':
                isPushEnabled = false;
                break;
            case 'enabled':
            case 'computing':
            case 'incompatible':
                break;
            default:

                console.error('Unhandled push button state', state);
                break;
        }

        if (!isPushEnabled) {
            const blockWebPushPrompt = getCookie('webPushPromptBlock');
            if (blockWebPushPrompt) {
                return;
            }
            $.confirm({
                title: '',
                draggable: false,
                columnClass: '',
                content: '<div class="promt-content">' +
                    '<div class="prompt-img"><img src="' + PATHS.basePath + '/front/design/icons/logo/logo_big.png" alt=""></div>' +
                    '<div class="prompt-text">' +
                    'Rádi bychom Vám ukázali upozornění na nejnovější novinky a zprávy.' +
                    '</div>' +
                '</div>',
                buttons: {
                    cancel: {
                        text: 'Ne děkuji',
                        action: function() {
                            setCookie('webPushPromptBlock', 1, 30);
                        }
                    },
                    confirm: {
                        text: 'Povolit',
                        btnClass: 'btn-blue',
                        keys: ['enter'],
                        action: function() {
                            push_subscribe();
                        }
                    }
                }
            });
        }
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    function push_subscribe() {
        changePushButtonState('computing');
        navigator.serviceWorker.ready
        .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(applicationServerKey),
        }))
        .then(subscription => {
             // Subscription was successful
            // create subscription on your server
            return push_sendSubscriptionToServer(subscription, 'POST');
        })
        .then(subscription => subscription && changePushButtonState('enabled')) // update your UI
        .catch(e => {
            if (Notification.permission === 'denied') {
                // The user denied the notification permission which
                // means we failed to subscribe and the user will need
                // to manually change the notification permission to
                // subscribe to push messages
                console.warn('Notifications are denied by the user.');
                changePushButtonState('incompatible');
            } else {
                // A problem occurred with the subscription; common reasons
                // include network errors or the user skipped the permission
                console.error('Impossible to subscribe to push notifications', e);
                changePushButtonState('disabled');
            }
        });
    }

    function push_updateSubscription() {
        navigator.serviceWorker.ready.then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
        .then(subscription => {
            if (!subscription) {
                changePushButtonState('disabled');
                return;
            }

            // Keep your server in sync with the latest endpoint
            return push_sendSubscriptionToServer(subscription, 'PUT');
        })
        .then(subscription => subscription && changePushButtonState('enabled')) // Set your UI to show they have subscribed for push messages
        .catch(e => {
            console.error('Error when updating the subscription', e);
        });
    }

    function push_unsubscribe() {
        changePushButtonState('computing');

        // To unsubscribe from push messaging, you need to get the subscription object
        navigator.serviceWorker.ready
        .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
        .then(subscription => {
            // Check that we have a subscription to unsubscribe
            if (!subscription) {
                // No subscription object, so set the state
                // to allow the user to subscribe to push
                changePushButtonState('disabled');
                return;
            }

            // We have a subscription, unsubscribe
            // Remove push subscription from server
            return push_sendSubscriptionToServer(subscription, 'DELETE');
        })
        .then(subscription => subscription.unsubscribe())
        .then(() => changePushButtonState('disabled'))
        .catch(e => {
            // We failed to unsubscribe, this can lead to
            // an unusual state, so  it may be best to remove
            // the users data from your data store and
            // inform the user that you have done so
            console.error('Error when unsubscribing the user', e);
            changePushButtonState('disabled');
        });
    }

    function push_sendSubscriptionToServer(subscription, method) {
        const key = subscription.getKey('p256dh');
        const token = subscription.getKey('auth');

        return fetch(PATHS.basePath + '/web-push/subscription', {
            method,
            body: JSON.stringify({
                endpoint: subscription.endpoint,
                key: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
                token: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null
            }),
        }).then(() => subscription);
    }
});
