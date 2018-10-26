<template>
    <form class="form-horizontal form-label-left" id="store">
        <!-- TAGS -->
        <h4>TAGS</h4>
        <div class="form-group">
            <label class="control-label col-md-2 col-sm-2 col-xs-12">Title: </label>
            <div class="col-md-8 col-sm-8 col-xs-12">
                <input type="text" class="form-control" v-model="title">
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-md-2 col-sm-2 col-xs-12">Meta Description: </label>
            <div class="col-md-8 col-sm-8 col-xs-12">
                <textarea class="form-control" v-model="description"></textarea>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-md-2 col-sm-2 col-xs-12">Meta Robots: </label>
            <div class="col-md-8 col-sm-8 col-xs-12">

                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default yes" :class="{active: $store.state.data.tags.robots == true}" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default" @click="changeBoolean('robots',true)">
                        <input type="radio" value="enabled"> &nbsp; Enabled &nbsp;
                    </label>
                    <label class="btn btn-primary no" :class="{active: $store.state.data.tags.robots == false}" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default" @click="changeBoolean('robots',false)">
                        <input type="radio" value="disabled"> Disabled
                    </label>
                </div>

            </div>
        </div>

        <div class="alert alert-success">
            <h5>Variables to use within fields</h5>
            <p>{{ titlePlaceholder }} - Will be replaced with the tag title</p>
            <p>{{ sitenamePlaceholder }} - The site's name</p>
            <p>{{ pagePlaceholder }} - Will be replaced with the current page number (i.e. page 2 of 4)</p>
        </div>

    </form>
</template>

<script>
    export default {
        data(){
            return{
                titlePlaceholder: '{{title}}',
                sitenamePlaceholder: '{{sitename}}',
                pagePlaceholder: '{{page}}',

            }
        },
        methods:{
            changeBoolean(key, value){
                this.$store.commit('setData', {group: 'tags', state: key, value: value});
            }
        },
        computed: {
            title: {
                get() {
                    return this.$store.state.data.tags.title;
                },
                set(value) {
                    this.$store.commit('setData', {state: 'title', value: value});
                }
            },
            description: {
                get() {
                    return this.$store.state.data.tags.description;
                },
                set(value) {
                    this.$store.commit('setData', {group: 'tags', state: 'description', value: value});
                }
            },
        }
    }
</script>

<style scoped>

</style>