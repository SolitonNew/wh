<script setup>
    import Spinner from '@/components/Spinner.vue';
    import { lang } from '@/lang.js';
</script>

<template>
    <div class="container center">
        <form id="login" class="modal-box box-sm border" v-on:submit="onSubmit">
            <div class="modal-header">
                <h3>{{ lang('Login - Web Terminal') }}</h3>
            </div>
            <div class="modal-body">
                <div class="row" v-if="error">
                    <div class="error">Bad credentials</div>
                </div>
                <div class="row">
                    <label>{{ lang('Login') }}:</label>
                    <div class="login-input">
                        <input ref="login" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <label>{{ lang('Password') }}:</label>
                    <div class="login-input">
                        <input ref="password" class="form-control" type="password">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">{{ lang('Login Button') }}</button>
            </div>
        </form>
    </div>
    <Spinner v-if="loading" />
</template>

<script>
    import {api} from '@/api.js'

    export default {
        data() {
            return {
                error: false,
                loading: false,
            }
        },
        emits: ['login-success'],
        methods: {
            onSubmit: function (e) {
                e.preventDefault();
                this.error = false;
                this.loading = true;
                api.login(this.$refs.login.value, this.$refs.password.value, (success) => {
                    this.loading = false;
                    if (!success) {
                        this.error = true;
                    }
                });
            }
        }
    }
</script>

<style scoped>
    .container {
        padding-top: 0px;
    }

    #login .row label {
        width: 90px;
    }

    #login .login-input {
        flex-grow: 1;
    }

    #login .error {
        display: block;
        padding: 0.5rem 0.75rem;
        background-color: rgba(255, 0, 0, 0.15);
        border: 1px solid #ff0000;
        width: 100%;
    }
</style>

