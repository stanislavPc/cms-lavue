<template>
    <div class="row justify-content-center my-5">
        <div class="col-lg-8 m-auto">
            <card>
                <form @submit.prevent="login" @keydown="form.onKeydown($event)">
                    <!-- Email -->
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label text-md-right">{{ $t('User.your_email') }}</label>
                        <div class="col-md-7">
                            <input v-model="form.email" :class="{ 'is-invalid': form.errors.has('email') }" type="email"
                                   name="email" class="form-control">
                            <has-error :form="form" field="email"/>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label text-md-right">{{ $t('User.password') }}</label>
                        <div class="col-md-7">
                            <input v-model="form.password" :class="{ 'is-invalid': form.errors.has('password') }" type="password"
                                   name="password" class="form-control">
                            <has-error :form="form" field="password"/>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="form-group row">
                        <div class="col-md-3"/>
                        <div class="col-md-7 d-flex">
                            <checkbox v-model="remember" name="remember">
                                {{ $t('User.remember_me') }}
                            </checkbox>

                            <router-link :to="{ name: 'password.request' }" class="small ml-auto my-auto text-success">
                                {{ $t('User.forgot_password') }}
                            </router-link>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-7 offset-md-3 d-flex">
                            <!-- Submit Button -->
                            <button :class="{'btn btn-success': true, 'btn-loading': form.busy}" type="submit"
                                    :disabled="form.busy">
                                {{ $t('User.login') }}
                            </button>
                        </div>
                    </div>
                </form>
            </card>
        </div>
    </div>
</template>

<script>
import Form from 'vform'

export default {
    middleware: 'guest',
    head() {
        return {title: this.$t('User.login')}
    },

    data: () => ({
        form: new Form({
            email: '',
            password: ''
        }),
        remember: false
    }),

    methods: {
        async login() {
            let data

            // Submit the form.
            try {
                const response = await this.form.post('/login')
                data = response.data
            } catch (e) {
                return
            }

            // Save the token.
            this.$store.dispatch('auth/saveToken', {
                token: data.access_token,
                remember: this.remember
            })

            // Fetch the user.
            await this.$store.dispatch('auth/fetchUser')

            // Redirect home.
            this.$router.push({name: 'dashboard.index'})
        }
    }
}
</script>
