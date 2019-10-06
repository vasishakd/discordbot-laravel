<template>
    <div class="container">
        <table class="table table-sm table-dark">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Channel</th>
                <th scope="col">Service</th>
                <th scope="col">Status</th>
                <th scope="col">Actions <a :href="createAction" style="float: right;">Create</a></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(item, index) in rows">
                <td scope="row">{{ index + 1 }}</td>
                <td>{{ item.key }}</td>
                <td>{{ item.service_name}}</td>
                <td>{{ item.status }}</td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button @click="deleteChannel(item.links.delete, index)" class="btn btn-sm btn-outline-dark"><i class="material-icons md-light">delete</i></button>
                        <a href="" role="button" class="btn btn-sm btn-outline-dark"><i class="material-icons md-light">edit</i></a>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        props: {
            items: Array,
            createAction: String,
        },
        data() {
            return {
                rows: this.items,
            }
        },
        methods: {
            deleteChannel(action, index) {
                const vm = this;
                axios.delete(action)
                    .then(function (response) {
                        vm.$delete(vm.rows, index);
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },
        },
        mounted() {
            const vm = this;
            window.Echo.channel('channel-list')
                .listen('ChannelCreated', (e) => {
                    vm.rows.push(e.channel);
                });
        }
    }
</script>