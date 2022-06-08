import axios from 'axios'

export const api = {
    apiHost: 'http://localhost',
    token: null,
    timer: null,
    timerLastID: -1,
    timerGlobalHandler: null,
    loginCallback: null,
    logoutCallback: null,
    eventCallback: null,
    config: {
        timerSuccessTimeout: 500,
        timerErrorTimeout: 2500,
    },
    init(loginCallback, logoutCallback, eventCallback) {
        this.apiHost = 'http://' + window.location.hostname;
        this.loginCallback = loginCallback;
        this.logoutCallback = logoutCallback;
        this.eventCallback = eventCallback;
    },
    destroy() {
        this.stopEventTimer();
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
        
        this.stopEventTimer();
        
        this.post('login', {
            login: login, 
            password: password
        }, (data) => {
            if (data.token) {
                this.token = data.token;
                doLoginCallback(true);
                this.runEventTimer();
            } else {
                doLoginCallback(false);
            }
        }, (error) => {
            console.log(error);
            doLoginCallback(false);
        });
    },
    logout() {
        this.stopEventTimer();
        this.token = null;
        if (typeof(this.logoutCallback) === 'function') {
            this.logoutCallback();
        }
    },
    runEventTimer() {
        clearTimeout(this.timer);
        
        this.timer = setTimeout(() => {
            this.get('events/' + this.timerLastID, null, (data) => {
                this.handleEvents(data);
                this.runEventTimer(this.config.timerSuccessTimeout);
            }, (error) => {
                this.runEventTimer(this.config.timerErrorTimeout);
            });
        }, this.config.timerSuccessTimeout);
    },
    handleEvents(data) {
        if (data.lastID !== undefined) {
            this.timerLastID = data.lastID;
        } else {
            data.forEach((event) => {
                this.timerLastID = event.id;
                
                if (typeof(this.eventCallback) === 'function') {
                    this.eventCallback(event);
                }
            });
        }
    },
    stopEventTimer() {
        clearTimeout(this.timer);
    },
    setDeviceValue(deviceID, value) {
        this.post('set-device-value/' + deviceID, {value: value});
    }
}