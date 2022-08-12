import axios from 'axios'
import storage from '@/storage.js'
import Echo from "laravel-echo"

export const api = {
    apiHost: 'http://localhost',
    token: null,
    loginCallback: null,
    logoutCallback: null,
    eventCallback: null,

    init(loginCallback, logoutCallback, eventCallback) {
        this.apiHost = 'http://' + window.location.hostname;
        this.loginCallback = loginCallback;
        this.logoutCallback = logoutCallback;
        this.eventCallback = eventCallback;
    },
    destroy() {
        
    },
    get(path, data, callbackSuccess, callbackError) {
        if (!data) data = {};
        data.api_token = this.token;
        
        let params = [];
        for (let key in data) {
            params.push(key + '=' + data[key]);
        }
        
        let paramsStr = params.join('&');
        if (paramsStr) {
            paramsStr = '?' + paramsStr;
        }
        
        axios
        .get(this.apiHost + '/api/' + path + paramsStr)
        .then(response => {
            if (typeof(callbackSuccess) === 'function') {
                callbackSuccess(response.data);
            }
        })
        .catch(error => {
            if (error.response.status == 401) {
                this.logout();
            } else {
                if (typeof(callbackError) === 'function') {
                    callbackError(error);
                }
            }
        });
    },
    post(path, data, callbackSuccess, callbackError) {
        if (!data) data = {};
        data.api_token = this.token;
        
        axios
        .post(this.apiHost + '/api/' + path, data)
        .then(response => {
            if (typeof(callbackSuccess) === 'function') {
                callbackSuccess(response.data);
            }
        })
        .catch(error => {
            console.log(error);
            
            if (error.response.status == 401) {
                this.logout();
            } else {
                if (typeof(callbackError) === 'function') {
                    callbackError(error);
                }
            }
        });
    },
    delete(path, data, callbackSuccess, callbackError) {
        if (!data) data = {};
        data.api_token = this.token;
        
        axios
        .delete(this.apiHost + '/api/' + path, {data: data})
        .then(response => {
            if (typeof(callbackSuccess) === 'function') {
                callbackSuccess(response.data);
            }
        })
        .catch(error => {
            if (error.response.status == 401) {
                this.logout();
            } else {
                if (typeof(callbackError) === 'function') {
                    callbackError(error);
                }
            }
        });
    },
    login(login, password, stateCallback) {
        const doStateCallback = (success) => {
            if (typeof(stateCallback) === 'function') {
                stateCallback(success);
            }
        }
        
        const doLoginCallback = (success) => {
            if (typeof(this.loginCallback) === 'function') {
                this.loginCallback(success);
            }
            
            doStateCallback(success);
        }
        
        this.post('login', {
            login: login, 
            password: password
        }, (data) => {
            if (data.token) {
                this.token = data.token;
                
                this.get('start-after-login', null, (data) => {
                    storage.app_controls = data.app_controls;
                    storage.columns = data.columns;
                    doLoginCallback(true);
                    this.runEcho();
                }, (error) => {
                    this.token = null;
                });
            } else {
                doLoginCallback(false);
            }
        }, (error) => {
            console.log(error);
            doLoginCallback(false);
        });
    },
    logout() {
        this.token = null;
        if (typeof(this.logoutCallback) === 'function') {
            this.logoutCallback();
        }
    },
    runEcho: function () {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: 'web-terminal',
            wsHost: window.location.hostname,
            wsPort: 6001,
            forceTLS: false,
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
            authEndpoint: 'http://' + window.location.hostname + '/broadcasting/auth',
            auth: {
                headers: {
                    token: api.token,
                }
            }
        });

        window.Echo.private('logout')
            .listen('LogoutEvent', (e) => {
                if (e.token == api.token) {
                    window.location.reload();
                }
            });

        window.Echo.private('device-changes')
            .listen('DeviceChangeEvent', (e) => {
                if (typeof(this.eventCallback) === 'function') {
                    this.eventCallback(e.data);
                }
            });
    },
    
    setDeviceValue(deviceID, value) {
        this.post('set-device-value/' + deviceID, {value: value});
    }
}